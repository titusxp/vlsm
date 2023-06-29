<?php

namespace App\DamaServices;

class DamaUpload
{

private $url;
private $data;

function __construct($url, $data){
   $this->url = $url;
   $this->data = $data;
}


function upload(){
    $jsonData = json_encode($this->data);


    $ch = curl_init($this->url);
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    
    curl_close($ch);
    
    return $response;
}


}
