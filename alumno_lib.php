<?php

function textoLargo($texto, $maximo) {
    return mb_strlen($texto, 'UTF-8') > $maximo;
}

function validarAlumno($nombres, $apellidos, $correo, $aficiones) {
    if ($nombres === '' || $apellidos === '' || $correo === '' || $aficiones === '') {
        return 'Datos incompletos';
    }
    if (strpos($correo, '@') === false) {
        return 'Correo electrónico no válido';
    }
    if (textoLargo($nombres, 50) || textoLargo($apellidos, 50)) {
        return 'Nombre o apellido demasiado largo';
    }
    if (textoLargo($correo, 30)) {
        return 'Correo demasiado largo';
    }
    if (textoLargo($aficiones, 100)) {
        return 'Aficiones demasiado largas';
    }
    return null;
}

function validarCursos($anios, $nombres) {
    if (!is_array($anios) || !is_array($nombres)) {
        return 'Cursos inválidos';
    }
    $total = max(count($anios), count($nombres));
    for ($i = 0; $i < $total; $i++) {
        $anio = isset($anios[$i]) ? trim($anios[$i]) : '';
        $nombre = isset($nombres[$i]) ? trim($nombres[$i]) : '';
        if ($anio === '' && $nombre === '') {
            continue;
        }
        if ($anio === '' || $nombre === '') {
            return 'Todos los campos de curso son requeridos';
        }
        if (!ctype_digit($anio) || (int)$anio < 1900 || (int)$anio > 2100) {
            return 'Año debe ser numérico entre 1900 y 2100';
        }
        if (textoLargo($nombre, 50)) {
            return 'El nombre del curso supera 50 caracteres';
        }
    }
    return null;
}

function guardarCursosAlumno($pdo, $idAlumno, $anios, $nombres) {
    $busca = $pdo->prepare('SELECT id_curso FROM cursos WHERE nombre = :nom');
    $insertaCurso = $pdo->prepare('INSERT INTO cursos (nombre) VALUES (:nom)');
    $insertaRelacion = $pdo->prepare(
        'INSERT INTO alumno_cursos (id_alumno, anio, id_curso) VALUES (:ide, :anio, :idc)'
    );
    $vistos = array();
    $total = max(count($anios), count($nombres));

    for ($i = 0; $i < $total; $i++) {
        $anio = isset($anios[$i]) ? trim($anios[$i]) : '';
        $nombre = isset($nombres[$i]) ? trim($nombres[$i]) : '';
        if ($anio === '' && $nombre === '') {
            continue;
        }
        $clave = $anio . '|' . strtolower($nombre);
        if (isset($vistos[$clave])) {
            continue;
        }
        $vistos[$clave] = true;

        $busca->execute(array(':nom' => $nombre));
        $row = $busca->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            $idCurso = $row['id_curso'];
        } else {
            $insertaCurso->execute(array(':nom' => $nombre));
            $idCurso = $pdo->lastInsertId();
        }

        $insertaRelacion->execute(array(
            ':ide' => $idAlumno,
            ':anio' => $anio,
            ':idc' => $idCurso
        ));
    }
}

function htmlFilaCurso($indice, $anio, $nombre) {
    $id = 'curso' . (int)$indice;
    $anio = htmlspecialchars($anio, ENT_QUOTES, 'UTF-8');
    $nombre = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');

    return '<div class="border rounded p-3 mb-3 bg-light" id="' . $id . '">'
        . '<div class="row g-2 align-items-end">'
        . '<div class="col-sm-3"><label class="form-label">Año</label>'
        . '<input class="form-control" type="text" name="cur_anio[]" value="' . $anio . '"></div>'
        . '<div class="col-sm-7"><label class="form-label">Nombre</label>'
        . '<input class="form-control cursos" type="text" name="cur_nombre[]" value="' . $nombre . '"></div>'
        . '<div class="col-sm-2"><button type="button" class="btn btn-outline-danger w-100" '
        . 'onclick="$(\'#' . $id . '\').remove();return false;">Quitar</button></div>'
        . '</div></div>';
}
