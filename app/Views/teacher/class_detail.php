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
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sessionModal" data-mode="create">New Custom Class Session</button>
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
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sessionModal" data-mode="edit" 
                                        data-session='<?= json_encode($session) ?>'>Edit</button>
                                    <form action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>" method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_session">
                                        <input type="hidden" name="session_id" value="<?= $session['class_session_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($roster as $student): ?>
                                                <tr>
                                                    <td><?php echo esc($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                    <td><input class="form-radio-styled" type="radio" name="attendance[<?php echo $student['user_id']; ?>]" value="present" <?php echo ($attendance[$student['user_id']] ?? '') === 'present' ? 'checked' : ''; ?> required></td>
                                                    <td><input class="form-radio-styled" type="radio" name="attendance[<?php echo $student['user_id']; ?>]" value="absent" <?php echo ($attendance[$student['user_id']] ?? '') === 'absent' ? 'checked' : ''; ?> required></td>
                                                    <td><input class="form-radio-styled" type="radio" name="attendance[<?php echo $student['user_id']; ?>]" value="late" <?php echo ($attendance[$student['user_id']] ?? '') === 'late' ? 'checked' : ''; ?> required></td>
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

<!-- Session Modal -->
<div class="modal fade" id="sessionModal" tabindex="-1" aria-labelledby="sessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionModalLabel">New Custom Class Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="sessionForm" action="<?= base_url("/teacher/classes/{$class['class_id']}") ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" id="form_action" value="start_session">
                    <input type="hidden" name="class_session_id" id="class_session_id">
                    <div class="mb-3">
                        <label for="class_session_name" class="form-label">Session Name</label>
                        <input type="text" name="class_session_name" id="class_session_name" class="form-control" maxlength="255" required>
                        <div class="invalid-feedback">Please enter a session name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="session_description" class="form-label">Description</label>
                        <textarea name="session_description" id="session_description" class="form-control" maxlength="1000"></textarea>
                        <div class="invalid-feedback">Description cannot exceed 1000 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label for="open_datetime" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" name="open_datetime" id="open_datetime" class="form-control" min="<?= date('Y-m-d\TH:i', strtotime('+1 minute')); ?>" required>
                        <div class="invalid-feedback">Please select a valid start date and time.</div>
                    </div>
                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" id="duration" class="form-control" min="1" required>
                        <div class="invalid-feedback">Please enter a duration of at least 1 minute.</div>
                    </div>
                    <div class="mb-3 status-field d-none">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="marked">Marked</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <div class="invalid-feedback">Please select a status.</div>
                    </div>
                    <div class="mb-3">
                        <label for="attendance_method" class="form-label">Attendance Method</label>
                        <select name="attendance_method" id="attendance_method" class="form-control" required>
                            <option value="manual">Manual</option>
                            <option value="automatic">Automatic</option>
                        </select>
                        <div class="invalid-feedback">Please select an attendance method.</div>
                    </div>
                    <div class="mb-3">
                        <label for="auto_mark_attendance" class="form-label">Auto Mark Attendance</label>
                        <select name="auto_mark_attendance" id="auto_mark_attendance" class="form-control" required>
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                        <div class="invalid-feedback">Please select whether to auto mark attendance.</div>
                    </div>
                    <div id="threshold_fields" class="d-none">
                        <div class="mb-3">
                            <label for="time_in_threshold" class="form-label">Time In Threshold (minutes)</label>
                            <input type="number" name="time_in_threshold" id="time_in_threshold" class="form-control" min="0" step="1">
                            <div class="invalid-feedback">Please enter a valid time in threshold.</div>
                        </div>
                        <div class="mb-3">
                            <label for="time_out_threshold" class="form-label">Time Out Threshold (minutes)</label>
                            <input type="number" name="time_out_threshold" id="time_out_threshold" class="form-control" min="0" step="1">
                            <div class="invalid-feedback">Please enter a valid time out threshold.</div>
                        </div>
                        <div class="mb-3">
                            <label for="late_threshold" class="form-label">Late Threshold (minutes)</label>
                            <input type="number" name="late_threshold" id="late_threshold" class="form-control" min="0" step="1">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tab persistence
    const savedTab = localStorage.getItem('teacherActiveTab');
    if (savedTab) {
        const tabTrigger = document.querySelector(`a[data-bs-toggle="tab"][href="${savedTab}"]`);
        if (tabTrigger) {
            new bootstrap.Tab(tabTrigger).show();
        }
    }
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            localStorage.setItem('teacherActiveTab', e.target.getAttribute('href'));
        });
    });

    // Show active tab from flashdata
    const activeNav = '<?= esc(session()->getFlashdata('activeNav') ?? 'roster') ?>';
    if (activeNav) {
        const tabTrigger = document.querySelector(`a[href="#${activeNav}"]`);
        if (tabTrigger) {
            new bootstrap.Tab(tabTrigger).show();
        }
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

    // Modal form handling
    const sessionModal = document.getElementById('sessionModal');
    const sessionForm = document.getElementById('sessionForm');
    const autoMarkSelect = document.getElementById('auto_mark_attendance');
    const thresholdFields = document.getElementById('threshold_fields');
    const thresholdInputs = thresholdFields.querySelectorAll('input');
    const statusField = document.querySelector('.status-field');
    const statusSelect = document.getElementById('status');

    function toggleThresholdFields() {
        const isAutoMark = autoMarkSelect.value === 'yes';
        thresholdFields.classList.toggle('d-none', !isAutoMark);
        thresholdInputs.forEach(input => {
            input.required = isAutoMark;
            if (!isAutoMark) {
                input.value = '';
            }
        });
    }

    function toggleStatusField(isEditMode) {
        statusField.classList.toggle('d-none', !isEditMode);
        statusSelect.required = isEditMode;
        if (!isEditMode) {
            statusSelect.value = '';
        }
    }

    sessionModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const mode = button.getAttribute('data-mode');
        const modalTitle = sessionModal.querySelector('.modal-title');
        const submitButton = sessionForm.querySelector('button[type="submit"]');
        const formAction = document.getElementById('form_action');
        const sessionIdInput = document.getElementById('class_session_id');

        if (mode === 'create') {
            modalTitle.textContent = 'New Custom Class Session';
            submitButton.textContent = 'Create Session';
            formAction.value = 'start_session';
            sessionForm.reset();
            sessionIdInput.value = '';
            toggleStatusField(false);
            toggleThresholdFields();
        } else if (mode === 'edit') {
            modalTitle.textContent = 'Edit Class Session';
            submitButton.textContent = 'Update Session';
            formAction.value = 'update_session';
            const session = JSON.parse(button.getAttribute('data-session'));
            sessionIdInput.value = session.class_session_id;
            document.getElementById('class_session_name').value = session.class_session_name;
            document.getElementById('session_description').value = session.class_session_description || '';
            document.getElementById('open_datetime').value = session.open_datetime.replace(' ', 'T').slice(0, 16);
            const duration = (new Date(session.close_datetime) - new Date(session.open_datetime)) / (1000 * 60);
            document.getElementById('duration').value = duration;
            document.getElementById('status').value = session.status;
            document.getElementById('attendance_method').value = session.attendance_method;
            document.getElementById('auto_mark_attendance').value = session.auto_mark_attendance;
            document.getElementById('time_in_threshold').value = session.time_in_threshold ? time_to_minutes(session.time_in_threshold) : '';
            document.getElementById('time_out_threshold').value = session.time_out_threshold ? time_to_minutes(session.time_out_threshold) : '';
            document.getElementById('late_threshold').value = session.late_threshold ? time_to_minutes(session.late_threshold) : '';
            toggleStatusField(true);
            toggleThresholdFields();
        }
    });

    autoMarkSelect.addEventListener('change', toggleThresholdFields);
    autoMarkSelect.addEventListener('input', toggleThresholdFields);
    toggleThresholdFields();

    sessionForm.addEventListener('submit', function (event) {
        if (!sessionForm.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            sessionForm.classList.add('was-validated');
        }
    });
});
</script>
<?php $this->endSection(); ?>