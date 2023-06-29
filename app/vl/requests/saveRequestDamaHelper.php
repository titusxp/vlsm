<?php

use App\Services\VlService;
use App\Services\UsersService;
use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var VlService $vlService */
$vlService = ContainerRegistry::get(VlService::class);

$instanceId = $general->getInstanceId();

$tableName = "form_vl";
$tableName1 = "activity_log";

$systemType = $general->getSystemConfig('sc_user_type');

$formId = $general->getGlobalConfig('vl_form');

function getFacilityId($facilityCode, $db)
{
    $db = $db->where('facility_code', $facilityCode);
    $result = $db->getOne('facility_details');
    return $result['facility_id'] ?? null;
}

function isBreastfeeding($value)
{
    switch ($value) {
        case 0:
            return "no";
        case 1:
            return "yes";
        default:
            return null;
    }
}
function isPregnant($value)
{
    switch ($value) {
        case 0:
            return "no";
        case 1:
            return "yes";
        default:
            return null;
    }
}

function sex($value)
{
    switch ($value) {
        case 1:
            return "male";
        case 2:
            return "female";
        default:
            return null;
    }
}

function regimen($regimenCode, $db){
    $db = $db->where('regime_code', $regimenCode);
    $result = $db->getOne('r_vl_art_regimen');
    return $result['art_code'] ?? null;
}


function saveDataToDatabase($data, $db, $instanceId, $formId)
{
    $response = "";
    try {
        foreach ($data as $item) {
            $vlData = array(

                'vlsm_instance_id'                      => $instanceId,
                'unique_id'                             => $item['Id'],
                'sample_code'                           => $item['Id'],
                'result_status'                         => 6,
                'request_created_by'                    => $item['PrescriberName'],
                'vlsm_country_id'                       => $formId ?? 9,
                'sample_reordered'                      => 'no',
                'external_sample_code'                  => null,
                'facility_id'                           => getFacilityId($item['SiteCode'], $db),
                'sample_collection_date'                => $item['SampleCollectionDate'],
                'sample_dispatched_datetime'            => $item['DateSentToLab'],
                'patient_gender'                        => sex($item['Sex']),
                'patient_dob'                           => $item['BirthDate'],
                'patient_age_in_years'                  => null,
                'patient_age_in_months'                 => null,
                'is_patient_pregnant'                   => isPregnant($item['IsPregnant']),
                'is_patient_breastfeeding'              => isBreastfeeding($item['IsBreastfeeding']),
                'pregnancy_trimester'                   => null,
                'patient_has_active_tb'                 => null,
                'patient_active_tb_phase'               => null,
                'patient_art_no'                        => $item['ExistingARTCode'] ?? null,
                'is_patient_new'                        => null,
                'treatment_duration'                    => null,
                'treatment_indication'                  => null,
                'treatment_initiated_date'              => DateUtility::getCurrentDateTime(),
                'current_regimen'                       => regimen($item['RegimenCode'], $db),
                'has_patient_changed_regimen'           => null,
                'reason_for_regimen_change'             => null,
                'regimen_change_date'                   => DateUtility::getCurrentDateTime(),
                'line_of_treatment'                     => null,
                'line_of_treatment_failure_assessed'    => null,
                'date_of_initiation_of_current_regimen' => DateUtility::getCurrentDateTime(),
                'patient_mobile_number'                 => null,
                'consent_to_receive_sms'                => null,
                'sample_type'                           => $item['SampleType'],
                'plasma_conservation_temperature'       => null,
                'plasma_conservation_duration'          => null,
                'arv_adherance_percentage'              => null,
                'reason_for_vl_testing'                 => $item['ViralLoadReason'],
                'last_viral_load_result'                => null,
                'last_viral_load_date'                  => DateUtility::getCurrentDateTime(),
                'community_sample'                      => null,
                'last_vl_date_routine'                  => DateUtility::getCurrentDateTime(),
                'last_vl_result_routine'                => null,
                'last_vl_sample_type_routine'           => null,
                'last_vl_date_failure_ac'               => DateUtility::getCurrentDateTime(),
                'last_vl_result_failure_ac'             => null,
                'last_vl_sample_type_failure_ac'        => null,
                'last_vl_date_failure'                  => DateUtility::getCurrentDateTime(),
                'last_vl_result_failure'                => null,
                'last_vl_sample_type_failure'           => null,
                'request_clinician_name'                => $item['PrescriberName'],
                'request_clinician_phone_number'        => null,
                'test_requested_on'                     => DateUtility::getCurrentDateTime(),
                'vl_focal_person'                       => null,
                'vl_focal_person_phone_number'          => null,
                'lab_id'                                => getFacilityId($item['ReferenceLabId'], $db),
                'vl_test_platform'                      => null,
                'sample_received_at_hub_datetime'       => DateUtility::getCurrentDateTime(),
                'sample_received_at_vl_lab_datetime'    => DateUtility::getCurrentDateTime(),
                'sample_tested_datetime'                => DateUtility::getCurrentDateTime(),
                'result_dispatched_datetime'            => DateUtility::getCurrentDateTime(),
                'result_value_hiv_detection'            => null,
                'reason_for_failure'                    => null,
                'is_sample_rejected'                    => null,
                'reason_for_sample_rejection'           => null,
                'rejection_on'                          => DateUtility::getCurrentDateTime(),
                'result_value_absolute'                 => null,
                'result_value_absolute_decimal'         => null,
                'result_value_text'                     => null,
                'result'                                => null,
                'result_value_log'                      => null,
                'result_reviewed_by'                    => null,
                'result_reviewed_datetime'              => null,
                'tested_by'                             => null,
                'result_approved_by'                    => null,
                'result_approved_datetime'              => DateUtility::getCurrentDateTime(),
                'date_test_ordered_by_physician'        => DateUtility::getCurrentDateTime(),
                'lab_tech_comments'                     => null,
                'funding_source'                        => null,
                'implementing_partner'                  => $item['ImplementingPartnerId'] ?? null,
                'vl_test_number'                        => null,
                'request_created_datetime'              => DateUtility::getCurrentDateTime(),
                'last_modified_datetime'                => DateUtility::getCurrentDateTime(),
                'manual_result_entry'                   => 'yes',
                'source_of_request'                     => 'dama',
                'remote_sample'                         => 'yes',
                'request_imported_datetime'             => DateUtility::getCurrentDateTime()
                
            );
            $response = $db->insert("form_vl", $vlData);
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        $response = $errorMessage;
    }

    return $response;
}

$data = $_POST['data'];

$viralLoadData = json_decode(json_encode($data), true);
$response = saveDataToDatabase($viralLoadData, $db, $instanceId, $formId);

if (is_int($response)) {
    $eventType = 'Import VL Request from Dama';
    $action = $_SESSION['userName'] . ' imported VL Request from Dama';
    $resource = 'vl-request';
    $general->activityLog($eventType, $action, $resource);
}


echo json_encode(array('message' => $response));
