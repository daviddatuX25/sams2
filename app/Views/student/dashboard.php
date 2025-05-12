<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Student Dashboard</h1>
    <div class="row mt-3 gy-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Today’s Classes</div>
                <div class="card-body">
                    <h5><?= count($todaySessions) ?> sessions today</h5>
                    <?php if (count($todaySessions) > 0): ?>
                        <ul>
                            <?php foreach (array_slice($todaySessions, 0, 2) as $session): ?>
                                <li><?= esc($session['class_session_name']) ?> at <?= date('H:i', strtotime($session['open_datetime'])) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No sessions scheduled for today.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Attendance Rate</div>
                <div class="card-body">
                    <h5><?= number_format($attendanceRate, 2) ?>%</h5>
                    <!-- Sparkline chart can be added here with Chart.js -->
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Notifications</div>
                <div class="card-body">
                    <h5><?= $unreadCount ?> unread</h5>
                    <!-- Optional: Add a view button or link -->
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>