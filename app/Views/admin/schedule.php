<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container">
    <h1>Leave Requests</h1>

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
            Leave Request List
            <div class="btn-group float-end">
                <button class="btn btn-outline-primary filter-btn" data-filter="all">All</button>
                <button class="btn btn-outline-primary filter-btn" data-filter="Pending">Pending</button>
                <button class="btn btn-outline-primary filter-btn" data-filter="Approved">Approved</button>
                <button class="btn btn-outline-primary filter-btn" data-filter="Rejected">Rejected</button>
            </div>
        </div>
        <div class="card-body">
            <table id="leaveRequestsTable" class="table table-striped data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leave_requests as $request): ?>
                        <tr>
                            <td><?= esc($request['leave_request_id']) ?></td>
                            <td><?= esc($request['student_name']) ?></td>
                            <td><?= esc($request['class_name']) ?></td>
                            <td><?= esc($request['start_date'] . ' to ' . $request['end_date']) ?></td>
                            <td><?= esc($request['status']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary view-btn" data-id="<?= $request['leave_request_id'] ?>" data-bs-toggle="modal" data-bs-target="#detailsModal">View Details</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Leave Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Student:</strong> <span id="detail-student"></span></p>
                    <p><strong>Class:</strong> <span id="detail-class"></span></p>
                    <p><strong>Dates:</strong> <span id="detail-dates"></span></p>
                    <p><strong>Reason:</strong> <span id="detail-reason"></span></p>
                    <p><strong>Status:</strong> <span id="detail-status"></span></p>
                    <form id="actionForm" class="ajax-form" method="post" action="<?= site_url('admin/leave-requests') ?>">
                        <input type="hidden" name="leave_request_id" id="detail-id">
                        <input type="hidden" name="action" id="action-type">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" form="actionForm" id="approve-btn" onclick="$('#action-type').val('approve')">Approve</button>
                    <button type="submit" class="btn btn-danger" form="actionForm" id="reject-btn" onclick="$('#action-type').val('reject')">Reject</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#leaveRequestsTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100]
    });

    $('.filter-btn').on('click', function() {
        var filter = $(this).data('filter');
        if (filter === 'all') {
            table.column(4).search('').draw();
        } else {
            table.column(4).search(filter).draw();
        }
    });

    $('.view-btn').on('click', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '<?= site_url('admin/leave-requests') ?>',
            type: 'GET',
            data: { leave_request_id: id, action: 'get' },
            success: function(response) {
                if (response.success) {
                    $('#detail-id').val(response.data.leave_request_id);
                    $('#detail-student').text(response.data.student_name);
                    $('#detail-class').text(response.data.class_name);
                    $('#detail-dates').text(response.data.start_date + ' to ' + response.data.end_date);
                    $('#detail-reason').text(response.data.reason || 'N/A');
                    $('#detail-status').text(response.data.status);
                    $('#approve-btn, #reject-btn').prop('disabled', response.data.status !== 'Pending');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error fetching leave request.');
            }
        });
    });
});
</script>
<?php $this->endSection(); ?>