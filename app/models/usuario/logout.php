<?php

require '../sql/conexion.php'; // Conexión a la base de datos
session_start();

// Registrar la acción de cierre de sesión en la bitácora
$sql = "INSERT INTO bitacora(fecha, accion, tabla, id_afectado, id_usuario) 
        VALUES(NOW(), 2, 'no aplica', 0, '$_SESSION[peliculas_id_usuario]')";
$resultado = mysqli_query($con, $sql);

// Eliminar las variables de sesión relacionadas con el sistema de películas
unset(
    $_SESSION['peliculas'],
    $_SESSION['peliculas_id_usuario'],
    $_SESSION['peliculas_usuario'],
    $_SESSION['peliculas_name'],
    $_SESSION['peliculas_id_rol'],
    $_SESSION['peliculas_rol']
);

// Respuesta JSON para redirigir al login
$response = array('success' => true, 'url' => "?mod=login");
echo json_encode($response);

?>