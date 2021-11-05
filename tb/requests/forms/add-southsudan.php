<?php
// imported in tb-add-request.php based on country in global config

ob_start();

//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);
/* To get testing platform names */
$testPlatformResult = $general->getTestingPlatforms('tb');
// Nationality
$nationalityQry = "SELECT * FROM `r_countries` ORDER BY `iso_name` ASC";
$nationalityResult = $db->query($nationalityQry);

foreach ($nationalityResult as $nrow) {
	$nationalityList[$nrow['id']] = ucwords($nrow['iso_name']) . ' (' . $nrow['iso3'] . ')';
}

foreach ($testPlatformResult as $row) {
	$testPlatformList[$row['machine_name']] = $row['machine_name'];
}
//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);

$pQuery = "SELECT * FROM province_details";
$pResult = $db->rawQuery($pQuery);

// $configQuery = "SELECT * from global_config";
// $configResult = $db->query($configQuery);
// $arr = array();
// $prefix = $arr['sample_code_prefix'];

// Getting the list of Provinces, Districts and Facilities

$tbObj = new \Vlsm\Models\Tb($db);


$tbXPertResults = $tbObj->getTbResults('x-pert');
$tbLamResults = $tbObj->getTbResults('lam');
$specimenTypeResult = $tbObj->getTbSampleTypes();
$tbReasonsForTesting = $tbObj->getTbReasonsForTesting();


