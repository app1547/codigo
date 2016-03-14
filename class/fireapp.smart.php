<?php

include('fireapp.php');

class FireappSmart extends Fireapp{
    
        public $con = null;
        public $status = null;
        
        public function __construct(){
            
        }
        public function accion(){
            
            $token = $this->token();
            print_r($token);
            $aux['estado'] = 1;
            $aux['mensaje'] = "Bienvenido Diegomez13";
            return $aux;
                
        }
}
?>