<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>

<?php
function minutes_to_time(int $minutes): string
{
    if ($minutes < 0) {
        return '00:00:00';
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    if ($hours > 23) {
        $hours = 23;
        $mins = 59;
    }
    return sprintf('%02d:%02d:00', $hours, $mins);
}

function time_to_minutes(?string $time): int
{
    if (empty($time)) {
        return 0;
    }
    list($hours, $minutes) = explode(':', $time);
    return ((int)$hours * 60) + (int)$minutes;
}
?>

<!-- Debug: Output session data for inspection -->
<?php if (defined('ENVIRONMENT') && ENVIRONMENT === 'development'): ?>
    <pre style="display: none;">
        <?php foreach ($sessions as $session): ?>
            Session ID: <?= esc($session['class_session_id']) ?>
            Raw Session Data: <?= print_r($session, true) ?>
            JSON Encoded: <?= json_encode($session) ?>
            Data-Session Attribute: <?= htmlspecialchars(json_encode($session), ENT_QUOTES, 'UTF-8') ?>
        <?php endforeach; ?>
    </pre>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4"><?php echo esc($class['class_name']); ?> (<?php echo esc($class['section']); ?>)</h1>
    </div>
    <div class="col-12">
        <ul class="nav nav-tabs mb-4" id="classDetailTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#roster">Roster</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#sessions">Sessions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#attendance">Attendance</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="roster">
                <div class="card">
                    <div class="card-body">
                        <?php if(empty($roster)): ?>
                            <p>No students enrolled.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roster as $student): ?>
                                        <tr>
                                            <td><?php echo esc($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="sessions" role="tabpanel" aria-labelledby="sessions-tab">
                <h3>Sessions</h3>
                <div class="mb-4">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSessionModal">New Custom Class Session</button>
                </div>
                <?php if (session()->has('success')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>
                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Name</th>
                            <th>Attendance Method</th>
                            <th>Auto Mark</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><?= date('Y-m-d', strtotime($session['open_datetime'])) ?></td>
                                <td><?= date('H:i', strtotime($session['open_datetime'])) ?> - <?= date('H:i', strtotime($session['close_datetime'])) ?></td>
                                <td><?= esc($session['class_session_name']) ?></td>
                                <td><?= ucfirst($session['attendance_method']) ?></td>
                                <td><?= $session['auto_mark_attendance'] === 'yes' ? 'Yes' : 'No' ?></td>
                                <td><?= ucfirst($session['status']) ?></td>
                                <td>
                                    <?php if ($session['status'] === 'active'): ?>
                                        <form action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="mark_finished">
                                            <input type="hidden" name="session_id" value="<?= $session['class_session_id'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Mark as Finished</button>
                                        </form>
                                        <form action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="cancel_session">
                                            <input type="hidden" name="session_id" value="<?= $session['class_session_id'] ?>">
                                            <button type="submit" class="btn btn-warning btn-sm">Cancel</button>
                                        </form>
                                        <form action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_session">
                                            <input type="hidden" name="session_id" value="<?= $session['class_session_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    <?php elseif ($session['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updatePendingSessionModal" 
                                            data-session='<?= htmlspecialchars(json_encode($session), ENT_QUOTES, 'UTF-8') ?>'>Update</button>
                                        <form action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="cancel_session">
                                            <input type="hidden" name="session_id" value="<?= $session['class_session_id'] ?>">
                                            <button type="submit" class="btn btn-warning btn-sm">Cancel</button>
                                        </form>
                                        <form action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_session">
                                            <input type="hidden" name="session_id" value="<?= $session['class_session_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    <?php elseif (in_array($session['status'], ['marked', 'finished'])): ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateMarkedSessionModal" 
                                            data-session='<?= htmlspecialchars(json_encode($session), ENT_QUOTES, 'UTF-8') ?>'>Update</button>
                                        <form action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_session">
                                            <input type="hidden" name="session_id" value="<?= $session['class_session_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    <?php elseif ($session['status'] === 'cancelled'): ?>
                                        <form action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>" method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_session">
                                            <input type="hidden" name="session_id" value="<?= $session['class_session_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="attendance">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="session_id" class="form-label">Select Session</label>
                            <select name="session_id" id="session_id" class="form-control" onchange="window.location.href='<?php echo site_url('teacher/classes/' . $class['class_id']); ?>?session_id='+this.value">
                                <option value="">Select a session</option>
                                <?php foreach ($sessions as $session): ?>
                                    <option value="<?php echo $session['class_session_id']; ?>" <?php echo ($selected_session_id ?? '') == $session['class_session_id'] ? 'selected' : ''; ?>>
                                        <?= esc($session['class_session_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if (!empty($selected_session_id)): ?>

                            <form method="POST">
                                <input type="hidden" name="session_id" value="<?php echo $selected_session_id; ?>">
                                <input type="hidden" name="action" value="update_attendance">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Unmarked</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($roster as $student): ?>
                                                <tr>
                                                    <td><?php echo esc($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                    <td><input class="form-radio-styled" type="radio" name="attendance[<?php echo $student['user_id']; ?>]" value="present" <?php echo ($attendance[$student['user_id']] ?? '') === 'present' ? 'checked' : ''; ?> required></td>
                                                    <td><input class="form-radio-styled" type="radio" name="attendance[<?php echo $student['user_id']; ?>]" value="absent" <?php echo ($attendance[$student['user_id']] ?? '') === 'absent' ? 'checked' : ''; ?> required></td>
                                                    <td><input class="form-radio-styled" type="radio" name="attendance[<?php echo $student['user_id']; ?>]" value="late" <?php echo ($attendance[$student['user_id']] ?? '') === 'late' ? 'checked' : ''; ?> required></td>
                                                    <td><input class="form-radio-styled" type="radio" name="attendance[<?php echo $student['user_id']; ?>]" value="unmarked" <?php echo ($attendance[$student['user_id']] ?? '') === 'unmarked' ? 'checked' : ''; ?> required></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Attendance</button>
                            </form>
                        <?php endif; ?>
                        <div class="row justify-content-center">
                            <div class="col-12 col-md-7 col-lg-5">
                                <canvas id="attendanceChart" class="mt-4"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Session Modal -->
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSessionModalLabel">New Custom Class Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="createSessionForm" action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_session">
                    <input type="hidden" name="status" value="pending">
                    <div class="mb-3">
                        <label for="create_class_session_name" class="form-label">Session Name</label>
                        <input type="text" name="class_session_name" id="create_class_session_name" class="form-control" maxlength="255" required>
                        <div class="invalid-feedback">Please enter a session name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="create_session_description" class="form-label">Description</label>
                        <textarea name="session_description" id="create_session_description" class="form-control" maxlength="1000"></textarea>
                        <div class="invalid-feedback">Description cannot exceed 1000 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="create_open_datetime" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" name="open_datetime" id="create_open_datetime" class="form-control" min="<?= date('Y-m-d\TH:i', strtotime('+1 minute')); ?>" required>
                        <div class="invalid-feedback">Please select a valid start date and time.</div>
                    </div>
                    <div class="mb-3">
                        <label for="create_duration" class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" id="create_duration" class="form-control" min="1" required>
                        <div class="invalid-feedback">Please enter a duration of at least 1 minute.</div>
                    </div>
                    <div class="mb-3">
                        <label for="create_attendance_method" class="form-label">Attendance Method</label>
                        <select name="attendance_method" id="create_attendance_method" class="form-control" required>
                            <option value="manual">Manual</option>
                            <option value="automatic">Automatic</option>
                        </select>
                        <div class="invalid-feedback">Please select an attendance method.</div>
                    </div>
                    <div class="mb-3">
                        <label for="create_auto_mark_attendance" class="form-label">Auto Mark Attendance</label>
                        <select name="auto_mark_attendance" id="create_auto_mark_attendance" class="form-control" required>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                        <div class="invalid-feedback">Please select whether to auto mark attendance.</div>
                    </div>
                    <div id="create_threshold_fields" class="threshold-fields d-none">
                        <div class="mb-3">
                            <label for="create_time_in_threshold" class="form-label">Time In Threshold (minutes)</label>
                            <input type="number" name="time_in_threshold" id="create_time_in_threshold" class="form-control" min="0" step="1">
                            <div class="invalid-feedback">Please enter a valid time in threshold.</div>
                        </div>
                        <div class="mb-3">
                            <label for="create_time_out_threshold" class="form-label">Time Out Threshold (minutes)</label>
                            <input type="number" name="time_out_threshold" id="create_time_out_threshold" class="form-control" min="0" step="1">
                            <div class="invalid-feedback">Please enter a valid time out threshold.</div>
                        </div>
                        <div class="mb-3">
                            <label for="create_late_threshold" class="form-label">Late Threshold (minutes)</label>
                            <input type="number" name="late_threshold" id="create_late_threshold" class="form-control" min="0" step="1">
                            <div class="invalid-feedback">Please enter a valid late threshold.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Session Modal (Pending) -->
<div class="modal fade" id="updatePendingSessionModal" tabindex="-1" aria-labelledby="updatePendingSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePendingSessionModalLabel">Update Class Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="updatePendingSessionForm" action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_session">
                    <input type="hidden" name="class_session_id" id="update_pending_class_session_id">
                    <input type="hidden" name="status" value="pending">
                    <div class="mb-3">
                        <label for="update_pending_class_session_name" class="form-label">Session Name</label>
                        <input type="text" name="class_session_name" id="update_pending_class_session_name" class="form-control" maxlength="255" required>
                        <div class="invalid-feedback">Please enter a session name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="update_pending_session_description" class="form-label">Description</label>
                        <textarea name="session_description" id="update_pending_session_description" class="form-control" maxlength="1000"></textarea>
                        <div class="invalid-feedback">Description cannot exceed 1000 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="update_pending_open_datetime" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" name="open_datetime" id="update_pending_open_datetime" class="form-control" min="<?= date('Y-m-d\TH:i', strtotime('+1 minute')); ?>" required>
                        <div class="invalid-feedback">Please select a valid start date and time.</div>
                    </div>
                    <div class="mb-3">
                        <label for="update_pending_duration" class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" id="update_pending_duration" class="form-control" min="1" required>
                        <div class="invalid-feedback">Please enter a duration of at least 1 minute.</div>
                    </div>
                    <div class="mb-3">
                        <label for="update_pending_attendance_method" class="form-label">Attendance Method</label>
                        <select name="attendance_method" id="update_pending_attendance_method" class="form-control" required>
                            <option value="manual">Manual</option>
                            <option value="automatic">Automatic</option>
                        </select>
                        <div class="invalid-feedback">Please select an attendance method.</div>
                    </div>
                    <div class="mb-3">
                        <label for="update_pending_auto_mark_attendance" class="form-label">Auto Mark Attendance</label>
                        <select name="auto_mark_attendance" id="update_pending_auto_mark_attendance" class="form-control" required>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                        <div class="invalid-feedback">Please select whether to auto mark attendance.</div>
                    </div>
                    <div id="update_pending_threshold_fields" class="threshold-fields d-none">
                        <div class="mb-3">
                            <label for="update_pending_time_in_threshold" class="form-label">Time In Threshold (minutes)</label>
                            <input type="number" name="time_in_threshold" id="update_pending_time_in_threshold" class="form-control" min="0" step="1">
                            <div class="invalid-feedback">Please enter a valid time in threshold.</div>
                        </div>
                        <div class="mb-3">
                            <label for="update_pending_time_out_threshold" class="form-label">Time Out Threshold (minutes)</label>
                            <input type="number" name="time_out_threshold" id="update_pending_time_out_threshold" class="form-control" min="0" step="1">
                            <div class="invalid-feedback">Please enter a valid time out threshold.</div>
                        </div>
                        <div class="mb-3">
                            <label for="update_pending_late_threshold" class="form-label">Late Threshold (minutes)</label>
                            <input type="number" name="late_threshold" id="update_pending_late_threshold" class="form-control" min="0" step="1">
                            <div class="invalid-feedback">Please enter a valid late threshold.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Session Modal (Marked/Finished) -->
<div class="modal fade" id="updateMarkedSessionModal" tabindex="-1" aria-labelledby="updateMarkedSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateMarkedSessionModalLabel">Update Class Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="updateMarkedSessionForm" action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_session">
                    <input type="hidden" name="class_session_id" id="update_marked_class_session_id">
                    <input type="hidden" name="status" id="update_marked_status">
                    <div class="mb-3">
                        <label for="update_marked_class_session_name" class="form-label">Session Name</label>
                        <input type="text" name="class_session_name" id="update_marked_class_session_name" class="form-control" maxlength="255" required>
                        <div class="invalid-feedback">Please enter a session name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="update_marked_session_description" class="form-label">Description</label>
                        <textarea name="session_description" id="update_marked_session_description" class="form-control" maxlength="1000"></textarea>
                        <div class="invalid-feedback">Description cannot exceed 1000 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="update_marked_open_datetime" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" name="open_datetime" id="update_marked_open_datetime" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="update_marked_duration" class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" id="update_marked_duration" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="update_marked_attendance_method" class="form-label">Attendance Method</label>
                        <select name="attendance_method" id="update_marked_attendance_method" class="form-control" disabled>
                            <option value="manual">Manual</option>
                            <option value="automatic">Automatic</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="update_marked_auto_mark_attendance" class="form-label">Auto Mark Attendance</label>
                        <select name="auto_mark_attendance" id="update_marked_auto_mark_attendance" class="form-control" disabled>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    <div id="update_marked_threshold_fields" class="threshold-fields d-none">
                        <div class="mb-3">
                            <label for="update_marked_time_in_threshold" class="form-label">Time In Threshold (minutes)</label>
                            <input type="number" name="time_in_threshold" id="update_marked_time_in_threshold" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="update_marked_time_out_threshold" class="form-label">Time Out Threshold (minutes)</label>
                            <input type="number" name="time_out_threshold" id="update_marked_time_out_threshold" class="form-control" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="update_marked_late_threshold" class="form-label">Late Threshold (minutes)</label>
                            <input type="number" name="late_threshold" id="update_marked_late_threshold" class="form-control" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    // Debug: Log when script initializes
    console.log('Class detail script initialized');

    // Ported from PHP: Convert time string (HH:MM:SS) to minutes
    function time_to_minutes(time) {
        if (!time || typeof time !== 'string') {
            return '';
        }
        const [hours, minutes] = time.split(':').map(Number);
        return (hours * 60) + minutes;
    }

    // Tab persistence
    const savedTab = localStorage.getItem('teacherActiveTab');
    if (savedTab) {
        $(`a[data-bs-toggle="tab"][href="${savedTab}"]`).tab('show');
    }
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('teacherActiveTab', $(e.target).attr('href'));
    });

    // Show active tab from flashdata
    const activeNav = '<?= esc(session()->getFlashdata('subNavActive')) ?>';
    if (activeNav) {
        $(`a[href="#${activeNav}"]`).tab('show');
    }

    // Initialize Chart.js
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Unmarked'],
            datasets: [{
                data: [
                    <?= $attendanceStats['present'] ?? 0 ?>,
                    <?= $attendanceStats['absent'] ?? 0 ?>,
                    <?= $attendanceStats['late'] ?? 0 ?>,
                    <?= $attendanceStats['unmarked'] ?? 0 ?>
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(246, 173, 85, 0.7)',
                    'rgba(198, 193, 188, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // Function to toggle threshold fields
    function toggleThresholdFields($modal) {
        const $attendanceMethod = $modal.find('[name="attendance_method"]');
        const isAutomatic = $attendanceMethod.val() === 'automatic';
        const $thresholdFields = $modal.find('.threshold-fields');
        const $thresholdInputs = $thresholdFields.find('input');

        console.log('Toggling threshold fields, isAutomatic:', isAutomatic); // Debug log

        $thresholdFields.toggleClass('d-none', !isAutomatic);
        $thresholdInputs.each(function () {
            $(this).prop('required', isAutomatic && !$(this).prop('disabled'));
            if (!isAutomatic) {
                $(this).val('');
            }
        });
    }

    // Initialize threshold fields and bind change event
    function initializeThresholdFields($modal) {
        const $attendanceMethod = $modal.find('[name="attendance_method"]');
        
        toggleThresholdFields($modal);
        
        $attendanceMethod.on('change', function () {
            toggleThresholdFields($modal);
        });
    }

    // Form validation
    function initializeFormValidation($form) {
        $form.on('submit', function (e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('was-validated');
            }
        });
    }

    // Create Session Modal
    $('#createSessionModal').on('show.bs.modal', function () {
        console.log('Opening createSessionModal'); // Debug log
        const $form = $('#createSessionForm');
        $form[0].reset();
        initializeThresholdFields($(this));
        initializeFormValidation($form);
    });

    // Update Pending Session Modal
    $('#updatePendingSessionModal').on('show.bs.modal', function (e) {
        console.log('Opening updatePendingSessionModal'); // Debug log
        const $button = $(e.relatedTarget);
        const rawData = $button.attr('data-session'); // Get raw attribute
        console.log('Raw data-session attribute:', rawData); // Debug log
        let session;
        try {
            session = JSON.parse(rawData);
            console.log('Parsed session data:', session); // Debug log
        } catch (error) {
            console.error('Error parsing session data:', error);
            // Reset form and show modal empty
            const $form = $('#updatePendingSessionForm');
            $form[0].reset();
            initializeThresholdFields($(this));
            initializeFormValidation($form);
            return;
        }
        const $form = $('#updatePendingSessionForm');

        $('#update_pending_class_session_id').val(session.class_session_id || '');
        $('#update_pending_class_session_name').val(session.class_session_name || '');
        $('#update_pending_session_description').val(session.class_session_description || '');
        $('#update_pending_open_datetime').val(session.open_datetime ? session.open_datetime.replace(' ', 'T').slice(0, 16) : '');
        const duration = session.open_datetime && session.close_datetime ? 
            (new Date(session.close_datetime) - new Date(session.open_datetime)) / (1000 * 60) : '';
        $('#update_pending_duration').val(duration);
        $('#update_pending_attendance_method').val(session.attendance_method || 'manual');
        $('#update_pending_auto_mark_attendance').val(session.auto_mark_attendance || 'no');
        $('#update_pending_time_in_threshold').val(session.time_in_threshold ? time_to_minutes(session.time_in_threshold) : '');
        $('#update_pending_time_out_threshold').val(session.time_out_threshold ? time_to_minutes(session.time_out_threshold) : '');
        $('#update_pending_late_threshold').val(session.late_threshold ? time_to_minutes(session.late_threshold) : '');

        initializeThresholdFields($(this));
        initializeFormValidation($form);
    });

    // Update Marked/Finished Session Modal
    $('#updateMarkedSessionModal').on('show.bs.modal', function (e) {
        console.log('Opening updateMarkedSessionModal'); // Debug log
        const $button = $(e.relatedTarget);
        const rawData = $button.attr('data-session'); // Get raw attribute
        console.log('Raw data-session attribute:', rawData); // Debug log
        let session;
        try {
            session = JSON.parse(rawData);
            console.log('Parsed session data:', session); // Debug log
        } catch (error) {
            console.error('Error parsing session data:', error);
            // Reset form and show modal empty
            const $form = $('#updateMarkedSessionForm');
            $form[0].reset();
            initializeThresholdFields($(this));
            initializeFormValidation($form);
            return;
        }
        const $form = $('#updateMarkedSessionForm');

        $('#update_marked_class_session_id').val(session.class_session_id || '');
        $('#update_marked_status').val(session.status || '');
        $('#update_marked_class_session_name').val(session.class_session_name || '');
        $('#update_marked_session_description').val(session.class_session_description || '');
        $('#update_marked_open_datetime').val(session.open_datetime ? session.open_datetime.replace(' ', 'T').slice(0, 16) : '');
        const duration = session.open_datetime && session.close_datetime ? 
            (new Date(session.close_datetime) - new Date(session.open_datetime)) / (1000 * 60) : '';
        $('#update_marked_duration').val(duration);
        $('#update_marked_attendance_method').val(session.attendance_method || 'manual');
        $('#update_marked_auto_mark_attendance').val(session.auto_mark_attendance || 'no');
        $('#update_marked_time_in_threshold').val(session.time_in_threshold ? time_to_minutes(session.time_in_threshold) : '');
        $('#update_marked_time_out_threshold').val(session.time_out_threshold ? time_to_minutes(session.time_out_threshold) : '');
        $('#update_marked_late_threshold').val(session.late_threshold ? time_to_minutes(session.late_threshold) : '');

        initializeThresholdFields($(this));
        initializeFormValidation($form);
    });
});
</script>
<?php $this->endSection(); ?>