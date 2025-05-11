<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
    <div class="row mt-5">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Project Details</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Automated scheduling</li>
                        <li class="list-group-item">Face recognition for attendance</li>
                        <li class="list-group-item">Multiple attendance modes</li>
                        <li class="list-group-item">Leave request management</li>
                        <li class="list-group-item">Detailed reporting</li>
                    </ul>
                    <button class="btn btn-primary open-modal mt-3" data-modal="project-modal">Learn More</button>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">About the Creators</div>
                <div class="card-body">
                    <p>SAMS was developed by a team dedicated to enhancing education through technology.</p>
                    <span class="badge badge-success">Innovators</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Example Components -->
    <h2 class="mb-4">More Features</h2>
    <div class="mb-4">
        <form>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" placeholder="Enter your name">
            </div>
        </form>
    </div>
    <div class="mb-4">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Attendance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>John Doe</td>
                    <td>
                        <div class="progress" role="progressbar">
                            <div class="progress-bar" style="width: 95%;">95%</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Jane Smith</td>
                    <td>
                        <div class="progress" role="progressbar">
                            <div class="progress-bar" style="width: 88%;">88%</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
        </ul>
    </nav>

    <!-- Modal -->
    <div class="modal fade" id="project-modal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectModalLabel">Project Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Learn more about SAMS features like face recognition and automated scheduling.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php $this->endSection(); ?>