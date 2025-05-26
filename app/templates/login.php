<?php
if (isset($_SESSION['peliculas'])) {
  header('Cache-control: no-cache; must-revalidate');
  header('location: ?mod=inicio');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="resources/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="resources/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="resources/dist/css/adminlte.min.css">
    <!-- jQuery -->
    <script src="resources/plugins/jquery/jquery.min.js"></script>
    <script src="resources/dist/js/md5.js"></script>
    <link rel="stylesheet" href="resources/dist/css/style.css">
</head>

<body class="hold-transition login-page">
  <div class="m-3">
    <?php
    @include(MODULO_PATH . "/" . $conf[$modulo]['archivo']);
    ?>
  </div>


  <!-- Bootstrap 4 -->
  <script src="resources/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="resources/dist/js/adminlte.min.js"></script>
  <!--sweetalert-->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
</body>

</html>