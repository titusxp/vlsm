<?php

require_once(dirname(__FILE__) . "/../../../startup.php");

//this file receives the lab results and updates in the remote db
$jsonResponse = file_get_contents('php://input');


$general = new \Vlsm\Models\General();
$usersModel = new \Vlsm\Models\Users();
$app = new \Vlsm\Models\App();

$arr  = $general->getGlobalConfig();
$sarr  = $general->getSystemConfig();

//get remote data
if (trim($sarr['sc_testing_lab_id']) == '') {
    $sarr['sc_testing_lab_id'] = "''";
}

if (!empty($jsonResponse) && $jsonResponse != '[]') {

    $allColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_SCHEMA = '" . SYSTEM_CONFIG['dbName'] . "' AND table_name='form_covid19'";
    $allColResult = $db->rawQuery($allColumns);
    $oneDimensionalArray = array_map('current', $allColResult);
    $sampleCode = array();

    $counter = 0;

    $lab = array();
    $options = [
        'pointer' => '/result',
        'decoder' => new \JsonMachine\JsonDecoder\ExtJsonDecoder(true)
    ];    
    $parsedData = \JsonMachine\Items::fromString($jsonResponse, $options);
    foreach ($parsedData as $key => $remoteData) {
        $counter++;
        foreach ($oneDimensionalArray as $result) {
            if (isset($remoteData[$result])) {
                $lab[$result] = $remoteData[$result];
            } else {
                $lab[$result] = null;
            }
        }

        //remove fields that we DO NOT NEED here
        $removeKeys = array(
            'covid19_id',
            'sample_package_id',
            'sample_package_code',
            //'last_modified_by',
            'request_created_by',
        );
        foreach ($removeKeys as $keys) {
            unset($lab[$keys]);
        }

        if (isset($remoteData['approved_by_name']) && $remoteData['approved_by_name'] != '') {

            $lab['result_approved_by'] = $usersModel->addUserIfNotExists($remoteData['approved_by_name']);
            $lab['result_approved_datetime'] =  $general->getCurrentDateTime();
            // we dont need this now
            //unset($remoteData['approved_by_name']);
        }

        $lab['data_sync'] = 1; //data_sync = 1 means data sync done. data_sync = 0 means sync is not yet done.
        $lab['last_modified_datetime'] = $general->getCurrentDateTime();

        unset($lab['request_created_by']);
        unset($lab['last_modified_by']);
        unset($lab['request_created_datetime']);

        if ($lab['result_status'] != 7 && $lab['result_status'] != 4) {
            unset($lab['result']);
            unset($lab['is_sample_rejected']);
            unset($lab['reason_for_sample_rejection']);
        }

        // Checking if Remote Sample Code is set, if not set we will check if Sample Code is set
        if (isset($lab['remote_sample_code']) && $lab['remote_sample_code'] != '') {
            $sQuery = "SELECT covid19_id,sample_code,remote_sample_code,remote_sample_code_key FROM form_covid19 WHERE remote_sample_code='" . $lab['remote_sample_code'] . "'";
        } else if (isset($lab['sample_code']) && !empty($lab['sample_code']) && !empty($lab['facility_id']) && !empty($lab['lab_id'])) {
            $sQuery = "SELECT covid19_id,sample_code,remote_sample_code,remote_sample_code_key FROM form_covid19 WHERE sample_code='" . $lab['sample_code'] . "' AND facility_id = " . $lab['facility_id'];
        } else {
            $sampleCode[] = $lab['sample_code'];
            continue;
        }
        $lab['source_of_request'] = 'vlsts';
        $sResult = $db->rawQuery($sQuery);
        if ($sResult) {
            $db = $db->where('covid19_id', $sResult[0]['covid19_id']);
            $id = $db->update('form_covid19', $lab);
        } else {
            $id = $db->insert('form_covid19', $lab);
        }

        if ($id > 0 && isset($lab['sample_code'])) {
            $sampleCode[] = $lab['sample_code'];
        }
    }

    
    // $parsedData = \JsonMachine\Items::fromString($jsonResponse, "/symptoms");
    // foreach ($parsedData as $covid19Id => $symptoms) {
    //     $db = $db->where('covid19_id', $covid19Id);
    //     $db->delete("covid19_patient_symptoms");
    //     foreach ($symptoms as $symId => $symValue) {
    //         $db->insert("covid19_patient_symptoms", array(
    //             "covid19_id"        => $symValue['covid19_id'],
    //             "symptom_id"        => $symValue['symptom_id'],
    //             "symptom_detected"  => $symValue['symptom_detected']
    //         ));
    //     }
    // }

    // $parsedData = \JsonMachine\Items::fromString($jsonResponse, "/comorbidities");
    // foreach ($parsedData as $covid19Id => $comorbidities) {
    //     $db = $db->where('covid19_id', $covid19Id);
    //     $db->delete("covid19_patient_comorbidities");

    //     foreach ($comorbidities as $comorbiditiesId => $comorbiditiesValue) {
    //         $db->insert("covid19_patient_comorbidities", array(
    //             "covid19_id"            => $comorbiditiesValue['covid19_id'],
    //             "comorbidity_id"        => $comorbiditiesValue['comorbidity_id'],
    //             "comorbidity_detected"  => $comorbiditiesValue['comorbidity_detected']
    //         ));
    //     }
    // }

    // $parsedData = \JsonMachine\Items::fromString($jsonResponse, "/testResults");
    // foreach ($parsedData as $covid19Id => $testResults) {
    //     $db = $db->where('covid19_id', $covid19Id);
    //     $db->delete("covid19_tests");
    //     foreach ($testResults as $testId => $test) {
    //         $db->insert("covid19_tests", array(
    //             "covid19_id"                => $test['covid19_id'],
    //             "test_name"                 => $test['test_name'],
    //             "facility_id"               => $test['facility_id'],
    //             "sample_tested_datetime"    => $test['sample_tested_datetime'],
    //             "testing_platform"          => $test['testing_platform'],
    //             "result"                    => $test['result']
    //         ));
    //     }
    // }


    if ($counter > 0) {
        $trackId = $app->addApiTracking(null, $counter, 'results', 'covid19', null, $sarr['sc_testing_lab_id'], 'sync-api');
    }
}

echo json_encode($sampleCode);
