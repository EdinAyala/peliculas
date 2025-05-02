<?php
require '../sql/conexion.php';

try {
    // Inicializar la respuesta
    $response = array(
        'success' => false,
        'msg' => '',
        'error' => ''
    );

    // Recolecta los parámetros enviados desde el cliente
    // mysqli_real_escape_string se utiliza para evitar inyecciones SQL y convertir caracteres especiales

    $titulo = isset($_POST['titulo']) ? mysqli_real_escape_string($con, $_POST['titulo']) : '';
    $anio = isset($_POST['anio']) ? mysqli_real_escape_string($con, $_POST['anio']) : '';
    $duracion = isset($_POST['duracion']) ? mysqli_real_escape_string($con, $_POST['duracion']) : '';
    $clasificacion = isset($_POST['clasificacion']) ? mysqli_real_escape_string($con, $_POST['clasificacion']) : '';
    $id_director = isset($_POST['id_director']) ? mysqli_real_escape_string($con, $_POST['id_director']) : '';
    $id_pelicula = isset($_POST['id_pelicula']) ? mysqli_real_escape_string($con, $_POST['id_pelicula']) : '';

    // Limpiar espacios en blanco al principio y al final de la cadena de texto
    $titulo = trim($titulo);
    $anio = trim($anio);
    $duracion = trim($duracion);
    $clasificacion = trim($clasificacion);
    $id_director = trim($id_director);
    $id_pelicula = trim($id_pelicula);

    // Validar que el ID de la película sea un número
    if (!empty($id_pelicula) && !is_numeric($id_pelicula)) {
        throw new Exception('El ID de la película debe ser un número');
    }

    // Validar campos obligatorios
    $contadorErrores = 0;
    $mensajeErrores = '';

    if (empty($titulo)) {
        $contadorErrores++;
        $mensajeErrores .= 'El campo título es obligatorio. ';
    } 
    
    if (empty($anio)) {
        $contadorErrores++;
        $mensajeErrores .= 'El campo año es obligatorio. ';
    }
    
    if (empty($duracion)) {
        $contadorErrores++;
        $mensajeErrores .= 'El campo duración es obligatorio. ';
    }
    
    if (empty($clasificacion)) {
        $contadorErrores++;
        $mensajeErrores .= 'El campo clasificación es obligatorio. ';
    }
    
    if (empty($id_director)) {
        $contadorErrores++;
        $mensajeErrores .= 'El campo director es obligatorio. ';
    }

    // Si hay errores, devolver mensaje de error
    if ($contadorErrores > 0) {
        throw new Exception($mensajeErrores);
    }

    // Validar que el año sea un número y esté en el rango correcto
    if (!is_numeric($anio) || $anio < 1900 || $anio > date("Y")) {
        throw new Exception('El año debe ser un número válido entre 1900 y el año actual');
    }

    // Procesar el archivo del póster
    $poster = null; //Variable para almacenar el contenido del póster o imagen
    $posterUpdated = false; // Bandera para verificar si se ha actualizado el póster

    // Verifica si se ha subido un archivo
    if (!empty($_FILES['poster']['tmp_name'])) {
        // Validar el tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['poster']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('El archivo debe ser una imagen (JPG, PNG o GIF)');
        }
        
        // Verificar el peso de la imagen (limite 2MB)
        if ($_FILES['poster']['size'] > 2097152) {
            throw new Exception('El tamaño del póster no debe exceder 2MB');
        }
        
        $poster = file_get_contents($_FILES['poster']['tmp_name']);
        $posterUpdated = true;
    }

    // Use prepared statements for database operations
    if (empty($id_pelicula)) {
        // INSERT new movie
        $stmt = $con->prepare("INSERT INTO pelicula (titulo, anio, duracion, clasificacion, poster, id_director) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $con->error);
        }
        
        $stmt->bind_param("sssssi", $titulo, $anio, $duracion, $clasificacion, $poster, $id_director);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['msg'] = 'La película fue registrada exitosamente';
        } else {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
        
    } else {
        // UPDATE existing movie
        if ($posterUpdated) {
            // With new poster
            $stmt = $con->prepare("UPDATE pelicula 
                                  SET titulo=?, anio=?, duracion=?, clasificacion=?, id_director=?, poster=?
                                  WHERE id_pelicula=?");
            $stmt->bind_param("ssssssi", $titulo, $anio, $duracion, $clasificacion, $id_director, $poster, $id_pelicula);
        } else {
            // Without changing poster
            $stmt = $con->prepare("UPDATE pelicula 
                                  SET titulo=?, anio=?, duracion=?, clasificacion=?, id_director=?
                                  WHERE id_pelicula=?");
            $stmt->bind_param("sssssi", $titulo, $anio, $duracion, $clasificacion, $id_director, $id_pelicula);
        }
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $con->error);
        }
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['msg'] = 'Película actualizada correctamente';
        } else {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

$con->close();

foreach (get_defined_vars() as $var => $value) {
    unset($$var);
}
?>