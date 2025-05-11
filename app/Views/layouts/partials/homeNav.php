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
                <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= site_url('project') ?>">The Project</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= site_url('auth') ?>">Enter School Portal</a></li>
                <li class="nav-item d-flex align-items-center">
                    <button class="theme-toggle" aria-label="Toggle theme"></button>
                    <span class="theme-toggle-label">Theme</span>
                </li>
            </ul>
        </div>
    </div>
</nav>