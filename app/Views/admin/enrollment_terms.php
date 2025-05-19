<?php $this->extend('layouts/admin'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Enrollment Terms</h1>

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
            Enrollment Term List
            <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addTermModal">Add New Term</button>
        </div>
        <div class="card-body">
            <table id="termsTable" class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Academic Year</th>
                        <th>Semester</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($terms as $term): ?>
                        <tr>
                            <td><?= esc($term['enrollment_term_id']) ?></td>
                            <td><?= esc($term['academic_year']) ?></td>
                            <td><?= esc($term['semester']) ?></td>
                            <td><?= esc($term['term_start']) ?></td>
                            <td><?= esc($term['term_end']) ?></td>
                            <td><?= esc($term['term_description'] ?? '') ?></td>
                            <td><?= esc($term['status'] ?? 'active') ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn" 
                                        data-id="<?= $term['enrollment_term_id'] ?>" 
                                        data-academic-year="<?= esc($term['academic_year']) ?>" 
                                        data-semester="<?= esc($term['semester']) ?>" 
                                        data-term-start="<?= esc($term['term_start']) ?>" 
                                        data-term-end="<?= esc($term['term_end']) ?>" 
                                        data-term-description="<?= esc($term['term_description'] ?? '') ?>" 
                                        data-status="<?= esc($term['status']) ?>" 
                                        data-url="<?= site_url('admin/enrollment-terms') ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editTermModal">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" 
                                        data-id="<?= $term['enrollment_term_id'] ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteTermModal">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Term Modal -->
    <div class="modal fade" id="addTermModal" tabindex="-1" aria-labelledby="addTermModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTermModalLabel">Add Enrollment Term</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addTermForm" class="ajax-form" method="post" action="<?= site_url('admin/enrollment-terms') ?>">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="addAcademicYear" class="form-label">Academic Year</label>
                            <input type="text" class="form-control" id="addAcademicYear" name="academic_year" value="<?= old('academic_year') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addSemester" class="form-label">Semester</label>
                            <select class="form-control" id="addSemester" name="semester">
                                <option value="1st" <?= old('semester') === '1st' ? 'selected' : '' ?>>1st</option>
                                <option value="2nd" <?= old('semester') === '2nd' ? 'selected' : '' ?>>2nd</option>
                                <option value="summer" <?= old('semester') === 'summer' ? 'selected' : '' ?>>Summer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addTermStart" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="addTermStart" name="term_start" value="<?= old('term_start') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addTermEnd" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="addTermEnd" name="term_end" value="<?= old('term_end') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="addTermDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="addTermDescription" name="term_description"><?= old('term_description') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="addStatus" class="form-label">Status</label>
                            <select class="form-control" id="addStatus" name="status">
                                <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="addTermForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Term Modal -->
    <div class="modal fade" id="editTermModal" tabindex="-1" aria-labelledby="editTermModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTermModalLabel">Edit Enrollment Term</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTermForm" class="ajax-form" method="post" action="<?= site_url('admin/enrollment-terms') ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="enrollment_term_id" id="edit-enrollment_term_id">
                        <div class="mb-3">
                            <label for="edit-academic_year" class="form-label">Academic Year</label>
                            <input type="text" class="form-control" id="edit-academic_year" name="academic_year">
                        </div>
                        <div class="mb-3">
                            <label for="edit-semester" class="form-label">Semester</label>
                            <select class="form-control" id="edit-semester" name="semester">
                                <option value="1st">1st</option>
                                <option value="2nd">2nd</option>
                                <option value="summer">Summer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-term_start" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edit-term_start" name="term_start">
                        </div>
                        <div class="mb-3">
                            <label for="edit-term_end" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="edit-term_end" name="term_end">
                        </div>
                        <div class="mb-3">
                            <label for="edit-term_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit-term_description" name="term_description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit-status" class="form-label">Status</label>
                            <select class="form-control" id="edit-status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" form="editTermForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Term Modal -->
    <div class="modal fade" id="deleteTermModal" tabindex="-1" aria-labelledby="deleteTermModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTermModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this enrollment term?
                    <form id="deleteTermForm" class="ajax-form" method="post" action="<?= site_url('admin/enrollment-terms') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="enrollment_term_id" id="deleteId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" form="deleteTermForm">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // Populate Delete Modal
    $('.delete-btn').on('click', function() {
        const id = $(this).data('id');
        $('#deleteId').val(id);
    });
});
</script>
<?php $this->endSection(); ?>