<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div style="min-height: 75vh" class="container d-flex flex-column justify-content-center align-items-center">
    <div class="row justify-content-center w-100">
        <div class="col-12 text-center mb-4">
            <h1><?= ucfirst($role) ?> Authentication</h1>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?=site_url('auth/' . $role .'/login')?>" class="btn btn-primary d-block text-center mb-3">Login as <?= ucfirst($role) ?></a>
        </div>
        <?php if ($role === 'student'): ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?=site_url('auth/' . $role .'/register')?>" class="btn btn-primary d-block text-center mb-3">Register as <?= ucfirst($role) ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $this->endSection(); ?>