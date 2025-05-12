<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1><?= esc($class['class_name']) ?> - <?= esc($class['subject_name']) ?></h1>
    <p>Teacher: <?= esc($class['first_name'] . ' ' . $class['last_name']) ?>, Section: <?= esc($class['section']) ?></p>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#attendance">Attendance</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sessions">Sessions</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#leave-requests">Leave Requests</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#history">History</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="attendance">
            <div class="d-flex justify-content-center align-items-center">
                <canvas id="attendanceChart"></canvas>
            </div>
            <a href="<?= site_url('student/attendance') ?>" class="btn btn-primary mt-3">View All History</a>
        </div>
        <div class="tab-pane fade" id="sessions">
            <div class="row">
                <?php foreach ($sessions as $session): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5><?= esc($session['class_session_name']) ?></h5>
                                <p>Date: <?= date('Y-m-d H:i', strtotime($session['open_datetime'])) ?></p>
                                <span class="badge bg-<?= $session['status'] === 'pending' ? 'warning' : ($session['status'] === 'marked' ? 'success' : 'danger') ?>">
                                    <?= ucfirst($session['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="tab-pane fade" id="leave-requests">
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#newLeaveModal">New Request</button>
            <div class="row">
                <?php foreach ($leaveRequests as $request): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <p>Reason: <?= esc($request['letter']) ?></p>
                                <p>Created: <?= esc($request['datetimestamp_created']) ?></p>
                                <span class="badge bg-<?= $request['status'] === 'pending' ? 'warning' : ($request['status'] === 'approved' ? 'success' : 'danger') ?>">
                                    <?= ucfirst($request['status']) ?>
                                </span>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <form action="<?= site_url('student/leave_requests') ?>" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="leave_id" value="<?= $request['attendance_leave_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="tab-pane fade" id="history">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Session</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): ?>
                        <tr>
                            <td><?= date('Y-m-d', strtotime($record['class_session_id'])) ?></td>
                            <td>Session</td>
                            <td><?= ucfirst($record['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Leave Request Modal -->
    <div class="modal fade" id="newLeaveModal" tabindex="-1" aria-labelledby="newLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newLeaveModalLabel">New Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= site_url('student/leave_requests') ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="letter" class="form-label">Reason for Leave</label>
                            <textarea class="form-control" id="letter" name="letter" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="datetimestamp_created" class="form-label">Date</label>
                            <input type="date" class="form-control" id="datetimestamp_created" name="datetimestamp_created" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Unmarked'],
            datasets: [{
                data: [<?= $attendanceStats['present'] ?>, <?= $attendanceStats['absent'] ?>, <?= $attendanceStats['late'] ?>, <?= $attendanceStats['unmarked'] ?>],
                backgroundColor: ['#10B981', '#EF4444', '#F59E0B', '#6B7280']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
<?php $this->endSection(); ?>