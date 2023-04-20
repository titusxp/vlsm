<?php

use App\Models\App;
use App\Models\Facilities;
use App\Utilities\DateUtils;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");
header('Content-Type: application/json');

$origData = $jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);


$payload = [];

$labId = $data['labName'] ?: $data['labId'] ?: null;

if (empty($labId)) {
    exit(0);
}
$dataSyncInterval = $general->getGlobalConfig('data_sync_interval');
$dataSyncInterval = (isset($dataSyncInterval) && !empty($dataSyncInterval)) ? $dataSyncInterval : 30;
$app = new App();

$transactionId = $general->generateUUID();

$facilityDb = new Facilities();
$fMapResult = $facilityDb->getTestingLabFacilityMap($labId);

if (!empty($fMapResult)) {
    $condition = "(lab_id =" . $labId . " OR facility_id IN (" . $fMapResult . "))";
} else {
    $condition = "lab_id =" . $labId;
}


$tbQuery = "SELECT * FROM form_tb WHERE $condition ";

if (!empty($data['manifestCode'])) {
    //$tbQuery .= " AND data_sync=0 AND sample_package_code like '" . $data['manifestCode'] . "%'";
    $tbQuery .= " AND sample_package_code like '" . $data['manifestCode'] . "%'";
} else {
    $tbQuery .= " AND data_sync=0 AND last_modified_datetime > SUBDATE( NOW(), INTERVAL $dataSyncInterval DAY)";
}

$tbRemoteResult = $db->rawQuery($tbQuery);
$data = [];
$counter = 0;
$sampleIds = $facilityIds = [];
if ($db->count > 0) {
    $counter = $db->count;

    $sampleIds = array_column($tbRemoteResult, 'tb_id');
    $facilityIds = array_column($tbRemoteResult, 'facility_id');

    $data['result'] = $tbRemoteResult;
}

$payload = json_encode($data);

$general->addApiTracking($transactionId, 'vlsm-system', $counter, 'requests', 'tb', $_SERVER['REQUEST_URI'], $origData, $payload, 'json', $labId);


$currentDateTime = DateUtils::getCurrentDateTime();
if (!empty($sampleIds)) {
    $sql = 'UPDATE form_tb SET data_sync = ?, 
                form_attributes = JSON_SET(COALESCE(form_attributes, "{}"), "$.remoteRequestsSync", ?, "$.requestSyncTransactionId", ?)
                WHERE tb_id IN (' . implode(",", $sampleIds) . ')';
    $db->rawQuery($sql, array(1, $currentDateTime, $transactionId));
}

if (!empty($facilityIds)) {
    $facilityIds = array_unique(array_filter($facilityIds));
    $sql = 'UPDATE facility_details 
                SET facility_attributes = JSON_SET(COALESCE(facility_attributes, "{}"), "$.remoteRequestsSync", ?, "$.tbRemoteRequestsSync", ?) 
                WHERE facility_id IN (' . implode(",", $facilityIds) . ')';
    $db->rawQuery($sql, array($currentDateTime, $currentDateTime));
}

// Whether any data got synced or not, we will update sync datetime for the lab
$sql = 'UPDATE facility_details 
          SET facility_attributes = JSON_SET(COALESCE(facility_attributes, "{}"), "$.lastRequestsSync", ?, "$.tbLastRequestsSync", ?) 
          WHERE facility_id = ?';
$db->rawQuery($sql, array($currentDateTime, $currentDateTime, $labId));

echo $payload;
