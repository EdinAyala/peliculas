<?php

require '../sql/conexion.php'; // Conexión a la base de datos

try {
    $params = $_POST; // Captura los parámetros enviados desde el cliente
    $response = array();

    // Consulta SQL para obtener los datos de un director específico
    $sql = "SELECT id_director, nombre, pais_origen, estado 
            FROM director 
            WHERE id_director = '{$params['id_director']}'";
    $resultado = mysqli_query($con, $sql);

    if ($resultado) {
        if (mysqli_num_rows($resultado) > 0) {
            $items = array();
            while ($fila = mysqli_fetch_assoc($resultado)) {
                // No es necesario codificar ningún campo ya que no se manejan archivos.
                array_push($items, $fila);
            }
            
            // Respuesta exitosa con los datos del director
            $response = array(
                'success'   => true,
                'resultado' => $items,
                'total'     => count($items)
            );
        } else {
            // Si no se encuentra el director seleccionado
            $response = array(
                'success' => false,
                'error'   => 'No se encontró el director seleccionado'
            );
        }
    } else {
        // Si ocurre un error en la consulta
        $response = array(
            'success' => false,
            'error'   => mysqli_error($con)
        );
    }
    
    echo json_encode($response); // Devuelve la respuesta en formato JSON
    
} catch (Exception $e) {
    // Captura excepciones y devuelve un mensaje de error
    $response = array(
        'success' => false,
        'error'   => 'Error en la consulta: ' . $e->getMessage()
    );
    
    echo json_encode($response); // Devuelve el error en formato JSON
}

$con->close(); // Cierra la conexión
unset($response); // Libera memoria

?>