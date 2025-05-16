<div class="d-flex flex-column flex-shrink-0 p-3 bg-primary text-white position-fixed" style="width: 280px; height: 100vh; top: 0; left: 0;">
    <a href="<?= site_url('admin') ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <img src="<?= base_url('assets/img/brand_logo/white_on_trans.png') ?>" alt="Logo" height="40" class="me-2">
        <span class="fs-4">SAMS Admin</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item"><a href="<?= site_url('admin') ?>" class="nav-link text-white">Dashboard</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/users') ?>" class="nav-link text-white">Users</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/students') ?>" class="nav-link text-white">Students</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/teachers') ?>" class="nav-link text-white">Teachers</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/classes') ?>" class="nav-link text-white">Classes</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/subjects') ?>" class="nav-link text-white">Subjects</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/enrollment_terms') ?>" class="nav-link text-white">Enrollment Terms</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/student_assignments') ?>" class="nav-link text-white">Student Assignments</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/teacher_assignments') ?>" class="nav-link text-white">Teacher Assignments</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/rooms') ?>" class="nav-link text-white">Rooms</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/trackers') ?>" class="nav-link text-white">Trackers</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/notifications') ?>" class="nav-link text-white">Notifications</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/class_sessions') ?>" class="nav-link text-white">Class Sessions</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/class_session_settings') ?>" class="nav-link text-white">Class Session Settings</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/attendance') ?>" class="nav-link text-white">Attendance</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/leave_requests') ?>" class="nav-link text-white">Leave Requests</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/schedule') ?>" class="nav-link text-white">Schedule</a></li>
        <li class="nav-item"><a href="<?= site_url('admin/profile_settings') ?>" class="nav-link text-white">Profile Settings</a></li>
    </ul>
    <hr>
    <div class="d-flex align-items-center">
        <a href="<?= site_url('auth/logout') ?>" class="text-white text-decoration-none me-3">Logout</a>
        <button class="theme-toggle" aria-label="Toggle theme"></button>
        <span class="theme-toggle-label">Theme</span>
    </div>
</div>