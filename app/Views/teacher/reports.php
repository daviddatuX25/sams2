<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Attendance Reports</h1>
    </div>
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-4">
                        <label for="class_id" class="form-label">Class</label>
                        <select name="class_id" id="class_id" class="form-control">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['class_id']; ?>" <?php echo $filters['class_id'] == $class['class_id'] ? 'selected' : ''; ?>>
                                    <?php echo esc($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo esc($filters['start_date'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo esc($filters['end_date'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <canvas id="attendanceChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceData as $row): ?>
                            <tr>
                                <td><?php echo esc($row['class_name']); ?></td>
                                <td><?php echo ucfirst($row['status']); ?></td>
                                <td><?php echo $row['count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: <?php echo $chartData; ?>,
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    });
</script>
<?php $this->endSection(); ?>