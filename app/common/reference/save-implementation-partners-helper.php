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

$tableName = "r_implementation_partners";
$primaryKey = "i_partner_id";

try {
	if (isset($_POST['partnerName']) && trim($_POST['partnerName']) != "") {

		$data = array(
			'i_partner_name' 	=> $_POST['partnerName'],
			'i_partner_status' 	=> $_POST['partnerStatus'],
			'updated_datetime'	=> DateUtility::getCurrentDateTime()
		);
		if (isset($_POST['partnerId']) && $_POST['partnerId'] != "") {
			$db = $db->where($primaryKey, base64_decode($_POST['partnerId']));
			$lastId = $db->update($tableName, $data);
		} else {
			$data['data_sync'] = 0;
			$db->insert($tableName, $data);
			$lastId = $db->getInsertId();
		}
		if ($lastId > 0) {
			$_SESSION['alertMsg'] = _("Implementation Partners saved successfully");
			$general->activityLog('Implementation Partners', $_SESSION['userName'] . ' added new Implementation Partner for ' . $_POST['partnerName'], 'common-reference');
		}
	}
	header("Location:implementation-partners.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
