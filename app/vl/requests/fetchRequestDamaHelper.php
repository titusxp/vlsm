<?php 
use App\DamaServices\DamaRequest;


$apiUrl = 'https://masculine-passenger.000webhostapp.com/data.php';
$facilityCode = '0000';
$password = '0000';
$startDate = $_POST['startDate'] ?? null;
//$endDate = $_POST['endDate'] ?? null ;


$apiHelper = new DamaRequest($apiUrl, $facilityCode, $password, $startDate);


$response = $apiHelper->fetchData();

header('Content-Type: application/json');
echo $response;
?>