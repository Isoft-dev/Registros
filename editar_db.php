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

    if (!isset($_SESSION['id_alumno_editar'])) {
        $_SESSION['error'] = 'Debe seleccionar el registro desde la lista';
        header('Location: index.php');
        return;
    }

    $idAlumno = (int)$_SESSION['id_alumno_editar'];
    if (!isset($_POST['id_alumno']) || (int)$_POST['id_alumno'] !== $idAlumno) {
        $_SESSION['error'] = 'El registro a editar no coincide con el seleccionado en la lista';
        header('Location: index.php');
        return;
    }

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
        header('Location: editar_db.php?id_alumno=' . $idAlumno);
        return;
    }

    $existe = $pdo->prepare('SELECT id_alumno FROM alumno WHERE id_alumno = :id');
    $existe->execute(array(':id' => $idAlumno));
    if ($existe->fetch(PDO::FETCH_ASSOC) === false) {
        $_SESSION['error'] = 'Alumno no encontrado';
        header('Location: index.php');
        return;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'UPDATE alumno
             SET nombres = :nom, apellidos = :ape, correo = :cor, aficiones = :afi
             WHERE id_alumno = :id'
        );
        $stmt->execute(array(
            ':nom' => $nombres,
            ':ape' => $apellidos,
            ':cor' => $correo,
            ':afi' => $aficiones,
            ':id' => $idAlumno
        ));
        $borra = $pdo->prepare('DELETE FROM alumno_cursos WHERE id_alumno = :id');
        $borra->execute(array(':id' => $idAlumno));
        guardarCursosAlumno($pdo, $idAlumno, $anios, $cursos);
        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = 'No se pudo actualizar el registro';
        header('Location: editar_db.php?id_alumno=' . $idAlumno);
        return;
    }

    unset($_SESSION['id_alumno_editar']);
    $_SESSION['success'] = 'Registro actualizado';
    header('Location: index.php');
    return;
}

if (!isset($_GET['id_alumno'])) {
    $_SESSION['error'] = 'Alumno no especificado';
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare('SELECT * FROM alumno WHERE id_alumno = :idx');
$stmt->execute(array(':idx' => $_GET['id_alumno']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row === false) {
    $_SESSION['error'] = 'Alumno no encontrado';
    header('Location: index.php');
    return;
}

$_SESSION['id_alumno_editar'] = (int)$row['id_alumno'];

$cursosStmt = $pdo->prepare(
    'SELECT ac.anio, c.nombre
     FROM alumno_cursos ac
     INNER JOIN cursos c ON c.id_curso = ac.id_curso
     WHERE ac.id_alumno = :idx
     ORDER BY ac.anio, c.nombre'
);
$cursosStmt->execute(array(':idx' => $row['id_alumno']));
$cursos = $cursosStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require_once "head.php"; ?>
<title>Editar estudiante</title>
</head>
<body class="bg-light">
<?php require_once "nav.php"; ?>
<main class="container py-4">
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card shadow-sm">
<div class="card-body p-4">
<h1 class="h3 mb-4">Editar estudiante</h1>
<?php
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . "</div>\n";
    unset($_SESSION['error']);
}
?>
<form method="post">
<input type="hidden" name="id_alumno" value="<?php echo (int)$row['id_alumno']; ?>">
<div class="mb-3">
<label class="form-label" for="fn">Nombres</label>
<input class="form-control" type="text" name="nombres" id="fn" value="<?php echo htmlspecialchars($row['nombres']); ?>">
</div>
<div class="mb-3">
<label class="form-label" for="ln">Apellidos</label>
<input class="form-control" type="text" name="apellidos" id="ln" value="<?php echo htmlspecialchars($row['apellidos']); ?>">
</div>
<div class="mb-3">
<label class="form-label" for="em">Correo</label>
<input class="form-control" type="text" name="correo" id="em" value="<?php echo htmlspecialchars($row['correo']); ?>">
</div>
<div class="mb-3">
<label class="form-label" for="afi">Aficiones</label>
<textarea class="form-control" name="aficiones" rows="4" id="afi"><?php echo htmlspecialchars($row['aficiones']); ?></textarea>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
<h2 class="h6 mb-0">Cursos y certificaciones IT</h2>
<button type="button" class="btn btn-outline-primary" id="addCurso">+</button>
</div>
<div id="curso_fields">
<?php
$indice = 0;
foreach ($cursos as $curso) {
    $indice++;
    echo htmlFilaCurso($indice, $curso['anio'], $curso['nombre']);
}
?>
</div>
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
var cuentaCur = <?php echo count($cursos); ?>;
</script>
<script src="cursos_form.js"></script>
</body>
</html>
