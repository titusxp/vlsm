<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

#require_once('../startup.php');

$db = MysqliDb::getInstance();

$general = new \Vlsm\Models\General();
$provinceTable = "province_details";
try {
	if (isset($_POST['geoName']) && trim($_POST['geoName']) != "") {
		$lastId = 0;
		$data = array(
			'geo_name' 			=> $_POST['geoName'],
			'geo_code' 			=> $_POST['geoCode'],
			'geo_parent' 		=> (isset($_POST['geoParent']) && trim($_POST['geoParent']) != "") ? $_POST['geoParent'] : 0,
			'geo_status' 		=> $_POST['geoStatus'],
			'updated_datetime'	=> $general->getDateTime()
		);
		if (isset($_POST['geoId']) && $_POST['geoId'] != "") {
			$db = $db->where("geo_id", base64_decode($_POST['geoId']));
			$lastId = $db->update("geographical_divisions", $data);
		} else {
			$data['created_by'] = $_SESSION['userId'];
			$data['created_on'] = $general->getDateTime();
			$data['data_sync'] = 0;
			$db->insert("geographical_divisions", $data);
			$lastId = $db->getInsertId();
		}
		if (!isset($data['geo_parent']) || $data['geo_parent'] == 0) {
			$provinceQuery = "SELECT province_name from province_details where province_name='" . $_POST['geoName'] . "'";
			$provinceInfo = $db->rawQueryOne($provinceQuery);
			$pdata = array(
				'province_name' => $_POST['geoName'],
				'province_code' => $_POST['geoCode'],
				'updated_datetime' => $general->getDateTime(),
			);
			if ($provinceInfo && $provinceInfo['province_id'] > 0) {
				$db->where("province_id", $provinceInfo['province_id']);
				$db->update($provinceTable, $pdata);
			} else {
				$db->insert($provinceTable, $pdata);
			}
		}
		if ($lastId > 0) {

			$facilityData = array();
			if ($data['geo_parent'] == 0) {
				$facilityData['facility_state'] = $data['geo_name'];
				$facilityData['facility_state_id'] = $data['geo_id'];
				$db->where("facility_state", $data['geo_name']);
				$db->where("facility_state_id", $data['geo_id']);
			} else {
				$facilityData['facility_state_id'] = $data['geo_parent'];
				$facilityData['facility_district'] = $data['geo_name'];
				$facilityData['facility_district_id'] = $data['geo_id'];
				$db->where('facility_state', $data['geo_parent']);
				$db->where("facility_district", $data['geo_name']);
				$db->where("facility_district_id", $data['geo_id']);
			}
			$db->update("facility_details", $facilityData);

			$_SESSION['alertMsg'] = _("Geographical Divisions details saved successfully");
			$general->activityLog('Geographical Divisions details', $_SESSION['userName'] . ' added new geographical divisions for ' . $_POST['geoName'], 'common-reference');
		}
	}
	header("location:geographical-divisions-details.php");
} catch (Exception $exc) {
	error_log($exc->getMessage());
	error_log($exc->getTraceAsString());
}
