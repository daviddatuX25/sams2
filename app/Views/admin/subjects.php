<?php $this->extend('layouts/admin'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Subjects</h1>

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
            Subject List
            <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addSubjectModal">Add New Subject</button>
        </div>
        <div class="card-body">
            <table id="subjectsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><?= esc($subject['subject_id']) ?></td>
                            <td><?= esc($subject['subject_name']) ?></td>
                            <td><?= esc($subject['subject_code']) ?></td>
                            <td><?= esc($subject['subject_description'] ?? '') ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-subject" data-id="<?= $subject['subject_id'] ?>" data-bs-toggle="modal" data-bs-target="#editSubjectModal">Edit</button>
                                <button class="btn btn-sm btn-danger delete-subject" data-id="<?= $subject['subject_id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteSubjectModal">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSubjectModalLabel">Add Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSubjectForm" method="post" action="<?= site_url('admin/subjects') ?>">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="addName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="addName" name="name" value="<?= old('subject_name') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addCode" class="form-label">Code</label>
                            <input type="text" class="form-control" id="addCode" name="code" value="<?= old('subject_code') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="addDescription" name="description"><?= old('subject_description') ?></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="addSubjectForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSubjectForm" method="post" action="<?= site_url('admin/subjects') ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="subject_id" id="editSubjectId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editName" name="name">
                        </div>
                        <div class="mb-3">
                            <label for="editCode" class="form-label">Code</label>
                            <input type="text" class="form-control" id="editCode" name="code">
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="editSubjectForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Subject Modal -->
    <div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-labelledby="deleteSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSubjectModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this subject?
                    <form id="deleteSubjectForm" method="post" action="<?= site_url('admin/subjects') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="subject_id" id="deleteSubjectId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" form="deleteSubjectForm">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#subjectsTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100]
    });

    // Edit button click
    $('.edit-subject').on('click', function() {
        var subjectId = $(this).data('id');
        $.ajax({
            url: '<?= site_url('admin/subjects') ?>',
            type: 'GET',
            data: { subject_id: subjectId, action: 'get' },
            success: function(response) {
                if (response.success) {
                    $('#editSubjectId').val(response.data.subject_id);
                    $('#editName').val(response.data.name);
                    $('#editCode').val(response.data.code);
                    $('#editDescription').val(response.data.description);
                } else {
                    alert('Error fetching subject data: ' + response.message);
                }
            },
            error: function() {
                alert('Error fetching subject data.');
            }
        });
    });

    // Delete button click
    $('.delete-subject').on('click', function() {
        var subjectId = $(this).data('id');
        $('#deleteSubjectId').val(subjectId);
    });

    // Form submissions via AJAX
    $('#addSubjectForm, #editSubjectForm, #deleteSubjectForm').on('submit', function(e) {
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