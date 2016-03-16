<?php
    header("Access-Control-Allow-Origin: http://localhost:8100");
    header('Content-type: text/json');
    header('Content-type: application/json');
    
    error_reporting(E_ALL ^ E_NOTICE);
    
    include('../class/fireapp.smart.php');
    
    $fireappsmart = new FireappSmart();
    echo json_encode($fireappsmart->accion(), JSON_PRETTY_PRINT);
?>