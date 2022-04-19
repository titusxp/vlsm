<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$formConfigQuery = "SELECT * FROM `global_config` WHERE `name`='vl_form'";
$configResult = $db->query($formConfigQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($configResult); $i++) {
    $arr[$configResult[$i]['name']] = $configResult[$i]['value'];
}
//system config
$systemConfigQuery = "SELECT * FROM `system_config`";
$systemConfigResult = $db->query($systemConfigQuery);
$sarr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($systemConfigResult); $i++) {
    $sarr[$systemConfigResult[$i]['name']] = $systemConfigResult[$i]['value'];
}

$general = new \Vlsm\Models\General();
$hepatitisDb = new \Vlsm\Models\Hepatitis();

//$hepatitisResults = $hepatitisDb->getHepatitisResults();

$tableName = "form_hepatitis";
$primaryKey = "hepatitis_id";

/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$sampleCode = 'sample_code';
$aColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_id', 'CONCAT(COALESCE(vl.patient_name,""), COALESCE(vl.patient_surname,""))', 'f.facility_name', 'vl.hcv_vl_count', 'vl.hbv_vl_count', "DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y')", 'ts.status_name');
$orderColumns = array('vl.sample_code', 'vl.remote_sample_code', 'b.batch_code', 'vl.patient_id', 'vl.patient_name', 'f.facility_name', 'vl.hcv_vl_count', 'vl.hbv_vl_count', 'vl.last_modified_datetime', 'ts.status_name');
if ($_SESSION['instanceType'] == 'remoteuser') {
    $sampleCode = 'remote_sample_code';
} else if ($sarr['sc_user_type'] == 'standalone') {
    $aColumns = array('vl.sample_code', 'b.batch_code', 'vl.patient_id', 'CONCAT(COALESCE(vl.patient_name,""), COALESCE(vl.patient_surname,""))', 'f.facility_name', 'vl.hcv_vl_count', 'vl.hbv_vl_count', "DATE_FORMAT(vl.last_modified_datetime,'%d-%b-%Y')", 'ts.status_name');
    $orderColumns = array('vl.sample_code', 'b.batch_code', 'vl.patient_id', 'vl.patient_name', 'f.facility_name', 'vl.hcv_vl_count', 'vl.hbv_vl_count', 'vl.last_modified_datetime', 'ts.status_name');
}
if (isset($_POST['vlPrint']) && $_POST['vlPrint'] == 'print') {
    array_unshift($orderColumns, "vl.hepatitis_id");
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

$sWhere = array();
if (isset($_POST['sSearch']) && $_POST['sSearch'] != "") {
    $searchArray = explode(" ", $_POST['sSearch']);
    $sWhereSub = "";
    foreach ($searchArray as $search) {
        if ($sWhereSub == "") {
            $sWhereSub .= " (";
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
        if ($sWhere == "") {
            $sWhere .= $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
        } else {
            $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($_POST['sSearch_' . $i]) . "%' ";
        }
    }
}

/*
* SQL queries
* Get data to display
*/
$sQuery = "SELECT SQL_CALC_FOUND_ROWS vl.*,b.*,ts.*,imp.*,
            f.facility_name,
            l_f.facility_name as labName,
            l_f.facility_logo as facilityLogo,
            l_f.header_text as headerText,
            f.facility_code,f.facility_state,f.facility_district,
            imp.i_partner_name,
            u_d.user_name as reviewedBy,
            a_u_d.user_name as approvedBy,
            c.iso_name as nationality,
            rs.rejection_reason_name 
            FROM form_hepatitis as vl 
            LEFT JOIN r_countries as c ON vl.patient_nationality=c.id
            LEFT JOIN facility_details as f ON vl.facility_id=f.facility_id 
            LEFT JOIN facility_details as l_f ON vl.lab_id=l_f.facility_id
            INNER JOIN r_sample_status as ts ON ts.status_id=vl.result_status 
            LEFT JOIN batch_details as b ON b.batch_id=vl.sample_batch_id 
            LEFT JOIN user_details as u_d ON u_d.user_id=vl.result_reviewed_by 
            LEFT JOIN user_details as a_u_d ON a_u_d.user_id=vl.result_approved_by 
            LEFT JOIN r_hepatitis_sample_rejection_reasons as rs ON rs.rejection_reason_id=vl.reason_for_sample_rejection 
            LEFT JOIN r_implementation_partners as imp ON imp.i_partner_id=vl.implementing_partner";
$start_date = '';
$end_date = '';
$t_start_date = '';
$t_end_date = '';
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $s_c_date = explode("to", $_POST['sampleCollectionDate']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $start_date = $general->dateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $end_date = $general->dateFormat(trim($s_c_date[1]));
    }
}

if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
    $s_t_date = explode("to", $_POST['sampleTestDate']);
    //print_r($s_t_date);die;
    if (isset($s_t_date[0]) && trim($s_t_date[0]) != "") {
        $t_start_date = $general->dateFormat(trim($s_t_date[0]));
    }
    if (isset($s_t_date[1]) && trim($s_t_date[1]) != "") {
        $t_end_date = $general->dateFormat(trim($s_t_date[1]));
    }
}

