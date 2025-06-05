<?php
require '../sql/conexion.php';

try {
    
    $params = $_POST;
    $info_previa = '';
    // Inicializar la respuesta
    $response = array(
        'success' => false,
        'msg'     => '',
        'error'   => ''
    );

    // Recolectar parámetros enviados desde el cliente
    $titulo         = isset($_POST['titulo']) ? mysqli_real_escape_string($con, $_POST['titulo']) : '';
    $anio           = isset($_POST['anio']) ? mysqli_real_escape_string($con, $_POST['anio']) : '';
    $duracion       = isset($_POST['duracion']) ? mysqli_real_escape_string($con, $_POST['duracion']) : '';
    $clasificacion  = isset($_POST['clasificacion']) ? mysqli_real_escape_string($con, $_POST['clasificacion']) : '';
    $id_director    = isset($_POST['id_director']) ? mysqli_real_escape_string($con, $_POST['id_director']) : '';
    $id_pelicula    = isset($_POST['id_pelicula']) ? mysqli_real_escape_string($con, $_POST['id_pelicula']) : '';

    // Limpiar espacios en blanco
    $titulo         = trim($titulo);
    $anio           = trim($anio);
    $duracion       = trim($duracion);
    $clasificacion  = trim($clasificacion);
    $id_director    = trim($id_director);
    $id_pelicula    = trim($id_pelicula);

    // Validaciones básicas
    if (!empty($id_pelicula) && !is_numeric($id_pelicula)) {
        throw new Exception('El ID de la película debe ser un número');
    }
    if (!is_numeric($anio) || $anio < 1900 || $anio > date("Y")) {
        throw new Exception('El año debe ser un número válido entre 1900 y el año actual');
    }

    // Procesar imagen (póster)
    $poster = null;
    $posterUpdated = false;
    if (!empty($_FILES['poster']['tmp_name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['poster']['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('El archivo debe ser una imagen (JPG, PNG o GIF)');
        }
        if ($_FILES['poster']['size'] > 2097152) {
            throw new Exception('El tamaño del póster no debe exceder 2MB');
        }
        $poster = file_get_contents($_FILES['poster']['tmp_name']);
        $posterUpdated = true;
    }

    // ===============================================================
    // Parte de la integración con la Bitácora:
    // ===============================================================

    $id_afectado = 0;               // ID del registro afectado (insertado o actualizado)
    $accion = '';                   // 'Insercion' o 'Actualizacion'
    $id_usuario = 1;                // Aquí puedes usar $_SESSION['id_usuario']
    $info_previa = '';              // Información anterior (solo en actualización)
    $info_posterior = '';           // Información posterior

    // Si se trata de una actualizacion, extraemos datos PREVIOS
    if (!empty($id_pelicula)) {
        $sql = "CALL extraer_datos($id_pelicula, 'pelicula', @datos)";
        if (!mysqli_query($con, $sql)) {
            throw new Exception("Error al extraer datos previos: " . mysqli_error($con));
        }
        $sql = "SELECT @datos AS datos";
        $resultado = mysqli_query($con, $sql);
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $fila = mysqli_fetch_assoc($resultado);
            $info_previa = $fila['datos'];
        }
        $accion = 'Actualizacion';
        $id_afectado = $id_pelicula;
    } else {
        $accion = 'Insercion';
        // En insercion no hay info previa
    }

    // ===============================================================
    // Ejecución de INSERT/UPDATE
    // ===============================================================
    if (empty($id_pelicula)) {
        // INSERT: Registrar nueva película
        $stmt = $con->prepare("INSERT INTO pelicula (titulo, anio, duracion, clasificacion, poster, id_director) VALUES (?, ?, ?, ?, ?, ?)");
        if(!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $con->error);
        }
        $stmt->bind_param("sssssi", $titulo, $anio, $duracion, $clasificacion, $poster, $id_director);
        if ($stmt->execute()) {
            $id_afectado = $con->insert_id;
            $response['msg'] = 'La película fue registrada exitosamente';
        } else {
            throw new Exception("Error al ejecutar la consulta de inserción: " . $stmt->error);
        }
    } else {
        // UPDATE: Actualizar película existente
        if ($posterUpdated) {
            $stmt = $con->prepare("UPDATE pelicula SET titulo=?, anio=?, duracion=?, clasificacion=?, id_director=?, poster=? WHERE id_pelicula=?");
            if(!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $con->error);
            }
            $stmt->bind_param("ssssssi", $titulo, $anio, $duracion, $clasificacion, $id_director, $poster, $id_pelicula);
        } else {
            $stmt = $con->prepare("UPDATE pelicula SET titulo=?, anio=?, duracion=?, clasificacion=?, id_director=? WHERE id_pelicula=?");
            if(!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $con->error);
            }
            $stmt->bind_param("sssssi", $titulo, $anio, $duracion, $clasificacion, $id_director, $id_pelicula);
        }
        if ($stmt->execute()) {
            $response['msg'] = 'Película actualizada correctamente';
        } else {
            throw new Exception("Error al ejecutar la consulta de actualización: " . $stmt->error);
        }
    }
    $stmt->close();

    // ===============================================================
    // Extraer información POSTERIOR (después de la operación)
    // ===============================================================
    $sql = "CALL extraer_datos($id_afectado, 'pelicula', @datos)";
    if (!mysqli_query($con, $sql)) {
        throw new Exception("Error al extraer datos posteriores: " . mysqli_error($con));
    }
    $sql = "SELECT @datos AS datos";
    $resultado = mysqli_query($con, $sql);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $info_posterior = $fila['datos'];
    }

    // ===============================================================
    // Registrar el cambio en la Bitácora
    // ===============================================================
    $sql = "CALL registrarBitacora($id_afectado, 'pelicula', '$accion', $id_usuario, '$info_previa', '$info_posterior')";
    if (!mysqli_query($con, $sql)) {
        throw new Exception("Error al registrar en la bitácora: " . mysqli_error($con));
    }

    $response['success'] = true;
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);

$con->close();
foreach (get_defined_vars() as $var => $value) {
    unset($$var);
}
?>