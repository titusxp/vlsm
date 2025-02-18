<?php


require_once APPLICATION_PATH . '/header.php';

// Sanitized values from $request object
/** @var Laminas\Diactoros\ServerRequest $request */
$request = $GLOBALS['request'];
$_GET = $request->getQueryParams();
$id = (isset($_GET['id'])) ? base64_decode($_GET['id']) : null;

if (!isset($id) || trim($id) == '') {
	header("Location:hepatitis-batches.php");
}
$content = '';
$newContent = '';
$displayOrder = [];
$batchQuery = "SELECT * from batch_details as b_d INNER JOIN instruments as i_c ON i_c.config_id=b_d.machine where batch_id= ? ";
$batchInfo = $db->rawQuery($batchQuery, [$id]);
// Config control
$configControlQuery = "SELECT * from instrument_controls where config_id= ? ";
$configControlInfo = $db->rawQuery($configControlQuery, [$batchInfo[0]['config_id']]);
$configControl = [];
foreach ($configControlInfo as $info) {
	if ($info['test_type'] == 'hepatitis') {
		$configControl[$info['test_type']]['noHouseCtrl'] = $info['number_of_in_house_controls'];
		$configControl[$info['test_type']]['noManufacturerCtrl'] = $info['number_of_manufacturer_controls'];
		$configControl[$info['test_type']]['noCalibrators'] = $info['number_of_calibrators'];
	}
}

