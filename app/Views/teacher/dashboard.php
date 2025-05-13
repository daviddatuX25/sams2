<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Teacher Dashboard</h1>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Today's Sessions</div>
            <div class="card-body">
                <?php if (empty($todaySessions)): ?>
                    <p>No sessions scheduled for today.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($todaySessions as $session): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <?php echo esc($session['class_name'] ?? 'Session ' . $session['class_session_id']); ?> 
                                    (<?php echo date('H:i', strtotime($session['open_datetime'])); ?>)
                                    <span class="badge bg-<?php echo $session['status'] === 'open' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($session['status']); ?>
                                    </span>
                                </span>
                                <a href="<?= site_url('teacher/classes/' . $session['class_id']) ?>" class="btn btn-sm btn-primary">View</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Alerts</div>
            <div class="card-body">
                <p>Pending Leave Requests: <span class="badge bg-primary"><?php echo esc($pendingLeaveCount); ?></span></p>
                <p>Unread Notifications: <span class="badge bg-primary"><?php echo esc($unreadCount); ?></span></p>
                <a href="<?= site_url('teacher/leave_requests') ?>" class="btn btn-primary">View Leave Requests</a>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>