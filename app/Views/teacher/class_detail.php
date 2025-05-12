<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4"><?php echo esc($class['class_name']); ?> (<?php echo esc($class['section']); ?>)</h1>
    </div>
    <div class="col-12">
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#roster">Roster</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#sessions">Sessions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#attendance">Attendance</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="roster">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($roster)): ?>
                            <p>No students enrolled.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roster as $student): ?>
                                        <tr>
                                            <td><?php echo esc($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="sessions">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($sessions)): ?>
                            <p>No sessions scheduled.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($sessions as $session): ?>
                                    <li class="list-group-item">
                                        <?php echo esc($session['class_session_name']); ?> 
                                        (<?php echo date('Y-m-d H:i', strtotime($session['open_datetime'])); ?>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="attendance">
                <div class="card">
                    <div class="card-body">
                        <canvas id="attendanceChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [
                        <?php echo $attendanceStats['present'] ?? 0; ?>,
                        <?php echo $attendanceStats['absent'] ?? 0; ?>,
                        <?php echo $attendanceStats['late'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(246, 173, 85, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    });
</script>
<?php $this->endSection(); ?>