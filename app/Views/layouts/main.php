<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
    <style>
        /* Full-screen layout */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .navbar {
            flex-shrink: 0; /* Prevent navbar from shrinking */
            background-color: var(--bs-primary) !important;
        }

        .footer {
            flex-shrink: 0; /* Prevent footer from shrinking */
            background-color: var(--bs-primary);
            color: var(--bs-body-bg);
        }

        /* Custom theming for Bootstrap */
        html {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Light Theme Variables */
        html[data-bs-theme="light"] {
            --bs-primary: #3A98B9;
            --bs-primary-rgb: 58, 152, 185;
            --bs-secondary: #E8D5C4;
            --bs-secondary-rgb: 232, 213, 196;
            --bs-body-bg: #FFF1DC;
            --bs-body-bg-rgb: 255, 241, 220;
            --bs-body-color: #2D3748;
            --bs-body-color-rgb: 45, 55, 72;
            --bs-link-color: #3A98B9;
            --bs-link-hover-color: #2B7A96;
            --bs-success: #10B981;
            --bs-success-rgb: 16, 185, 129;
            --bs-danger: #EF4444;
            --bs-danger-rgb: 239, 68, 68;
        }

        /* Dark Theme Variables */
        html[data-bs-theme="dark"] {
            --bs-primary: #0B2447;
            --bs-primary-rgb: 11, 36, 71;
            --bs-secondary:rgb(83, 100, 170);
            --bs-secondary-rgb: 87, 108, 188;
            --bs-body-bg: #19376D;
            --bs-body-bg-rgb: 25, 55, 109;
            --bs-body-color: #A5D7E8;
            --bs-body-color-rgb: 165, 215, 232;
            --bs-link-color: #576CBC;
            --bs-link-hover-color: #3E5196;
            --bs-success: #34D399;
            --bs-success-rgb: 52, 211, 153;
            --bs-danger: #B91C1C;
            --bs-danger-rgb: 185, 28, 28;
        }

        /* Links */
        a {
            color: var(--bs-link-color);
            text-decoration: none;
        }

        a:hover {
            color: var(--bs-link-hover-color);
            text-decoration: underline;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: var(--bs-body-bg);
        }

        .navbar-nav .nav-link:hover {
            color: var(--bs-link-hover-color);
        }

        /* Card */
        .card {
            background-color: var(--bs-secondary);
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: transparent;
            border-bottom: none;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--bs-body-color);
        }

        .card-body {
            color: var(--bs-body-color);
        }

        .card-body p, .card-body a, .card-body h1, .card-body h2, .card-body h3, .card-body h4, .card-body h5, .card-body h6 {
            color: var(--bs-body-color) !important;
        }

        .card-body a:hover {
            color: var(--bs-link-hover-color) !important;
        }

        /* Button */
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            color: var(--bs-body-color);
        }

        .btn-primary:hover {
            background-color: var(--bs-link-hover-color);
            border-color: var(--bs-link-hover-color);
            color: var(--bs-body-bg);
        }

        /* Form */
        .form-control {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            border-color: var(--bs-secondary);
        }

        .form-control:focus {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
        }

        .form-label {
            color: var(--bs-body-color);
        }

        /* Alert */
        .alert-success {
            background-color: var(--bs-success);
            color: var(--bs-body-bg);
        }

        .alert-danger {
            background-color: var(--bs-danger);
            color: #FFFFFF;
        }

        /* Badge */
        .badge-primary {
            background-color: var(--bs-primary);
            color: var(--bs-body-bg);
        }

        .badge-success {
            background-color: var(--bs-success);
            color: var(--bs-body-bg);
        }

        /* Progress */
        .progress {
            background-color: var(--bs-secondary);
        }

        .progress-bar {
            background-color: var(--bs-primary);
        }

        /* Modal */
        .modal-content {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        .modal-header,
        .modal-footer {
            border-color: var(--bs-secondary);
        }

        /* Dropdown */
        .dropdown-menu {
            background-color: var(--bs-secondary);
            color: var(--bs-body-color);
        }

        .dropdown-item {
            color: var(--bs-body-color);
        }

        .dropdown-item:hover {
            background-color: var(--bs-primary);
            color: var(--bs-body-bg);
        }

        /* Pagination */
        .page-link {
            background-color: var(--bs-secondary);
            color: var(--bs-body-color);
            border-color: var(--bs-secondary);
        }

        .page-link:hover {
            background-color: var(--bs-primary);
            color: var(--bs-body-bg);
            border-color: var(--bs-primary);
        }

        .page-item.active .page-link {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            color: var(--bs-body-bg);
        }

        /* Tooltip */
        .tooltip-inner {
            background-color: var(--bs-primary);
            color: var(--bs-body-bg);
        }

        .tooltip.bs-tooltip-top .tooltip-arrow::before {
            border-top-color: var(--bs-primary);
        }

        /* List Group */
        .list-group-item {
            background-color: var(--bs-secondary);
            color: var(--bs-body-color);
            border-color: var(--bs-body-bg);
        }

        .list-group-item-action:hover {
            background-color: var(--bs-primary);
            color: var(--bs-body-bg);
        }

        /* Breadcrumb */
        .breadcrumb {
            background-color: var(--bs-secondary);
        }

        .breadcrumb-item a {
            color: var(--bs-link-color);
        }

        .breadcrumb-item a:hover {
            color: var(--bs-link-hover-color);
        }

        .breadcrumb-item.active {
            color: var(--bs-body-color);
        }

        /* Accordion */
        .accordion-item {
            background-color: var(--bs-secondary);
            color: var(--bs-body-color);
            border-color: var(--bs-body-bg);
        }

        .accordion-button {
            background-color: var(--bs-secondary);
            color: var(--bs-body-color);
        }

        .accordion-button:not(.collapsed) {
            background-color: var(--bs-primary);
            color: var(--bs-body-bg);
        }

        /* Tabs */
        .nav-tabs .nav-link {
            color: var(--bs-body-color);
            background-color: var(--bs-secondary);
        }

        .nav-tabs .nav-link.active {
            background-color: var(--bs-primary);
            color: var(--bs-body-bg);
        }

        /* Error popup (alert) */
        .alert {
            z-index: 1050;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            position: relative;
            width: 60px;
            height: 34px;
            background-color: var(--bs-secondary);
            border-radius: 34px;
            border: 2px solid var(--bs-primary);
            cursor: pointer;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .theme-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
        }

        .theme-toggle::before {
            content: '';
            position: absolute;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background-color: var(--bs-primary);
            top: 2px;
            left: 2px;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        html[data-bs-theme="dark"] .theme-toggle::before {
            transform: translateX(26px);
            background-color: var(--bs-body-color);
        }

        .theme-toggle::after {
            content: '☀️';
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            font-size: 16px;
            color: var(--bs-body-color);
            transition: opacity 0.3s ease;
        }

        html[data-bs-theme="dark"] .theme-toggle::after {
            content: '🌙';
            left: auto;
            right: 10px;
        }

        .theme-toggle-label {
            margin-left: 10px;
            color: var(--bs-body-color);
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <?php
        // Dynamically include navbar based on $navbar variable
        $navbar = isset($navbar) ? $navbar : 'home';
        $navbarPath = "layouts/partials/{$navbar}Nav";
        if (file_exists(APPPATH . "Views/{$navbarPath}.php")) {
            echo $this->include($navbarPath);
        } else {
            echo $this->include('layouts/partials/homeNav');
        }
    ?>

    <main class="container h-100">
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