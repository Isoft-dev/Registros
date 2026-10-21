<?php
require_once "db.php";
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require_once "head.php"; ?>
<title>Lista de estudiantes</title>
</head>
<body class="bg-light">
<?php require_once "nav.php"; ?>
<main class="container py-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h1 class="h3 mb-0">Lista de estudiantes</h1>
<?php if (isset($_SESSION['id_usuario'])) { ?>
<a class="btn btn-primary" href="nuevo.php">Nuevo</a>
<?php } ?>
</div>
<?php
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . "</div>\n";
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . "</div>\n";
    unset($_SESSION['success']);
}
?>
<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead class="table-light">
<tr>
<th>Nombres</th>
<th>Apellidos</th>
<th>Correo</th>
<th>Aficiones</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
<?php
$stmt = $pdo->query("SELECT id_alumno, nombres, apellidos, correo, aficiones FROM alumno ORDER BY apellidos, nombres");
$hayFilas = false;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $hayFilas = true;
    $id = (int)$row['id_alumno'];
    echo "<tr><td>";
    echo htmlspecialchars($row['nombres']);
    echo "</td><td>";
    echo htmlspecialchars($row['apellidos']);
    echo "</td><td>";
    echo htmlspecialchars($row['correo']);
    echo "</td><td>";
    echo nl2br(htmlspecialchars($row['aficiones']));
    echo '</td><td class="text-nowrap">';
    echo '<a class="btn btn-outline-secondary btn-sm" href="ver.php?id_alumno=' . $id . '">ver</a>';
    if (isset($_SESSION['id_usuario'])) {
        echo ' <a class="btn btn-outline-primary btn-sm" href="editar_db.php?id_alumno=' . $id . '">editar</a>';
        echo ' <a class="btn btn-outline-danger btn-sm" href="delete.php?id_alumno=' . $id . '">eliminar</a>';
    }
    echo "</td></tr>\n";
}
if (!$hayFilas) {
    echo '<tr><td colspan="5" class="text-secondary">No hay estudiantes registrados.</td></tr>';
}
?>
</tbody>
</table>
</div>
</div>
</main>
</body>
</html>
