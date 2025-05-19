<?php
$events = [];

foreach ($chartData['labels'] as $i => $date) {
    foreach ($chartData['datasets'] as $dataset) {
        $status = $dataset['label']; // 'Present', 'Absent', etc.
        $count = $dataset['data'][$i]; // Number of sessions
        if ($count > 0) {
            $events[] = [
                'title' => "$status: $count",
                'start' => $date,
                'allDay' => true,
                'color' => $dataset['backgroundColor']
            ];
        }
    }
}
?>
<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1><?= esc($class['class_name']) ?> - <?= esc($class['subject_name']) ?></h1>
    <p>Teacher: <?= esc($class['teacher_first_name'] . ' ' . $class['teacher_last_name']) ?>, Section: <?= esc($class['section']) ?></p>
    <div id="ajax-message" class="alert d-none"></div>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#attendance">Attendance</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sessions">Sessions</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#leave-requests">Leave Requests</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#history">History</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="attendance">
            <div class="d-flex justify-content-center align-items-center">
                <div id="calendar" style="width: 80vw"></div>
            </div>
            <a href="<?= site_url('student/attendance') ?>" class="btn btn-primary mt-3">View All History</a>
        </div>
        <div class="tab-pane fade" id="sessions">
            <div class="row">
                <?php foreach ($sessions as $session): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5><?= esc($session['class_session_name']) ?></h5>
                                <p>Date: <?= date('Y-m-d H:i', strtotime($session['open_datetime'])) ?></p>
                                <span class="badge bg-<?= $session['status'] === 'pending' ? 'warning' : ($session['status'] === 'marked' ? 'success' : 'danger') ?>">
                                    <?= ucfirst($session['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="tab-pane fade" id="leave-requests">
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#newLeaveModal">New Request</button>
            <div class="row" id="leave-requests-list">
                <?php foreach ($leaveRequests as $request): ?>
                    <div class="col-md-4 mb-4 leave-request-item" data-id="<?= $request['attendance_leave_id'] ?>">
                        <div class="card">
                            <div class="card-body">
                                <p>Reason: <?= esc($request['reason']) ?></p>
                                <p>Date: <?= esc($request['leave_date']) ?></p>
                                <p>Created: <?= esc($request['created_at']) ?></p>
                                <span class="badge bg-<?= $request['status'] === 'pending' ? 'warning' : ($request['status'] === 'approved' ? 'success' : 'danger') ?>">
                                    <?= ucfirst($request['status']) ?>
                                </span>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <form class="cancel-leave-form d-inline" action="<?= site_url('student/classes/' . $class['class_id']) ?>" method="POST">
                                        <input type="hidden" name="action" value="cancel_leave_request">
                                        <input type="hidden" name="attendance_leave_id" value="<?= $request['attendance_leave_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="tab-pane fade" id="history">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Session</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceHistory as $session): ?>
                        <tr>
                            <td><?= date('Y-m-d', strtotime($session['marked_at'])) ?></td>
                            <td>Session</td>
                            <td><?= ucfirst($session['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Leave Request Modal -->
    <div class="modal fade" id="newLeaveModal" tabindex="-1" aria-labelledby="newLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newLeaveModalLabel">New Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="leave-request-form" action="<?= site_url('student/classes/'. $class['class_id']) ?>" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="submit_leave_request">
                        <input type="hidden" name="class_id" value="<?= $class['class_id'] ?>">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Leave</label>
                            <textarea class="form-control" id="reason" name="reason" required></textarea>
                            <div class="invalid-feedback" id="reason_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="leave_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="leave_date" name="leave_date" required>
                            <div class="invalid-feedback" id="leave_date_error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Add this CSS -->
<style>
  /* Make the calendar bigger */
  #calendar {
    margin: 0 auto;
    font-size: 1.2rem;   /* bigger font size */
  }

</style>
<script>
$(document).ready(function() {
    function showMessage(response, form) {
        $('#ajax-message').removeClass('d-none alert-success alert-danger');
        if (response.success) {
            $('#ajax-message').addClass('alert-success').text(response.message);
            form[0].reset();
            location.reload();
        } else {
            $('#ajax-message').addClass('alert-danger').text(response.message);
            if (response.errors) {
                $.each(response.errors, function(field, error) {
                    $(`#${field}_error`).text(error).addClass('invalid-feedback');
                    $(`#${field}`).addClass('is-invalid');
                });
            }
        }
        setTimeout(() => $('#ajax-message').addClass('d-none'), 5000);
        return response.success;
    }

    function refreshLeaveRequests() {
        $.ajax({
            url: '<?= site_url('student/classes/' . $class['class_id']) ?>',
            method: 'GET',
            dataType: 'html',
            success: function(html) {
                const newContent = $(html).find('#leave-requests-list').html();
                $('#leave-requests-list').html(newContent);
                bindCancelForms();
            }
        });
    }

    function bindCancelForms() {
        $('.cancel-leave-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this); // ✅ Store reference to the form
        console.log(form.serialize());
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (showMessage(response, form)) { // ✅ Use stored reference
                    refreshLeaveRequests();
                }
            },
            error: function() {
                $('#ajax-message').removeClass('d-none').addClass('alert-danger').text('An error occurred.');
                setTimeout(() => $('#ajax-message').addClass('d-none'), 5000);
            }
        });
    });

    }

    // Leave Request Form
    $('#leave-request-form').on('submit', function(e) {
        e.preventDefault();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (showMessage(response, $('#leave-request-form'))) {
                    $('#newLeaveModal').modal('hide');
                    refreshLeaveRequests();
                }
            },
            error: function() {
                $('#ajax-message').removeClass('d-none').addClass('alert-danger').text('An error occurred.');
                setTimeout(() => $('#ajax-message').addClass('d-none'), 5000);
            }
        });
    });

    // Cancel Leave Forms
    bindCancelForms();

    // Attendance Chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        datasets: <?php echo json_encode($chartData['datasets']); ?>
    },
    options: {
        responsive: true,
        scales: {
            x: {
                stacked: true,
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                stacked: true,
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Sessions'
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            title: {
                display: true,
                text: 'Attendance Status by Date'
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        }
    }
});

     // Save tab to localStorage when clicked
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const activeTabId = $(e.target).attr('href');
        localStorage.setItem('activeClassTab', activeTabId);
    });

    // On page load, activate saved tab
    const savedTab = localStorage.getItem('activeClassTab');
    if (savedTab) {
        const tabTrigger = document.querySelector(`a[href="${savedTab}"]`);
        if (tabTrigger) {
            new bootstrap.Tab(tabTrigger).show();
        }
    }
    
});
</script>
 <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?php echo json_encode($events); ?>
            });

            calendar.render();
        });
    </script>
<?php $this->endSection(); ?>