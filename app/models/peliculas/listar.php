<?php

require '../sql/conexion.php'; // Conexión a la base de datos

try {
    $params = $_POST; // Captura los parámetros enviados desde el cliente
    $response = array();

    // Consulta SQL para listar todas las películas
    $sql = "SELECT p.id_pelicula, p.titulo, p.anio, p.duracion, 
                   p.clasificacion, p.poster, d.nombre AS director 
            FROM pelicula p
            JOIN director d ON p.id_director = d.id_director";
    $resultado = mysqli_query($con, $sql);

    if ($resultado) {
        if (mysqli_num_rows($resultado) > 0) {
            $items = array();
            while ($fila = mysqli_fetch_assoc($resultado)) {
                // Codifica el campo 'poster' en base64 para usarlo en el frontend
                $fila['poster'] = base64_encode($fila['poster']);
                array_push($items, $fila);
            }

            // Respuesta exitosa con los resultados obtenidos
            $response = array(
                'success' => true,
                'resultado' => $items,
                'total' => COUNT($items)
            );
        } else {
            // Respuesta si no se encuentran películas

            $response = array(
                'success' => true,
                'resultado' => [],
                'total' => 0
            );
        }
    } else {
        // Respuesta si ocurre un error en la consulta
        $response = array(
            'success' => false,
            'error' => mysqli_error($con)
        );
    }

    // Devuelve la respuesta en formato JSON
    echo json_encode($response);
} catch (Exception $e) {
    // Captura excepciones y devuelve un mensaje de error
    $response = array(
        'success' => false,
        'error' => 'Error en la consulta: ' . $e->getMessage()
    );

    echo json_encode($response); // Devuelve el error en formato JSON
}

$con->close(); // Cierra la conexión
// Libera memoria
foreach (get_defined_vars() as $var => $value) {
    unset($$var);
}

?>