if (empty($batchInfo)) {
	header("Location:hepatitis-batches.php");
}
//Get batch controls order
$newJsonToArray = [];
if (isset($configControl['hepatitis']['noHouseCtrl']) && trim($configControl['hepatitis']['noHouseCtrl']) != '' && $configControl['hepatitis']['noHouseCtrl'] > 0) {
	foreach (range(1, $configControl['hepatitis']['noHouseCtrl']) as $h) {
		$newJsonToArray[] = "no_of_in_house_controls_" . $h;
	}
}
if (isset($configControl['hepatitis']['noManufacturerCtrl']) && trim($configControl['hepatitis']['noManufacturerCtrl']) != '' && $configControl['hepatitis']['noManufacturerCtrl'] > 0) {
	foreach (range(1, $configControl['hepatitis']['noManufacturerCtrl']) as $m) {
		$newJsonToArray[] = "no_of_manufacturer_controls_" . $m;
	}
}
if (isset($configControl['hepatitis']['noCalibrators']) && trim($configControl['hepatitis']['noCalibrators']) != '' && $configControl['hepatitis']['noCalibrators'] > 0) {
	foreach (range(1, $configControl['hepatitis']['noCalibrators']) as $c) {
		$newJsonToArray[] = "no_of_calibrators_" . $c;
	}
}
//Get machine's prev. label order
$machine = intval($batchInfo[0]['machine']);
$prevLabelQuery = "SELECT label_order from batch_details as b_d WHERE b_d.machine = $machine AND b_d.batch_id!= $id ORDER BY b_d.request_created_datetime DESC LIMIT 0,1";
$prevlabelInfo = $db->query($prevLabelQuery);
if (isset($prevlabelInfo[0]['label_order']) && trim($prevlabelInfo[0]['label_order']) != '') {
	$jsonToArray = json_decode($prevlabelInfo[0]['label_order'], true);
	$prevDisplaySampleArray = [];
	for ($j = 0; $j < count($jsonToArray); $j++) {
		$xplodJsonToArray = explode("_", $jsonToArray[$j]);
		if (count($xplodJsonToArray) > 1 && $xplodJsonToArray[0] == "s") {
			$prevDisplaySampleArray[] = $xplodJsonToArray[1];
		}
	}
	//Get display sample only
	$displaySampleOrderArray = [];
	$samplesQuery = "SELECT hepatitis_id,sample_code from form_hepatitis where sample_batch_id=$id ORDER BY sample_code ASC";
	$samplesInfo = $db->query($samplesQuery);
	foreach ($samplesInfo as $sample) {
		$displaySampleOrderArray[] = $sample['hepatitis_id'];
	}
	//Set content
	$sCount = 0;
	$displayNonSampleArray = [];
	$displaySampleArray = [];
	for ($j = 0; $j < count($jsonToArray); $j++) {
		$xplodJsonToArray = explode("_", $jsonToArray[$j]);
		if (count($xplodJsonToArray) > 1 && $xplodJsonToArray[0] == "s") {
			if (isset($displaySampleOrderArray[$sCount]) && $displaySampleOrderArray[$sCount] > 0) {
				if ($sCount <= $prevDisplaySampleArray) {
					$displayOrder[] = 's_' . $displaySampleOrderArray[$sCount];
					$displaySampleArray[] = $displaySampleOrderArray[$sCount];
					$sampleQuery = "SELECT sample_code from form_hepatitis where hepatitis_id=$displaySampleOrderArray[$sCount]";
					$sampleResult = $db->query($sampleQuery);
					$label = $sampleResult[0]['sample_code'];
					$content .= '<li class="ui-state-default" id="s_' . $displaySampleOrderArray[$sCount] . '">' . $label . '</li>';
					$sCount++;
				}
			}
		} else {
			if (in_array($jsonToArray[$j], $newJsonToArray)) {
				$displayOrder[] = $jsonToArray[$j];
				$displayNonSampleArray[] = $jsonToArray[$j];
				$label = str_replace("_", " ", $jsonToArray[$j]);
				$label = str_replace("in house", "In-House", $label);
				$label = (str_replace("no of ", " ", $label));
				$content .= '<li class="ui-state-default" id="' . $jsonToArray[$j] . '">' . $label . '</li>';
			}
		}
	}
	//Add new content
	$remainNewArray = array_values(array_diff($newJsonToArray, $displayNonSampleArray));
	$remainSampleNewArray = array_values(array_diff($displaySampleOrderArray, $displaySampleArray));
	//For new controls
	for ($n = 0; $n < count($remainNewArray); $n++) {
		$displayOrder[] = $remainNewArray[$n];
		$label = str_replace("_", " ", $remainNewArray[$n]);
		$label = str_replace("in house", "In-House", $label);
		$label = (str_replace("no of ", " ", $label));
		$newContent .= '<li class="ui-state-default" id="' . $remainNewArray[$n] . '">' . $label . '</li>';
	}
	//For new samples
	for ($ns = 0; $ns < count($remainSampleNewArray); $ns++) {
		$displayOrder[] = 's_' . $remainSampleNewArray[$ns];
		$sampleQuery = "SELECT sample_code from form_hepatitis where hepatitis_id=$remainSampleNewArray[$ns]";
		$sampleResult = $db->query($sampleQuery);
		$label = $sampleResult[0]['sample_code'];
		$newContent .= '<li class="ui-state-default" id="s_' . $remainSampleNewArray[$ns] . '">' . $label . '</li>';
	}
} else {
	/* if(isset($batchInfo[0]['number_of_in_house_controls']) && trim($batchInfo[0]['number_of_in_house_controls'])!='' && $batchInfo[0]['number_of_in_house_controls']>0){
		for($h=0;$h<$batchInfo[0]['number_of_in_house_controls'];$h++){
		   $displayOrder[] = "no_of_in_house_controls_".($h+1);
		   $content.='<li class="ui-state-default" id="no_of_in_house_controls_'.($h+1).'">In-House Controls '.($h+1).'</li>';
		}
	}if(isset($batchInfo[0]['number_of_manufacturer_controls']) && trim($batchInfo[0]['number_of_manufacturer_controls'])!='' && $batchInfo[0]['number_of_manufacturer_controls']>0){
		for($m=0;$m<$batchInfo[0]['number_of_manufacturer_controls'];$m++){
		   $displayOrder[] = "no_of_manufacturer_controls_".($m+1);
		   $content.='<li class="ui-state-default" id="no_of_manufacturer_controls_'.($m+1).'">Manufacturer Controls '.($m+1).'</li>';
		}
	}if(isset($batchInfo[0]['number_of_calibrators']) && trim($batchInfo[0]['number_of_calibrators'])!='' && $batchInfo[0]['number_of_calibrators']>0){
		for($c=0;$c<$batchInfo[0]['number_of_calibrators'];$c++){
		   $displayOrder[] = "no_of_calibrators_".($c+1);
		   $content.='<li class="ui-state-default" id="no_of_calibrators_'.($c+1).'">Calibrators '.($c+1).'</li>';
		}
	} */

	if (isset($configControl['hepatitis']['noHouseCtrl']) && trim($configControl['hepatitis']['noHouseCtrl']) != '' && $configControl['hepatitis']['noHouseCtrl'] > 0) {
		foreach (range(1, $configControl['hepatitis']['noHouseCtrl']) as $h) {
			$displayOrder[] = "no_of_in_house_controls_" . $h;
			$content .= '<li class="ui-state-default" id="no_of_in_house_controls_' . $h . '">In-House Controls ' . $h . '</li>';
		}
	}
	if (isset($configControl['hepatitis']['noManufacturerCtrl']) && trim($configControl['hepatitis']['noManufacturerCtrl']) != '' && $configControl['hepatitis']['noManufacturerCtrl'] > 0) {
		foreach (range(1, $configControl['hepatitis']['noManufacturerCtrl']) as $m) {
			$displayOrder[] = "no_of_manufacturer_controls_" . $m;
			$content .= '<li class="ui-state-default" id="no_of_manufacturer_controls_' . $m . '">Manufacturer Controls ' . $m . '</li>';
		}
	}
	if (isset($configControl['hepatitis']['noCalibrators']) && trim($configControl['hepatitis']['noCalibrators']) != '' && $configControl['hepatitis']['noCalibrators'] > 0) {
		foreach (range(1, $configControl['hepatitis']['noCalibrators']) as $c) {
			$displayOrder[] = "no_of_calibrators_" . $c;
			$content .= '<li class="ui-state-default" id="no_of_calibrators_' . $c . '">Calibrators ' . $c . '</li>';
		}
	}
	$samplesQuery = "SELECT hepatitis_id,sample_code from form_hepatitis where sample_batch_id=$id ORDER BY sample_code ASC";
	$samplesInfo = $db->query($samplesQuery);
	foreach ($samplesInfo as $sample) {
		$displayOrder[] = "s_" . $sample['hepatitis_id'];
		$content .= '<li class="ui-state-default" id="s_' . $sample['hepatitis_id'] . '">' . $sample['sample_code'] . '</li>';
	}
}
?>
<style>
	#sortableRow {
		list-style-type: none;
		margin: 0px 0px 30px 0px;
		padding: 0;
		width: 100%;
		text-align: center;
	}

	#sortableRow li {
		color: #333 !important;
		font-size: 16px;
		border-radius: 10px;
		margin-bottom: 4px;
	}
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><em class="fa-solid fa-pen-to-square"></em> Add Batch Controls Position</h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em> Home</a></li>
			<li class="active">Batch</li>
		</ol>
	</section>

	<!-- Main content -->
	<section class="content">

		<div class="box box-default">
			<div class="box-header with-border">
				<h4><strong>Batch Code : <?php echo $batchInfo[0]['batch_code']; ?></strong></h4>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<!-- <pre><?php print_r($configControl); ?></pre> -->
				<!-- form start -->
				<form class="form-horizontal" method='post' name='addBatchControlsPosition' id='addBatchControlsPosition' autocomplete="off" action="hepatitis-add-batch-position-helper.php">
					<div class="box-body">
						<div class="row" id="displayOrderDetails">
							<div class="col-md-8">
								<ul id="sortableRow">
									<?php
									echo $content . $newContent;
									?>
								</ul>
							</div>
						</div>
					</div>
					<!-- /.box-body -->
					<div class="box-footer">
						<input type="hidden" name="sortOrders" id="sortOrders" value="<?php echo implode(",", $displayOrder); ?>" />
						<input type="hidden" name="batchId" id="batchId" value="<?php echo $id; ?>" />
						<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
						<a href="hepatitis-batches.php" class="btn btn-default"> Cancel</a>
					</div>
					<!-- /.box-footer -->
				</form>
				<!-- /.row -->
			</div>

		</div>
		<!-- /.box -->

	</section>
	<!-- /.content -->
</div>
<script>
	sortedTitle = [];
	$(document).ready(function() {
		function cleanArray(actual) {
			var newArray = [];
			for (var i = 0; i < actual.length; i++) {
				if (actual[i]) {
					newArray.push(actual[i]);
				}
			}
			return newArray;
		}

		$("#sortableRow").sortable({
			opacity: 0.6,
			cursor: 'move',
			update: function() {
				sortedTitle = cleanArray($(this).sortable("toArray"));
				$("#sortOrders").val("");
				$("#sortOrders").val(sortedTitle);
			}
		}).disableSelection();
	});

	function validateNow() {
		flag = deforayValidator.init({
			formId: 'addBatchControlsPosition'
		});

		if (flag) {
			$.blockUI();
			document.getElementById('addBatchControlsPosition').submit();
		}
	}
</script>
<?php
require_once(APPLICATION_PATH . '/footer.php');
?>
