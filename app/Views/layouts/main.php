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
    <?php
        echo $this->include('layouts/components/notification_popup');
        //Navbar
        $navbar = isset($navbar) ? $navbar : 'home';
        $navbarPath = "layouts/partials/{$navbar}Nav";
        if (file_exists(APPPATH . "Views/{$navbarPath}.php")) {
            echo $this->include($navbarPath);
        } else {
            echo $this->include('layouts/partials/homeNav');
        }
    ?>

    <main class="container mt-3">
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