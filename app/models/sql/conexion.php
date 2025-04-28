<?php

error_reporting(E_ERROR);

$servidor = 'localhost:3306';
$usuario = 'root';
$clave = 'root';
$bd = 'bd_peliculas';

$con = mysqli_connect($servidor, $usuario,$clave, $bd);

if($con){
    $con->set_charset("utf-8");
}else{
    $response = array(
        'success'=>false,
        'error'=>'No hay conexión a la base de datos'
    );

    echo json_encode($response);

    exit();
}

?>