<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div style="min-height: 75vh" class="container d-flex flex-column justify-content-center align-items-center ">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Forgot Password</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('auth/forgot_password') ?>">
                        <div class="mb-3">
                            <label for="user_key" class="form-label">User Key</label>
                            <input type="text" name="user_key" id="user_key" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>