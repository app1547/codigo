<?php

include('fireapp.php');

class FireappSmart extends Fireapp{

    public $con = null;
    public $status = null;
    public $debug = true;

    public function __construct(){
        $this->con = new Conexion();
    }
    public function getpost($var){
        if($this->debug){
            if(isset($_GET[$var]))
                return $_GET[$var];
        }else{
            if(isset($_POST[$var]))
                return $_POST[$var];
        }
    }
    public function accion(){
        
        $accion = $this->getpost('accion');
        if(isset($accion)){

            if(isset($accion)){

                /* INFO SOBRE LLAMADOS */
                if($accion == "getLlamados"){
                    return $this->getLlamados();
                }
                if($accion == "getLlamado"){
                    return $this->getLlamado();
                }
                /* INFO PERSONA */
                if($accion == "getPerfil"){
                    return $this->getLlamado();
                }
                if($accion == "getUltimosLlamados"){
                    return $this->getLlamado();
                }
                /* INGRESO AL SISTEMA */
                if($accion == "ingresoUser"){
                    return $this->ingresoUser();
                }

            }
        }

    }
    private function token(){
            
        $token = $this->getpost('token');
        $ret['estado'] = false;
        
        if(isset($token)){
        
            $id_token = substr($token, 0, 18);
            $id_user = intval(substr($token, 18, 7));
        
            $tok = $this->con->sql("SELECT token, id_cia, id_cue FROM usuarios WHERE id_user='".$id_user."'");
            if($tok['count'] == 1 && $tok['resultado'][0]['token'] == $id_token){

                $ret['id_user'] = $id_user;
                $ret['id_cia'] = $tok['resultado'][0]['id_cia'];
                $ret['id_cue'] = $tok['resultado'][0]['id_cue'];
                $ret['estado'] = true;

            }
            
        }
        return $ret;

    }
    
    public function getLlamados(){
        
        $info = $this->token();
        $this->status(); // FALTA STATUS

        if($info['estado']){
            
            // SI ES USER
            $id_user = $info['id_user'];
            $id_cia = $info['id_cia'];
            $id_cue = $info['id_cue'];
            
            $llamados = $this->con->sql("SELECT DISTINCT(t1.id_act), t1.id_cue, t3.nombre, t1.direccion, t1.fecha, t1.lat, t1.lng, t1.maquinas FROM actos t1, actos_cia t2, claves t3 WHERE t1.id_cla=t3.id_cla AND t1.active='1' AND ((t1.id_cue='".$id_cue."') OR (t2.id_cia='".$id_cia."' AND t1.id_act=t2.id_act)) ORDER BY t1.fecha DESC");
            $this->anexostatus("llamados", $llamados['resultado']);
            $this->setstatus(1, "Lista de Llamados");
            
        }else{
            
            // SI NO ES USER
            $this->setstatus(0, "No se reconoce Usuario");
            
        }
        return $this->getstatus();
        
    }
    
    
    
}
?>