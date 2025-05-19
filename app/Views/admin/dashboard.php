<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Admin Dashboard</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-header">Total Users by Role</div>
                <div class="card-body">
                    <p>Admins: <?= esc($stats['admins'] ?? 5) ?></p>
                    <p>Teachers: <?= esc($stats['teachers'] ?? 15) ?></p>
                    <p>Students: <?= esc($stats['students'] ?? 150) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-header">Active Classes</div>
                <div class="card-body">
                    <h3><?= esc($stats['active_classes'] ?? 10) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-header">Pending Leave Requests</div>
                <div class="card-body">
                    <h3><?= esc($stats['pending_leaves'] ?? 3) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-header">Pending Class Sessions</div>
                <div class="card-body">
                    <h3><?= esc($stats['pending_sessions'] ?? 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">Today's Attendance</div>
                <div class="card-body">
                    <h3><?= esc($stats['attendance_rate'] ?? '85.50') ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">Latest Leave Requests</div>
                <div class="card-body">
                    <table id="leaveRequestsTable" class="table table-striped data-table">
                        <thead>
                            <tr>
                                <th>Requester</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leaves as $leave): ?>
                                <tr>
                                    <td><?= esc($leave['requester']) ?></td>
                                    <td><?= esc($leave['date']) ?></td>
                                    <td><?= esc($leave['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>