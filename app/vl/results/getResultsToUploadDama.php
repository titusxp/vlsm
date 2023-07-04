<?php

use App\Registries\ContainerRegistry;

use function GuzzleHttp\json_encode;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

$startDate = $_POST['startDate'] ?? null;


$query = "";
$queryOne = "SELECT 

vl_sample_id,
unique_id,
vlsm_instance_id,
sample_received_at_vl_lab_datetime,
result_approved_datetime,
result_approved_by,
tested_by,
lab_tech_comments,
result_reviewed_by,
result_reviewed_datetime,
result,
approver_comments,
result_value_log,
result_value_absolute,
vl_test_platform,
cphl_vl_result,
rejection_on,
reason_for_sample_rejection,
sample_testing_date


 FROM form_vl WHERE source_of_request = 'dama' AND data_sync  = '0'";
// AND result_approved_datetime != NULL


$queryTwo = "SELECT * FROM form_vl  WHERE source_of_request = 'dama' AND result_approved_datetime > '$startDate'";

if ($startDate == null) {
    $query = $queryOne;
} else {
    $query = $queryTwo;
}

$results = $db->rawQuery($query);

echo json_encode($results);
