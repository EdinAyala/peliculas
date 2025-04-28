<?php

require '../sql/conexion.php'; // Conexión a la base de datos

try {
    $params = $_POST; // Captura los parámetros enviados desde el cliente
    $response = array();

    // Consulta SQL para obtener los datos de una película específica
    $sql = "SELECT p.id_pelicula, p.titulo, p.anio, p.duracion, 
                   p.clasificacion, p.poster, d.nombre AS director, p.id_director
            FROM pelicula p
            JOIN director d ON p.id_director = d.id_director
            WHERE p.id_pelicula = '{$params['id_pelicula']}'";
    $resultado = mysqli_query($con, $sql);

    if ($resultado) {
        if (mysqli_num_rows($resultado) > 0) {
            $items = array();
            while ($fila = mysqli_fetch_assoc($resultado)) {
                // Codificar el campo 'poster' en base64 para enviar al frontend
                $fila['poster'] = base64_encode($fila['poster']);
                array_push($items, $fila);
            }

            // Respuesta exitosa con los datos de la película
            $response = array(
                'success' => true,
                'resultado' => $items,
                'total' => COUNT($items)
            );
        } else {
            // Si no se encuentra la película seleccionada
            $response = array(
                'success' => false,
                'error' => 'No se encontró la película seleccionada'
            );
        }
    } else {
        // Si ocurre un error en la consulta
        $response = array(
            'success' => false,
            'error' => mysqli_error($con)
        );
    }

    echo json_encode($response); // Devuelve la respuesta en formato JSON
} catch (Exception $e) {
    // Captura excepciones y devuelve un mensaje de error
    $response = array(
        'success' => false,
        'error' => 'Error en la consulta: ' . $e->getMessage()
    );

    echo json_encode($response); // Devuelve el error en formato JSON
}

$con->close(); // Cierra la conexión
unset($response); // Libera memoria

?>