<?php
// Fetch user data for profile picture
$userModel = new \App\Models\UserModel();
$user = $userModel->find(session()->get('user_id'));
$profilePicture = $user['profile_picture'] ?? base_url('assets/img/default_avatar.png');
?>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('/') ?>">
            <div class="d-flex flex-column">
                <img src="<?= base_url('assets/img/brand_logo/white_on_trans.png') ?>" alt="Logo" height="50" class="me-2">
                <span class="fw-bold">Teacher Portal</span>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#teacherNav" aria-controls="teacherNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse ml-5" id="teacherNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentSegment === '' ? 'active' : ''; ?>" href="<?= site_url('teacher') ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentSegment === 'classes' ? 'active' : ''; ?>" href="<?= site_url('teacher/classes') ?>">
                        Classes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentSegment === 'leave_requests' ? 'active' : ''; ?>" href="<?= site_url('teacher/leave_requests') ?>">
                        Leave Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentSegment === 'schedule' ? 'active' : ''; ?>" href="<?= site_url('teacher/schedule') ?>">
                        Schedule
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentSegment === 'reports' ? 'active' : ''; ?>" href="<?= site_url('teacher/reports') ?>">
                        Reports
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown mt-2">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= site_url(session()->get('profile_picture') ?? 'assets/img/profile.png') ?>" alt="Profile Picture" class="rounded-circle" width="40" height="40">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="<?= site_url('user/profile') ?>">View Profile</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('user/logout') ?>">Logout</a></li>
                    </ul>
                </li>
                <li class="nav-item d-flex align-items-center ms-2 ms-sm-0">
                    <button class="theme-toggle" aria-label="Toggle theme"></button>
                    <span class="theme-toggle-label">Theme</span>
                </li>
                <!-- Notification Dropdown -->
                <?= $this->include('layouts/partials/user_notifications') ?>
            </ul>
        </div>
    </div>
</nav>

<style>
    .navbar .rounded-circle {
        border: 2px solid #3A98B9;
        object-fit: cover;
    }
    .navbar .rounded-circle:hover {
        border-color: #2a7a94;
    }
    .dropdown-menu {
        background-color: var(--bs-body-bg);
        border-color: #3A98B9;
    }
    .dropdown-item:hover {
        background-color: #3A98B9;
        color: #FFFFFF;
    }
</style>