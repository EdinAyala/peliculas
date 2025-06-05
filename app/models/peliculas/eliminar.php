<?php
require '../sql/conexion.php'; // Conexión a la base de datos

try {
    $params = $_POST; // Captura los parámetros enviados desde el cliente

    // Validación básica del ID de la película
    if (!isset($params['id_pelicula']) || !is_numeric($params['id_pelicula'])) {
        throw new Exception('El ID de la película debe ser un número válido');
    }

    $id_pelicula = mysqli_real_escape_string($con, $params['id_pelicula']);

    // ------------------------------------------------------------
    // 1. Extraer los datos actuales del registro (para la bitácora)
    // ------------------------------------------------------------
    $sql = "CALL extraer_datos($id_pelicula, 'pelicula', @datos)";
    if (!mysqli_query($con, $sql)) {
        throw new Exception("Error al extraer datos: " . mysqli_error($con));
    }
    // Obtener el valor del parámetro OUT @datos
    $sql = "SELECT @datos AS datos";
    $resultado = mysqli_query($con, $sql);
    $info_anterior = "";
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $info_anterior = $fila['datos'];
    }

    // ------------------------------------------------------------
    // 2. Eliminar el registro de la tabla 'pelicula'
    // ------------------------------------------------------------
    $sql = "DELETE FROM pelicula WHERE id_pelicula = '$id_pelicula'";
    mysqli_query($con, $sql);

    if (mysqli_affected_rows($con) > 0) {
        // ------------------------------------------------------------
        // 3. Registrar la eliminación en la bitácora
        // ------------------------------------------------------------
        // Definir:
        //   - $accion: código o descripción de la acción (en este caso, 'Eliminacion')
        //   - $id_usuario: ID del usuario que realiza la acción (puedes tomarlo de la sesión)
        $accion = "'Eliminacion'"; // Como el campo en la tabla es ENUM, asegúrate de pasar el valor entre comillas simples
        $id_usuario = 1; // Ajusta este valor según tu implementación (por ejemplo: $_SESSION['id_usuario'])

        // Llamamos al procedimiento almacenado para registrar la bitácora
        $sql = "CALL registrar_bitacora($id_pelicula, 'pelicula', $accion, $id_usuario, '$info_previa', '')";
        if (!mysqli_query($con, $sql)) {
            throw new Exception("Error al registrar en la bitácora: " . mysqli_error($con));
        }

        // Respuesta exitosa
        $response = array(
            'success' => true,
            'msg'     => 'Película eliminada correctamente'
        );
    } else {
        // Si no se afectó ninguna fila (registro inexistente o error)
        $response = array(
            'success' => false,
            'error'   => mysqli_error($con) ?: 'No se encontró la película para eliminar'
        );
    }
} catch (Exception $e) {
    $response = array(
        'success' => false,
        'error'   => $e->getMessage()
    );
}

header('Content-Type: application/json');
echo json_encode($response);

$con->close();

// Libera memoria
foreach (get_defined_vars() as $var => $value) {
    unset($$var);
}
?>