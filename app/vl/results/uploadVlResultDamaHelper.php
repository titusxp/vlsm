<?php
use App\DamaServices\DamaUpload;

$data = $_POST['data'] ?? null;

$url = 'https://masculine-passenger.000webhostapp.com/receive_data.php';

$apiHelper = new DamaUpload($url, $data);

$response = $apiHelper->upload();

header('Content-Type: application/json');
echo json_encode($response);

?>

