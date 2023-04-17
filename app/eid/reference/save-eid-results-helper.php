<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
  
$general = new \App\Models\General();
$tableName = "r_eid_results";
$primaryKey = "result_id";
// print_r(base64_decode($_POST['resultId']));die;
try {
	if (isset($_POST['resultName']) && trim($_POST['resultName']) != "") {
		$data = array(
			'result' 		=> ($_POST['resultName']),
			'status' 	    => $_POST['resultStatus'],
			'updated_datetime' 	=> \App\Utilities\DateUtils::getCurrentDateTime(),
		);
		if (isset($_POST['resultId']) && $_POST['resultId'] != "") {
			$db = $db->where($primaryKey, base64_decode($_POST['resultId']));
			$lastId = $db->update($tableName, $data);
		} else {
			$db->insert($tableName, $data);
			$lastId = $db->getInsertId();
		}

		if ($lastId > 0) {
			$_SESSION['alertMsg'] = _("EID Results details saved successfully");
			$general->activityLog('EID Results details', $_SESSION['userName'] . ' added new results for ' . $_POST['resultName'], 'eid-reference');
		}
	}
	header("location:eid-results.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
