<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management System - Admin</title>
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
    <!-- Datatables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script defer src="<?= base_url('assets/js/main.js') ?>"></script>
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= base_url('/assets/css/styles.css') ?>">
</head>
<body>
    <?php
        echo $this->include('layouts/components/notification_popup');
        // Sidebar for admin
        echo $this->include('layouts/partials/navbars/adminNav');
    ?>

    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="text-white text-center py-3">
        <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" height="20" class="me-2">
        <span>© 2025 SAMS. All rights reserved.</span>
    </footer>

    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('#error-popup').alert('close');
            }, 3000);
            // Load saved theme from localStorage
            const savedTheme = localStorage.getItem('theme') || 'light';
            $('html').attr('data-bs-theme', savedTheme);

            // Theme toggle button click handler
            $('.theme-toggle').on('click', function() {
                const currentTheme = $('html').attr('data-bs-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                $('html').attr('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });

            // Accessibility: Allow toggling with Enter or Space key
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