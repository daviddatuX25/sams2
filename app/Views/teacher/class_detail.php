<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4"><?php echo esc($class['class_name']); ?> (<?php echo esc($class['section']); ?>)</h1>
    </div>
    <div class="col-12">
        <ul class="nav nav-tabs mb-4">
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
                        <?php if (empty($roster)): ?>
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
            <div class="tab-pane fade" id="sessions">
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#sessionModal">Start New Session</button>
                        <?php if (empty($sessions)): ?>
                            <p>No sessions scheduled.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Session Name</th>
                                            <th>Date & Time</th>
                                            <th>Duration</th>
                                            <th>Time In Window</th>
                                            <th>Time Out Window</th>
                                            <th>Late Window</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php helper('main')?>
                                        <?php foreach ($sessions as $session): ?>
                                            <tr>
                                                <td><?php echo esc($session['class_session_name']); ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($session['open_datetime'])); ?></td>
                                                <td><?= esc(formatDuration(gmdate('H:i:s', strtotime($session['close_datetime']) - strtotime($session['open_datetime'])))) ?></td>
                                                <td><?= esc(formatDuration($session['time_in_threshold'])) ?></td>
                                                <td><?= esc(formatDuration($session['time_out_threshold'])) ?></td>
                                                <td><?= esc(formatDuration($session['late_threshold'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $session['status'] === 'open' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($session['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#sessionModal"
                                                            data-session-id="<?php echo $session['class_session_id']; ?>"
                                                            data-open-datetime="<?php echo $session['open_datetime']; ?>"
                                                            data-duration="<?php echo (strtotime($session['close_datetime']) - strtotime($session['open_datetime'])) / 60; ?>"
                                                            data-session-name="<?php echo esc($session['class_session_name']); ?>"
                                                            data-time-in="<?php echo esc($session['time_in_threshold']); ?>"
                                                            data-time-out="<?php echo esc($session['time_out_threshold']); ?>"
                                                            data-late="<?php echo esc($session['late_threshold']); ?>">
                                                        Edit
                                                    </button>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="session_id" value="<?php echo $session['class_session_id']; ?>">
                                                        <input type="hidden" name="action" value="delete_session">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this session?');">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
                                        <?= $session['class_session_name']; ?>
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
                                <canvas id="attendanceChart" class="mt-4 "></canvas>
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
                <h5 class="modal-title" id="sessionModalLabel">Create/Edit Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="sessionForm">
                <div class="modal-body">
                    <input type="hidden" name="class_session_id" id="class_session_id">
                    <input type="hidden" name="action" id="form_action" value="start_session">
                    <div class="mb-3">
                        <label for="attendance_method" class="form-label">Attendance Method</label>
                        <select name="attendance_method" id="attendance_method" class="form-control" required>
                            <option value="manual">Manual</option>
                            <option value="automatic">Automatic</option>
                        </select>
                        <div class="invalid-feedback" id="attendance_method_error"></div>
                    </div>
                    <div id="custom_fields">
                        <div class="mb-3">
                            <label for="class_session_name" class="form-label">Session Name</label>
                            <input type="text" name="class_session_name" id="class_session_name" class="form-control" required>
                            <div class="invalid-feedback" id="class_session_name_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="open_datetime" class="form-label">Start Date & Time</label>
                            <input type="datetime-local" name="open_datetime" id="open_datetime" class="form-control" min="<?= date('Y-m-d\TH:i', strtotime('+1 minute')); ?>" required>
                            <div class="invalid-feedback" id="open_datetime_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="duration" class="form-label">Duration (minutes)</label>
                            <input type="number" name="duration" id="duration" class="form-control" min="1" required>
                            <div class="invalid-feedback" id="duration_error"></div>
                        </div>
                        <div class="automatic-only" style="display: none;">
                            <div class="mb-3">
                                <label for="time_in_threshold" class="form-label">Time In Threshold (minutes)</label>
                                <input type="number" name="time_in_threshold" id="time_in_threshold" class="form-control" min="0" step="1">
                                <div class="invalid-feedback" id="time_in_threshold_error"></div>
                            </div>
                            <div class="mb-3">
                                <label for="time_out_threshold" class="form-label">Time Out Threshold (minutes)</label>
                                <input type="number" name="time_out_threshold" id="time_out_threshold" class="form-control" min="0" step="1">
                                <div class="invalid-feedback" id="time_out_threshold_error"></div>
                            </div>
                            <div class="mb-3">
                                <label for="late_threshold" class="form-label">Late Threshold (minutes)</label>
                                <input type="number" name="late_threshold" id="late_threshold" class="form-control" min="0" step="1">
                                <div class="invalid-feedback" id="late_threshold_error"></div>
                            </div>
                            <div class="mb-3">
                                <label for="auto_mark_attendance" class="form-label">Auto Mark Attendance</label>
                                <select name="auto_mark_attendance" id="auto_mark_attendance" class="form-control">
                                    <option value="yes">Yes</option>
                                    <option value="no">No</option>
                                </select>
                                <div class="invalid-feedback" id="auto_mark_attendance_error"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Initialize Chart.js
    const ctx = $('#attendanceChart')[0].getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent', 'Late'],
            datasets: [{
                data: [
                    <?php echo $attendanceStats['present']; ?>,
                    <?php echo $attendanceStats['absent']; ?>,
                    <?php echo $attendanceStats['late']; ?>
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(246, 173, 85, 0.7)'
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

    // Toggle automatic fields and required status
    const toggleAutomaticFields = function() {
        const $automaticFields = $('.automatic-only');
        const isAutomatic = $('#attendance_method').val() === 'automatic';
        $automaticFields.css('display', isAutomatic ? 'block' : 'none');
        $automaticFields.find('input, select').prop('required', isAutomatic);
    };

    // Modal button click handler
    $('[data-bs-target="#sessionModal"]').on('click', function() {
        const sessionId = $(this).data('session-id') || '';
        const openDatetime = $(this).data('open-datetime') || '';
        const duration = $(this).data('duration') || '';
        const sessionName = $(this).data('session-name') || '';
        const attendanceMethod = $(this).data('attendance-method') || 'manual';
        const timeIn = $(this).data('time-in') || '0';
        const timeOut = $(this).data('time-out') || '0';
        const late = $(this).data('late') || '0';
        const autoMark = $(this).data('auto-mark') || 'yes';
        $('#class_session_id').val(sessionId);
        $('#open_datetime').val(openDatetime ? openDatetime.replace(' ', 'T') : '');
        $('#duration').val(duration);
        $('#class_session_name').val(sessionName);
        $('#attendance_method').val(attendanceMethod);
        $('#time_in_threshold').val(timeIn);
        $('#time_out_threshold').val(timeOut);
        $('#late_threshold').val(late);
        $('#auto_mark_attendance').val(autoMark);
        $('#settings_id').val('');
        $('#sessionModalLabel').text(sessionId ? 'Edit Session' : 'Create Session');
        $('#form_action').val(sessionId ? 'update_session' : 'start_session');

        toggleAutomaticFields();
    });

    // Autofill settings
    $('#settings_id').on('change', function() {
        const $select = $(this);
        const $nameInput = $('#class_session_name');
        const $durationInput = $('#duration');
        const $attendanceMethodInput = $('#attendance_method');
        const $timeInInput = $('#time_in_threshold');
        const $timeOutInput = $('#time_out_threshold');
        const $lateInput = $('#late_threshold');
        const $autoMarkInput = $('#auto_mark_attendance');

        if ($select.val()) {
            const $option = $select.find('option:selected');
            $nameInput.val($option.data('name') || '');
            $durationInput.val($option.data('duration') || '');
            $attendanceMethodInput.val($option.data('attendance-method') || 'manual');
            $timeInInput.val($option.data('time-in') || '0');
            $timeOutInput.val($option.data('time-out') || '0');
            $lateInput.val($option.data('late') || '0');
            $autoMarkInput.val($option.data('auto-mark') || 'yes');
        } else {
            $nameInput.val('');
            $durationInput.val('');
            $attendanceMethodInput.val('manual');
            $timeInInput.val('0');
            $timeOutInput.val('0');
            $lateInput.val('0');
            $autoMarkInput.val('yes');
        }
        toggleAutomaticFields();
    });

    // Attendance method change handler
    $('#attendance_method').on('change', toggleAutomaticFields);

    // Client-side validation
    $('#open_datetime').on('input', function() {
        const $input = $(this);
        const $errorDiv = $('#open_datetime_error');
        const now = new Date();
        const selectedDate = new Date($input.val());
        if (selectedDate <= now) {
            $input.addClass('is-invalid');
            $errorDiv.text('Start date and time must be in the future.');
        } else {
            $input.removeClass('is-invalid');
            $errorDiv.text('');
        }
    });

    $('#duration').on('input', function() {
        const $input = $(this);
        const $errorDiv = $('#duration_error');
        if ($input.val() <= 0) {
            $input.addClass('is-invalid');
            $errorDiv.text('Duration must be a positive number.');
        } else {
            $input.removeClass('is-invalid');
            $errorDiv.text('');
        }
    });

    $('#class_session_name').on('input', function() {
        const $input = $(this);
        const $errorDiv = $('#class_session_name_error');
        if (!$input.val().trim()) {
            $input.addClass('is-invalid');
            $errorDiv.text('Session name is required.');
        } else if ($input.val().length > 255) {
            $input.addClass('is-invalid');
            $errorDiv.text('Session name cannot exceed 255 characters.');
        } else {
            $input.removeClass('is-invalid');
            $errorDiv.text('');
        }
    });

    $('#time_in_threshold').on('input', function() {
        const $input = $(this);
        const $errorDiv = $('#time_in_threshold_error');
        if ($input.is(':visible') && $input.val() < 0) {
            $input.addClass('is-invalid');
            $errorDiv.text('Time in threshold must be non-negative.');
        } else {
            $input.removeClass('is-invalid');
            $errorDiv.text('');
        }
    });

    $('#time_out_threshold').on('input', function() {
        const $input = $(this);
        const $errorDiv = $('#time_out_threshold_error');
        if ($input.is(':visible') && $input.val() < 0) {
            $input.addClass('is-invalid');
            $errorDiv.text('Time out threshold must be non-negative.');
        } else {
            $input.removeClass('is-invalid');
            $errorDiv.text('');
        }
    });

    $('#late_threshold').on('input', function() {
        const $input = $(this);
        const $errorDiv = $('#late_threshold_error');
        if ($input.is(':visible') && $input.val() < 0) {
            $input.addClass('is-invalid');
            $errorDiv.text('Late threshold must be non-negative.');
        } else {
            $input.removeClass('is-invalid');
            $errorDiv.text('');
        }
    });

    // Initialize form state on modal show
    $('#sessionModal').on('shown.bs.modal', function() {
        toggleAutomaticFields();
    });
});
</script>
<?php $this->endSection(); ?>