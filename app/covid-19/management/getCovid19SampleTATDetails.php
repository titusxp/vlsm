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
$whereCondition = '';
$tableName = "form_covid19";
$primaryKey = "covid19_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
if ($_SESSION['instanceType'] == 'remoteuser') {
	$sampleCode = 'remote_sample_code';
} else {
	$sampleCode = 'sample_code';
}
$aColumns = array('vl.' . $sampleCode, "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", "DATE_FORMAT(vl.sample_received_at_vl_lab_datetime,'%d-%b-%Y')", "DATE_FORMAT(vl.sample_tested_datetime,'%d-%b-%Y')", "DATE_FORMAT(vl.result_printed_datetime,'%d-%b-%Y')", "DATE_FORMAT(vl.result_mail_datetime,'%d-%b-%Y')");
$orderColumns = array('vl.' . $sampleCode, 'vl.sample_collection_date', 'vl.sample_received_at_vl_lab_datetime', 'vl.sample_tested_datetime', 'vl.result_printed_datetime', 'vl.result_mail_datetime');

/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = $primaryKey;

$sTable = $tableName;
/*
         * Paging
         */
$sLimit = "";
if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
	$sOffset = $_POST['iDisplayStart'];
	$sLimit = $_POST['iDisplayLength'];
}

/*
         * Ordering
        */

$sOrder = "";
if (isset($_POST['iSortCol_0'])) {
	$sOrder = "";
	for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {
		if ($_POST['bSortable_' . intval($_POST['iSortCol_' . $i])] == "true") {
			$sOrder .= $orderColumns[intval($_POST['iSortCol_' . $i])] . "
				 	" . ($_POST['sSortDir_' . $i]) . ", ";
		}
	}
	$sOrder = substr_replace($sOrder, "", -2);
}

/*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
        */

$sWhere = [];
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
	$searchArray = explode(" ", $_POST['sSearch']);
	$sWhereSub = "";
	foreach ($searchArray as $search) {
		if ($sWhereSub == "") {
			$sWhereSub .= "(";
		} else {
			$sWhereSub .= " AND (";
		}
		$colSize = count($aColumns);

		for ($i = 0; $i < $colSize; $i++) {
			if ($i < $colSize - 1) {
				$sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
			} else {
				$sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
			}
		}
		$sWhereSub .= ")";
	}
	$sWhere[] = $sWhereSub;
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
	if (isset($_POST['bSearchable_' . $i]) && $_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
		$sWhere[] = $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
	}
}

/*
         * SQL queries
         * Get data to display
        */
$sQuery = "select SQL_CALC_FOUND_ROWS vl.sample_collection_date,vl.sample_tested_datetime,vl.sample_received_at_vl_lab_datetime,vl.result_printed_datetime,vl.result_mail_datetime,vl.request_created_by,vl." . $sampleCode . " from form_covid19 as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where (vl.sample_collection_date is not null AND vl.sample_collection_date not like '' AND DATE(vl.sample_collection_date) !='1970-01-01')
                        AND (vl.sample_tested_datetime is not null AND vl.sample_tested_datetime not like '' AND DATE(vl.sample_tested_datetime) !='1970-01-01')
                        AND vl.result is not null
                        AND vl.result != ''";
if ($_SESSION['instanceType'] == 'remoteuser') {
	if (!empty($_SESSION['facilityMap'])) {
		$sWhere[] = " vl.facility_id IN (" . $_SESSION['facilityMap'] . ")";
	}
} else {
	$sWhere[] =  " vl.result_status!=9";
}
$start_date = '';
$end_date = '';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
	$s_c_date = explode("to", $_POST['sampleCollectionDate']);
	if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
		$start_date = DateUtility::isoDateFormat(trim($s_c_date[0]));
	}
	if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
		$end_date = DateUtility::isoDateFormat(trim($s_c_date[1]));
	}
}

$labStartDate = '';
$labEndDate = '';
if (isset($_POST['sampleReceivedDateAtLab']) && trim($_POST['sampleReceivedDateAtLab']) != '') {
	$s_c_date = explode("to", $_POST['sampleReceivedDateAtLab']);
	if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
		$labStartDate = DateUtility::isoDateFormat(trim($s_c_date[0]));
	}
	if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
		$labEndDate = DateUtility::isoDateFormat(trim($s_c_date[1]));
	}
}

$testedStartDate = '';
$testedEndDate = '';
if (isset($_POST['sampleTestedDate']) && trim($_POST['sampleTestedDate']) != '') {
	$s_c_date = explode("to", $_POST['sampleTestedDate']);
	if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
		$testedStartDate = DateUtility::isoDateFormat(trim($s_c_date[0]));
	}
	if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
		$testedEndDate = DateUtility::isoDateFormat(trim($s_c_date[1]));
	}
}
$seWhere = [];
if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
	$seWhere[] =  ' b.batch_code = "' . $_POST['batchCode'] . '"';
}
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
	if ($start_date != '0000-00-00') {
		if (trim($start_date) == trim($end_date)) {
			$seWhere[] = ' DATE(vl.sample_collection_date) = "' . $start_date . '"';
		} else {
			$seWhere[] =  ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
		}
	}
}
if (isset($_POST['sampleReceivedDateAtLab']) && trim($_POST['sampleReceivedDateAtLab']) != '') {
	if (trim($labStartDate) == trim($labEndDate)) {
		$seWhere[] = ' DATE(vl.sample_received_at_vl_lab_datetime) = "' . $labStartDate . '"';
	} else {
		$seWhere[] =  ' DATE(vl.sample_received_at_vl_lab_datetime) >= "' . $labStartDate . '" AND DATE(vl.sample_received_at_vl_lab_datetime) <= "' . $labEndDate . '"';
	}
}

