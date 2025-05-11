<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('student') ?>">
            <img src="<?= base_url('assets/img/brand_logo/white_on_trans.png') ?>" alt="Logo" height="80" class="me-2">
            SAMS Student
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav" aria-controls="studentNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="studentNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= site_url('student') ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('student/classes') ?>">My Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('student/attendance') ?>">Attendance Logs</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('student/schedule') ?>">Schedule</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('student/profile') ?>">Profile</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= site_url('auth/logout') ?>">Logout</a></li>
                <li class="nav-item d-flex align-items-center">
                    <button class="theme-toggle" aria-label="Toggle theme"></button>
                    <span class="theme-toggle-label">Theme</span>
                </li>
            </ul>
        </div>
    </div>
</nav>