$rKey = '';
$sKey = '';
$sFormat = '';
$pdQuery = "SELECT * from province_details";
if ($_SESSION['accessType'] == 'collection-site') {
	$sampleCodeKey = 'remote_sample_code_key';
	$sampleCode = 'remote_sample_code';
	//check user exist in user_facility_map table
	$chkUserFcMapQry = "SELECT user_id FROM vl_user_facility_map WHERE user_id='" . $_SESSION['userId'] . "'";
	$chkUserFcMapResult = $db->query($chkUserFcMapQry);
	if ($chkUserFcMapResult) {
		$pdQuery = "SELECT * FROM province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "' group by province_name";
	}
	$rKey = 'R';
} else {
	$sampleCodeKey = 'sample_code_key';
	$sampleCode = 'sample_code';
	$rKey = '';
}
$pdResult = $db->query($pdQuery);
$province = "";
$province .= "<option value=''> -- Select -- </option>";
foreach ($pdResult as $provinceName) {
	$province .= "<option data-code='" . $provinceName['province_code'] . "' data-province-id='" . $provinceName['province_id'] . "' data-name='" . $provinceName['province_name'] . "' value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}

$facility = $general->generateSelectOptions($healthFacilities, null, '-- Select --');

?>

<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1><i class="fa fa-edit"></i> TB LABORATORY TEST REQUEST FORM</h1>
		<ol class="breadcrumb">
			<li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">Add New Request</li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="box box-default">
			<div class="box-header with-border">

				<div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				<!-- form start -->
				<form class="form-horizontal" method="post" name="addTbRequestForm" id="addTbRequestForm" autocomplete="off" action="tb-add-request-helper.php">
					<div class="box-body">
						<div class="box box-default">
							<div class="box-body">
								<div class="box-header with-border sectionHeader">
									<h3 class="box-title">TESTING LAB INFORMATION</h3>
								</div>
								<div class="box-header with-border">
									<h3 class="box-title" style="font-size:1em;">To be filled by requesting Clinician/Nurse</h3>
								</div>
								<table class="table" style="width:100%">
									<tr>
										<?php if ($_SESSION['accessType'] == 'collection-site') { ?>
											<th style="width: 16.6%;"><label class="label-control" for="sampleCode">Sample ID </label></th>
											<td style="width: 16.6%;">
												<span id="sampleCodeInText" style="width:100%;border-bottom:1px solid #333;"></span>
												<input type="hidden" id="sampleCode" name="sampleCode" />
											</td>
										<?php } else { ?>
											<th style="width: 14%;"><label class="label-control" for="sampleCode">Sample ID </label><span class="mandatory">*</span></th>
											<td style="width: 18%;">
												<input type="text" class="form-control isRequired" id="sampleCode" name="sampleCode" readonly="readonly" placeholder="Sample ID" title="Please enter sample code" style="width:100%;" onchange="checkSampleNameValidation('form_tb','<?php echo $sampleCode; ?>',this.id,null,'The sample id that you entered already exists. Please try another sample id',null)" />
											</td>
										<?php } ?>
										<th></th>
										<td></td>
										<th></th>
										<td></td>
									</tr>
									<?php if ($_SESSION['accessType'] == 'collection-site') { ?>
										<tr>
											<td><label class="label-control" for="province">Health Facility/POE State </label><span class="mandatory">*</span></td>
											<td>
												<select class="form-control select2 isRequired" name="province" id="province" title="Please choose State" onchange="getfacilityDetails(this);" style="width:100%;">
													<?php echo $province; ?>
												</select>
											</td>
											<td><label class="label-control" for="district">Health Facility/POE County </label><span class="mandatory">*</span></td>
											<td>
												<select class="form-control select2 isRequired" name="district" id="district" title="Please choose County" style="width:100%;" onchange="getfacilityDistrictwise(this);">
													<option value=""> -- Select -- </option>
												</select>
											</td>
											<td><label class="label-control" for="labId">Testing Laboratory <span class="mandatory">*</span></label> </td>
											<td>
												<select name="labId" id="labId" class="form-control select2 isRequired" title="Please select Testing Testing Laboratory" style="width:100%;">
													<?= $general->generateSelectOptions($testingLabs, null, '-- Select --'); ?>
												</select>
											</td>
										</tr>
									<?php } ?>
								</table>
								<div class="box-header with-border sectionHeader">
									<h3 class="box-title">REFERRING HEALTH FACILITY INFORMATION</h3>
								</div>
								<table class="table" style="width:100%">
									<tr>
										<td><label class="label-control" for="province">Health Facility/POE State </label><span class="mandatory">*</span></td>
										<td>
											<select class="form-control select2 isRequired" name="province" id="province" title="Please choose State" onchange="getfacilityDetails(this);" style="width:100%;">
												<?php echo $province; ?>
											</select>
										</td>
										<td><label class="label-control" for="district">Health Facility/POE County </label><span class="mandatory">*</span></td>
										<td>
											<select class="form-control select2 isRequired" name="district" id="district" title="Please choose County" style="width:100%;" onchange="getfacilityDistrictwise(this);">
												<option value=""> -- Select -- </option>
											</select>
										</td>
										<td><label class="label-control" for="facilityId">Health Facility/POE </label><span class="mandatory">*</span></td>
										<td>
											<select class="form-control isRequired " name="facilityId" id="facilityId" title="Please choose service provider" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
												<?php echo $facility; ?>
											</select>
										</td>
									</tr>
									<tr>
										<th><label for="requestedDate">Date of request <span class="mandatory">*</span></label></th>
										<td>
											<input type="text" class="date-time form-control" id="requestedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter date of request date" style="width:100%;" />
										</td>
										<th><label class="label-control" for="sno">S/No</label></th>
										<td>
											<input class="form-control" type="text" name="sno" id="sno" placeholder="Enter serial numner" title="Please enter the serial numner" />
										</td>
										<td><label class="label-control" for="referringUnit">Referring Unit </label></td>
										<td>
											<select class="form-control " name="referringUnit" id="referringUnit" title="Please choose referring unit" style="width:100%;">
												<option value="">-- Select --</option>
												<option value="art">ART</option>
												<option value="opd">OPD</option>
												<option value="tb">TB</option>
												<option value="pmtct">PMTCT</option>
												<option value="medical">Medical</option>
												<option value="paediatric">Paediatric</option>
												<option value="nutrition">Nutrition</option>
												<option value="others">Others</option>
											</select>
										</td>
									</tr>
								</table>


								<div class="box-header with-border sectionHeader">
									<h3 class="box-title">PATIENT INFORMATION</h3>
								</div>
								<div class="box-header with-border">
									<input style="width:30%;" type="text" name="patientNoSearch" id="patientNoSearch" class="" placeholder="Enter Patient ID or Patient Name" title="Enter art number or patient name" />&nbsp;&nbsp;
									<a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><i class="fa fa-search">&nbsp;</i>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><b>&nbsp;No Patient Found</b></span>
								</div>
								<table class="table" style="width:100%">
									<tr>
										<th><label for="patientId">Unique ART Number <span class="mandatory">*</span> </label></th>
										<td>
											<input type="text" class="form-control isRequired" id="patientId" name="patientId" placeholder="Patient Identification" title="Please enter Patient ID" style="width:100%;" onchange="" />
										</td>
										<th><label for="firstName">First Name <span class="mandatory">*</span> </label></th>
										<td>
											<input type="text" class="form-control isRequired" id="firstName" name="firstName" placeholder="First Name" title="Please enter First name" style="width:100%;" onchange="" />
										</td>
									</tr>
									<tr>
										<th><label for="lastName">Sur name </label></th>
										<td>
											<input type="text" class="form-control " id="lastName" name="lastName" placeholder="Last name" title="Please enter Last name" style="width:100%;" onchange="" />
										</td>
										<th><label for="patientDob">Date of Birth </label></th>
										<td>
											<input type="text" class="form-control" id="patientDob" name="patientDob" placeholder="Date of Birth" title="Please enter Date of birth" style="width:100%;" onchange="calculateAgeInYears();" />
										</td>
									</tr>
									<tr>
										<th>Age (years)</th>
										<td><input type="number" max="150" maxlength="3" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="patientAge" name="patientAge" placeholder="Patient Age (in years)" title="Patient Age" style="width:100%;" onchange="" /></td>
										<th><label for="patientGender">Gender <span class="mandatory">*</span> </label></th>
										<td>
											<select class="form-control isRequired" name="patientGender" id="patientGender" title="Please select the gender">
												<option value=''> -- Select -- </option>
												<option value='male'> Male </option>
												<option value='female'> Female </option>
												<option value='other'> Other </option>
											</select>
										</td>
									</tr>
									<tr>
										<th><label for="typeOfPatient">Type of patient</label></th>
										<td>
											<select class="form-control isRequired" name="typeOfPatient" id="typeOfPatient" title="Please select the type of patient">
												<option value=''> -- Select -- </option>
												<option value='new'> New </option>
												<option value='loss-to-follow-up'> Loss to Follow Up </option>
												<option value='treatment-failure'> Treatment Failure </option>
												<option value='relapse'> Relapse </option>
												<option value='other'> Other </option>
											</select>
											<input type="text" class="form-control" id="typeOfPatientOther" name="typeOfPatientOther" placeholder="Enter type of patient if others" title="Please enter type of patient if others" style="display: none;" />
										</td>
										<th><label for="typeOfPatient">Type of Examination <span class="mandatory">*</span> </label></th>
										<td>
											<select name="reasonForTbTest" id="reasonForTbTest" class="form-control isRequired" title="Please choose reason for examination" style="width:100%">
												<?= $general->generateSelectOptions($tbReasonsForTesting, null, '-- Select --'); ?>
											</select>
										</td>
									</tr>
								</table>

								<div class="box-header with-border sectionHeader">
									<h3 class="box-title">SPECIMEN INFORMATION</h3>
								</div>
								<table class="table">
									<tr>
										<th><label class="label-control" for="sampleCollectionDate">Date of Specimen Collected <span class="mandatory">*</span></label></th>
										<td>
											<input class="form-control isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" onchange="sampleCodeGeneration();" />
										</td>
										<th><label class="label-control" for="sampleReceivedDate">Date of Specimen Reception <span class="mandatory">*</span></label></th>
										<td>
											<input type="text" class="date-time form-control" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter sample receipt date" style="width:100%;" />
										</td>
									</tr>
									<tr>
										<th><label class="label-control" for="specimenType">Specimen Type <span class="mandatory">*</span></label></th>
										<td>
											<select name="specimenType" id="specimenType" class="form-control isRequired" title="Please choose specimen type" style="width:100%">
												<?php echo $general->generateSelectOptions($specimenTypeResult, null, '-- Select --'); ?>
												<option value='other'> Other </option>
											</select>
											<input type="text" id="sampleTypeOther" name="sampleTypeOther" placeholder="Enter sample type of others" title="Please enter the sample type if others" style="display: none;" />
										</td>
										<th>
											<label class="label-control" for="testNumber">Specimen Number</label>
										</th>
										<td>
											<select class="form-control" name="testNumber" id="testNumber" title="Prélévement" style="width:100%;">
												<option value="">--Select--</option>
												<?php foreach (range(1, 5) as $element) {
													echo '<option value="' . $element . '">' . $element . '</option>';
												} ?>
											</select>
										</td>
									</tr>
									<tr>
										<th>
											<label class="label-control" for="testTypeRequested">Test(s) requested </label>
										</th>
										<td>
											<select name="testTypeRequested[]" id="testTypeRequested" class="select2 form-control" title="Please choose type of test request" style="width:100%" multiple>
												<option value="">-- Select --</option>
												<optgroup label="Microscopy">
													<option value="ZN">ZN</option>
													<option value="FM">FM</option>
												</optgroup>
												<optgroup label="X pert MTB">
													<option value="MTB/RIF">MTB/RIF</option>
													<option value="MTB/RIF ULTRA">MTB/RIF ULTRA</option>
													<option value="TB LAM">TB LAM</option>
												</optgroup>
											</select>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<?php if ($usersModel->isAllowed('tb-update-result.php', $systemConfig) || $_SESSION['accessType'] != 'collection-site') { ?>
							<?php // if (false) { 
							?>
							<div class="box box-primary">
								<div class="box-body">
									<div class="box-header with-border">
										<h3 class="box-title">Results (To be completed in the Laboratory) </h3>
									</div>
									<table class="table" style="width:100%">
										<tr>
											<td><label class="label-control" for="labId">Testing Laboratory</label> </td>
											<td>
												<select name="labId" id="labId" class="form-control select2" title="Please select Testing Testing Laboratory" style="width:100%;">
													<?= $general->generateSelectOptions($testingLabs, null, '-- Select --'); ?>
												</select>
											</td>
											<th><label class="label-control" for="sampleTestedDateTime">Date of Sample Tested</label></th>
											<td>
												<input type="text" class="date-time form-control" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="e.g 09-Jan-1992 05:30" title="Please enter sample tested" style="width:100%;" />
											</td>
										</tr>
										<tr>
											<th><label class="label-control" for="isSampleRejected">Is Sample Rejected?</label></th>
											<td>
												<select class="form-control" name="isSampleRejected" id="isSampleRejected" title="Please select the Is sample rejected?">
													<option value=''> -- Select -- </option>
													<option value="yes"> Yes </option>
													<option value="no"> No </option>
												</select>
											</td>

											<th class="show-rejection" style="display:none;"><label class="label-control" for="sampleRejectionReason">Reason for Rejection<span class="mandatory">*</span></label></th>
											<td class="show-rejection" style="display:none;">
												<select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason" title="Please select the reason for rejection">
													<option value=''> -- Select -- </option>
													<?php echo $rejectionReason; ?>
												</select>
											</td>
										</tr>
										<tr class="show-rejection" style="display:none;">
											<th><label class="label-control" for="rejectionDate">Rejection Date<span class="mandatory">*</span></label></th>
											<td><input class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="Select rejection date" title="Please select the rejection date" /></td>
											<td></td>
											<td></td>
										</tr>
										<tr>
											<td colspan="4">
												<table class="table table-bordered table-striped">
													<thead>
														<tr>
															<th style="width: 10%;" class="text-center">No AFB</th>
															<th style="width: 40%;" class="text-center">Actual No</th>
															<th style="width: 40%;" class="text-center">Test Result</th>
															<th style="width: 10%;" class="text-center">Action</th>
														</tr>
													</thead>
													<tbody id="testKitNameTable">
														<tr>
															<td class="text-center">1</td>
															<td>
																<input type="text" class="form-control" id="actualNo1" name="actualNo[]" placeholder="Enter the actual number" title="Please enter the actual number" />
															</td>
															<td>
																<select class="form-control test-result test-name-table-input" name="testResult[]" id="testResult1" title="Please select the result for row 1">
																	<?= $general->generateSelectOptions($tbLamResults, null, '-- Select --'); ?>
																</select>
															</td>
															<td style="vertical-align:middle;text-align: center;width:100px;">
																<a class="btn btn-xs btn-primary test-name-table" href="javascript:void(0);" onclick="addTestRow();"><i class="fa fa-plus"></i></a>&nbsp;
																<a class="btn btn-xs btn-default test-name-table" href="javascript:void(0);" onclick="removeTestRow(this.parentNode.parentNode);"><i class="fa fa-minus"></i></a>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
										<tr>
											<th><label class="label-control" for="xPertMTMResult">X pert MTB Result</label></th>
											<td>
												<select class="form-control" name="xPertMTMResult" id="xPertMTMResult" title="Please select the X Pert MTM Result">
													<?= $general->generateSelectOptions($tbXPertResults, null, '-- Select --'); ?>
												</select>
											</td>
											<th><label class="label-control" for="result">TB LAM Result</label></th>
											<td>
												<select class="form-control" name="result" id="result" title="Please select the TB LAM result">
													<?= $general->generateSelectOptions($tbLamResults, null, '-- Select --'); ?>
												</select>
											</td>
										</tr>
										<tr>
											<th><label class="label-control" for="reviewedBy">Reviewed By</label></th>
											<td>
												<select name="reviewedBy" id="reviewedBy" class="select2 form-control" title="Please choose reviewed by" style="width: 100%;">
													<?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
												</select>
											</td>
											<th><label class="label-control" for="reviewedOn">Reviewed on</label></td>
											<td><input type="text" name="reviewedOn" id="reviewedOn" class="dateTime disabled-field form-control" placeholder="Reviewed on" title="Please enter the Reviewed on" /></td>
										</tr>
										<tr>
											<th><label class="label-control" for="testedBy">Tested By</label></th>
											<td>
												<select name="testedBy" id="testedBy" class="select2 form-control" title="Please choose approved by" style="width: 100%;">
													<?= $general->generateSelectOptions($userInfo, null, '-- Select --'); ?>
												</select>
											</td>
										</tr>
									</table>
								</div>
							</div>
						<?php } ?>
					</div>
					<!-- /.box-body -->
					<div class="box-footer">
						<?php if ($arr['tb_sample_code'] == 'auto' || $arr['tb_sample_code'] == 'YY' || $arr['tb_sample_code'] == 'MMYY') { ?>
							<input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat; ?>" />
							<input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey; ?>" />
							<input type="hidden" name="saveNext" id="saveNext" />
						<?php } ?>
						<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
						<a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();$('#saveNext').val('next');return false;">Save and Next</a>
						<input type="hidden" name="formId" id="formId" value="<?php echo $arr['vl_form']; ?>" />
						<input type="hidden" name="tbSampleId" id="tbSampleId" value="" />
						<a href="/tb/requests/tb-requests.php" class="btn btn-default"> Cancel</a>
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
<script type="text/javascript">
	changeProvince = true;
	changeFacility = true;
	provinceName = true;
	facilityName = true;
	machineName = true;
	tableRowId = 2;

	function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
		var removeDots = obj.value.replace(/\./g, "");
		var removeDots = removeDots.replace(/\,/g, "");
		removeDots = removeDots.replace(/\s{2,}/g, ' ');

		$.post("/includes/checkDuplicate.php", {
				tableName: tableName,
				fieldName: fieldName,
				value: removeDots.trim(),
				fnct: fnct,
				format: "html"
			},
			function(data) {
				if (data === '1') {
					alert(alrt);
					document.getElementById(obj.id).value = "";
				}
			});
	}

	function getTestingPoints() {
		var labId = $("#labId").val();
		var selectedTestingPoint = null;
		if (labId) {
			$.post("/includes/getTestingPoints.php", {
					labId: labId,
					selectedTestingPoint: selectedTestingPoint
				},
				function(data) {
					if (data != "") {
						$(".testingPointField").show();
						$("#testingPoint").html(data);
					} else {
						$(".testingPointField").hide();
						$("#testingPoint").html('');
					}
				});
		}
	}

	function getfacilityDetails(obj) {

		$.blockUI();
		var cName = $("#facilityId").val();
		var pName = $("#province").val();
		if (pName != '' && provinceName && facilityName) {
			facilityName = false;
		}
		if ($.trim(pName) != '') {
			$.post("/includes/siteInformationDropdownOptions.php", {
					pName: pName,
					testType: 'tb'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#facilityId").html(details[0]);
						$("#district").html(details[1]);
					}
				});
			sampleCodeGeneration();
		} else if (pName == '') {
			provinceName = true;
			facilityName = true;
			$("#province").html("<?php echo $province; ?>");
			$("#facilityId").html("<?php echo $facility; ?>");
			$("#facilityId").select2("val", "");
			$("#district").html("<option value=''> -- Select -- </option>");
		}
		$.unblockUI();
	}

	function getPatientDistrictDetails(obj) {

		$.blockUI();
		var pName = obj.value;
		if ($.trim(pName) != '') {
			$.post("/includes/siteInformationDropdownOptions.php", {
					pName: pName,
					testType: 'tb'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#patientDistrict").html(details[1]);
					}
				});
		} else if (pName == '') {
			$(obj).html("<?php echo $province; ?>");
			$("#patientDistrict").html("<option value=''> -- Select -- </option>");
		}
		$.unblockUI();
	}

	function setPatientDetails(pDetails) {
		patientArray = pDetails.split("##");
		$("#patientProvince").val(patientArray[14] + '##' + patientArray[16]).trigger('change');
		$("#firstName").val(patientArray[0]);
		$("#lastName").val(patientArray[1]);
		$("#patientPhoneNumber").val(patientArray[8]);
		$("#patientGender").val(patientArray[2]);
		$("#patientAge").val(patientArray[4]);
		$("#patientDob").val(patientArray[3]);
		$("#patientId").val(patientArray[9]);
		$("#patientPassportNumber").val(patientArray[10]);
		$("#patientAddress").text(patientArray[11]);
		$("#patientNationality").select2('val', patientArray[12]);
		$("#patientCity").val(patientArray[13]);

		setTimeout(function() {
			$("#patientDistrict").val(patientArray[15]).trigger('change');
		}, 3000);
	}

	function sampleCodeGeneration() {
		var pName = $("#province").val();
		var sDate = $("#sampleCollectionDate").val();
		if (pName != '' && sDate != '') {
			$.post("/tb/requests/generate-sample-code.php", {
					sDate: sDate,
					pName: pName
				},
				function(data) {
					var sCodeKey = JSON.parse(data);
					$("#sampleCode").val(sCodeKey.sampleCode);
					$("#sampleCodeInText").html(sCodeKey.sampleCodeInText);
					$("#sampleCodeFormat").val(sCodeKey.sampleCodeFormat);
					$("#sampleCodeKey").val(sCodeKey.sampleCodeKey);
				});
		}
	}

	function getfacilityDistrictwise(obj) {
		$.blockUI();
		var dName = $("#district").val();
		var cName = $("#facilityId").val();
		if (dName != '') {
			$.post("/includes/siteInformationDropdownOptions.php", {
					dName: dName,
					cliName: cName,
					testType: 'tb'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#facilityId").html(details[0]);
					}
				});
		} else {
			$("#facilityId").html("<option value=''> -- Select -- </option>");
		}
		$.unblockUI();
	}

	function getfacilityProvinceDetails(obj) {
		$.blockUI();
		//check facility name
		var cName = $("#facilityId").val();
		var pName = $("#province").val();
		if (cName != '' && provinceName && facilityName) {
			provinceName = false;
		}
		if (cName != '' && facilityName) {
			$.post("/includes/siteInformationDropdownOptions.php", {
					cName: cName,
					testType: 'tb'
				},
				function(data) {
					if (data != "") {
						details = data.split("###");
						$("#province").html(details[0]);
						$("#district").html(details[1]);
						$("#clinicianName").val(details[2]);
					}
				});
		} else if (pName == '' && cName == '') {
			provinceName = true;
			facilityName = true;
			$("#province").html("<?php echo $province; ?>");
			$("#facilityId").html("<?php echo $facility; ?>");
		}
		$.unblockUI();
	}


	function validateNow() {
		if ($('#isResultAuthorized').val() != "yes") {
			$('#authorizedBy,#authorizedOn').removeClass('isRequired');
		}
		flag = deforayValidator.init({
			formId: 'addTbRequestForm'
		});
		if (flag) {
			<?php
			if ($arr['tb_sample_code'] == 'auto' || $arr['tb_sample_code'] == 'YY' || $arr['tb_sample_code'] == 'MMYY') {
			?>
				insertSampleCode('addTbRequestForm', 'tbSampleId', 'sampleCode', 'sampleCodeKey', 'sampleCodeFormat', 3, 'sampleCollectionDate');
			<?php
			} else {
			?>
				document.getElementById('addTbRequestForm').submit();
			<?php
			} ?>
		}
	}

	$(document).ready(function() {
		$(".select2").select2();
		$(".select2").select2({
			tags: true
		});
		$('#facilityId').select2({
			placeholder: "Select Clinic/Health Center"
		});
		$('#labTechnician').select2({
			placeholder: "Select Lab Technician"
		});

		$('#patientNationality').select2({
			placeholder: "Select Nationality"
		});

		$('#patientProvince').select2({
			placeholder: "Select Patient State"
		});

		$('#isResultAuthorized').change(function(e) {
			checkIsResultAuthorized();
		});

		$('#sourceOfAlertPOE').change(function(e) {
			if (this.value == 'others') {
				$('.show-alert-poe').show();
				$('#alertPoeOthers').addClass('isRequired');
			} else {
				$('.show-alert-poe').hide();
				$('#alertPoeOthers').removeClass('isRequired');
			}
		});
		<?php if (isset($arr['tb_positive_confirmatory_tests_required_by_central_lab']) && $arr['tb_positive_confirmatory_tests_required_by_central_lab'] == 'yes') { ?>
			$(document).on('change', '.test-result, #result', function(e) {
				checkPostive();
			});
		<?php } ?>

	});

	let testCounter = 1;

	function addTestRow() {
		testCounter++;
		let rowString = `<tr>
			<td class="text-center">${testCounter}</td>
            <td><input type="text" class="form-control" id="actualNo${testCounter}" name="actualNo[]" placeholder="Enter the actual number" title="Please enter the actual number" /></td>
            <td><select class="form-control test-result test-name-table-input" name="testResult[]" id="testResult${testCounter}" title="Please select the result for row ${testCounter}"><?= $general->generateSelectOptions($tbLamResults, null, '-- Select --'); ?></select></td>
            <td style="vertical-align:middle;text-align: center;width:100px;">
                <a class="btn btn-xs btn-primary test-name-table" href="javascript:void(0);" onclick="addTestRow(this);"><i class="fa fa-plus"></i></a>&nbsp;
                <a class="btn btn-xs btn-default test-name-table" href="javascript:void(0);" onclick="removeTestRow(this.parentNode.parentNode);"><i class="fa fa-minus"></i></a>
            </td>
        </tr>`;

		$("#testKitNameTable").append(rowString);

	}

	function removeTestRow(el) {
		$(el).fadeOut("slow", function() {
			el.parentNode.removeChild(el);
			rl = document.getElementById("testKitNameTable").rows.length;
			if (rl == 0) {
				testCounter = 0;
				addTestRow();
			}
		});
	}

	function checkIsResultAuthorized() {
		if ($('#isResultAuthorized').val() == 'no') {
			$('#authorizedBy,#authorizedOn').val('');
			$('#authorizedBy,#authorizedOn').prop('disabled', true);
			$('#authorizedBy,#authorizedOn').addClass('disabled');
			$('#authorizedBy,#authorizedOn').removeClass('isRequired');
		} else {
			$('#authorizedBy,#authorizedOn').prop('disabled', false);
			$('#authorizedBy,#authorizedOn').removeClass('disabled');
			$('#authorizedBy,#authorizedOn').addClass('isRequired');
		}
	}

	function otherTbTestName(val, id) {
		if (val == 'other') {
			$('.testNameOther' + id).show();
		} else {
			$('.testNameOther' + id).hide();
		}
	}
</script>