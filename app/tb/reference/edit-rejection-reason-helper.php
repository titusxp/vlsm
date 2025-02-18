<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;


if (session_status() == PHP_SESSION_NONE) {
	session_start();
}




/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);



$tableName = "r_tb_sample_rejection_reasons";
$rejectionReasonId = base64_decode($_POST['rejectionReasonId']);

try {
	if (isset($_POST['rejectionReasonName']) && trim($_POST['rejectionReasonName']) != "") {


		$data = array(
			'rejection_reason_name' => $_POST['rejectionReasonName'],
			'rejection_type' => $_POST['rejectionType'],
			'rejection_reason_status' => $_POST['rejectionReasonStatus'],
			'rejection_reason_code' => $_POST['rejectionReasonCode'],
			'updated_datetime' => DateUtility::getCurrentDateTime(),
		);

		$db = $db->where('rejection_reason_id', $rejectionReasonId);
		$db->update($tableName, $data);

		$_SESSION['alertMsg'] = "TB Sample Rejection Reasons details updated successfully";
		$general->activityLog('update-sample-rejection-reasons', $_SESSION['userName'] . ' updated new reference sample rejection reasons ' . $_POST['rejectionReasonName'], 'reference-tb-sample-rejection-reasons');
	}
	header("Location:tb-sample-rejection-reasons.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
