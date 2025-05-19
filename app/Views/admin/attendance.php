<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Attendance</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if ($validation->getErrors()): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($validation->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Today's Attendance</div>
                <div class="card-body">
                    <h3><?= esc($stats['attendance_rate'] ?? '75.00') ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Sessions Open</div>
                <div class="card-body">
                    <h3><?= esc($stats['open_sessions'] ?? 3) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Attendance Drilldown
            <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">Add Session</button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="selectClass" class="form-label">Select Class</label>
                    <select class="form-control" id="selectClass">
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['class_id'] ?>"><?= esc($class['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="selectSession" class="form-label">Select Session</label>
                    <select class="form-control" id="selectSession">
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= $session['class_session_id'] ?>"><?= esc($session['date'] . ', ' . $session['start_time']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <table id="attendanceTable" class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Session Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): ?>
                        <tr>
                            <td><?= esc($record['attendance_id']) ?></td>
                            <td><?= esc($record['student_name']) ?></td>
                            <td><?= esc($record['class_name']) ?></td>
                            <td><?= esc($record['session_date']) ?></td>
                            <td><?= esc($record['status']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn" data-id="<?= $record['attendance_id'] ?>" data-url="<?= site_url('admin/attendance') ?>" data-bs-toggle="modal" data-bs-target="#editAttendanceModal">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $record['attendance_id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteAttendanceModal">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Attendance Modal -->
    <div class="modal fade" id="addAttendanceModal" tabindex="-1" aria-labelledby="addAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAttendanceModalLabel">Add Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addAttendanceForm" class="ajax-form" method="post" action="<?= site_url('admin/attendance') ?>">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="addUserId" class="form-label">Student</label>
                            <select class="form-control" id="addUserId" name="user_id">
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['user_id'] ?>"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addSessionId" class="form-label">Session</label>
                            <select class="form-control" id="addSessionId" name="class_session_id">
                                <?php foreach ($sessions as $session): ?>
                                    <option value="<?= $session['class_session_id'] ?>"><?= esc($session['date'] . ', ' . $session['start_time']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addStatus" class="form-label">Status</label>
                            <select class="form-control" id="addStatus" name="status">
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Late">Late</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="addAttendanceForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAttendanceForm" class="ajax-form" method="post" action="<?= site_url('admin/attendance') ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="attendance_id" id="edit-attendance_id">
                        <div class="mb-3">
                            <label for="edit-user_id" class="form-label">Student</label>
                            <select class="form-control" id="edit-user_id" name="user_id">
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['user_id'] ?>"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-class_session_id" class="form-label">Session</label>
                            <select class="form-control" id="edit-class_session_id" name="class_session_id">
                                <?php foreach ($sessions as $session): ?>
                                    <option value="<?= $session['class_session_id'] ?>"><?= esc($session['date'] . ', ' . $session['start_time']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-status" class="form-label">Status</label>
                            <select class="form-control" id="edit-status" name="status">
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Late">Late</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="editAttendanceForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Attendance Modal -->
    <div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-labelledby="deleteAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAttendanceModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this attendance record?
                    <form id="deleteAttendanceForm" class="ajax-form" method="post" action="<?= site_url('admin/attendance') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="attendance_id" id="deleteId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" form="deleteAttendanceForm">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Filter table based on class/session
    $('#selectClass, #selectSession').on('change', function() {
        var classId = $('#selectClass').val();
        var sessionId = $('#selectSession').val();
        $('#attendanceTable').DataTable().ajax.url('<?= site_url('admin/attendance') ?>?class_id=' + classId + '&session_id=' + sessionId).load();
    });
});
</script>
<?php $this->endSection(); ?>