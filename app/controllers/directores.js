$(document).ready(function () {
    listar_directores();

    // Registrar o actualizar director
    $("#btn_guardar_director").click(function () {
        guardar_director();
    });

    // Reinicia el formulario y el campo oculto del ID al cerrar el modal
    $('#mdl_registro_director').on('hidden.bs.modal', function () {
        $("#frm_registro_director").trigger('reset');
        $("#id_director").val("");
    });

    // Asignar evento para editar un director
    $("#tabla_directores").on('click', '.editar-director', function () {
        let id_director = $(this).attr('data-id');
        obtener_director(id_director);
    });

    // Asignar evento para eliminar un director
    $("#tabla_directores").on('click', '.eliminar-director', function () {
        let id_director = $(this).attr('data-id');
        eliminar_director(id_director);
    });

    // Configuración para abrir el modal de registro de director
    $("#btn_nueva_director").click(function () {
        $("#mdl_title_registro").html('<i class="fas fa-plus"></i> Registrar nuevo director');
        $("#btn_guardar_director")
            .addClass('btn-success')
            .removeClass('btn-warning')
            .html('<i class="fas fa-save"></i> Guardar');
    });
});

function listar_directores() {
    $.ajax({
        url: 'app/models/directores/listar.php', // Ruta al archivo PHP que lista los directores
        type: 'POST',
        dataType: 'json',
        data: {}
    })
    .done(function (response) {
        if (response.success) {
            console.log(response);

            let cuerpo = '';
            if (response.resultado.length === 0) {
                cuerpo = `
                <tr>
                    <td colspan="5" class="text-center">No hay directores registrados</td>
                </tr>`;
            } else {
                response.resultado.forEach((director, index) => {
                    cuerpo += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${director.nombre}</td>
                        <td>${director.pais_origen}</td>
                        <td>${director.estado}</td>
                        <td>
                            <button type="button" title="Editar" class="btn btn-warning editar-director" data-id="${director.id_director}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" title="Eliminar" class="btn btn-danger eliminar-director" data-id="${director.id_director}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }

            $("#tb_directores").html(cuerpo);
        } else {
            Swal.fire({
                title: "Atención",
                icon: "info",
                html: response.error,
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Error al realizar la solicitud:", textStatus, errorThrown);
    });
}

function guardar_director() {
    let formData = new FormData($("#frm_registro_director")[0]);

    $.ajax({
        url: 'app/models/directores/registrar.php', // Ruta para registrar o actualizar director
        type: 'POST',
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false
    })
    .done(function (response) {
        if (response.success) {
            listar_directores();
            $("#mdl_registro_director").modal('hide');
            Swal.fire({
                title: "Éxito",
                icon: "success",
                html: response.msg,
                confirmButtonText: 'Aceptar'
            });
        } else {
            Swal.fire({
                title: "Atención",
                icon: "info",
                html: response.error,
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Error al guardar el director:", textStatus, errorThrown);
    });
}

function obtener_director(id_director) {
    $.ajax({
        url: 'app/models/directores/obtener.php', // Ruta para obtener datos de un director específico
        type: 'POST',
        dataType: 'json',
        data: { id_director: id_director }
    })
    .done(function (response) {
        if (response.success) {
            let valores = response.resultado[0];
            $("#mdl_title_registro").html('<i class="fas fa-edit"></i> Actualizar director');
            $("#btn_guardar_director")
                .removeClass('btn-success')
                .addClass('btn-warning')
                .html('<i class="fas fa-edit"></i> Actualizar');
            $("#id_director").val(valores.id_director);
            $("#nombre").val(valores.nombre);
            $("#pais_origen").val(valores.pais_origen);
            $("#estado").val(valores.estado);
            $("#mdl_registro_director").modal('show');
        } else {
            Swal.fire({
                title: "Atención",
                icon: "info",
                html: response.error,
                confirmButtonText: "Aceptar"
            });
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Error al obtener el director:", textStatus, errorThrown);
    });
}

function eliminar_director(id_director) {
    Swal.fire({
        title: "¿Desea eliminar este director?",
        text: "No podrá revertir esta acción",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'app/models/directores/eliminar.php', // Ruta para eliminar directores
                type: 'POST',
                dataType: 'json',
                data: { id_director: id_director }
            })
            .done(function (response) {
                if (response.success) {
                    listar_directores();
                    Swal.fire({
                        title: "Éxito",
                        icon: "success",
                        html: response.msg,
                        confirmButtonText: 'Aceptar'
                    });
                } else {
                    Swal.fire({
                        title: "Atención",
                        icon: "info",
                        html: response.error,
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.error("Error al eliminar el director:", textStatus, errorThrown);
            });
        }
    });
}