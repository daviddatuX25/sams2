<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Class Sessions</h1>

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

    <div class="card mb-3">
        <div class="card-header">
            Class Session List
            <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addSessionModal">New Session</button>
        </div>
        <div class="card-body">
            <table id="sessionsTable" class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Class</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?= esc($session['class_session_id']) ?></td>
                            <td><?= esc($session['class_name']) ?></td>
                            <td><?= esc($session['date']) ?></td>
                            <td><?= esc($session['start_time'] . ' - ' . $session['end_time']) ?></td>
                            <td><?= esc($session['status']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn" data-id="<?= $session['class_session_id'] ?>" data-url="<?= site_url('admin/class-sessions') ?>" data-bs-toggle="modal" data-bs-target="#editSessionModal">Edit</button>
                                <button class="btn btn-sm btn-danger" data-id="<?= $session['class_session_id'] ?>" data-bs-toggle="modal" data-bs-target="#cancelSessionModal">Cancel</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Session Modal -->
    <div class="modal fade" id="addSessionModal" tabindex="-1" aria-labelledby="addSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSessionModalLabel">New Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSessionForm" class="ajax-form" method="post" action="<?= site_url('admin/class-sessions') ?>">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="addClassId" class="form-label">Class</label>
                            <select class="form-control" id="addClassId" name="class_id">
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['class_id'] ?>"><?= esc($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="addDate" name="date" value="<?= old('date') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addStartTime" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="addStartTime" name="start_time" value="<?= old('start_time') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addEndTime" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="addEndTime" name="end_time" value="<?= old('end_time') ?>">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="addSessionForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Session Modal -->
    <div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSessionModalLabel">Edit Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSessionForm" class="ajax-form" method="post" action="<?= site_url('admin/class-sessions') ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="class_session_id" id="edit-class_session_id">
                        <div class="mb-3">
                            <label for="edit-class_id" class="form-label">Class</label>
                            <select class="form-control" id="edit-class_id" name="class_id">
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['class_id'] ?>"><?= esc($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="edit-date" name="date">
                        </div>
                        <div class="mb-3">
                            <label for="edit-start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="edit-start_time" name="start_time">
                        </div>
                        <div class="mb-3">
                            <label for="edit-end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="edit-end_time" name="end_time">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="editSessionForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Session Modal -->
    <div class="modal fade" id="cancelSessionModal" tabindex="-1" aria-labelledby="cancelSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelSessionModalLabel">Confirm Cancel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel this session?
                    <form id="cancelSessionForm" class="ajax-form" method="post" action="<?= site_url('admin/class-sessions') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="class_session_id" id="deleteId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" form="cancelSessionForm">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>