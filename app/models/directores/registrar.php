<?php
require '../sql/conexion.php';

try {
    // Inicializar variables y respuesta
    $params = $_POST;
    $info_anterior = '';
    $response = array(
        'success' => false,
        'msg'     => '',
        'error'   => ''
    );

    // Recolectar parámetros enviados desde el cliente
    $nombre       = isset($_POST['nombre']) ? mysqli_real_escape_string($con, $_POST['nombre']) : '';
    $pais_origen  = isset($_POST['pais_origen']) ? mysqli_real_escape_string($con, $_POST['pais_origen']) : '';
    $estado       = isset($_POST['estado']) ? mysqli_real_escape_string($con, $_POST['estado']) : '';
    $id_director  = isset($_POST['id_director']) ? mysqli_real_escape_string($con, $_POST['id_director']) : '';

    // Limpiar espacios en blanco al inicio y al final de cada parámetro
    $nombre       = trim($nombre);
    $pais_origen  = trim($pais_origen);
    $estado       = trim($estado);
    $id_director  = trim($id_director);

    // Validar que el ID del director, en caso de recibirse, sea numérico
    if (!empty($id_director) && !is_numeric($id_director)) {
        throw new Exception('El ID del director debe ser un número');
    }

    // Validaciones de campos obligatorios
    $contadorErrores = 0;
    $mensajeErrores  = '';

    if (empty($nombre)) {
        $contadorErrores++;
        $mensajeErrores .= 'El campo nombre es obligatorio. ';
    }

    if (empty($estado)) {
        $contadorErrores++;
        $mensajeErrores .= 'El campo estado es obligatorio. ';
    } else {
        // Validar que el estado tenga uno de los valores permitidos
        if ($estado !== 'Activo' && $estado !== 'Inactivo') {
            $contadorErrores++;
            $mensajeErrores .= 'El campo estado debe ser "Activo" o "Inactivo". ';
        }
    }

    // Devolver error si existen campos obligatorios sin completar
    if ($contadorErrores > 0) {
        throw new Exception($mensajeErrores);
    }

    // Operación INSERT o UPDATE dependiendo del valor de id_director
    if (empty($id_director)) {
        // INSERT: Nuevo director
        $stmt = $con->prepare("INSERT INTO director (nombre, pais_origen, estado) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $con->error);
        }
        $stmt->bind_param("sss", $nombre, $pais_origen, $estado);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['msg']     = 'El director fue registrado exitosamente';
            $id = $con->insert_id; // ID del nuevo director
        } else {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
    } else {
        // UPDATE: Actualización de un director existente
        $stmt = $con->prepare("UPDATE director SET nombre = ?, pais_origen = ?, estado = ? WHERE id_director = ?");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $con->error);
        }
        $stmt->bind_param("sssi", $nombre, $pais_origen, $estado, $id_director);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['msg']     = 'Director actualizado correctamente';
            $id = $id_director; // Usar el ID del director que se actualizó
        } else {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
    }

    $stmt->close();

    // Llamar al procedimiento almacenado para extraer datos del director
    $sql = "CALL extraer_datos($id, 'director', @datos)";
    mysqli_query($con, $sql);

    // Obtener los datos extraídos
    $sql = "SELECT @datos AS datos";
    $registro_datos = mysqli_query($con, $sql);
    $datos = mysqli_fetch_assoc($registro_datos);
    $info_posterior = $datos['datos'];

    // Registrar la bitácora, asumiendo que posees un procedimiento similar para directores
    // Se utiliza $id_usuario obtenido de la sesión o se asigna un valor predeterminado
    $id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 1;
    $operacion = empty($id_director) ? 1 : 2; // 1 para INSERT y 2 para UPDATE
    $sql = "CALL registrar_bitacora($id, 'director', $operacion, $id_usuario, '$info_anterior', '$info_posterior')";
    $registro_bitacora = mysqli_query($con, $sql);

    if (!$registro_bitacora) {
        throw new Exception("Error al registrar en la bitácora: " . mysqli_error($con));
    }

    $response['success'] = true;
    $response['msg']     = empty($id_director) ? 'Director registrado exitosamente' : 'Director actualizado exitosamente';

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);

// Cerrar la conexión y liberar variables
$con->close();
foreach (get_defined_vars() as $var => $value) {
    unset($$var);
}
?>