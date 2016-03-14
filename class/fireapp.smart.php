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
        // TERMINADA NO TOCAR 
        $token = $this->getpost('token');
        $ret['estado'] = false;
        if(isset($token)){
            $id_user = $this->getpost('iduser');
            $tok = $this->con->sql("SELECT token, id_cia, id_cue FROM usuarios WHERE id_user='".$id_user."'");
            if($tok['count'] == 1 && $tok['resultado'][0]['token'] == $token){
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
        $this->status();

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
    
    public function getLlamado(){
        
        $info = $this->token();
        $this->status();

        if($info['estado']){
            
            // SI ES USER
            $id_user = $info['id_user'];
            $id_cia = $info['id_cia'];
            $id_cue = $info['id_cue'];
            
            $aux['clave'] = "10-0-1";
            $aux['direccion'] = "Jose Tomas Rider 1185";
            $aux['maquinas'] = "B13 B14 Q15";
            $aux['acargocuerpo'] = "DIEGO GOMEZ B 13 cia"; // FUNCION QUE MUESTRA EL QUE ESTA A CARGO DEL CUERPO
            $aux['lat'] = "-33.439797";
            $aux['lng'] = "-70.616939";
            $aux['datetime'] = "27-09-1984 18:30";
            $aux['preinforme'] = "Se trata de fuego en cocina, se trabaja";
            
            $aux2['nombre'] = "B13";
            $aux2['acargo'] = "Diego Gomez B.";
            $aux2['6'] = "6";
            $aux2['lat'] = "-33.439797";
            $aux2['lng'] = "-70.616939";
            
            $aux['lmaquinas'][] = $aux2;

            $aux2['nombre'] = "B13";
            $aux2['acargo'] = "Diego Gomez B.";
            $aux2['6'] = "6";
            $aux2['lat'] = "-33.439797";
            $aux2['lng'] = "-70.616939";
            
            $aux['lmaquinas'][] = $aux2;
            
            $aux2['nombre'] = "B13";
            $aux2['acargo'] = "Diego Gomez B.";
            $aux2['6'] = "6";
            $aux2['lat'] = "-33.439797";
            $aux2['lng'] = "-70.616939";
            
            $aux['lmaquinas'][] = $aux2;
            
            $aux['grifos'][0]['lat'] = "-33.439797";
            $aux['grifos'][0]['lng'] = "-70.616939";
            $aux['grifos'][0]['metros'] = "35";
            
            $aux['grifos'][1]['lat'] = "-33.437797";
            $aux['grifos'][1]['lng'] = "-70.612939";
            $aux['grifos'][1]['metros'] = "245";
            
            $aux['grifos'][2]['lat'] = "-33.435797";
            $aux['grifos'][2]['lng'] = "-70.611939";
            $aux['grifos'][2]['metros'] = "785";
            
            
            $aux['volcamino'][0]['nombre'] = "Diego Gomez";
            $aux['volcamino'][0]['lat'] = "-33.439797";
            $aux['volcamino'][0]['lng'] = "-70.612939";
            $aux['volcamino'][0]['metros'] = "2100";
            $aux['volcamino'][0]['segundos'] = "180";
            $aux['volcamino'][1]['cia'] = "13";
            
            $aux['volcamino'][1]['nombre'] = "Juan Perez";
            $aux['volcamino'][1]['lat'] = "-33.439197";
            $aux['volcamino'][1]['lng'] = "-70.614939";
            $aux['volcamino'][1]['metros'] = "1700";
            $aux['volcamino'][1]['segundos'] = "130";
            $aux['volcamino'][1]['cia'] = "14";
            
            $aux['volcamino'][1]['nombre'] = "Jorge Diaz";
            $aux['volcamino'][1]['lat'] = "-33.439297";
            $aux['volcamino'][1]['lng'] = "-70.614239";
            $aux['volcamino'][1]['metros'] = "1950";
            $aux['volcamino'][1]['segundos'] = "145";
            $aux['volcamino'][1]['cia'] = "15";
            
            
            $this->anexostatus("llamados", $aux);
            $this->setstatus(1, "Info Llamado");
            
        }else{
            
            // SI NO ES USER
            $this->setstatus(0, "No se reconoce Usuario");
            
        }
        return $this->getstatus();
        
    }
    
}
?>