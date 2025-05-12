<?php
// Check for flashdata messages
$success = session()->getFlashdata('success_notification');
$error = session()->getFlashdata('error_notification');
$info = session()->getFlashdata('info_notification');
?>

<?php if ($success || $error || $info): ?>
<div class="notification-popup position-fixed top-0 end-0 m-3" style="z-index: 1050; max-width: 300px;">
    <?php if ($success): ?>
    <div class="card alert alert-success fade show" role="alert">
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        <i class="fas fa-check-circle me-2"></i>
        <span><?= esc($success) ?></span>
        
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="card alert alert-danger fade show" role="alert">
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        <i class="fas fa-exclamation-triangle me-2"></i>
        <span><?= esc($error) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($info): ?>
    <div class="card alert alert-primary fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <span><?= esc($info) ?></span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>


<style>
.notification-popup .card {
    background-color: var(--bs-secondary);
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    margin-bottom: 0.5rem;
}

.notification-popup .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.notification-popup .alert-success {
    background-color: var(--bs-success);
    color: var(--bs-body-bg);
}

.notification-popup .alert-danger {
    background-color: var(--bs-danger);
}
.notification-popup .alert-danger span{
    color: white;
}

.notification-popup .alert-primary {
    background-color: var(--bs-primary);
    color: var(--bs-body-bg);
}

.notification-popup .card-body {
    padding: 0.75rem;
    font-size: 0.9rem;
}

.notification-popup .btn-close {
    filter: invert(1); /* Ensures visibility on dark backgrounds */
}
</style>

<script>
$(document).ready(function() {
    // Auto-destroy notifications after 5 seconds
    setTimeout(function() {
        $('.notification-popup .alert').alert('close');
    }, 5000);
});
</script>