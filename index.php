<?php

session_start();
date_default_timezone_set('America/El_salvador');
setlocale(LC_TIME, 'spanish');

require_once('conf.php');

if(isset($_GET['mod'])){
    $modulo = $_GET['mod'];
}else{
    $modulo = MODULO_DEFECTO;
}

if(isset($conf[$modulo]['layout'])){
    $path_layout = LAYOUT_PATH . '/' . $conf[$modulo]['layout'];
    if(!empty($conf[$modulo]['layout'])){
        include($path_layout);
    }else{
        $modulo = 'inicio';
        $path_layout = LAYOUT_PATH . '/' . $conf[$modulo]['layout'];
        include($path_layout);
    }
}

?>