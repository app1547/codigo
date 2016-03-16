<?php

include('fireapp.php');

class FireappSmart extends Fireapp{

    public $con = null;
    public $status = null;
    public $debug = false;

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
            if($accion == "setPosicion"){
                return $this->setPosicion();
            }

        }
        print_r($_POST);
        $ret['estado'] = 0;
        $ret['mensaje'] = "No se reconoce accion";
        return $ret;

    }
    private function token(){
        
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
            
            $volcamino = $this->con->sql("SELECT * FROM actos_user_camino t1, usuarios t2, companias t3 WHERE id_act='".$id_act."' AND t1.id_user=t2.id_user AND t2.id_cia=t3.id_cia");
            for($i=0; $i<$volcamino['count']; $i++){
                
                
                $id_cia = $volcamino['resultado'][$i]['id_cia'];
                $cia = $volcamino['resultado'][$i]['numero'];
                $id_user = $volcamino['resultado'][$i]['id_user'];
                
                $auxvol['id_user'] = $volcamino['resultado'][$i]['id_user'];
                $auxvol['nombre'] = $volcamino['resultado'][$i]['nombremostrar'];
                $auxvol['lat'] = $volcamino['resultado'][$i]['lat_actual'];
                $auxvol['lng'] = $volcamino['resultado'][$i]['lng_actual'];
                
                $aux['totalvol'][$id_cia][$id_user]['id_user'] = $volcamino['resultado'][$i]['id_user']; 
                $aux['totalvol'][$id_cia][$id_user]['nombre'] = $volcamino['resultado'][$i]['nombremostrar'];
                
                //$diflat = $aux['lat'];
                // CALCULAR NUEVA DISTANCIA Y TIEMPO
                $aux['volcamino'][$cia][] = $auxvol;
                
                //$aux['volcamino'][$i]['metros'] = $volcamino['resultado'][$i]['lng_actual'];
                //$aux['volcamino'][$i]['segundos'] = $volcamino['resultado'][$i]['lng_actual'];
                
            }
            
            $totalvol = $this->con->sql("SELECT * FROM actos_cia t1, usuarios t2 WHERE t1.id_act='".$id_act."' AND t1.id_cia=t2.id_cia AND t2.cuartel='1'");
            
            for($i=0; $i<$totalvol['count']; $i++){
                
                $id_cia = $totalvol['resultado'][$i]['id_cia'];
                $id_user = $totalvol['resultado'][$i]['id_user'];
                $aux['totalvol'][$id_cia][$id_user]['id_user'] = $totalvol['resultado'][$i]['id_user']; 
                $aux['totalvol'][$id_cia][$id_user]['nombre'] = $totalvol['resultado'][$i]['nombremostrar']; 
                
            }
            
            $this->anexostatus("llamados", $aux);
            $this->setstatus(1, "Info Llamado");
        }
        
        return $this->getstatus();
        
    }
    
    public function setPosicion(){
        
        
        $info = $this->token();
        $this->status();
        $this->anexostatus("post", $_POST);
        if($info['estado']){
            
            $id_user = $info['id_user'];
            $id_act = $this->getpost('idact');
            $lat = $this->getpost('lat');
            $lng = $this->getpost('lng');
            $modo = $this->getpost('modo');
            
            $result = $this->con->sql("SELECT * FROM actos_user_camino WHERE id_act='".$id_act."' AND id_user='".$id_user."'");
            if($result['count'] == 0){
                
                $this->con->sql("INSERT INTO actos_user_camino (id_act, id_user) VALUES ('".$id_act."', '".$id_user."')");
                if($lat != "" && $lng != ""){
                    $act = $this->con->sql("SELECT lat, lng FROM actos WHERE id_act='".$id_act."'");
                    $google = $this->getgoogledist($act['resultado'][0]['lat'], $lat, $act['resultado'][0]['lng'], $lng, $modo);
                    $s = $this->con->sql("UPDATE actos_user_camino SET lat='".$lat."', lng='".$lng."', lat_actual='".$lat."', lng_actual='".$lng."', modo='".$modo."', fecha=now(), posicion='1', distancia='".$google['distvalue']."', tiempo='".$google['timevalue']."' WHERE id_act='".$id_act."' AND id_user='".$id_user."'");
                    
                }
                $this->setstatus(1, "Primera Vez Ingresado");    
                
            }else{
                
                if($lat != "" && $lng != ""){
                    if($result['resultado'][0]['posicion'] == 0){
                        $act = $this->con->sql("SELECT lat, lng FROM actos WHERE id_act='".$id_act."'");
                        $google = $this->getgoogledist($act['resultado'][0]['lat'], $lat, $act['resultado'][0]['lng'], $lng, $modo);
                        $this->con->sql("UPDATE actos_user_camino SET lat='".$lat."', lng='".$lng."', lat_actual='".$lat."', lng_actual='".$lng."', modo='".$modo."', fecha=now(), posicion='1', distancia='".$google['distvalue']."', tiempo='".$google['timevalue']."' WHERE id_act='".$id_act."' AND id_user='".$id_user."'");
                    }else{
                        $s = $this->con->sql("UPDATE actos_user_camino SET lat_actual='".$lat."', lng_actual='".$lng."' WHERE id_act='".$id_act."' AND id_user='".$id_user."'");
                        $this->con->sql("INSERT INTO history (id_user, lat, lng) VALUES ('".$id_user."', '".$lat."', '".$lng."')");
                    }
                }
                $this->setstatus(1, "Mas de un Vez Ingresado");   
            }

            
        }else{
            $this->setstatus(0, "Error: no es posible efectuar la instruccion");
        }
        
        return $this->getstatus();
        
    }
    
    
    
}
?>