if (isset($_POST['batchCode']) && trim($_POST['batchCode']) != '') {
    $sWhere[] = ' b.batch_code = "' . $_POST['batchCode'] . '"';
}

if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    if (trim($start_date) == trim($end_date)) {
        $sWhere[] = ' DATE(vl.sample_collection_date) = "' . $start_date . '"';
    } else {
        $sWhere[] = ' DATE(vl.sample_collection_date) >= "' . $start_date . '" AND DATE(vl.sample_collection_date) <= "' . $end_date . '"';
    }
}

if (isset($_POST['sampleTestDate']) && trim($_POST['sampleTestDate']) != '') {
    if (trim($t_start_date) == trim($t_end_date)) {
        $sWhere[] = ' DATE(vl.sample_tested_datetime) = "' . $t_start_date . '"';
    } else {
        $sWhere[] = ' DATE(vl.sample_tested_datetime) >= "' . $t_start_date . '" AND DATE(vl.sample_tested_datetime) <= "' . $t_end_date . '"';
    }
}
if (isset($_POST['sampleType']) && trim($_POST['sampleType']) != '') {
    $sWhere[] = ' s.sample_id = "' . $_POST['sampleType'] . '"';
}
if (isset($_POST['facilityName']) && trim($_POST['facilityName']) != '') {
    $sWhere[] = ' f.facility_id IN (' . $_POST['facilityName'] . ')';
}
if (isset($_POST['artNo']) && trim($_POST['artNo']) != '') {
    $sWhere[] = " vl.child_id LIKE '%" . $_POST['artNo'] . "%' ";
}
if (isset($_POST['status']) && trim($_POST['status']) != '') {
    if ($_POST['status'] == 'no_result') {
        $statusCondition = ' AND  (vl.hcv_vl_count is NULL AND vl.hcv_vl_count  ="" AND vl.hbv_vl_count is NULL AND vl.hbv_vl_count  ="")';
    } else if ($_POST['status'] == 'result') {
        $statusCondition = ' AND (vl.hcv_vl_count is NOT NULL OR vl.hcv_vl_count  !="" OR vl.hbv_vl_count is NOT NULL OR vl.hbv_vl_count  !="")';
    } else {
        $statusCondition = ' AND vl.result_status=4';
    }
    $sWhere[] = $statusCondition;
}
if (isset($_POST['gender']) && trim($_POST['gender']) != '') {
    if (trim($_POST['gender']) == "not_recorded") {
        $sWhere[] = ' (vl.patient_gender = "not_recorded" OR vl.patient_gender ="" OR vl.patient_gender IS NULL)';
    } else {
        $sWhere[] = ' vl.patient_gender ="' . $_POST['gender'] . '"';
    }
}
if (isset($_POST['fundingSource']) && trim($_POST['fundingSource']) != '') {
    $sWhere[] = ' vl.funding_source ="' . base64_decode($_POST['fundingSource']) . '"';
}
if (isset($_POST['implementingPartner']) && trim($_POST['implementingPartner']) != '') {
    $sWhere[] = ' vl.implementing_partner ="' . base64_decode($_POST['implementingPartner']) . '"';
}

