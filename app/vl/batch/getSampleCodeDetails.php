<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Utilities\DateUtility;

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_POST = $request->getParsedBody();

$start_date = '';
$end_date = '';
$urgent = $_POST['urgent'] ?? null;
$fName = $_POST['fName'] ?? null;
$sample = $_POST['sName'] ?? null;
$gender = $_POST['gender'];
$pregnant = $_POST['pregnant'];
$breastfeeding = $_POST['breastfeeding'];

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
if (isset($_POST['sampleReceivedAtLab']) && trim($_POST['sampleReceivedAtLab']) != '') {
	$s_c_date = explode("to", $_POST['sampleReceivedAtLab']);
	//print_r($s_c_date);die;
	if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
		$sampleReceivedStartDate = DateUtility::isoDateFormat(trim($s_c_date[0]));
	}
	if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
		$sampleReceivedEndDate = DateUtility::isoDateFormat(trim($s_c_date[1]));
	}
}

$query = "SELECT vl.sample_code,vl.vl_sample_id,vl.facility_id,vl.result_status,f.facility_name,f.facility_code FROM form_vl as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id WHERE (vl.is_sample_rejected IS NULL OR vl.is_sample_rejected = '' OR vl.is_sample_rejected = 'no') AND (vl.reason_for_sample_rejection IS NULL OR vl.reason_for_sample_rejection ='' OR vl.reason_for_sample_rejection = 0) AND (vl.result is NULL or vl.result = '') AND vl.sample_code!=''";
if (isset($_POST['batchId'])) {
	$query = $query . " AND (sample_batch_id = '" . $_POST['batchId'] . "' OR sample_batch_id IS NULL OR sample_batch_id = '')";
} else {
	$query = $query . " AND (sample_batch_id IS NULL OR sample_batch_id='')";
}
if (trim($urgent) != '') {
	$query = $query . " AND vl.test_urgency='" . $urgent . "'";
}
if (!empty($_POST['fName']) && is_array($_POST['fName']) && !empty($_POST['fName'])) {
	$query = $query . " AND vl.facility_id IN (" . implode(',', $_POST['fName']) . ")";
}
if (trim($sample) != '') {
	$query = $query . " AND vl.sample_type='" . $sample . "'";
}
if (trim($gender) != '') {
	$query = $query . " AND vl.patient_gender='" . $gender . "'";
}
if (trim($pregnant) != '') {
	$query = $query . " AND vl.is_patient_pregnant='" . $pregnant . "'";
}
if (trim($breastfeeding) != '') {
	$query = $query . " AND vl.is_patient_breastfeeding='" . $breastfeeding . "'";
}
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
	if (trim($start_date) == trim($end_date)) {
		$query = $query . ' AND DATE(sample_collection_date) = "' . $start_date . '"';
	} else {
		$query = $query . ' AND DATE(sample_collection_date) >= "' . $start_date . '" AND DATE(sample_collection_date) <= "' . $end_date . '"';
	}
}

if (isset($_POST['sampleReceivedAtLab']) && trim($_POST['sampleReceivedAtLab']) != '') {
	if (trim($sampleReceivedStartDate) == trim($sampleReceivedEndDate)) {
		$query = $query . ' AND DATE(sample_received_at_vl_lab_datetime) = "' . $sampleReceivedStartDate . '"';
	} else {
		$query = $query . ' AND DATE(sample_received_at_vl_lab_datetime) >= "' . $sampleReceivedStartDate . '" AND DATE(sample_received_at_vl_lab_datetime) <= "' . $sampleReceivedEndDate . '"';
	}
}

$query = $query . " ORDER BY vl.sample_code ASC";
// echo $query;die;
$result = $db->rawQuery($query);
?>
<!-- <div class="col-lg-5"> -->
<select name="sampleCode[]" id="search" class="form-control" size="8" multiple="multiple">
	<?php
	if ($result > 0) {
		foreach ($result as $sample) {
	?>
			<option value="<?php echo $sample['vl_sample_id']; ?>"><?php echo ($sample['sample_code']) . " - " . ($sample['facility_name']); ?></option>
	<?php
		}
	}
	?>
</select>

<script>
	$(document).ready(function() {
		$('#search').multiselect({
			search: {
				left: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
			},
			fireSearch: function(value) {
				return value.length > 3;
			}
		});
	});
</script>
