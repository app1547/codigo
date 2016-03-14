<?php

include('mysql_class.php');

class Fireapp{
    
    public $status = null;
    
    public function __construct(){
            
    }
    public function status(){
        // 0 = ERROR, 1 = OK
        $this->status['estado'] = 0;
        $this->status['mensaje'] = "";
    }
    public function setstatus($estado, $mensaje){
        $this->status['estado'] = $estado;
        $this->status['mensaje'] = $mensaje;
    }
    public function anexostatus($key, $mensaje){
        $this->status[$key] = $mensaje;
    }
    public function getstatus(){
        return $this->status;
    }
}
?>