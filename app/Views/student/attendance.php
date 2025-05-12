<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1>Attendance Logs</h1>
    <form class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?= esc($filters['marked_at'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="class_id" class="form-label">Class</label>
                <select class="form-control" id="class_id" name="class_id">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['class_id'] ?>" <?= $filters['class_id'] == $class['class_id'] ? 'selected' : '' ?>>
                            <?= esc($class['class_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="present" <?= $filters['status'] == 'present' ? 'selected' : '' ?>>Present</option>
                    <option value="absent" <?= $filters['status'] == 'absent' ? 'selected' : '' ?>>Absent</option>
                    <option value="late" <?= $filters['status'] == 'late' ? 'selected' : '' ?>>Late</option>
                    <option value="unmarked" <?= $filters['status'] == 'unmarked' ? 'selected' : '' ?>>Unmarked</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Class</th>
                <th>Session</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendanceLogs as $log): ?>
                <tr>
                    <td><?= date('Y-m-d', strtotime($log['marked_at'])) ?></td>
                    <td><?= esc($log['class_name']) ?></td>
                    <td><?= esc($log['class_session_name']) ?></td>
                    <td><?= ucfirst($log['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $this->endSection(); ?>