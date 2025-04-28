<?php

require '../sql/conexion.php'; // Conexión a la base de datos

$params = array();
$params['completo'] = $_POST['datos'];

$campos = explode("&", $_POST['datos']); // Procesa los datos enviados desde el formulario

foreach ($campos as $value) {
    $dato = explode('=', $value);
    $params[$dato[0]] = urldecode($dato[1]); // Decodifica caracteres especiales
}

try {
    if (isset($params['id_pelicula']) && $params['id_pelicula'] == "") {
        // Inserción de una nueva película
        $poster = !empty($_FILES['poster']['tmp_name']) ? addslashes(file_get_contents($_FILES['poster']['tmp_name'])) : null;

        $sql = "INSERT INTO pelicula (titulo, anio, duracion, clasificacion, poster, id_director) 
                VALUES ('$params[titulo]', '$params[anio]', '$params[duracion]', 
                        '$params[clasificacion]', '$poster', '$params[id_director]')";
        if (mysqli_query($con, $sql)) {
            $response = array(
                'success' => true,
                'msg' => 'La película fue registrada exitosamente'
            );
        } else {
            $response = array(
                'success' => false,
                'error' => mysqli_error($con)
            );
        }
    } else {
        // Actualización de una película existente
        $posterQuery = "";
        if (!empty($_FILES['poster']['tmp_name'])) { // Si se sube un nuevo póster
            $poster = addslashes(file_get_contents($_FILES['poster']['tmp_name']));
            $posterQuery = ", poster='$poster'";
        }

        $sql = "UPDATE pelicula 
                SET titulo='$params[titulo]', 
                    anio='$params[anio]', 
                    duracion='$params[duracion]', 
                    clasificacion='$params[clasificacion]', 
                    id_director='$params[id_director]' 
                    $posterQuery
                WHERE id_pelicula='$params[id_pelicula]'";
        if (mysqli_query($con, $sql)) {
            $response = array(
                'success' => true,
                'msg' => 'Película actualizada correctamente'
            );
        } else {
            $response = array(
                'success' => false,
                'error' => mysqli_error($con)
            );
        }
    }
} catch (Exception $e) {
    $response = array(
        'success' => false,
        'error' => 'Error en la consulta: ' . $e->getMessage()
    );
}

echo json_encode($response);

$con->close(); // Cierra la conexión
unset($response, $params, $dato, $sql);

?>