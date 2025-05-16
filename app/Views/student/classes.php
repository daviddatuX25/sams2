<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1>My Classes</h1>
    <div class="row">
        <?php foreach ($classes as $class): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= esc($class['class_name']) ?></h5>
                        <p class="card-text">Teacher: <?= esc($class['teacher_first_name'] . ' ' . $class['teacher_last_name']) ?></p>
                        <a href="<?= site_url('student/classes/' . $class['class_id']) ?>" class="btn btn-primary">Explore</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php $this->endSection(); ?>