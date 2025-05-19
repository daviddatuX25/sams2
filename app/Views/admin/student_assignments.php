<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Student Assignments</h1>

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
            Student Assignment List
            <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">Add New Assignment</button>
        </div>
        <div class="card-body">
            <table id="assignmentsTable" class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Enrollment Term</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?= esc($assignment['enrollment_id']) ?></td>
                            <td><?= esc($assignment['student_name']) ?></td>
                            <td><?= esc($assignment['class_name']) ?></td>
                            <td><?= esc($assignment['enrollment_term']) ?></td>
                            <td><?= esc($assignment['status'] ?? 'Active') ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn" data-id="<?= $assignment['enrollment_id'] ?>" data-url="<?= site_url('admin/student-assignments') ?>" data-bs-toggle="modal" data-bs-target="#editAssignmentModal">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $assignment['enrollment_id'] ?>" data-bs-toggle="modal" data-bs-target="#deleteAssignmentModal">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Assignment Modal -->
    <div class="modal fade" id="addAssignmentModal" tabindex="-1" aria-labelledby="addAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAssignmentModalLabel">Add Student Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addAssignmentForm" class="ajax-form" method="post" action="<?= site_url('admin/student-assignments') ?>">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label for="addUserId" class="form-label">Student</label>
                            <select class="form-control" id="addUserId" name="user_id">
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['user_id'] ?>"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addClassId" class="form-label">Class</label>
                            <select class="form-control" id="addClassId" name="class_id">
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['class_id'] ?>"><?= esc($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addEnrollmentTermId" class="form-label">Enrollment Term</label>
                            <select class="form-control" id="addEnrollmentTermId" name="enrollment_term_id">
                                <?php foreach ($terms as $term): ?>
                                    <option value="<?= $term['enrollment_term_id'] ?>"><?= esc($term['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
                    <button type="submit" class="btn btn-primary" form="addAssignmentForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssignmentModalLabel">Edit Student Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAssignmentForm" class="ajax-form" method="post" action="<?= site_url('admin/student-assignments') ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="enrollment_id" id="edit-enrollment_id">
                        <div class="mb-3">
                            <label for="edit-user_id" class="form-label">Student</label>
                            <select class="form-control" id="edit-user_id" name="user_id">
                                <?php foreach ($students as $student): ?>
                                    <option value="<?= $student['user_id'] ?>"><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-class_id" class="form-label">Class</label>
                            <select class="form-control" id="edit-class_id" name="class_id">
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['class_id'] ?>"><?= esc($class['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-enrollment_term_id" class="form-label">Enrollment Term</label>
                            <select class="form-control" id="edit-enrollment_term_id" name="enrollment_term_id">
                                <?php foreach ($terms as $term): ?>
                                    <option value="<?= $term['enrollment_term_id'] ?>"><?= esc($term['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
                    <button type="submit" class="btn btn-primary" form="editAssignmentForm">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Assignment Modal -->
    <div class="modal fade" id="deleteAssignmentModal" tabindex="-1" aria-labelledby="deleteAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAssignmentModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this student assignment?
                    <form id="deleteAssignmentForm" class="ajax-form" method="post" action="<?= site_url('admin/student-assignments') ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="enrollment_id" id="deleteId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" form="deleteAssignmentForm">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>