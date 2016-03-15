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
    public function getgoogledist($lat1, $lat2, $lng1, $lng2, $mode = null){
            
        if($mode == null){
            $mode = "driving";
        }

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$lng1."&destinations=".$lat2.",".$lng2."&mode=".$mode."&language=es";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        print_r($response);
        $aux['disttext'] = $response['rows'][0]['elements'][0]['distance']['text'];
        $aux['distvalue'] = $response['rows'][0]['elements'][0]['distance']['value'];
        $aux['timetext'] = $response['rows'][0]['elements'][0]['duration']['text'];
        $aux['timevalue'] = $response['rows'][0]['elements'][0]['duration']['value'];

        //$aux['originadress'] = $response['origin_addresses'][0];
        //$aux['destinadress'] = $response['destination_addresses'][0];

        return $aux;

    }
    
}
?>