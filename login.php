<?php
require_once "db.php";
session_start();

if (isset($_POST['correo']) && isset($_POST['pass'])) {
    $correo = trim($_POST['correo']);
    $pass = $_POST['pass'];

    if ($correo === '' || $pass === '') {
        $_SESSION['error'] = 'datos incompletos';
        header("Location: login.php");
        return;
    }

    if (strpos($correo, '@') === false) {
        $_SESSION['error'] = 'Correo no válido';
        header("Location: login.php");
        return;
    }

    $stmt = $pdo->prepare('SELECT id_usuario, nombre, password FROM usuario WHERE correo = :em');
    $stmt->execute(array(':em' => $correo));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row !== false && password_verify($pass, $row['password'])) {
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['success'] = "Bienvenido " . $row['nombre'];
        header("Location: index.php");
        return;
    }

    $_SESSION['error'] = 'usuario no encontrado, revisar usuario y/o contraseña';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require_once "head.php"; ?>
<title>Ingreso al sistema</title>
</head>
<body class="bg-light">
<?php require_once "nav.php"; ?>
<main class="container py-5">
<div class="row justify-content-center">
<div class="col-md-6 col-lg-5">
<div class="card shadow-sm">
<div class="card-body p-4">
<h1 class="h3 mb-4">Ingrese sus credenciales</h1>
<?php
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . "</div>\n";
    unset($_SESSION['error']);
}
?>
<form method="post">
<div class="mb-3">
<label class="form-label" for="f_correo">Usuario</label>
<input class="form-control" type="text" name="correo" id="f_correo" autocomplete="username">
</div>
<div class="mb-4">
<label class="form-label" for="f_pass">Contraseña</label>
<input class="form-control" type="password" name="pass" id="f_pass" autocomplete="current-password">
</div>
<div class="d-flex gap-2">
<button type="submit" class="btn btn-primary" onclick="return doValidate();">Aceptar</button>
<a class="btn btn-outline-secondary" href="index.php">Cancelar</a>
</div>
</form>
</div>
</div>
</div>
</div>
</main>
<script>
function doValidate() {
    try {
        var pw = document.getElementById('f_pass').value;
        var em = document.getElementById('f_correo').value;
        if (pw == null || pw == "" || em == null || em == "") {
            alert("Debe especificar ambos campos");
            return false;
        }
        return true;
    } catch (e) {
        return false;
    }
}
</script>
</body>
</html>
