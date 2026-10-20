<?php
require_once "db.php";
require_once "alumno_lib.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    die("Ingresar al sistema");
}

if (isset($_POST['nombres'])
    && isset($_POST['apellidos'])
    && isset($_POST['correo'])
    && isset($_POST['aficiones'])) {

    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $aficiones = trim($_POST['aficiones']);
    $anios = isset($_POST['cur_anio']) ? $_POST['cur_anio'] : array();
    $cursos = isset($_POST['cur_nombre']) ? $_POST['cur_nombre'] : array();

    $error = validarAlumno($nombres, $apellidos, $correo, $aficiones);
    if ($error === null) {
        $error = validarCursos($anios, $cursos);
    }

    if ($error !== null) {
        $_SESSION['error'] = $error;
        header("Location: nuevo.php");
        return;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'INSERT INTO alumno (id_usuario, nombres, apellidos, correo, aficiones)
             VALUES (:ide, :nom, :ape, :cor, :afi)'
        );
        $stmt->execute(array(
            ':ide' => $_SESSION['id_usuario'],
            ':nom' => $nombres,
            ':ape' => $apellidos,
            ':cor' => $correo,
            ':afi' => $aficiones
        ));
        $idAlumno = $pdo->lastInsertId();
        guardarCursosAlumno($pdo, $idAlumno, $anios, $cursos);
        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = 'No se pudo guardar el registro';
        header("Location: nuevo.php");
        return;
    }

    $_SESSION['success'] = 'Registro agregado';
    header('Location: index.php');
    return;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require_once "head.php"; ?>
<title>Ingreso de nuevo estudiante</title>
</head>
<body class="bg-light">
<?php require_once "nav.php"; ?>
<main class="container py-4">
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card shadow-sm">
<div class="card-body p-4">
<h1 class="h3 mb-4">Ingreso de nuevo estudiante</h1>
<?php
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . "</div>\n";
    unset($_SESSION['error']);
}
?>
<form method="post">
<div class="mb-3">
<label class="form-label" for="fn">Nombres</label>
<input class="form-control" type="text" name="nombres" id="fn">
</div>
<div class="mb-3">
<label class="form-label" for="ln">Apellidos</label>
<input class="form-control" type="text" name="apellidos" id="ln">
</div>
<div class="mb-3">
<label class="form-label" for="em">Correo</label>
<input class="form-control" type="text" name="correo" id="em">
</div>
<div class="mb-3">
<label class="form-label" for="afi">Aficiones</label>
<textarea class="form-control" name="aficiones" rows="4" id="afi"></textarea>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
<h2 class="h6 mb-0">Cursos y certificaciones IT</h2>
<button type="button" class="btn btn-outline-primary" id="addCurso">+</button>
</div>
<div id="curso_fields"></div>
<div class="d-flex gap-2 mt-4">
<button type="submit" class="btn btn-primary" onclick="return doValidate();">Grabar</button>
<a class="btn btn-outline-secondary" href="index.php">Cancelar</a>
</div>
</form>
</div>
</div>
</div>
</div>
</main>
<script>
var cuentaCur = 0;
</script>
<script src="cursos_form.js"></script>
</body>
</html>
