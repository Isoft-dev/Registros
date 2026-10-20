var cuentaCur = (typeof cuentaCur === 'number') ? cuentaCur : 0;

function doValidate() {
    try {
        var fn = document.getElementById('fn').value.trim();
        var ln = document.getElementById('ln').value.trim();
        var em = document.getElementById('em').value.trim();
        var afi = document.getElementById('afi').value.trim();
        if (fn === '' || ln === '' || em === '' || afi === '') {
            alert('Todos los campos son requeridos');
            return false;
        }
        var filas = document.querySelectorAll('#curso_fields > div');
        for (var i = 0; i < filas.length; i++) {
            var anio = filas[i].querySelector('input[name="cur_anio[]"]').value.trim();
            var nombre = filas[i].querySelector('input[name="cur_nombre[]"]').value.trim();
            if (anio === '' || nombre === '') {
                alert('Todos los campos de curso son requeridos');
                return false;
            }
            if (!/^\d{4}$/.test(anio)) {
                alert('Año debe ser numérico de 4 dígitos');
                return false;
            }
        }
        return true;
    } catch (e) {
        return false;
    }
}

$(document).ready(function () {
    function activarAutocomplete() {
        $('.cursos').each(function () {
            if (!$(this).hasClass('ui-autocomplete-input')) {
                $(this).autocomplete({ source: 'cursos.php' });
            }
        });
    }

    activarAutocomplete();

    $('#addCurso').click(function (event) {
        event.preventDefault();
        if ($('#curso_fields > div').length >= 9) {
            alert('Número máximo de cursos ingresados');
            return;
        }
        cuentaCur++;
        $('#curso_fields').append(
            '<div class="border rounded p-3 mb-3 bg-light" id="curso' + cuentaCur + '">' +
            '<div class="row g-2 align-items-end">' +
            '<div class="col-sm-3"><label class="form-label">Año</label>' +
            '<input class="form-control" type="text" name="cur_anio[]" value=""></div>' +
            '<div class="col-sm-7"><label class="form-label">Nombre</label>' +
            '<input class="form-control cursos" type="text" name="cur_nombre[]" value=""></div>' +
            '<div class="col-sm-2"><button type="button" class="btn btn-outline-danger w-100" ' +
            'onclick="$(\'#curso' + cuentaCur + '\').remove();return false;">Quitar</button></div>' +
            '</div></div>'
        );
        activarAutocomplete();
    });
});
