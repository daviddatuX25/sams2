<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?=esc($pageTitle)?></title>

    <!-- Custom css for this template-->
    <!-- <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet"> -->

    <!-- Css libraries -->
    <link href="<?=base_url('assets/lib/fontawesome-free/css/all.min.css')?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/lib/sb-admin-2/sb-admin-2.css')?>" rel="stylesheet">
    
    <!-- Bootstrap core JavaScript-->
    <script src="<?= base_url('assets/lib/jquery/jquery.min.js')?>"></script>
    <script src="<?= base_url('assets/lib/bootstrap/js/bootstrap.bundle.min.js')?>"></script>

    <!-- Custom JS -->
    <script src="<?= base_url('assets/js/main.js')?>"></script>

</head>
<body id="page-top">
<div id="notification" class="d-none position-absolute m-3 border rounded shadow-sm"
     style="z-index: 1050; top: 0; right: 0; min-width: 250px;">
        <button style="top: 0.15rem; right: 0.2rem;" type="button" class="close position-absolute" id="closeNotification" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    <p class="mt-3" id="notifMessage"></p>
</div>


<?php 
$notification = session()->getFlashdata('notification');
if (isset($notification)): ?>
    <script>
        showNotification("<?= esc($notification['type']) ?>", "<?= esc($notification['message']) ?>");
    </script>
<?php 
session()->remove('notification');
endif; ?>