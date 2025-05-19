<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Rooms</h1>

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

    <div class="card mb-3">
        <div class="card-header">
            Room List
            <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addRoomModal">Add New Room</button>
        </div>
        <div class="card-body">
            <table id="roomsTable" class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Building</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?= esc($room['room_id']) ?></td>
                            <td><?= esc($room['name']) ?></td>
                            <td><?= esc($room['building']) ?></td>
                            <td><?= esc($room['capacity']) ?></td>
                            <td><?= esc($room['status'] ?? 'Active') ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn" data-id="<?= $room['room_id'] ?>" data-url="<?= site_url('admin/rooms') ?>" data-bs-toggle="modal" data-bs-target="#editRoomModal">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $room['room_id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteRoomModal">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoomModalLabel">Add Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addRoomForm" class="ajax-form" method="post" action="<?= site_url('admin/rooms') ?>">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="addName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="addName" name="name" value="<?= old('name') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addBuilding" class="form-label">Building</label>
                            <input type="text" class="form-control" id="addBuilding" name="building" value="<?= old('building') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addCapacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="addCapacity" name="capacity" value="<?= old('capacity') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addStatus" class="form-label">Status</label>
                            <select class="form-control" id="addStatus" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="addRoomForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRoomForm" class="ajax-form" method="post" action="<?= site_url('admin/rooms') ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="room_id" id="edit-room_id">
                        <div class="mb-3">
                            <label for="edit-name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit-name" name="name">
                        </div>
                        <div class="mb-3">
                            <label for="edit-building" class="form-label">Building</label>
                            <input type="text" class="form-control" id="edit-building" name="building">
                        </div>
                        <div class="mb-3">
                            <label for="edit-capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="edit-capacity" name="capacity">
                        </div>
                        <div class="mb-3">
                            <label for="edit-status" class="form-label">Status</label>
                            <select class="form-control" id="edit-status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="editRoomForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Room Modal -->
    <div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-labelledby="deleteRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRoomModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this room?
                    <form id="deleteRoomForm" class="ajax-form" method="post" action="<?= site_url('admin/rooms') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="room_id" id="deleteId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" form="deleteRoomForm">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>