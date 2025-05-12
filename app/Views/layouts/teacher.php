
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= base_url('/assets/css/styles.css') ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('/') ?>">
            <img src="<?= base_url('assets/img/brand_logo/white_on_trans.png') ?>" alt="Logo" height="80" class="me-2">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= site_url('teacher') ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('teacher/classes') ?>">Classes</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('teacher/leave-requests') ?>">Leave Requests</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('teacher/schedule') ?>">Schedule</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('teacher/reports') ?>">Reports</a></li>
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

<?php if (session()->has('error')): ?>
    <div id="error-popup" class="alert alert-danger alert-dismissible fade show fixed-top m-3" role="alert">
        <?= esc(session('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <script>
            setTimeout(() => {
                document.getElementById('error-popup').remove();
            }, 5000);
        </script>
    <?php endif; ?>
<?php if (session()->has('success')): ?>
    <div id="success-popup" class="alert alert-success alert-dismissible fade show fixed-top m-3" role="alert">
        <?= esc(session('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <script>
            setTimeout(() => {
                document.getElementById('success-popup').remove();
            }, 5000);
        </script>
    <?php endif; ?>

<div class="container-fluid">
    <div class="row">
        <?php echo view('layouts/partials/teacherNav'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

<footer class="text-white text-center py-3">
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" height="20" class="me-2">
    <span>© 2025 SAMS. All rights reserved.</span>
</footer>

<script>
    $(document).ready(function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        $('html').attr('data-bs-theme', savedTheme);

        $('.theme-toggle').on('click', function() {
            const currentTheme = $('html').attr('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            $('html').attr('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        $('.theme-toggle').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });
    });
</script>
</body>
</html>