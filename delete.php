<?php
require_once "db.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    die("Not logged in");
}

if (isset($_POST['delete'])) {
    if (!isset($_SESSION['id_alumno_eliminar'])) {
        $_SESSION['error'] = 'Debe seleccionar el registro desde la lista';
        header('Location: index.php');
        return;
    }

    $idAlumno = (int)$_SESSION['id_alumno_eliminar'];
    if (!isset($_POST['id_alumno']) || (int)$_POST['id_alumno'] !== $idAlumno) {
        $_SESSION['error'] = 'El registro a eliminar no coincide con el seleccionado en la lista';
        header('Location: index.php');
        return;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('DELETE FROM alumno_cursos WHERE id_alumno = :id');
        $stmt->execute(array(':id' => $idAlumno));
        $stmt = $pdo->prepare('DELETE FROM alumno WHERE id_alumno = :id');
        $stmt->execute(array(':id' => $idAlumno));
        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = 'No se pudo eliminar el registro';
        header('Location: index.php');
        return;
    }

    unset($_SESSION['id_alumno_eliminar']);
    $_SESSION['success'] = 'Registro eliminado';
    header('Location: index.php');
    return;
}

if (!isset($_GET['id_alumno'])) {
    $_SESSION['error'] = 'no se especificó alumno a eliminar';
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare(
    'SELECT id_alumno, nombres, apellidos, correo FROM alumno WHERE id_alumno = :idx'
);
$stmt->execute(array(':idx' => $_GET['id_alumno']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['error'] = 'Alumno no encontrado';
    header('Location: index.php');
    return;
}

$_SESSION['id_alumno_eliminar'] = (int)$row['id_alumno'];
$nombre = $row['nombres'] . ', ' . $row['apellidos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require_once "head.php"; ?>
<title>Eliminar alumno</title>
</head>
<body class="bg-light">
<?php require_once "nav.php"; ?>
<main class="container py-4">
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card shadow-sm border-danger">
<div class="card-body p-4">
<h1 class="h3 mb-3">Eliminar alumno</h1>
<div class="alert alert-warning">Desea eliminar al alumno: <?php echo htmlspecialchars($nombre); ?></div>
<form method="post">
<p class="mb-1"><span class="text-secondary">Alumno:</span> <?php echo htmlspecialchars($nombre); ?></p>
<p><span class="text-secondary">Correo:</span> <?php echo htmlspecialchars($row['correo']); ?></p>
<input type="hidden" name="id_alumno" value="<?php echo (int)$row['id_alumno']; ?>">
<div class="d-flex gap-2">
<button type="submit" class="btn btn-danger" name="delete" value="Eliminar">Eliminar</button>
<a class="btn btn-outline-secondary" href="index.php">Cancelar</a>
</div>
</form>
</div>
</div>
</div>
</div>
</main>
</body>
</html>
