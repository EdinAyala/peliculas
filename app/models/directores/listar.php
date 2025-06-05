<?php

require '../sql/conexion.php'; // Conexión a la base de datos

try {
    $params = $_POST; // Captura los parámetros enviados desde el cliente
    $response = array();

    // Consulta SQL para listar todos los directores
    $sql = "SELECT id_director, nombre, pais_origen, estado FROM director";
    $resultado = mysqli_query($con, $sql);

    if ($resultado) {
        if (mysqli_num_rows($resultado) > 0) {
            $items = array();
            while ($fila = mysqli_fetch_assoc($resultado)) {
                array_push($items, $fila);
            }
            // Respuesta exitosa con los datos obtenidos
            $response = array(
                'success'   => true,
                'resultado' => $items,
                'total'     => count($items)
            );
        } else {
            // Respuesta en caso de no encontrar registros
            $response = array(
                'success'   => true,
                'resultado' => [],
                'total'     => 0
            );
        }
    } else {
        // Respuesta si ocurre un error en la consulta SQL
        $response = array(
            'success' => false,
            'error'   => mysqli_error($con)
        );
    }

    // Devuelve la respuesta en formato JSON
    echo json_encode($response);

} catch (Exception $e) {
    // Captura excepciones y devuelve un mensaje de error
    $response = array(
        'success' => false,
        'error'   => 'Error en la consulta: ' . $e->getMessage()
    );
    echo json_encode($response);
}

$con->close(); // Cierra la conexión

// Libera memoria
foreach (get_defined_vars() as $var => $value) {
    unset($$var);
}

?>