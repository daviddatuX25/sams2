<?php
// Fetch user data for profile picture
$userModel = new \App\Models\UserModel();
$user = $userModel->find(session()->get('user_id'));
$profilePicture = $user['profile_picture'] ?? base_url('assets/img/profile.png');
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <a class="sidebar-brand" href="<?= site_url('/') ?>">
            <div class="d-flex flex-column align-items-center">
                <img src="<?= base_url('assets/img/brand_logo/white_on_trans.png') ?>" alt="Logo" height="50" class="mb-2">
                <span class="fw-bold">Admin Portal</span>
            </div>
        </a>
        <button class="sidebar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
    <div class="sidebar-collapse collapse show" id="adminSidebar">
        <ul class="sidebar-nav">
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'dashboard' ? 'active' : '') ?>" href="<?= site_url('admin') ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'users' ? 'active' : '') ?>" href="<?= site_url('admin/users') ?>">Users</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'subjects' ? 'active' : '') ?>" href="<?= site_url('admin/subjects') ?>">Subjects</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'classes' ? 'active' : '') ?>" href="<?= site_url('admin/classes') ?>">Classes</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'enrollment-terms' ? 'active' : '') ?>" href="<?= site_url('admin/enrollment-terms') ?>">Enrollment Terms</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'student-assignments' ? 'active' : '') ?>" href="<?= site_url('admin/student-assignments') ?>">Student Assignments</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'teacher-assignments' ? 'active' : '') ?>" href="<?= site_url('admin/teacher-assignments') ?>">Teacher Assignments</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'rooms' ? 'active' : '') ?>" href="<?= site_url('admin/rooms') ?>">Rooms</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'trackers' ? 'active' : '') ?>" href="<?= site_url('admin/trackers') ?>">Trackers</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'notifications' ? 'active' : '') ?>" href="<?= site_url('admin/notifications') ?>">Notifications</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'class-sessions' ? 'active' : '') ?>" href="<?= site_url('admin/class-sessions') ?>">Class Sessions</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'class-session-settings' ? 'active' : '') ?>" href="<?= site_url('admin/class-session-settings') ?>">Class Session Settings</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'attendance' ? 'active' : '') ?>" href="<?= site_url('admin/attendance') ?>">Attendance</a></li>
            <li class="nav-item"><a class="nav-link <?= esc($currentSegment === 'tracker-logs' ? 'active' : '') ?>" href="<?= site_url('admin/tracker-logs') ?>">Tracker Logs</a></li>
        </ul>
        <ul class="sidebar-nav sidebar-footer">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= esc($profilePicture) ?>" alt="Profile Picture" class="rounded-circle" width="40" height="40">
                </a>
                <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="<?= site_url('user/profile') ?>">View Profile</a></li>
                    <li><a class="dropdown-item" href="<?= site_url('user/logout') ?>">Logout</a></li>
                </ul>
            </li>
            <li class="nav-item d-flex align-items-center">
                <button class="theme-toggle" aria-label="Toggle theme"></button>
                <span class="theme-toggle-label">Theme</span>
            </li>
            <!-- Notification Dropdown -->
            <?= $this->include('layouts/partials/user_notifications') ?>
        </ul>
    </div>
</aside>