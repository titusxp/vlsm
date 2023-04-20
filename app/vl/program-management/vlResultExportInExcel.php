<?php

use App\Models\General;
use App\Utilities\DateUtils;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;


if (session_status() == PHP_SESSION_NONE) {
	session_start();
}


$general = new General();
$dateTimeUtil = new DateUtils();
//system config

if (isset($_SESSION['vlResultQuery']) && trim($_SESSION['vlResultQuery']) != "") {

	$rResult = $db->rawQuery($_SESSION['vlResultQuery']);

	$excel = new Spreadsheet();
	$output = [];
	$sheet = $excel->getActiveSheet();
	$sheet->setTitle('VL Results');
	if (isset($_POST['patientInfo']) && $_POST['patientInfo'] == 'yes') {
		$headings = array("No.", "Sample Code", "Remote Sample Code", "Health Facility Name", "Testing Lab", "Health Facility Code", "District/County", "Province/State", "Unique ART No.",  "Patient Name", "Date of Birth", "Age", "Gender", "Date of Sample Collection", "Sample Type", "Date of Treatment Initiation", "Current Regimen", "Date of Initiation of Current Regimen", "Is Patient Pregnant?", "Is Patient Breastfeeding?", "ARV Adherence", "Indication for Viral Load Testing", "Requesting Clinican", "Request Date", "Is Sample Rejected?", "Rejection Reason", "Sample Tested On", "Result (cp/ml)", "Result (log)", "Sample Receipt Date", "Date Result Dispatched", "Comments", "Funding Source", "Implementing Partner", "Request Created On");
	} else {
		$headings = array("No.", "Sample Code", "Remote Sample Code", "Health Facility Name", "Testing Lab", "Health Facility Code", "District/County", "Province/State", "Date of Birth", "Age", "Gender", "Date of Sample Collection", "Sample Type", "Date of Treatment Initiation", "Current Regimen", "Date of Initiation of Current Regimen", "Is Patient Pregnant?", "Is Patient Breastfeeding?", "ARV Adherence", "Indication for Viral Load Testing", "Requesting Clinican", "Request Date", "Is Sample Rejected?", "Rejection Reason", "Sample Tested On", "Result (cp/ml)", "Result (log)", "Sample Receipt Date", "Date Result Dispatched", "Comments", "Funding Source", "Implementing Partner", "Request Created On");
	}
	if ($_SESSION['instanceType'] == 'standalone') {
		if (($key = array_search("Remote Sample Code", $headings)) !== false) {
			unset($headings[$key]);
		}
	}
	$colNo = 1;

	$styleArray = array(
		'font' => array(
			'bold' => true,
			'size' => 12,
		),
		'alignment' => array(
			'horizontal' => Alignment::HORIZONTAL_CENTER,
			'vertical' => Alignment::VERTICAL_CENTER,
		),
		'borders' => array(
			'outline' => array(
				'style' => Border::BORDER_THIN,
			),
		)
	);

	$borderStyle = array(
		'alignment' => array(
			'horizontal' => Alignment::HORIZONTAL_CENTER,
		),
		'borders' => array(
			'outline' => array(
				'style' => Border::BORDER_THIN,
			),
		)
	);

	$sheet->mergeCells('A1:AH1');
	$nameValue = '';

	foreach ($_POST as $key => $value) {
		if (trim($value) != '' && trim($value) != '-- Select --') {
			$nameValue .= str_replace("_", " ", $key) . " : " . $value . "&nbsp;&nbsp;";
		}
	}
	$sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '1')
		->setValueExplicit(html_entity_decode($nameValue), DataType::TYPE_STRING);
	if ($_POST['withAlphaNum'] == 'yes') {
		foreach ($headings as $field => $value) {
			$string = str_replace(' ', '', $value);
			$value = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
			$sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '3')
				->setValueExplicit(html_entity_decode($value), DataType::TYPE_STRING);
			$colNo++;
		}
	} else {
		foreach ($headings as $field => $value) {
			$sheet->getCell(Coordinate::stringFromColumnIndex($colNo) . '3')
				->setValueExplicit(html_entity_decode($value), DataType::TYPE_STRING);
			$colNo++;
		}
	}
	$sheet->getStyle('A3:AI3')->applyFromArray($styleArray);

	$no = 1;
	foreach ($rResult as $aRow) {
		$row = [];
		//date of birth
		$dob = '';
		if (!empty($aRow['patient_dob'])) {
			$dob =  DateUtils::humanReadableDateFormat($aRow['patient_dob']);
		}

		$age = null;
		$aRow['patient_age_in_years'] = (int) $aRow['patient_age_in_years'];
		if (!empty($aRow['patient_dob'])) {
			$age = $dateTimeUtil->ageInYearMonthDays($aRow['patient_dob']);
			if (!empty($age) && $age['year'] > 0) {
				$aRow['patient_age_in_years'] = $age['year'];
			}
		}
		//set gender
		$gender = '';
		if ($aRow['patient_gender'] == 'male') {
			$gender = 'M';
		} else if ($aRow['patient_gender'] == 'female') {
			$gender = 'F';
		} else if ($aRow['patient_gender'] == 'not_recorded') {
			$gender = 'Unreported';
		}
		//sample collecion date
		$sampleCollectionDate = '';
		if (!empty($aRow['sample_collection_date'])) {
			$sampleCollectionDate =  DateUtils::humanReadableDateFormat($aRow['sample_collection_date']);
		}
		//treatment initiation date
		$treatmentInitiationDate = '';
		if (!empty($aRow['treatment_initiated_date'])) {
			$treatmentInitiationDate =  DateUtils::humanReadableDateFormat($aRow['treatment_initiated_date']);
		}
		//date of initiation of current regimen
		$dateOfInitiationOfCurrentRegimen = '';
		if (!empty($aRow['date_of_initiation_of_current_regimen'])) {
			$dateOfInitiationOfCurrentRegimen =  DateUtils::humanReadableDateFormat($aRow['date_of_initiation_of_current_regimen']);
		}
		//requested date
		$requestedDate = '';
		if (!empty($aRow['test_requested_on'])) {
			$requestedDate =  DateUtils::humanReadableDateFormat($aRow['test_requested_on']);
		}
		//request created date time
		$requestCreatedDatetime = '';
		if (!empty($aRow['request_created_datetime'])) {
			$requestCreatedDatetime =  DateUtils::humanReadableDateFormat($aRow['request_created_datetime'], true);
		}

		$sampleTestedOn = '';
		if (!empty($aRow['sample_tested_datetime'])) {
			$sampleTestedOn =  DateUtils::humanReadableDateFormat($aRow['sample_tested_datetime']);
		}

		$sampleReceivedOn = '';
		if (!empty($aRow['sample_received_at_vl_lab_datetime'])) {
			$sampleReceivedOn =  DateUtils::humanReadableDateFormat($aRow['sample_received_at_vl_lab_datetime']);
		}

		//set ARV adherecne
		$arvAdherence = '';
		if (trim($aRow['arv_adherance_percentage']) == 'good') {
			$arvAdherence = 'Good >= 95%';
		} else if (trim($aRow['arv_adherance_percentage']) == 'fair') {
			$arvAdherence = 'Fair 85-94%';
		} else if (trim($aRow['arv_adherance_percentage']) == 'poor') {
			$arvAdherence = 'Poor <85%';
		}

		//set sample rejection
		$sampleRejection = null;
		if (isset($aRow['is_sample_rejected']) && trim($aRow['is_sample_rejected']) == 'yes' || $aRow['result_status'] == 4) {
			$sampleRejection = 'Yes';
		} else if (trim($aRow['is_sample_rejected']) == 'no') {
			$sampleRejection = 'No';
		}
		//result dispatched date
		$lastViralLoadTest = '';
		if (!empty($aRow['last_viral_load_date'])) {
			$lastViralLoadTest =  DateUtils::humanReadableDateFormat($aRow['last_viral_load_date']);
		}

		//result dispatched date
		$resultDispatchedDate = '';
		if (!empty($aRow['result_printed_datetime'])) {
			$resultDispatchedDate =  DateUtils::humanReadableDateFormat($aRow['result_printed_datetime']);
		}

		//set result log value
		$logVal = '';
		if (!empty($aRow['result_value_log']) && is_numeric($aRow['result_value_log'])) {
			$logVal = round($aRow['result_value_log'], 1);
		}

		if ($aRow['patient_first_name'] != '') {
			$patientFname = ($general->crypto('doNothing', $aRow['patient_first_name'], $aRow['patient_art_no']));
		} else {
			$patientFname = '';
		}
		if ($aRow['patient_middle_name'] != '') {
			$patientMname = ($general->crypto('doNothing', $aRow['patient_middle_name'], $aRow['patient_art_no']));
		} else {
			$patientMname = '';
		}
		if ($aRow['patient_last_name'] != '') {
			$patientLname = ($general->crypto('doNothing', $aRow['patient_last_name'], $aRow['patient_art_no']));
		} else {
			$patientLname = '';
		}

		$row[] = $no;
		if ($_SESSION['instanceType'] == 'standalone') {
			$row[] = $aRow["sample_code"];
		} else {
			$row[] = $aRow["sample_code"];
			$row[] = $aRow["remote_sample_code"] ?: null;
		}
		$row[] = $aRow['facility_name'];
		$row[] = $aRow['lab_name'];
		$row[] = $aRow['facility_code'];
		$row[] = ($aRow['facility_district']);
		$row[] = ($aRow['facility_state']);

		if (isset($_POST['patientInfo']) && $_POST['patientInfo'] == 'yes') {
			$row[] = $aRow['patient_art_no'];
			$row[] = ($patientFname . " " . $patientMname . " " . $patientLname);
		}
		$row[] = $dob;
		$row[] = $aRow['patient_age_in_years'];
		$row[] = $gender;
		$row[] = $sampleCollectionDate;
		$row[] = $aRow['sample_name'] ?: null;
		$row[] = $treatmentInitiationDate;
		$row[] = $aRow['current_regimen'];
		$row[] = $dateOfInitiationOfCurrentRegimen;
		$row[] = ($aRow['is_patient_pregnant']);
		$row[] = ($aRow['is_patient_breastfeeding']);
		$row[] = $arvAdherence;
		$row[] = (str_replace("_", " ", $aRow['test_reason_name']));
		$row[] = ($aRow['request_clinician_name']);
		$row[] = $requestedDate;
		$row[] = $sampleRejection;
		$row[] = $aRow['rejection_reason'];
		$row[] = $sampleTestedOn;
		$row[] = $aRow['result'];
		$row[] = $logVal;
		$row[] = $sampleReceivedOn;
		$row[] = $resultDispatchedDate;
		$row[] = ($aRow['lab_tech_comments']);
		$row[] = (isset($aRow['funding_source_name']) && trim($aRow['funding_source_name']) != '') ? ($aRow['funding_source_name']) : '';
		$row[] = (isset($aRow['i_partner_name']) && trim($aRow['i_partner_name']) != '') ? ($aRow['i_partner_name']) : '';

		$row[] = $requestCreatedDatetime;
		$output[] = $row;
		$no++;
	}

	$start = (count($output)) + 2;
	foreach ($output as $rowNo => $rowData) {
		$colNo = 1;
		$rRowCount = $rowNo + 4;
		foreach ($rowData as $field => $value) {
			$sheet->setCellValue(
				Coordinate::stringFromColumnIndex($colNo) . $rRowCount,
				html_entity_decode($value)
			);
			$colNo++;
		}
	}
	$writer = IOFactory::createWriter($excel, 'Xlsx');
	$filename = TEMP_PATH . DIRECTORY_SEPARATOR . 'VLSM-VIRAL-LOAD-Data-' . date('d-M-Y-H-i-s') . '-' . $general->generateRandomString(5) . '.xlsx';
	$writer->save($filename);
	echo base64_encode($filename);
}
