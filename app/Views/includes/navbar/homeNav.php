<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?=site_url('')?>">
      <img src="<?=base_url('assets/img/brand_logo/white_on_trans.png')?>" alt="Brand Logo" width="150" height="50" class="d-inline-block align-top">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#homeNav" aria-controls="homeNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="homeNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= esc($activePage == "index" ? "active" : '')?>" href="<?=site_url('index')?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= esc($activePage == "project" ? "active" : '')?>" href="<?=site_url('project')?>">The Project</a>
        </li>
        <li class="nav-item ">
          <a class="nav-link <?= esc($activePage == "creator" ? "active" : '')?>" href="<?=site_url('creators')?>">Creators</a>
        </li>
      </ul>
        <nav class="navbar-nav my-1 ml-auto">
            <a class="btn btn-outline-light m-1 <?= esc($activePage == "portal" ? "bg-light text-dark" : '') ?>" href="<?=site_url('portal')?>">Enter School Portal</a>
        </nav>
    </div>
  </div>
</nav>