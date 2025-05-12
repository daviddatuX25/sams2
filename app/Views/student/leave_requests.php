<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1>Leave Requests</h1>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
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
<?php $this->endSection(); ?>