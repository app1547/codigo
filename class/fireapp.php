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
    public function ubicagrifos($lat, $lng, $distance){
        
        $return = array();
        $earthRadius = 6371;
        $distance = $distance / 1000;
        $cardinalCoords = array('north' => '0', 'south' => '180', 'east' => '90', 'west' => '270');
        $rLat = deg2rad($lat);
        $rLng = deg2rad($lng);
        $rAngDist = $distance/$earthRadius;

        foreach ($cardinalCoords as $name => $angle){

            $rAngle = deg2rad($angle);
            $rLatB = asin(sin($rLat) * cos($rAngDist) + cos($rLat) * sin($rAngDist) * cos($rAngle));
            $rLonB = $rLng + atan2(sin($rAngle) * sin($rAngDist) * cos($rLat), cos($rAngDist) - sin($rLat) * sin($rLatB));
            $return[$name] = array('lat' => (float) rad2deg($rLatB), 'lng' => (float) rad2deg($rLonB));

        }

        return array('min_lat'  => $return['south']['lat'], 'max_lat' => $return['north']['lat'], 'min_lng' => $return['west']['lng'], 'max_lng' => $return['east']['lng']);
        
    }
    
}
?>