if (!isset($_POST['status']) || trim($_POST['status']) == '') {
    if (isset($_POST['vlPrint']) && $_POST['vlPrint'] == 'not-print') {
        $sWhere[] = " ((vl.result_status = 7 AND (vl.hcv_vl_count is NOT NULL OR vl.hcv_vl_count  !='' OR vl.hbv_vl_count is NOT NULL OR vl.hbv_vl_count  !='')) OR (vl.result_status = 4 AND (vl.hcv_vl_count is NULL OR vl.hcv_vl_count  ='' OR vl.hbv_vl_count is NULL OR vl.hbv_vl_count  =''))) AND (result_printed_datetime is NULL OR result_printed_datetime like '')";
    } else {
        $sWhere[] = " ((vl.result_status = 7 AND (vl.hcv_vl_count is NOT NULL OR vl.hcv_vl_count  !='' OR vl.hbv_vl_count is NOT NULL OR vl.hbv_vl_count  !='')) OR (vl.result_status = 4 AND (vl.hcv_vl_count is NULL OR vl.hcv_vl_count  ='' OR vl.hbv_vl_count is NULL OR vl.hbv_vl_count  =''))) AND (result_printed_datetime is not NULL OR result_printed_datetime not like '')";
    }
} else {
    $sWhere[] = " vl.vlsm_country_id='" . $arr['vl_form'] . "' AND vl.result_status!=9";
}
if ($_SESSION['instanceType'] == 'remoteuser') {
    $userfacilityMapQuery = "SELECT GROUP_CONCAT(DISTINCT facility_id ORDER BY facility_id SEPARATOR ',') as facility_id FROM user_facility_map where user_id='" . $_SESSION['userId'] . "'";
    $userfacilityMapresult = $db->rawQuery($userfacilityMapQuery);
    if ($userfacilityMapresult[0]['facility_id'] != null && $userfacilityMapresult[0]['facility_id'] != '') {
        $sWhere[] = " vl.facility_id IN (" . $userfacilityMapresult[0]['facility_id'] . ")  ";
    }
}
if (isset($sWhere) && sizeof($sWhere) > 0) {
    $sQuery = $sQuery . ' WHERE' . implode(" AND ", $sWhere);
}
$_SESSION['hepatitisPrintQuery'] = $sQuery;

if (isset($sOrder) && $sOrder != "") {
    $sOrder = preg_replace('/(\v|\s)+/', ' ', $sOrder);
    $sQuery = $sQuery . ' order by ' . $sOrder;
}
$_SESSION['hepatitisPrintSearchResultQuery'] = $sQuery;
if (isset($sLimit) && isset($sOffset)) {
    $sQuery = $sQuery . ' LIMIT ' . $sOffset . ',' . $sLimit;
}
//error_log($sQuery);
// die($sQuery);
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
    $row = array();
    if (isset($_POST['vlPrint']) && $_POST['vlPrint'] == 'print') {
        $row[] = '<input type="checkbox" name="chk[]" class="checkRows" id="chk' . $aRow['hepatitis_id'] . '"  value="' . $aRow['hepatitis_id'] . '" onclick="checkedRow(this);"  />';
        $print = '<a href="javascript:void(0);" class="btn btn-primary btn-xs" style="margin-right: 2px;" title="' . _("Print") . '" onclick="resultPDF(' . $aRow['hepatitis_id'] . ',\'\');"><i class="fa fa-print"> ' . _("Print") . '</i></a>';
    }

    $patientFname = $general->crypto('decrypt', $aRow['patient_name'], $aRow['patient_id']);
    $patientLname = $general->crypto('decrypt', $aRow['patient_surname'], $aRow['patient_id']);

    $row[] = $aRow['sample_code'];
    if ($sarr['sc_user_type'] != 'standalone') {
        $row[] = $aRow['remote_sample_code'];
    }
    $row[] = $aRow['batch_code'];
    $row[] = $aRow['patient_id'];
    $row[] = ucwords($patientFname . " " . $patientLname);
    $row[] = ucwords($aRow['facility_name']);
    $row[] = $aRow['hcv_vl_count'];
    $row[] = $aRow['hbv_vl_count'];

    if (isset($aRow['last_modified_datetime']) && trim($aRow['last_modified_datetime']) != '' && $aRow['last_modified_datetime'] != '0000-00-00 00:00:00') {
        $xplodDate = explode(" ", $aRow['last_modified_datetime']);
        $aRow['last_modified_datetime'] = $general->humanDateFormat($xplodDate[0]) . " " . $xplodDate[1];
    } else {
        $aRow['last_modified_datetime'] = '';
    }

    $row[] = $aRow['last_modified_datetime'];
    $row[] = ucwords($aRow['status_name']);
    $row[] = $print;
    $output['aaData'][] = $row;
}

echo json_encode($output);
