<?php $this->extend('layouts/admin'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Users</h1>

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
            User List
            <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addUserModal">Add New User</button>
        </div>
        <div class="card-body">
            <table id="usersTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= esc($user['user_id']) ?></td>
                            <td><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= esc($user['role']) ?></td>
                            <td><?= esc($user['status'] ?? 'Active') ?></td>
                            <td><?= esc($user['created_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-user" data-id="<?= $user['user_id'] ?>" data-bs-toggle="modal" data-bs-target="#editUserModal">Edit</button>
                                <button class="btn btn-sm btn-danger delete-user" data-id="<?= $user['user_id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteUserModal">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" method="post" action="<?= site_url('admin/users') ?>">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="addFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="addFirstName" name="first_name" value="<?= old('first_name') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="addLastName" name="last_name" value="<?= old('last_name') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addStatus" class="form-label">Status</label>
                            <select class="form-control" id="addStatus" name="status">
                                <option value="pending">Pending</option>
                                <option value="active">Active</option> 
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addRole" class="form-label">Role</label>
                            <select class="form-control" id="addRole" name="role">
                                <option value="admin">Admin</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="addUserForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="post" action="<?= site_url('admin/users') ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="mb-3">
                            <label for="editFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editFirstName" name="first_name">
                        </div>
                        <div class="mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" name="last_name">
                        </div>
                        <div class="mb-3">
                            <label for="editRole" class="form-label">Role</label>
                            <select class="form-control" id="editRole" name="role">
                                <option value="admin">Admin</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                         <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-control" id="editStatus" name="status">
                                <option value="pending">Pending</option>
                                <option value="active">Active</option> 
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="editUserForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user?
                    <form id="deleteUserForm" method="post" action="<?= site_url('admin/users') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" id="deleteUserId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" form="deleteUserForm">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#usersTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100]
    });

    // Edit button click
    $('.edit-user').on('click', function() {
        var userId = $(this).data('id');
        $.ajax({
            url: '<?= site_url('admin/users') ?>',
            type: 'POST',
            data: { user_id: userId, action: 'get' },
            success: function(response) {
                if (response.success) {
                    $('#editUserId').val(response.data.user_id);
                    $('#editFirstName').val(response.data.first_name);
                    $('#editLastName').val(response.data.last_name);
                    $('#editStatus').val(response.data.status);
                    $('#editRole').val(response.data.role);
                } else {
                    alert('Error fetching user data: ' + response.message);
                }
            },
            error: function() {
                alert('Error fetching user data.');
            }
        });
    });

    // Delete button click
    $('.delete-user').on('click', function() {
        var userId = $(this).data('id');
        $('#deleteUserId').val(userId);
    });

    // Form submissions via AJAX
    $('#addUserForm, #editUserForm, #deleteUserForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload(); // Refresh table
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error submitting form.');
            }
        });
    });
});
</script>
<?php $this->endSection(); ?>