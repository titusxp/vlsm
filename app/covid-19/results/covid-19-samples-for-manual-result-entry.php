<?php

use App\Services\Covid19Service;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

if (session_status() == PHP_SESSION_NONE) {
     session_start();
}



// $formConfigQuery = "SELECT * FROM global_config WHERE name='vl_form'";
// $configResult = $db->query($formConfigQuery);
// $arr = [];
// // now we create an associative array so that we can easily create view variables
// for ($i = 0; $i < sizeof($configResult); $i++) {
//      $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
// }
// //system config
// $systemConfigQuery = "SELECT * from system_config";
// $systemConfigResult = $db->query($systemConfigQuery);
// $sarr = [];
// // now we create an associative array so that we can easily create view variables
// for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
//      $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
// }
/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);


$arr = $general->getGlobalConfig();
$sarr = $general->getSystemConfig();



/** @var Covid19Service $covid19Service */
$covid19Service = ContainerRegistry::get(Covid19Service::class);
$covid19Results = $covid19Service->getCovid19Results();

$tableName = "form_covid19";
$primaryKey = "covid19_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$sampleCode = 'sample_code';
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_id', 'CONCAT(COALESCE(vl.patient_name,""), COALESCE(vl.patient_surname,""))', 'f.facility_name', 'vl.result', "DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y')", 'ts.status_name');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_id', 'vl.patient_name', 'f.facility_name',  'vl.result', 'vl.last_modified_datetime', 'ts.status_name');
if ($_SESSION['instanceType'] == 'remoteuser') {
     $sampleCode = 'remote_sample_code';
} else if ($sarr['sc_user_type'] == 'standalone') {
     if (($key = array_search('vl.remote_sample_code', $aColumns)) !== false) {
          unset($aColumns[$key]);
     }
     if (($key = array_search('vl.remote_sample_code', $orderColumns)) !== false) {
          unset($orderColumns[$key]);
     }
}
if (isset($_POST['vlPrint']) && $_POST['vlPrint'] == 'print') {
     array_unshift($orderColumns, "vl.covid19_id");
}
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
          $sWhere[] =  $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
     }
}

/*
          * SQL queries
          * Get data to display
          */
$sQuery = "SELECT SQL_CALC_FOUND_ROWS vl.*,b.*,ts.*,f.facility_name,
          l_f.facility_name as labName,
          l_f.facility_logo as facilityLogo,
          l_f.header_text as headerText,
          f.facility_code,
          f.facility_state,
          f.facility_district,
          u_d.user_name as reviewedBy,
          a_u_d.user_name as approvedBy
          FROM form_covid19 as vl
          LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id
          LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id
          INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status
          LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id
          LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by
          LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by";
$start_date = '';
$end_date = '';
$t_start_date = '';
$t_end_date = '';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
     $s_c_date = explode("to", $_POST['sampleCollectionDate']);
     //print_r($s_c_date);die;
     if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
          $start_date = DateUtility::isoDateFormat(trim($s_c_date[0]));
     }
     if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
          $end_date = DateUtility::isoDateFormat(trim($s_c_date[1]));
     }
}

