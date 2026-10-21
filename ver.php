<?php
require_once "db.php";
session_start();

if (!isset($_GET['id_alumno'])) {
    $_SESSION['error'] = "Alumno no especificado";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT * FROM alumno WHERE id_alumno = :idx");
$stmt->execute(array(":idx" => $_GET['id_alumno']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['error'] = 'Alumno no encontrado';
    header('Location: index.php');
    return;
}

$cursosStmt = $pdo->prepare(
    "SELECT ac.anio, c.nombre
     FROM alumno_cursos ac
     INNER JOIN cursos c ON c.id_curso = ac.id_curso
     WHERE ac.id_alumno = :idx
     ORDER BY ac.anio, c.nombre"
);
$cursosStmt->execute(array(":idx" => $_GET['id_alumno']));
$cursos = $cursosStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require_once "head.php"; ?>
<title>Consulta de Alumno</title>
</head>
<body class="bg-light">
<?php require_once "nav.php"; ?>
<main class="container py-4">
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card shadow-sm">
<div class="card-body p-4">
<h1 class="h3 mb-4">Consulta de Alumno</h1>
<?php
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . "</div>\n";
    unset($_SESSION['error']);
}
?>
<dl class="row mb-4">
<dt class="col-sm-3">Nombres</dt>
<dd class="col-sm-9"><?php echo htmlspecialchars($row['nombres']); ?></dd>
<dt class="col-sm-3">Apellidos</dt>
<dd class="col-sm-9"><?php echo htmlspecialchars($row['apellidos']); ?></dd>
<dt class="col-sm-3">Correo</dt>
<dd class="col-sm-9"><?php echo htmlspecialchars($row['correo']); ?></dd>
<dt class="col-sm-3">Aficiones</dt>
<dd class="col-sm-9"><?php echo nl2br(htmlspecialchars($row['aficiones'])); ?></dd>
</dl>
<h2 class="h6">Cursos y certificaciones</h2>
<ul class="list-group mb-4">
<?php
if (count($cursos) === 0) {
    echo '<li class="list-group-item text-secondary">Sin cursos registrados.</li>';
}
foreach ($cursos as $curso) {
    echo '<li class="list-group-item">' . htmlspecialchars($curso['anio']) . ' — ' . htmlspecialchars($curso['nombre']) . '</li>';
}
?>
</ul>
<a class="btn btn-outline-secondary" href="index.php">Regresar</a>
</div>
</div>
</div>
</div>
</main>
</body>
</html>
