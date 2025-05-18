document.addEventListener("DOMContentLoaded", () => {
    listarPeliculas();
});

function listarPeliculas() {
    $.ajax({
        url: 'app/models/poster/listar.php', // Ruta al archivo PHP que lista las películas
        type: 'POST',
        dataType: 'json',
        data: {}
    })
    .done(function (response) {
        if (response.success) {
            console.log(response);

            let cards = '';
            if (response.resultado.length === 0) {
                cards = `
                <div class="col-12 text-center">
                    <p class="text-muted">No hay películas registradas</p>
                </div>`;
            } else {
                response.resultado.forEach((pelicula) => {
                    let poster = pelicula.poster ? `data:image/jpeg;base64,${pelicula.poster}` : 'media/img/default.jpg'; // Manejo de imagen

                    cards += `
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm" style="width: 18rem;">
                            <img src="${poster}" class="card-img-top" alt="${pelicula.titulo}">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Titulo:</strong> ${pelicula.titulo}</h5>
                                <p class="card-text"><strong>Director:</strong> ${pelicula.director}</p>
                                <p class="card-text"><strong>Año:</strong> ${pelicula.anio}</p>
                                <p class="card-text"><strong>Clasificación:</strong> ${pelicula.clasificacion}</p>
                                <p class="card-text"><strong>Duración:</strong> ${pelicula.duracion} minutos</p>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-warning editar-pelicula" data-id="${pelicula.id_pelicula}">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-danger eliminar-pelicula" data-id="${pelicula.id_pelicula}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
            }

            $("#lista_peliculas").html(cards);
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