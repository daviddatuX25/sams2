<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Leave Requests</h1>
    </div>
    <?php if (empty($leaveRequests)): ?>
        <div class="col-12">
            <p>No leave requests found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($leaveRequests as $request): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo esc($request['first_name'] . ' ' . $request['last_name']); ?></h5>
                        <p class="card-text">Class: <?php echo esc($request['class_name']); ?></p>
                        <p class="card-text">Reason: <?php echo esc($request['letter']); ?></p>
                        <p class="card-text">Date: <?php echo date('Y-m-d', strtotime($request['datetimestamp_created'])); ?></p>
                        <p class="card-text">Status: <span class="badge badge-<?php echo $request['status'] === 'pending' ? 'primary' : ($request['status'] === 'approved' ? 'success' : 'danger'); ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span></p>
                        <?php if ($request['status'] === 'pending'): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="leave_id" value="<?php echo $request['leave_id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="leave_id" value="<?php echo $request['leave_id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>