<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">Estudiantes</a>
    <div class="d-flex align-items-center gap-3">
<?php if (isset($_SESSION['id_usuario'])) { ?>
      <span class="navbar-text"><?php echo htmlspecialchars(isset($_SESSION['nombre']) ? $_SESSION['nombre'] : ''); ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Salir</a>
<?php } else { ?>
      <a class="btn btn-outline-light btn-sm" href="login.php">Ingresar (log in)</a>
<?php } ?>
    </div>
  </div>
</nav>
