<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">My Classes</h1>
    </div>
    <?php if (empty($classes)): ?>
        <div class="col-12">
            <p>No classes assigned.</p>
        </div>
    <?php else: ?>
        <?php foreach ($classes as $class): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo esc($class['class_name']); ?> (<?php echo esc($class['section']); ?>)</h5>
                        <p class="card-text"><?php echo esc($class['subject_name']); ?></p>
                        <a href="<?= site_url('teacher/classes/' . $class['class_id']) ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>