if (isset($_POST['sampleTestedDate']) && trim($_POST['sampleTestedDate']) != '') {
	if ($testedStartDate != '0000-00-00') {
		if (trim($testedStartDate) == trim($testedEndDate)) {
			$seWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $testedStartDate . '"';
		} else {
			$seWhere[] = ' DATE(vl.sample_tested_datetime) >= "' . $testedStartDate . '" AND DATE(vl.sample_tested_datetime) <= "' . $testedEndDate . '"';
		}
	}
}
if (isset($_POST['sampleType']) && trim($_POST['sampleType']) != '') {
	$seWhere[] = ' s.sample_id = "' . $_POST['sampleType'] . '"';
}
if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
	$seWhere[] = ' f.facility_id IN (' . $_POST['facilityName'] . ')';
}


if (isset($sWhere) && !empty($sWhere)) {
	$sQuery = $sQuery . ' AND ' . implode(' AND ', $sWhere);
}
if (isset($seWhere) && !empty($seWhere)) {
	$sQuery = $sQuery . ' AND ' . implode(' AND ', $seWhere);
}
$_SESSION['covid19TATQuery'] = $sQuery;
if (isset($sOrder) && !empty($sOrder)) {
	$sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
	$sQuery = $sQuery . " order by " . $sOrder;
}

if (isset($sLimit) && isset($sOffset)) {
	$sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
//echo $sQuery; die();
$rResult = $db->rawQuery($sQuery);
/* Data set length after filtering */
$rUser = '';
if ($_SESSION['instanceType'] == 'remoteuser') {
	$rUser = $rUser . $whereCondition;
} else {
	$rUser = " vl.result_status!=9";
}
$aResultFilterTotal = $db->rawQuery("select vl.sample_collection_date,vl.sample_tested_datetime,vl.sample_received_at_vl_lab_datetime,vl.result_printed_datetime,vl.result_mail_datetime from form_covid19 as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id where (vl.sample_collection_date is not null AND vl.sample_collection_date not like '' AND DATE(vl.sample_collection_date) !='1970-01-01' )
                        AND (vl.sample_tested_datetime is not null AND vl.sample_tested_datetime not like '' AND DATE(vl.sample_tested_datetime) !='1970-01-01' )
                        AND vl.result is not null
                        AND vl.result != '' AND $saWhere $rUser");
$iFilteredTotal = count($aResultFilterTotal);

/* Total data set length
$aResultTotal =  $db->rawQuery("select vl.sample_collection_date,vl.sample_tested_datetime,vl.sample_received_at_vl_lab_datetime,vl.result_printed_datetime,vl.result_mail_datetime from form_covid19 as vl INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status where (vl.sample_collection_date is not null AND vl.sample_collection_date not like '' AND DATE(vl.sample_collection_date) !='1970-01-01' )
                        AND (vl.sample_tested_datetime is not null AND vl.sample_tested_datetime not like '' AND DATE(vl.sample_tested_datetime) !='1970-01-01' )
                        AND vl.result is not null
                        AND vl.result != '' AND vl.result_status!=9 $rUser");*/
// $aResultTotal = $countResult->fetch_row();
//print_r($aResultTotal);
$iTotal = count($aResultFilterTotal);

/*
         * Output
        */
$output = array(
	"sEcho" => intval($_POST['sEcho']),
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => array()
);

foreach ($rResult as $aRow) {
	if (isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date']) != '' && $aRow['sample_collection_date'] != '0000-00-00 00:00:00') {
		$aRow['sample_collection_date'] = DateUtility::humanReadableDateFormat($aRow['sample_collection_date']);
	} else {
		$aRow['sample_collection_date'] = '';
	}
	if (isset($aRow['sample_received_at_vl_lab_datetime']) && trim($aRow['sample_received_at_vl_lab_datetime']) != '' && $aRow['sample_received_at_vl_lab_datetime'] != '0000-00-00 00:00:00') {
		$aRow['sample_received_at_vl_lab_datetime'] = DateUtility::humanReadableDateFormat($aRow['sample_received_at_vl_lab_datetime']);
	} else {
		$aRow['sample_received_at_vl_lab_datetime'] = '';
	}
	if (isset($aRow['sample_tested_datetime']) && trim($aRow['sample_tested_datetime']) != '' && $aRow['sample_tested_datetime'] != '0000-00-00 00:00:00') {
		$aRow['sample_tested_datetime'] = DateUtility::humanReadableDateFormat($aRow['sample_tested_datetime']);
	} else {
		$aRow['sample_tested_datetime'] = '';
	}
	if (isset($aRow['result_printed_datetime']) && trim($aRow['result_printed_datetime']) != '' && $aRow['result_printed_datetime'] != '0000-00-00 00:00:00') {
		$aRow['result_printed_datetime'] = DateUtility::humanReadableDateFormat($aRow['result_printed_datetime']);
	} else {
		$aRow['result_printed_datetime'] = '';
	}
	if (isset($aRow['result_mail_datetime']) && trim($aRow['result_mail_datetime']) != '' && $aRow['result_mail_datetime'] != '0000-00-00 00:00:00') {
		$aRow['result_mail_datetime'] = DateUtility::humanReadableDateFormat($aRow['result_mail_datetime']);
	} else {
		$aRow['result_mail_datetime'] = '';
	}
	$row = [];
	$row[] = $aRow[$sampleCode];
	$row[] = $aRow['sample_collection_date'];
	$row[] = $aRow['sample_received_at_vl_lab_datetime'];
	$row[] = $aRow['sample_tested_datetime'];
	$row[] = $aRow['result_printed_datetime'];
	$row[] = $aRow['result_mail_datetime'];
	$output['aaData'][] = $row;
}

echo json_encode($output);
