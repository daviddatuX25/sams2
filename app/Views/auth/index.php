<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div style="min-height: 75vh" class=" container d-flex justify-content-center align-items-center">
    <div class="row justify-content-center w-100">
        <div class="col-12 text-center mb-4">
            <h1>Choose Your Role</h1>
        </div>
        <div class="col-md-4">
            <a href="<?=site_url('auth/student')?>" class="btn btn-primary d-block text-center mb-3">Student</a>
        </div>
        <div class="col-md-4">
            <a href="<?=site_url('auth/teacher')?>" class="btn btn-primary d-block text-center mb-3">Teacher</a>
        </div>
        <div class="col-md-4">
            <a href="<?=site_url('auth/admin')?>" class="btn btn-primary d-block text-center mb-3">Admin</a>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>