<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\GenericTestsService;
use App\Utilities\DateUtility;

if (session_status() == PHP_SESSION_NONE) {
     session_start();
}



/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var GenericTestsService $genericTestsService */
$genericTestsService = ContainerRegistry::get(GenericTestsService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();

$tableName = "form_generic";
$testTableName = "generic_test_results";
$vlTestReasonTable = "r_generic_test_reasons";
$fDetails = "facility_details";
$vl_result_category = null;
$vlResult = null;
$logVal = null;
$absDecimalVal = null;
$absVal = null;
$txtVal = null;
$resultStatus = null;
// echo "<pre>";print_r($_POST);die;
try {
     if (isset($_POST['api']) && $_POST['api'] == "yes") {
     } else {
          $validateField = array($_POST['sampleCode'], $_POST['sampleCollectionDate']);
          $chkValidation = $general->checkMandatoryFields($validateField);
          if ($chkValidation) {
               $_SESSION['alertMsg'] = _("Please enter all mandatory fields to save the test request");
               header("Location:edit-request.php?id=" . base64_encode($_POST['vlSampleId']));
               die;
          }
     }
     //system config
     $systemConfigQuery = "SELECT * from system_config";
     $systemConfigResult = $db->query($systemConfigQuery);
     $sarr = [];
     // now we create an associative array so that we can easily create view variables
     for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
          $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
     }

     //add province
     $splitProvince = explode("##", $_POST['province']);
     if (isset($splitProvince[0]) && trim($splitProvince[0]) != '') {
          $provinceQuery = "SELECT * from geographical_divisions where geo_name='" . $splitProvince[0] . "'";
          $provinceInfo = $db->query($provinceQuery);
          if (!isset($provinceInfo) || empty($provinceInfo)) {
               $db->insert('geographical_divisions', array('geo_name' => $splitProvince[0], 'geo_code' => $splitProvince[1]));
          }
     }
     if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != "") {
          $sampleDate = explode(" ", $_POST['sampleCollectionDate']);
          $_POST['sampleCollectionDate'] = DateUtility::isoDateFormat($sampleDate[0]) . " " . $sampleDate[1];
     } else {
          $_POST['sampleCollectionDate'] = null;
     }

     if (isset($_POST['dob']) && trim($_POST['dob']) != "") {
          $_POST['dob'] = DateUtility::isoDateFormat($_POST['dob']);
     } else {
          $_POST['dob'] = null;
     }

     //Sample type section
     if (isset($_POST['specimenType']) && trim($_POST['specimenType']) != "") {
          if (trim($_POST['specimenType']) != 2) {
               $_POST['conservationTemperature'] = null;
               $_POST['durationOfConservation'] = null;
          }
     } else {
          $_POST['specimenType'] = null;
          $_POST['conservationTemperature'] = null;
          $_POST['durationOfConservation'] = null;
     }

     //update facility code
     if (trim($_POST['fCode']) != '') {
          $fData = array('facility_code' => $_POST['fCode']);
          $db = $db->where('facility_id', $_POST['fName']);
          $id = $db->update($fDetails, $fData);
     }

     if (isset($_POST['gender']) && trim($_POST['gender']) == 'male') {
          $_POST['patientPregnant'] = '';
          $_POST['breastfeeding'] = '';
     }
     $instanceId = '';
     if (isset($_SESSION['instanceId'])) {
          $instanceId = $_SESSION['instanceId'];
     }
     $testingPlatform = '';
     if (isset($_POST['testPlatform']) && trim($_POST['testPlatform']) != '') {
          $platForm = explode("##", $_POST['testPlatform']);
          $testingPlatform = $platForm[0];
     }
     if (isset($_POST['sampleReceivedDate']) && trim($_POST['sampleReceivedDate']) != "") {
          $sampleReceivedDateLab = explode(" ", $_POST['sampleReceivedDate']);
          $_POST['sampleReceivedDate'] = DateUtility::isoDateFormat($sampleReceivedDateLab[0]) . " " . $sampleReceivedDateLab[1];
     } else {
          $_POST['sampleReceivedDate'] = null;
     }


     if (isset($_POST['sampleReceivedAtHubOn']) && trim($_POST['sampleReceivedAtHubOn']) != "") {
          $sampleReceivedAtHubOn = explode(" ", $_POST['sampleReceivedAtHubOn']);
          $_POST['sampleReceivedAtHubOn'] = DateUtility::isoDateFormat($sampleReceivedAtHubOn[0]) . " " . $sampleReceivedAtHubOn[1];
     } else {
          $_POST['sampleReceivedAtHubOn'] = null;
     }

     if (isset($_POST['approvedOn']) && trim($_POST['approvedOn']) != "") {
          $approvedOn = explode(" ", $_POST['approvedOn']);
          $_POST['approvedOn'] = DateUtility::isoDateFormat($approvedOn[0]) . " " . $approvedOn[1];
     } else {
          $_POST['approvedOn'] = null;
     }

     if (isset($_POST['sampleTestingDateAtLab']) && trim($_POST['sampleTestingDateAtLab']) != "") {
          $sampleTestingDateAtLab = explode(" ", $_POST['sampleTestingDateAtLab']);
          $_POST['sampleTestingDateAtLab'] = DateUtility::isoDateFormat($sampleTestingDateAtLab[0]) . " " . $sampleTestingDateAtLab[1];
     } else {
          $_POST['sampleTestingDateAtLab'] = null;
     }
     if (isset($_POST['resultDispatchedOn']) && trim($_POST['resultDispatchedOn']) != "") {
          $resultDispatchedOn = explode(" ", $_POST['resultDispatchedOn']);
          $_POST['resultDispatchedOn'] = DateUtility::isoDateFormat($resultDispatchedOn[0]) . " " . $resultDispatchedOn[1];
     } else {
          $_POST['resultDispatchedOn'] = null;
     }
     if (isset($_POST['sampleDispatchedDate']) && trim($_POST['sampleDispatchedDate']) != "") {
          $sampleDispatchedDate = explode(" ", $_POST['sampleDispatchedDate']);
          $_POST['sampleDispatchedDate'] = DateUtility::isoDateFormat($sampleDispatchedDate[0]) . " " . $sampleDispatchedDate[1];
     } else {
          $_POST['sampleDispatchedDate'] = null;
     }

     if (isset($_POST['newRejectionReason']) && trim($_POST['newRejectionReason']) != "") {
          $rejectionReasonQuery = "SELECT rejection_reason_id FROM r_generic_sample_rejection_reasons where rejection_reason_name='" . $_POST['newRejectionReason'] . "' OR rejection_reason_name='" . strtolower($_POST['newRejectionReason']) . "' OR rejection_reason_name='" . (strtolower($_POST['newRejectionReason'])) . "'";
          $rejectionResult = $db->rawQuery($rejectionReasonQuery);
          if (!isset($rejectionResult[0]['rejection_reason_id'])) {
               $data = array(
                    'rejection_reason_name' => $_POST['newRejectionReason'],
                    'rejection_type' => 'general',
                    'rejection_reason_status' => 'active',
                    'updated_datetime' => DateUtility::getCurrentDateTime(),
               );
               $id = $db->insert('r_generic_sample_rejection_reasons', $data);
               $_POST['rejectionReason'] = $id;
          } else {
               $_POST['rejectionReason'] = $rejectionResult[0]['rejection_reason_id'];
          }
     }

     $isRejected = false;
     if (isset($_POST['noResult']) && $_POST['noResult'] == 'yes') {
          $vl_result_category = 'rejected';
          $isRejected = true;
          $vldata['result_status'] = 4;
     }

     $reasonForChanges = '';
     $allChange = [];
     if (isset($_POST['reasonForResultChangesHistory']) && $_POST['reasonForResultChangesHistory'] != '') {
          $allChange = json_decode(base64_decode($_POST['reasonForResultChangesHistory']), true);
     }
     if (isset($_POST['reasonForResultChanges']) && trim($_POST['reasonForResultChanges']) != '') {
          $allChange[] = array(
               'usr' => $_SESSION['userId'],
               'msg' => $_POST['reasonForResultChanges'],
               'dtime' => DateUtility::getCurrentDateTime()
          );
     }
     if (!empty($allChange)) {
          $reasonForChanges = json_encode($allChange);
     }
   
     if (isset($_POST['reviewedOn']) && trim($_POST['reviewedOn']) != "") {
          $reviewedOn = explode(" ", $_POST['reviewedOn']);
          $_POST['reviewedOn'] = DateUtility::isoDateFormat($reviewedOn[0]) . " " . $reviewedOn[1];
     } else {
          $_POST['reviewedOn'] = null;
     }

     if (isset($_POST['treatmentIndication']) && $_POST['treatmentIndication'] == "Other") {
          $_POST['treatmentIndication'] = $_POST['newTreatmentIndication'] . '_Other';
     }
     $interpretationResult = null;
     if(isset($_POST['resultType']) && isset($_POST['testType']) && !empty($_POST['resultType']) && !empty($_POST['testType'])){
          $interpretationResult = $genericTestsService->getInterpretationResults($_POST['testType'], $_POST['result']);
     }


     $vldata = array(
          'vlsm_instance_id'                      => $instanceId,
          'vlsm_country_id'                       => $_POST['countryFormId'] ?? 1,
          'sample_reordered'                      => (isset($_POST['sampleReordered']) && $_POST['sampleReordered'] != '') ? $_POST['sampleReordered'] :  'no',
          'sample_code_format'                    => (isset($_POST['sampleCodeFormat']) && $_POST['sampleCodeFormat'] != '') ? $_POST['sampleCodeFormat'] :  null,
          'external_sample_code'                  => (isset($_POST['serialNo']) && $_POST['serialNo'] != '' ? $_POST['serialNo'] : null),
          'facility_id'                           => (isset($_POST['fName']) && $_POST['fName'] != '') ? $_POST['fName'] :  null,
          'sample_collection_date'                => $_POST['sampleCollectionDate'],
          'sample_dispatched_datetime'            => $_POST['sampleDispatchedDate'],
          'patient_gender'                        => (isset($_POST['gender']) && $_POST['gender'] != '') ? $_POST['gender'] :  null,
          'patient_dob'                           => $_POST['dob'],
          'patient_age_in_years'                  => (isset($_POST['ageInYears']) && $_POST['ageInYears'] != '') ? $_POST['ageInYears'] :  null,
          'patient_age_in_months'                 => (isset($_POST['ageInMonths']) && $_POST['ageInMonths'] != '') ? $_POST['ageInMonths'] :  null,
          'is_patient_pregnant'                   => (isset($_POST['patientPregnant']) && $_POST['patientPregnant'] != '') ? $_POST['patientPregnant'] :  null,
          'is_patient_breastfeeding'              => (isset($_POST['breastfeeding']) && $_POST['breastfeeding'] != '') ? $_POST['breastfeeding'] :  null,
          'pregnancy_trimester'                   => (isset($_POST['trimester']) && $_POST['trimester'] != '') ? $_POST['trimester'] :  null,
          'patient_id'                            => (isset($_POST['artNo']) && $_POST['artNo'] != '') ? $_POST['artNo'] :  null,
          'treatment_indication'                  => (isset($_POST['treatmentIndication']) && $_POST['treatmentIndication'] != '') ? $_POST['treatmentIndication'] :  null,
          'treatment_initiated_date'              => $_POST['dateOfArtInitiation'],
          'patient_mobile_number'                 => (isset($_POST['patientPhoneNumber']) && $_POST['patientPhoneNumber'] != '') ? $_POST['patientPhoneNumber'] :  null,
          'consent_to_receive_sms'                => (isset($_POST['receiveSms']) && $_POST['receiveSms'] != '') ? $_POST['receiveSms'] :  null,
          'sample_type'                           => (isset($_POST['specimenType']) && $_POST['specimenType'] != '') ? $_POST['specimenType'] :  null,
          'request_clinician_name'                => (isset($_POST['reqClinician']) && $_POST['reqClinician'] != '') ? $_POST['reqClinician'] :  null,
          'request_clinician_phone_number'        => (isset($_POST['reqClinicianPhoneNumber']) && $_POST['reqClinicianPhoneNumber'] != '') ? $_POST['reqClinicianPhoneNumber'] :  null,
          'test_requested_on'                     => (isset($_POST['requestDate']) && $_POST['requestDate'] != '') ? DateUtility::isoDateFormat($_POST['requestDate']) :  null,
          'testing_lab_focal_person'              => (isset($_POST['vlFocalPerson']) && $_POST['vlFocalPerson'] != '') ? $_POST['vlFocalPerson'] :  null,
          'testing_lab_focal_person_phone_number' => (isset($_POST['vlFocalPersonPhoneNumber']) && $_POST['vlFocalPersonPhoneNumber'] != '') ? $_POST['vlFocalPersonPhoneNumber'] :  null,
          'lab_id'                                => (isset($_POST['labId']) && $_POST['labId'] != '') ? $_POST['labId'] :  null,
          'test_platform'                         => $testingPlatform,
          'sample_received_at_hub_datetime'       => $_POST['sampleReceivedAtHubOn'],
          'sample_received_at_testing_lab_datetime' => $_POST['sampleReceivedDate'],
          'sample_tested_datetime'                => $_POST['sampleTestingDateAtLab'],
          'reason_for_testing'                    => (isset($_POST['reasonForTesting']) && $_POST['reasonForTesting'] != '') ? $_POST['reasonForTesting'] :  null,
          'result_dispatched_datetime'            => $_POST['resultDispatchedOn'],
          'is_sample_rejected'                    => (isset($_POST['noResult']) && $_POST['noResult'] != '') ? $_POST['noResult'] :  null,
          'reason_for_sample_rejection'           => (isset($_POST['rejectionReason']) && $_POST['rejectionReason'] != '') ? $_POST['rejectionReason'] :  null,
          'rejection_on'                          => (!empty($_POST['rejectionDate'])) ? DateUtility::isoDateFormat($_POST['rejectionDate']) : null,
          'result'                                => $_POST['result'] ?: null,
          'final_result_interpretation'           => $interpretationResult,
          'result_reviewed_by'                    => (isset($_POST['reviewedBy']) && $_POST['reviewedBy'] != "") ? $_POST['reviewedBy'] : null,
          'result_reviewed_datetime'              => (isset($_POST['reviewedOn']) && $_POST['reviewedOn'] != "") ? $_POST['reviewedOn'] : null,
          'tested_by'                             => (isset($_POST['testedBy']) && $_POST['testedBy'] != '') ? $_POST['testedBy'] :  null,
          'result_approved_by'                    => (isset($_POST['approvedBy']) && $_POST['approvedBy'] != '') ? $_POST['approvedBy'] :  null,
          'result_approved_datetime'              => (isset($_POST['approvedOn']) && $_POST['approvedOn'] != '') ? $_POST['approvedOn'] :  null,
          'date_test_ordered_by_physician'        => $_POST['dateOfDemand'],
          'lab_tech_comments'                     => (isset($_POST['labComments']) && trim($_POST['labComments']) != '') ? trim($_POST['labComments']) :  null,
          'funding_source'                        => (isset($_POST['fundingSource']) && trim($_POST['fundingSource']) != '') ? base64_decode($_POST['fundingSource']) : null,
          'implementing_partner'                  => (isset($_POST['implementingPartner']) && trim($_POST['implementingPartner']) != '') ? base64_decode($_POST['implementingPartner']) : null,
          'test_number'                           => (isset($_POST['viralLoadNo']) && $_POST['viralLoadNo'] != '') ? $_POST['viralLoadNo'] :  null,
          'request_created_datetime'              => DateUtility::getCurrentDateTime(),
          'last_modified_datetime'                => DateUtility::getCurrentDateTime(),
          'manual_result_entry'                   => 'yes',
          'test_type'                             => $_POST['testType'],
          'test_type_form'                        => json_encode($_POST['dynamicFields']),
          //'reason_for_failure'                    => (isset($_POST['reasonForFailure']) && $_POST['reasonForFailure'] != '') ? $_POST['reasonForFailure'] :  null
     );

     // only if result status has changed, let us update
     if (!empty($resultStatus)) {
          $vldata['result_status'] = $resultStatus;
     }

     if (isset($_POST['api']) && $_POST['api'] == "yes") {
     } else
          $vldata['last_modified_by'] =  $_SESSION['userId'];
     if ($_SESSION['instanceType'] == 'remoteuser') {
          $vldata['remote_sample_code'] = (isset($_POST['sampleCode']) && $_POST['sampleCode'] != '') ? $_POST['sampleCode'] :  null;
     } else if ($_POST['sampleCodeCol'] != '') {
          $vldata['sample_code'] = (isset($_POST['sampleCodeCol']) && $_POST['sampleCodeCol'] != '') ? $_POST['sampleCodeCol'] :  null;
     }

     if (isset($_POST['cdDate']) && trim($_POST['cdDate']) != "") {
          $_POST['cdDate'] = DateUtility::isoDateFormat($_POST['cdDate']);
     } else {
          $_POST['cdDate'] = null;
     }

     if (isset($_POST['failedTestDate']) && trim($_POST['failedTestDate']) != "") {
          $failedtestDate = explode(" ", $_POST['failedTestDate']);
          $_POST['failedTestDate'] = DateUtility::isoDateFormat($failedtestDate[0]) . " " . $failedtestDate[1];
     } else {
          $_POST['failedTestDate'] = null;
     }
     if (isset($_POST['failedTestingTech']) && $_POST['failedTestingTech'] != '') {
          $platForm = explode("##", $_POST['failedTestingTech']);
          $_POST['failedTestingTech'] = $platForm[0];
     }
     if (isset($_POST['qcDate']) && trim($_POST['qcDate']) != "") {
          $_POST['qcDate'] = DateUtility::isoDateFormat($_POST['qcDate']);
     } else {
          $_POST['qcDate'] = null;
     }

     if (isset($_POST['reportDate']) && trim($_POST['reportDate']) != "") {
          $_POST['reportDate'] = DateUtility::isoDateFormat($_POST['reportDate']);
     } else {
          $_POST['reportDate'] = null;
     }

     //For PNG form
     $pngSpecificFields = [];
     if (isset($_POST['countryFormId']) && $_POST['countryFormId'] == '5') {
          $pngSpecificFields['art_cd_cells'] = $_POST['cdCells'];
          $pngSpecificFields['art_cd_date'] = $_POST['cdDate'];
          $pngSpecificFields['who_clinical_stage'] = $_POST['clinicalStage'];
          $pngSpecificFields['sample_to_transport'] = (isset($_POST['typeOfSample']) && $_POST['typeOfSample'] != '' ? $_POST['typeOfSample'] : null);
          $pngSpecificFields['whole_blood_ml'] = (isset($_POST['wholeBloodOne']) && $_POST['wholeBloodOne'] != '' ? $_POST['wholeBloodOne'] : null);
          $pngSpecificFields['whole_blood_vial'] = (isset($_POST['wholeBloodTwo']) && $_POST['wholeBloodTwo'] != '' ? $_POST['wholeBloodTwo'] : null);
          $pngSpecificFields['plasma_ml'] = (isset($_POST['plasmaOne']) && $_POST['plasmaOne'] != '' ? $_POST['plasmaOne'] : null);
          $pngSpecificFields['plasma_vial'] = (isset($_POST['plasmaTwo']) && $_POST['plasmaTwo'] != '' ? $_POST['plasmaTwo'] : null);
          $pngSpecificFields['plasma_process_time'] = (isset($_POST['processTime']) && $_POST['processTime'] != '' ? $_POST['processTime'] : null);
          $pngSpecificFields['plasma_process_tech'] = (isset($_POST['processTech']) && $_POST['processTech'] != '' ? $_POST['processTech'] : null);
          $pngSpecificFields['sample_collected_by'] = (isset($_POST['collectedBy']) && $_POST['collectedBy'] != '' ? $_POST['collectedBy'] : null);
          $pngSpecificFields['tech_name_png'] = (isset($_POST['techName']) && $_POST['techName'] != '') ? $_POST['techName'] : null;
          $pngSpecificFields['cphl_vl_result'] = (isset($_POST['cphlvlResult']) && $_POST['cphlvlResult'] != '' ? $_POST['cphlvlResult'] : null);
          $pngSpecificFields['batch_quality'] = (isset($_POST['batchQuality']) && $_POST['batchQuality'] != '' ? $_POST['batchQuality'] : null);
          $pngSpecificFields['sample_test_quality'] = (isset($_POST['testQuality']) && $_POST['testQuality'] != '' ? $_POST['testQuality'] : null);
          $pngSpecificFields['sample_batch_id'] = (isset($_POST['batchNo']) && $_POST['batchNo'] != '' ? $_POST['batchNo'] : null);
          $pngSpecificFields['failed_test_date'] = $_POST['failedTestDate'];
          $pngSpecificFields['failed_test_tech'] = (isset($_POST['failedTestingTech']) && $_POST['failedTestingTech'] != '') ? $_POST['failedTestingTech'] : null;
          $pngSpecificFields['failed_vl_result'] = (isset($_POST['failedvlResult']) && $_POST['failedvlResult'] != '' ? $_POST['failedvlResult'] : null);
          $pngSpecificFields['failed_batch_quality'] = (isset($_POST['failedbatchQuality']) && $_POST['failedbatchQuality'] != '' ? $_POST['failedbatchQuality'] : null);
          $pngSpecificFields['failed_sample_test_quality'] = (isset($_POST['failedtestQuality']) && $_POST['failedtestQuality'] != '' ? $_POST['failedtestQuality'] : null);
          $pngSpecificFields['failed_batch_id'] = (isset($_POST['failedbatchNo']) && $_POST['failedbatchNo'] != '' ? $_POST['failedbatchNo'] : null);
          $pngSpecificFields['result'] = (isset($_POST['finalViralResult']) && trim($_POST['finalViralResult']) != '') ? $_POST['finalViralResult'] : null;
          $pngSpecificFields['qc_tech_name'] = (isset($_POST['qcTechName']) && $_POST['qcTechName'] != '' ? $_POST['qcTechName'] : null);
          $pngSpecificFields['qc_tech_sign'] = (isset($_POST['qcTechSign']) && $_POST['qcTechSign'] != '' ? $_POST['qcTechSign'] : null);
          $pngSpecificFields['qc_date'] = $_POST['qcDate'];
          $pngSpecificFields['report_date'] = $_POST['reportDate'];
     }
     $vldata = array_merge($vldata, $pngSpecificFields);

     if (isset($_POST['vlSampleId']) && $_POST['vlSampleId'] != '' && ($_POST['noResult'] == 'no' || $_POST['noResult'] == '')) {
          if (isset($_POST['testName']) && !empty($_POST['testName'])) {
               $db = $db->where('generic_id', $_POST['vlSampleId']);
               $db->delete($testTableName);
               foreach ($_POST['testName'] as $testKey => $testKitName) {
                    if (isset($testKitName) && !empty($testKitName)) {
                         if (isset($_POST['testDate'][$testKey]) && trim($_POST['testDate'][$testKey]) != "") {
                              $testedDateTime = explode(" ", $_POST['testDate'][$testKey]);
                              $_POST['testDate'][$testKey] = DateUtility::isoDateFormat($testedDateTime[0]) . " " . $testedDateTime[1];
                         } else {
                              $_POST['testDate'][$testKey] = null;
                         }
                         $covid19TestData = array(
                              'generic_id'                    => $_POST['vlSampleId'],
                              'test_name'                         => ($testKitName == 'other') ? $_POST['testNameOther'][$testKey] : $testKitName,
                              'facility_id'                => $_POST['labId'] ?? null,
                              'sample_tested_datetime'      => date('Y-m-d H:i:s', strtotime($_POST['testDate'][$testKey])),
                              'testing_platform'           => $_POST['testingPlatform'][$testKey] ?? null,
                              'kit_lot_no'                     => (strpos($testKitName, 'RDT') !== false) ? $_POST['lotNo'][$testKey] : null,
                              'kit_expiry_date'                => (strpos($testKitName, 'RDT') !== false) ? DateUtility::isoDateFormat($_POST['expDate'][$testKey]) : null,
                              'result'                         => $_POST['testResult'][$testKey]
                         );
                         $db->insert($testTableName, $covid19TestData);
                    }
               }
          }
     } else {
          $db = $db->where('generic_id', $_POST['vlSampleId']);
          $db->delete($testTableName);
          $covid19Data['sample_tested_datetime'] = null;
     }

     $vldata['patient_first_name'] = $general->crypto('doNothing', $_POST['patientFirstName'], $vldata['patient_art_no']);
     $db = $db->where('sample_id', $_POST['vlSampleId']);
     $id = $db->update($tableName, $vldata);
     error_log($db->getLastError());
     if ($id > 0) {
          $_SESSION['alertMsg'] = _("Request updated successfully");
          //Add event log

          $eventType = 'update-vl-request-sudan';
          $action = $_SESSION['userName'] . ' updated a request data with the sample code ' . $_POST['sampleCode'];
          $resource = 'vl-request-ss';

          $general->activityLog($eventType, $action, $resource);
     } else {
          $_SESSION['alertMsg'] = _("Please try again later");
     }
     header("Location:view-requests.php");
} catch (Exception $exc) {
     error_log($exc->getMessage());
     error_log($exc->getTraceAsString());
}
