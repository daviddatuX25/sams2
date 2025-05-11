<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container d-flex justify-content-center align-items-center mt-3">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Forgot Password</h3>
                </div>
                <div class="card-body">
                    <?php if (session()->has('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= esc(session('success')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php elseif (session()->has('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= esc(session('error')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
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