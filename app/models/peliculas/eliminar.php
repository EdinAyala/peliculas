<?php

require '../sql/conexion.php'; // Conexión a la base de datos

$params = $_POST; // Captura los parámetros enviados desde el cliente

// Consulta para eliminar una película específica por su ID
$sql = "DELETE FROM pelicula 
        WHERE id_pelicula = '$params[id_pelicula]'";
$eliminar = mysqli_query($con, $sql);

if (mysqli_affected_rows($con) > 0) {
    // Respuesta exitosa si se eliminó el registro
    $response = array(
        'success' => true,
        'msg' => 'Película eliminada correctamente'
    );
} else {
    // Respuesta en caso de error o si no se encuentra el registro
    $response = array(
        'success' => false,
        'error' => mysqli_error($con)
    );
}

echo json_encode($response); // Devuelve la respuesta en formato JSON

$con->close(); // Cierra la conexión

?>