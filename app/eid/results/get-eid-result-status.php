<?php

use App\Services\EidService;
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

$sarr = $general->getSystemConfig();



/** @var EidService $eidService */
$eidService = ContainerRegistry::get(EidService::class);
$eidResults = $eidService->getEidResults();


$tableName = "form_eid";
$primaryKey = "eid_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$sampleCode = 'sample_code';
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', "DATE_FORMAT(vl.sample_collection_date,'%d-%b-%Y')", 'b.batch_code', 'vl.child_id', 'vl.child_name', 'vl.mother_id', 'vl.mother_name', 'f.facility_name', 'f.facility_code', 'vl.result', "DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y')", 'ts.status_name');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'vl.sample_collection_date', 'b.batch_code', 'vl.child_id', 'vl.child_name', 'vl.mother_id', 'vl.mother_name', 'f.facility_name', 'f.facility_code',  'vl.result', 'vl.last_modified_datetime', 'ts.status_name');
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
    $sWhereSub = '';
    foreach ($searchArray as $search) {
        if ($sWhereSub == "") {
            $sWhereSub .= "(";
        } else {
            $sWhereSub .= " AND (";
        }
        $colSize = count($aColumns);

        for ($i = 0; $i < $colSize; $i++) {
            if (isset($aColumns[$i]) && !empty($aColumns[$i])) {
                if ($i < $colSize - 1) {
                    $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' OR ";
                } else {
                    $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search) . "%' ";
                }
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
$sQuery = "SELECT SQL_CALC_FOUND_ROWS *
            FROM form_eid as vl
            INNER JOIN facility_details as f ON vl.facility_id=f.facility_id
            INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status
            LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id";

//echo $sQuery;die;
$start_date = '';
$end_date = '';
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

if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
    $sWhere[] =  '  b.batch_code LIKE "%' . $_POST['batchCode'] . '%"';
}
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    if (trim($start_date) == trim($end_date)) {
        $sWhere[] =  '  DATE(vl.sample_collection_date) = "' . $start_date . '"';
    } else {
        $sWhere[] =  '  DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
    }
}
if (isset($_POST['facilityName']) && $_POST['facilityName'] != '') {
    $sWhere[] =  '  f.facility_id IN (' . $_POST['facilityName'] . ')';
}
if (isset($_POST['statusFilter']) && $_POST['statusFilter'] != '') {
    if ($_POST['statusFilter'] == 'approvedOrRejected') {
        $sWhere[] =  ' vl.result_status IN (4,7)';
    } else if ($_POST['statusFilter'] == 'notApprovedOrRejected') {
        //$sWhere[] = ' vl.result_status NOT IN (4,7)';
        $sWhere[] = ' vl.result_status IN (6,8)';
    }
}

if (!empty($_SESSION['facilityMap'])) {
    $sWhere[] = "  vl.facility_id IN (" . $_SESSION['facilityMap'] . ")  ";
}

$sWhere[] =  ' vl.result not like "" AND vl.result is not null ';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    if (trim($start_date) == trim($end_date)) {
        $sWhere[] = ' DATE(vl.sample_collection_date) like  "' . $start_date . '"';
    } else {
        $sWhere[] = ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
    }
}
if (isset($sWhere) && !empty($sWhere)) {
    $sWhere = ' where ' . implode(' AND ', $sWhere);
} else {
    $sWhere = "";
}
$sQuery = $sQuery . ' ' . $sWhere;
//echo $sQuery;die;
//echo $sQuery;die;
if (isset($sOrder) && !empty($sOrder)) {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' order by ' . $sOrder;
}

if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
// echo $sQuery;
$_SESSION['eidRequestSearchResultQuery'] = $sQuery;
$rResult = $db->rawQuery($sQuery);

$aResultFilterTotal = $db->rawQueryOne("SELECT FOUND_ROWS() as `totalCount`");
$iTotal = $iFilteredTotal = $aResultFilterTotal['totalCount'];

$_SESSION['eidRequestSearchResultQueryCount'] = $iTotal;

/*
          * Output
          */
$output = array(
    "sEcho" => intval($_POST['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
);
$vlRequest = false;
$vlView = false;
if (isset($_SESSION['privileges']) && (in_array("editVlRequest.php", $_SESSION['privileges']))) {
    $vlRequest = true;
}
if (isset($_SESSION['privileges']) && (in_array("viewVlRequest.php", $_SESSION['privileges']))) {
    $vlView = true;
}

foreach ($rResult as $aRow) {
    if (isset($aRow['sample_collection_date']) && trim($aRow['sample_collection_date']) != '' && $aRow['sample_collection_date'] != '0000-00-00 00:00:00') {
        $aRow['sample_collection_date'] = DateUtility::humanReadableDateFormat($aRow['sample_collection_date']);
    } else {
        $aRow['sample_collection_date'] = '';
    }

    $childName = ($general->crypto('doNothing', $aRow['child_name'], $aRow['child_id']));


    $status = '<select class="form-control"  name="status[]" id="' . $aRow['eid_id'] . '" title="' . _("Please select status") . '" onchange="updateStatus(this,' . $aRow['status_id'] . ')">
               <option value="">' . _("-- Select --") . '</option>
               <option value="7" ' . ($aRow['status_id'] == "7" ? "selected=selected" : "") . '>' . _("Accepted") . '</option>
               <option value="4" ' . ($aRow['status_id'] == "4"  ? "selected=selected" : "") . '>' . _("Rejected") . '</option>
               <option value="2" ' . ($aRow['status_id'] == "2"  ? "selected=selected" : "") . '>' . _("Lost") . '</option>
               </select><br><br>';

    $row = [];
    $row[] = '<input type="checkbox" name="chk[]" class="checkTests" id="chk' . $aRow['eid_id'] . '"  value="' . $aRow['eid_id'] . '" onclick="toggleTest(this);"  />';
    $row[] = $aRow['sample_code'];
    if ($_SESSION['instanceType'] != 'standalone') {
        $row[] = $aRow['remote_sample_code'];
    }
    $row[] = $aRow['sample_collection_date'];
    $row[] = $aRow['batch_code'];
    $row[] = $aRow['child_id'];
    $row[] = $aRow['child_name'];
    $row[] = $aRow['mother_id'];
    $row[] = $aRow['mother_name'];
    $row[] = ($aRow['facility_name']);
    $row[] = $eidResults[$aRow['result']] ?? $aRow['result'];
    if (isset($aRow['last_modified_datetime']) && trim($aRow['last_modified_datetime']) != '' && $aRow['last_modified_datetime'] != '0000-00-00 00:00:00') {
        $aRow['last_modified_datetime'] = DateUtility::humanReadableDateFormat($aRow['last_modified_datetime'], true);
    } else {
        $aRow['last_modified_datetime'] = '';
    }
    $row[] = $aRow['last_modified_datetime'];
    $row[] = $status;
    //$row[] = '<a href="updateVlTestResult.php?id=' . base64_encode($aRow['eid_id']) . '" class="btn btn-success btn-xs" style="margin-right: 2px;" title="Result"><em class="fa-solid fa-pen-to-square"></em> Result</a>';

    $output['aaData'][] = $row;
}

echo json_encode($output);
