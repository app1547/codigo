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

        $this->status();
        $id_act = $this->getpost('idact');
        
        if(is_numeric($id_act)){
            
            $llamado = $this->con->sql("SELECT * FROM actos t1, claves t2 WHERE t1.id_act='".$id_act."' AND t1.id_cla=t2.id_cla");
        
            $aux['clave'] = $llamado['resultado'][0]['nombre'];
            $aux['direccion'] = $llamado['resultado'][0]['direccion'];
            $aux['maquinas'] = $llamado['resultado'][0]['maquinas'];
            $aux['lat'] = $llamado['resultado'][0]['lat'];
            $aux['lng'] = $llamado['resultado'][0]['lng'];
            $aux['datetime'] = $llamado['resultado'][0]['fecha'];
            $aux['preinforme'] = $llamado['resultado'][0]['preinforme'];
            
            //$aux['acargocuerpo'] = "DIEGO GOMEZ B 13 cia"; // FUNCION QUE MUESTRA EL QUE ESTA A CARGO DEL CUERPO
            
            $maquinas = $this->con->sql("SELECT t2.sigla, t4.numero, t3.nombremostrar, t1.cantidad, t1.id_user, t1.lat, t1.lng FROM actos_maquina t1, maquinas t2, usuarios t3, companias t4 WHERE t1.id_act='".$id_act."' AND t1.id_maq=t2.id_maq AND t1.id_user=t3.id_user AND t2.id_cia=t4.id_cia");
            for($i=0; $i<$maquinas['count']; $i++){
                
                $aux['lmaquinas'][$i]['nombre'] = $maquinas['resultado'][$i]['sigla'].$maquinas['resultado'][$i]['numero'];
                $aux['lmaquinas'][$i]['id_acargo'] = $maquinas['resultado'][$i]['id_user'];
                $aux['lmaquinas'][$i]['nombre_acargo'] = $maquinas['resultado'][$i]['nombremostrar'];
                $aux['lmaquinas'][$i]['cantidad'] = $maquinas['resultado'][$i]['cantidad'];
                $aux['lmaquinas'][$i]['lat'] = $maquinas['resultado'][$i]['lat'];
                $aux['lmaquinas'][$i]['lng'] = $maquinas['resultado'][$i]['lng'];
                
            }
            
            $distance = 2000;
            $coor = $this->ubicagrifos($aux['lat'], $aux['lng'], 2);
            $grifos = $this->con->sql("SELECT id_gri, lat, lng, (6371 * ACOS(SIN(RADIANS(lat)) * SIN(RADIANS(".$aux['lat'].")) + COS(RADIANS(lng - ".$aux['lng'].")) * COS(RADIANS(lat)) * COS(RADIANS(".$aux['lat']."))) * 1000) AS distance
                            FROM grifos
                            WHERE (lat BETWEEN ".$coor['min_lat']." AND ".$coor['max_lat'].")
                            AND (lng BETWEEN ".$coor['min_lng']." AND ".$coor['max_lng'].")
                            HAVING distance  < ".$distance."                             
                            ORDER BY distance ASC ");

            for($i=0; $i<$grifos['count']; $i++){
                
                $aux['grifos'][$i]['id_gri'] = $grifos['resultado'][$i]['id_gri'];
                $aux['grifos'][$i]['lat'] = $grifos['resultado'][$i]['lat'];
                $aux['grifos'][$i]['lng'] = $grifos['resultado'][$i]['lng'];
                $aux['grifos'][$i]['metros'] = $grifos['resultado'][$i]['distance']*1000;
                
            }
            
            $volcamino = $this->con->sql("SELECT * FROM actos_user_camino t1, usuarios t2, companias t3 WHERE t1.id_act='".$id_act."' AND t1.id_user=t2.id_user");
            print_r($volcamino);
            for($i=0; $i<$volcamino['count']; $i++){
                
                $id_cia = $volcamino['resultado'][$i]['id_cia'];
                $cia = $volcamino['resultado'][$i]['numero'];
                
                $auxvol['id_user'] = $volcamino['resultado'][$i]['id_user'];
                $auxvol['nombre'] = $volcamino['resultado'][$i]['nombremostrar'];
                $auxvol['lat'] = $volcamino['resultado'][$i]['lat_actual'];
                $auxvol['lng'] = $volcamino['resultado'][$i]['lng_actual'];
                
                $aux['volcamino'][$cia][] = $auxvol;
                
                
                //$aux['volcamino'][$i]['metros'] = $volcamino['resultado'][$i]['lng_actual'];
                //$aux['volcamino'][$i]['segundos'] = $volcamino['resultado'][$i]['lng_actual'];
                
            }
            
            


            $this->anexostatus("llamados", $aux);
            $this->setstatus(1, "Info Llamado");
        }
        
        return $this->getstatus();
        
    }
    
}
?>