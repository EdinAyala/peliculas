$(document).ready(function () {
    listar_peliculas();
    cargar_catalogo()

    $("#btn_guardar_movie").click(function () { 
        guardar_pelicula();
    });

    $('#mdl_registro_movie').on('hidden.bs.modal', function () {
        $("#frm_registro_movie").trigger('reset');
        $("#poster_preview").attr("src", "#"); // Resetea la vista previa del póster
        $("#id_pelicula").val(""); // Resetea el campo ID oculto
    });

    $("#tabla_peliculas").on('click', '.editar-pelicula', function () {
        let id_pelicula = $(this).attr('data-id');
        obtener_pelicula(id_pelicula);
    });

    $("#tabla_peliculas").on('click', '.eliminar-pelicula', function () {
        let id_pelicula = $(this).attr('data-id');
        eliminar_pelicula(id_pelicula);
    });

    $("#btn_nueva_movie").click(function () { 
        $("#mdl_title_registro").html('<i class="fas fa-plus"></i> Registrar nueva película');
        $("#btn_guardar_movie").addClass('btn-success').removeClass('btn-warning').html('<i class="fas fa-save"></i> Guardar');
    });
});

function listar_peliculas() {
    $.ajax({
        url: 'app/models/peliculas/listar.php', // Ruta al archivo PHP que lista las películas
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
                    <td colspan="8" class="text-center">No hay películas registradas</td>
                </tr>`;
            }else{

                response.resultado.forEach((pelicula, index) => {
                    cuerpo += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${pelicula.titulo}</td>
                        <td>${pelicula.director}</td>
                        <td>${pelicula.anio}</td>
                        <td>${pelicula.clasificacion}</td>
                        <td>${pelicula.duracion} minutos</td>
                        <td><img src="data:image/jpeg;base64,${pelicula.poster}" alt="Póster" width="100"></td>
                        <td>
                            <button type="button" title="Editar" class="btn btn-warning editar-pelicula" data-id="${pelicula.id_pelicula}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" title="Eliminar" class="btn btn-danger eliminar-pelicula" data-id="${pelicula.id_pelicula}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            
            $("#tb_peliculas").html(cuerpo);
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
        console.log(jqXHR);
        
        console.error("Error al realizar la solicitud:", textStatus, errorThrown);
    });
}

function cargar_catalogo() {
    $.ajax({
        url: 'app/models/peliculas/catalogo.php', // Ruta al archivo PHP que lista las películas
        type: 'POST',
        dataType: 'json',
        data: {}
    })
    .done(function (response) {
        if (response.success) {
            let cuerpo = '';
            if (response.resultado.length === 0) {
                cuerpo = `<option value="0">No hay directores disponibles</option>`;
            }else{
                cuerpo += `<option selected value="0">Seleccione un director...</option>`;
                response.resultado.forEach((pelicula) => {
                    cuerpo += `
                    <option value="${pelicula.id_director}">${pelicula.nombre}</option>`;
                });
            }
            
            $("#id_director").html("");
            $("#id_director").html(cuerpo);
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
        console.log(jqXHR);
        
        console.error("Error al realizar la solicitud:", textStatus, errorThrown);
    });
}


function guardar_pelicula() {
    let formData = new FormData($("#frm_registro_movie")[0]);
    
    $.ajax({
        url: 'app/models/peliculas/registrar.php',
        type: 'POST',
        dataType: 'json',
        data: formData,
        contentType: false, 
        processData: false 
    })
    .done(function (response) {
        if (response.success) {
            listar_peliculas();
            $("#mdl_registro_movie").modal('hide');
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
        console.error("Error al guardar la película:", textStatus, errorThrown);
    });
}

function obtener_pelicula(id_pelicula) {
    $.ajax({
        url: 'app/models/peliculas/obtener.php', // Ruta para obtener datos de una película específica
        type: 'POST',
        dataType: 'json',
        data: { id_pelicula: id_pelicula }
    })
    .done(function (response) {
        if (response.success) {
            let valores = response.resultado[0];
            $("#mdl_title_registro").html('<i class="fas fa-edit"></i> Actualizar película');
            $("#btn_guardar_movie").removeClass('btn-success').addClass('btn-warning').html('<i class="fas fa-edit"></i> Actualizar');
            $("#id_pelicula").val(valores.id_pelicula);
            $("#titulo").val(valores.titulo);
            $("#id_director").val(valores.id_director);
            $("#anio").val(valores.anio);
            $("#clasificacion").val(valores.clasificacion);
            $("#duracion").val(valores.duracion);
            $("#poster_preview").attr("src", "data:image/jpeg;base64," + valores.poster); // Muestra el póster actual
            $("#mdl_registro_movie").modal('show');
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
        console.error("Error al obtener la película:", textStatus, errorThrown);
    });
}

function eliminar_pelicula(id_pelicula) {
    Swal.fire({
        title: "¿Desea eliminar esta película?",
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
                url: 'app/models/peliculas/eliminar.php', // Ruta para eliminar películas
                type: 'POST',
                dataType: 'json',
                data: { id_pelicula: id_pelicula }
            })
            .done(function (response) {
                if (response.success) {
                    listar_peliculas();
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
                console.error("Error al eliminar la película:", textStatus, errorThrown);
            });
        }
    });
}