<?php

use App\Registries\ContainerRegistry;

use function GuzzleHttp\json_encode;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

$modifiedResults = [];
$startDate = $_POST['startDate'] ?? null;


$query = "";
$queryOne = "SELECT * FROM form_vl WHERE source_of_request = 'dama' AND data_sync  = '0'";
$queryTwo = "SELECT * FROM form_vl  WHERE source_of_request = 'dama' AND result_approved_datetime > '$startDate'";

if ($startDate == null) {
    $query = $queryOne;
} else {
    $query = $queryTwo;
}

$results = $db->rawQuery($query);

foreach ($results as $rows) {
    $rows['Id'] = $rows['unique_id'];
    unset($rows['unique_id']);

    $rows['SampleReceptionDate'] = $rows['sample_received_at_vl_lab_datetime'];
    unset($rows['sample_received_at_vl_lab_datetime']);

    $rows['RejectioReason'] = $rows['reason_for_sample_rejection'];
    unset($rows['reason_for_sample_rejection']);

    $rows['NotesFromLab'] = $rows['lab_tech_comments'];
    unset($rows['lab_tech_comments']);

    $row['DateOfTest'] = $rows['sample_tested_datetime'];
    unset($rows['sample_tested_datetime']);

    $rows['TestDoneBy'] = $rows['lab_technician'];
    unset($rows['lab_technician']);

    $rows['Result'] = $rows['result'];
    unset($rows['result']);

    $rows['ReviewedBy'] = $rows['result_reviewed_by'];
    unset($rows['result_reviewed_by']);

    $rows['ReviewDate'] = $rows['result_reviewed_datetime'];
    unset($rows['result_reviewed_datetime']);

    $rows['AuthorisedBy'] = $rows['result_approved_by'];
    unset($rows['result_approved_by']);

    $rows['AuthorisedDate'] = $rows['result_approved_datetime'];
    unset($rows['result_approved_datetime']);

    $rows['Comments'] = $rows['approver_comments'];
    unset($rows['approver_comments']);

    $rows['ResultDispatchDate'] = $rows['result_dispatched_datetime'];
    unset($rows['result_dispatched_datetime']);

    $rows['LabFacilityCode'] = $rows['facility_id'];
    unset($rows['facility_id']);

    $modifiedResults[] = $rows;
}




echo json_encode($modifiedResults);
