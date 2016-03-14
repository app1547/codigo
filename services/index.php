<?php
    header('Content-type: text/json');
    header('Content-type: application/json');

    include('../class/fireapp.smart.php');
    
    $fireappsmart = new FireappSmart();
    echo json_encode($fireappsmart->accion());
?>