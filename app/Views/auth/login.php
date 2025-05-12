<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div style="min-height: 75vh" class="container d-flex flex-column justify-content-center align-items-center ">
    <div class="row justify-content-center w-100">
        <div class="col-11 col-md-8 col-lg-6 col-xl-5">
            <div class="card">
                <div class="card-header text-center">
                    <h3>Login<?php if ($role) echo " as " . ucfirst($role); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (session()->has('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= esc(session('error')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="<?=site_url('auth/' . $role . '/login') ?>">
                        <div class="mb-3">
                            <label for="user_key" class="form-label">User Key</label>
                            <input type="text" name="user_key" id="user_key" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="<?=site_url('auth/forgot_password') ?>">Forgot Password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>