<?php
//get data from remote db send to lab db
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

require_once(dirname(__FILE__) . "/../../../bootstrap.php");
ini_set('memory_limit', -1);
ini_set('max_execution_time', -1);

header('Content-Type: application/json');

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$payload = [];



$origData = $jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

//error_log($jsonData);
$counter = 0;

$transactionId = $general->generateUUID();

if ($data['Key'] == 'vlsm-get-remote') {

    $labId = $data['labId'] ?: null;

    $response = [];

    if (isset(SYSTEM_CONFIG['modules']['genericTests']) && SYSTEM_CONFIG['modules']['genericTests'] === true) {

        $condition = null;
        if (!empty($data['genericSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['genericSampleTypesLastModified'] . "'";
        }
        $response['genericSampleTypes'] = $general->fetchDataFromTable('r_generic_sample_types', $condition);

        $condition = null;
        if (!empty($data['genericRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['genericRejectionReasonsLastModified'] . "'";
        }
        $response['genericRejectionReasons'] = $general->fetchDataFromTable('r_generic_sample_rejection_reasons', $condition);

        $condition = null;
        if (!empty($data['genericFailureReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['genericFailureReasonsLastModified'] . "'";
        }
        $response['genericFailureReasons'] = $general->fetchDataFromTable('r_generic_test_failure_reasons', $condition);

        $counter += (count($response['genericSampleTypes']) + count($response['genericRejectionReasons']) + count($response['genericFailureReasons']));
    }

    if (isset(SYSTEM_CONFIG['modules']['vl']) && SYSTEM_CONFIG['modules']['vl'] === true) {


        $condition = null;
        if (!empty($data['vlRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlRejectionReasonsLastModified'] . "'";
        }
        $response['vlRejectionReasons'] = $general->fetchDataFromTable('r_vl_sample_rejection_reasons', $condition);


        $condition = null;
        if (!empty($data['vlSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlSampleTypesLastModified'] . "'";
        }
        $response['vlSampleTypes'] = $general->fetchDataFromTable('r_vl_sample_type', $condition);

        $condition = null;
        if (!empty($data['vlArtCodesLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlArtCodesLastModified'] . "'";
        }
        $response['vlArtCodes'] = $general->fetchDataFromTable('r_vl_art_regimen', $condition);

        $condition = null;
        if (!empty($data['vlFailureReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlFailureReasonsLastModified'] . "'";
        }
        $response['vlFailureReasons'] = $general->fetchDataFromTable('r_vl_test_failure_reasons', $condition);

        // $condition = null;
        //$response['vlResults'] = [];
        if (!empty($data['vlResultsLastModified'])) {
            $condition = "updated_datetime > '" . $data['vlResultsLastModified'] . "'";
        }
        $response['vlResults'] = $general->fetchDataFromTable('r_vl_results', $condition);

        $counter += (count($response['vlRejectionReasons']) + count($response['vlSampleTypes']) + count($response['vlArtCodes']) + count($response['vlFailureReasons']) + count($response['vlResults']));
    }


    if (isset(SYSTEM_CONFIG['modules']['eid']) && SYSTEM_CONFIG['modules']['eid'] === true) {

        $condition = null;
        if (!empty($data['eidRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['eidRejectionReasonsLastModified'] . "'";
        }
        $response['eidRejectionReasons'] = $general->fetchDataFromTable('r_eid_sample_rejection_reasons', $condition);


        $condition = null;
        if (!empty($data['eidSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['eidSampleTypesLastModified'] . "'";
        }
        $response['eidSampleTypes'] = $general->fetchDataFromTable('r_eid_sample_type', $condition);

        $condition = null;
        if (!empty($data['eidResultsLastModified'])) {
            $condition = "updated_datetime > '" . $data['eidResultsLastModified'] . "'";
        }
        $response['eidResults'] = $general->fetchDataFromTable('r_eid_results', $condition);

        $condition = null;
        if (!empty($data['eidReasonForTestingLastModified'])) {
            $condition = "updated_datetime > '" . $data['eidReasonForTestingLastModified'] . "'";
        }
        $response['eidReasonForTesting'] = $general->fetchDataFromTable('r_eid_test_reasons', $condition);

        $counter += (count($response['eidRejectionReasons']) + count($response['eidSampleTypes']) + count($response['eidResults']) + count($response['eidReasonForTesting']));
    }

    if (isset(SYSTEM_CONFIG['modules']['covid19']) && SYSTEM_CONFIG['modules']['covid19'] === true) {

        $condition = null;
        if (!empty($data['covid19RejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19RejectionReasonsLastModified'] . "'";
        }
        $response['covid19RejectionReasons'] = $general->fetchDataFromTable('r_covid19_sample_rejection_reasons', $condition);


        $condition = null;
        if (!empty($data['covid19SampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19SampleTypesLastModified'] . "'";
        }
        $response['covid19SampleTypes'] = $general->fetchDataFromTable('r_covid19_sample_type', $condition);

        $condition = null;
        if (!empty($data['covid19ComorbiditiesLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19ComorbiditiesLastModified'] . "'";
        }
        $response['covid19Comorbidities'] = $general->fetchDataFromTable('r_covid19_comorbidities', $condition);

        $condition = null;
        if (!empty($data['covid19ResultsLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19ResultsLastModified'] . "'";
        }
        $response['covid19Results'] = $general->fetchDataFromTable('r_covid19_results', $condition);

        $condition = null;
        if (!empty($data['covid19SymptomsLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19SymptomsLastModified'] . "'";
        }
        $response['covid19Symptoms'] = $general->fetchDataFromTable('r_covid19_symptoms', $condition);

        $condition = null;
        if (!empty($data['covid19ReasonForTestingLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19ReasonForTestingLastModified'] . "'";
        }
        $response['covid19ReasonForTesting'] = $general->fetchDataFromTable('r_covid19_test_reasons', $condition);

        $condition = null;
        if (!empty($data['covid19QCTestKitsLastModified'])) {
            $condition = "updated_datetime > '" . $data['covid19QCTestKitsLastModified'] . "'";
        }
        $response['covid19QCTestKits'] = $general->fetchDataFromTable('r_covid19_qc_testkits', $condition);

        $counter += (count($response['covid19RejectionReasons']) + count($response['covid19SampleTypes']) + count($response['covid19Comorbidities']) + count($response['covid19Results']) + count($response['covid19Symptoms']) + count($response['covid19ReasonForTesting']) + count($response['covid19QCTestKits']));
    }

    if (isset(SYSTEM_CONFIG['modules']['hepatitis']) && SYSTEM_CONFIG['modules']['hepatitis'] === true) {

        $condition = null;
        if (!empty($data['hepatitisRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisRejectionReasonsLastModified'] . "'";
        }
        $response['hepatitisRejectionReasons'] = $general->fetchDataFromTable('r_hepatitis_sample_rejection_reasons', $condition);


        $condition = null;
        if (!empty($data['hepatitisSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisSampleTypesLastModified'] . "'";
        }
        $response['hepatitisSampleTypes'] = $general->fetchDataFromTable('r_hepatitis_sample_type', $condition);

        $condition = null;
        if (!empty($data['hepatitisComorbiditiesLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisComorbiditiesLastModified'] . "'";
        }
        $response['hepatitisComorbidities'] = $general->fetchDataFromTable('r_hepatitis_comorbidities', $condition);

        $condition = null;
        if (!empty($data['hepatitisResultsLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisResultsLastModified'] . "'";
        }
        $response['hepatitisResults'] = $general->fetchDataFromTable('r_hepatitis_results', $condition);

        $condition = null;
        if (!empty($data['hepatitisReasonForTestingLastModified'])) {
            $condition = "updated_datetime > '" . $data['hepatitisReasonForTestingLastModified'] . "'";
        }
        $response['hepatitisReasonForTesting'] = $general->fetchDataFromTable('r_hepatitis_test_reasons', $condition);

        $counter += (count($response['hepatitisRejectionReasons']) + count($response['hepatitisSampleTypes']) + count($response['hepatitisComorbidities']) + count($response['hepatitisResults']) + count($response['hepatitisReasonForTesting']));
    }

    if (isset(SYSTEM_CONFIG['modules']['tb']) && SYSTEM_CONFIG['modules']['tb'] === true) {

        $condition = null;
        if (!empty($data['tbRejectionReasonsLastModified'])) {
            $condition = "updated_datetime > '" . $data['tbRejectionReasonsLastModified'] . "'";
        }
        $response['tbRejectionReasons'] = $general->fetchDataFromTable('r_tb_sample_rejection_reasons', $condition);

        $condition = null;
        if (!empty($data['tbSampleTypesLastModified'])) {
            $condition = "updated_datetime > '" . $data['tbSampleTypesLastModified'] . "'";
        }
        $response['tbSampleTypes'] = $general->fetchDataFromTable('r_tb_sample_type', $condition);

        $condition = null;
        if (!empty($data['tbResultsLastModified'])) {
            $condition = "updated_datetime > '" . $data['tbResultsLastModified'] . "'";
        }
        $response['tbResults'] = $general->fetchDataFromTable('r_tb_results', $condition);

        $condition = null;
        if (!empty($data['tbReasonForTestingLastModified'])) {
            $condition = "updated_datetime > '" . $data['tbReasonForTestingLastModified'] . "'";
        }
        $response['tbReasonForTesting'] = $general->fetchDataFromTable('r_tb_test_reasons', $condition);

        $counter += (count($response['tbRejectionReasons']) + count($response['tbSampleTypes']) + count($response['tbResults']) + count($response['tbReasonForTesting']));
    }

    $condition = null;
    if (!empty($data['globalConfigLastModified'])) {
        $condition = "updated_on > '" . $data['globalConfigLastModified'] . "' AND remote_sync_needed = 'yes'";
    }
    $response['globalConfig'] = $general->fetchDataFromTable('global_config', $condition);

    $condition = null;
    if (!empty($data['provinceLastModified'])) {
        $condition = "updated_datetime > '" . $data['provinceLastModified'] . "'";
    }
    $response['province'] = $general->fetchDataFromTable('geographical_divisions', $condition);


    $condition = null;
    $signatureCondition = null;
    // Using same facilityLastModified to check if any signatures were added
    if (!empty($data['facilityLastModified'])) {
        $condition = "updated_datetime > '" . $data['facilityLastModified'] . "'";
        $signatureCondition = "added_on > '" . $data['facilityLastModified'] . "'";
    }
    $response['facilities'] = $general->fetchDataFromTable('facility_details', $condition);

    $response['users'] = [];
    $userIds = array_column($response['facilities'], 'contact_person');

    foreach ($userIds as $userId) {
        if (!empty($userId)) {
            $userInfo = $general->fetchDataFromTable('user_details', "user_id = '$userId'");
            if (!empty($userInfo)) {
                $response['users'][] = $userInfo[0];
            }
        }
    }

    $response['labReportSignatories'] = $general->fetchDataFromTable('lab_report_signatories', $signatureCondition);


    $condition = null;
    if (!empty($data['healthFacilityLastModified'])) {
        $condition = "updated_datetime > '" . $data['healthFacilityLastModified'] . "'";
    }

    $response['healthFacilities'] = $general->fetchDataFromTable('health_facilities', $condition);

    $condition = null;
    if (!empty($data['testingLabsLastModified'])) {
        $condition = "updated_datetime > '" . $data['testingLabsLastModified'] . "'";
    }
    $response['testingLabs'] = $general->fetchDataFromTable('testing_labs', $condition);

    $condition = null;
    if (!empty($data['fundingSourcesLastModified'])) {
        $condition = "updated_datetime > '" . $data['fundingSourcesLastModified'] . "'";
    }
    $response['fundingSources'] = $general->fetchDataFromTable('r_funding_sources', $condition);

    $condition = null;
    if (!empty($data['partnersLastModified'])) {
        $condition = "updated_datetime > '" . $data['partnersLastModified'] . "'";
    }
    $response['partners'] = $general->fetchDataFromTable('r_implementation_partners', $condition);

    $condition = null;
    if (!empty($data['geoDivisionsLastModified'])) {
        $condition = "updated_datetime > '" . $data['geoDivisionsLastModified'] . "'";
    }
    $response['geoDivisions'] = $general->fetchDataFromTable('geographical_divisions', $condition);


    if (!empty($response)) {
        // using array_filter without callback will remove keys with empty values
        $payload = json_encode(array_filter($response));
    } else {
        $payload = json_encode([]);
    }

    $general->addApiTracking($transactionId, 'vlsm-system', $counter, 'common-data-sync', 'common', $_SERVER['REQUEST_URI'], $origData, $payload, 'json', $labId);

    $sql = 'UPDATE facility_details SET facility_attributes = JSON_SET(COALESCE(facility_attributes, "{}"), "$.lastHeartBeat", ?) WHERE facility_id = ?';
    $db->rawQuery($sql, array(DateUtility::getCurrentDateTime(), $labId));

    echo $payload;
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid request'));
}
