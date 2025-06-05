<?php

require '../sql/conexion.php'; // Conexión a la base de datos

$params = $_POST; // Captura los parámetros enviados desde el cliente

// Inicializar la respuesta
$response = array(
    'success' => false,
    'msg'     => '',
    'error'   => ''
);

// Validar que el ID del director sea un número válido
if (!isset($params['id_director']) || !is_numeric($params['id_director'])) {
    $response['error'] = 'El ID del director debe ser un número válido';
    echo json_encode($response);
    exit;
}

// Obtener el ID del director a eliminar
$id_director = mysqli_real_escape_string($con, $params['id_director']);

// Primero, extraer los datos del director antes de eliminarlo
$sql = "CALL extraer_datos($id_director, 'director', @datos)";
mysqli_query($con, $sql);

// Obtener los datos extraídos para la bitácora
$sql = "SELECT @datos AS datos";
$registro_datos = mysqli_query($con, $sql);
$datos = mysqli_fetch_assoc($registro_datos);
$info_anterior = $datos['datos'];

// Consulta para eliminar un director específico por su ID
$sql = "DELETE FROM director 
        WHERE id_director = '$id_director'";
$eliminar = mysqli_query($con, $sql);

if (mysqli_affected_rows($con) > 0) {
    // Registrar la acción en la bitácora
    $id_usuario = 1; // Cambia esto por el ID del usuario que realiza la acción
    $accion = 3; // Suponiendo que 3 es el código para eliminar en tu sistema

    $sql = "CALL registrar_bitacora($id_director, 'director', $accion, $id_usuario, '$info_anterior', '')";
    $registro_bitacora = mysqli_query($con, $sql);

    if (!$registro_bitacora) {
        $response['error'] = "Error al registrar en la bitácora: " . mysqli_error($con);
    } else {
        // Respuesta exitosa si se eliminó el registro
        $response['success'] = true;
        $response['msg']     = 'Director eliminado correctamente';
    }
} else {
    // Respuesta en caso de error o si no se encuentra el registro
    $response['error'] = mysqli_error($con) ?: 'No se encontró el director para eliminar';
}

echo json_encode($response); // Devuelve la respuesta en formato JSON

$con->close(); // Cierra la conexión

?>