if (isset($sWhere) && !empty($sWhere)) {
     //$sWhere = ' where ' . $sWhere;
     if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
          $sWhere[] = ' b.batch_code = "' . $_POST['batchCode'] . '"';
     }
     if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
          if (trim($start_date) == trim($end_date)) {
               $sWhere[] = ' DATE(vl.sample_collection_date) like  "' . $start_date . '"';
          } else {
               $sWhere[] =  ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
          }
     }

     if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
          $sWhere[] =  '  f.facility_id IN (' . $_POST['facilityName'] . ')';
     }
     if (isset($_POST['vlLab']) && trim($_POST['vlLab']) != '') {
          $sWhere[] =  ' vl.lab_id IN (' . $_POST['vlLab'] . ')';
     }
     if (isset($_POST['status']) && trim($_POST['status']) != '') {
          if ($_POST['status'] == 'no_result') {
               $statusCondition = ' (vl.result is NULL OR vl.result ="") AND vl.result_status != 4';
          } else if ($_POST['status'] == 'result') {
               $statusCondition = ' (vl.result is NOT NULL AND vl.result !="" AND vl.result_status != 4)';
          } else {
               $statusCondition = ' vl.result_status=4';
          }
          $sWhere[] = $statusCondition;
     }

     if (isset($_POST['fundingSource']) && trim($_POST['fundingSource']) != '') {
          $sWhere[] =  ' vl.funding_source ="' . base64_decode($_POST['fundingSource']) . '"';
     }
     if (isset($_POST['implementingPartner']) && trim($_POST['implementingPartner']) != '') {
          $sWhere[] = ' vl.implementing_partner ="' . base64_decode($_POST['implementingPartner']) . '"';
     }
} else {
     if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
          $sWhere[] = ' b.batch_code = "' . $_POST['batchCode'] . '"';
     }

     if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
          if (trim($start_date) == trim($end_date)) {
               $sWhere[] =  ' DATE(vl.sample_collection_date) = "' . $start_date . '"';
          } else {
               $sWhere[] =  ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
          }
     }


     if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
          $sWhere[] = ' f.facility_id IN (' . $_POST['facilityName'] . ')';
     }
     if (isset($_POST['vlLab']) && trim($_POST['vlLab']) != '') {
          $sWhere[] = ' vl.lab_id IN (' . $_POST['vlLab'] . ')';
     }

     if (isset($_POST['status']) && trim($_POST['status']) != '') {
          if ($_POST['status'] == 'no_result') {
               $statusCondition = '  (vl.result is NULL OR vl.result ="")  AND vl.result_status !=4 ';
          } else if ($_POST['status'] == 'result') {
               $statusCondition = ' (vl.result is NOT NULL AND vl.result !=""  AND vl.result_status !=4 )';
          } else {
               $statusCondition = ' vl.result_status=4';
          }
          $sWhere[] =  $statusCondition;
     }

     if (isset($_POST['fundingSource']) && trim($_POST['fundingSource']) != '') {
          $sWhere[] = ' vl.funding_source ="' . base64_decode($_POST['fundingSource']) . '"';
     }
     if (isset($_POST['implementingPartner']) && trim($_POST['implementingPartner']) != '') {
          $sWhere[] = '  vl.implementing_partner ="' . base64_decode($_POST['implementingPartner']) . '"';
     }
}
// Only approved results can be printed
if (isset($_POST['vlPrint']) && $_POST['vlPrint'] == 'print') {
     if (!isset($_POST['status']) || trim($_POST['status']) == '') {
          $sWhere[] = " ((vl.result_status = 7 AND vl.result is NOT NULL AND vl.result !='') OR (vl.result_status = 4 AND (vl.result is NULL OR vl.result = ''))) AND (result_printed_datetime is NULL OR result_printed_datetime like '')";
     }
} else {
     $sWhere[] = " vl.result_status!=9";
}
if ($_SESSION['instanceType'] == 'remoteuser') {
     //$sWhere = $sWhere." AND request_created_by='".$_SESSION['userId']."'";
     //$dWhere = $dWhere." AND request_created_by='".$_SESSION['userId']."'";
     $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT facility_id ORDER BY facility_id SEPARATOR ',') as facility_id FROM user_facility_map where user_id='" . $_SESSION['userId'] . "'";
     $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
     if ($userfacilityMapresult[0]['facility_id'] != null && $userfacilityMapresult[0]['facility_id'] != '') {
          $sWhere[] = " vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ")  ";
     }
}

if (isset($sWhere) && !empty($sWhere)) {
     $sWhere = implode(' AND ', $sWhere);
}

$sQuery = $sQuery . ' WHERE ' . $sWhere;
$_SESSION['vlResultQuery'] = $sQuery;
//echo $_SESSION['vlResultQuery'];die;

if (isset($sOrder) && !empty($sOrder)) {
     $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
     $sQuery = $sQuery . ' ORDER BY ' . $sOrder;
}

if (isset($sLimit) && isset($sOffset)) {
     $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
//echo ($sQuery);die();
$rResult = $db->rawQuery($sQuery);
/* Data set length after filtering */

$aResultFilterTotal = $db->rawQueryOne("SELECT FOUND_ROWS() as `totalCount`");
$iTotal = $iFilteredTotal = $aResultFilterTotal['totalCount'];

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
     $row = [];
     $print = '<a href="covid-19-update-result.php?id=' . base64_encode($aRow['covid19_id']) . '" class="btn btn-success btn-xs" style="margin-right: 2px;" title="' . _("Result") . '"><em class="fa-solid fa-pen-to-square"></em> ' . _("Enter Result") . '</a>';
     if ($aRow['result_status'] == 7 && $aRow['locked'] == 'yes') {
          if (isset($_SESSION['privileges']) && !in_array("edit-locked-covid19-samples", $_SESSION['privileges'])) {
               $print = '<a href="javascript:void(0);" class="btn btn-default btn-xs" style="margin-right: 2px;" title=' . _("Locked") . ' disabled><em class="fa-solid fa-lock"></em> ' . _("Locked") . '</a>';
          }
     }



     $row[] = $aRow['sample_code'];
     if ($_SESSION['instanceType'] != 'standalone') {
          $row[] = $aRow['remote_sample_code'];
     }
     $row[] = $aRow['batch_code'];
     $row[] = ($aRow['facility_name']);
     $row[] = $aRow['patient_id'];
     $row[] = $aRow['patient_name'] . " " . $aRow['patient_surname'];
     $row[] = $covid19Results[$aRow['result']] ?? $aRow['result'];

     if (isset($aRow['last_modified_datetime']) && trim($aRow['last_modified_datetime']) != '' && $aRow['last_modified_datetime'] != '0000-00-00 00:00:00') {
          $aRow['last_modified_datetime'] = DateUtility::humanReadableDateFormat($aRow['last_modified_datetime'], true);
     } else {
          $aRow['last_modified_datetime'] = '';
     }

     $row[] = $aRow['last_modified_datetime'];
     $row[] = ($aRow['status_name']);
     $row[] = $print;
     $output['aaData'][] = $row;
}

echo json_encode($output);
