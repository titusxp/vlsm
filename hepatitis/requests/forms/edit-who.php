<?php

// imported in hepatitis-edit-request.php based on country in global config

ob_start();


//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);

//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);


// Getting the list of Provinces, Districts and Facilities

$rKey = '';
$pdQuery = "SELECT * FROM province_details";


if ($sarr['user_type'] == 'remoteuser') {
    $sampleCodeKey = 'remote_sample_code_key';
    $sampleCode = 'remote_sample_code';
    //check user exist in user_facility_map table
    $chkUserFcMapQry = "SELECT user_id from vl_user_facility_map where user_id='" . $_SESSION['userId'] . "'";
    $chkUserFcMapResult = $db->query($chkUserFcMapQry);
    if ($chkUserFcMapResult) {
        $pdQuery = "SELECT * from province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='" . $_SESSION['userId'] . "' group by province_name";
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

$facility = $general->generateSelectOptions($healthFacilities, $hepatitisInfo['facility_id'], '-- Select --');


//suggest sample id when lab user add request sample
$sampleSuggestion = '';
$sampleSuggestionDisplay = 'display:none;';
$sCode = (isset($_GET['c']) && $_GET['c'] != '') ? $_GET['c'] : '';
if ($sarr['user_type'] == 'vluser' && $sCode != '') {
    $vlObj = new \Vlsm\Models\Hepatitis($db);
    $sampleCollectionDate = explode(" ", $sampleCollectionDate);
    $sampleCollectionDate = $general->humanDateFormat($sampleCollectionDate[0]);
    $sampleSuggestionJson = $vlObj->generatehepatitisSampleCode($stateResult[0]['province_code'], $sampleCollectionDate, 'png');
    $sampleCodeKeys = json_decode($sampleSuggestionJson, true);
    $sampleSuggestion = $sampleCodeKeys['sampleCode'];
    $sampleSuggestionDisplay = 'display:block;';
}

?>


<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-edit"></i> WHO HEPATITIS LABORATORY TEST REQUEST FORM</h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
            <?php if (isset($hepatitisInfo['result']) && $hepatitisInfo['result'] != "") { ?>
                <li class="active">View Request</li>
            <?php } else { ?>
                <li class="active">Update Request</li>
            <?php } ?>
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
                <form class="form-horizontal" method="post" name="editHepatitisRequestForm" id="editHepatitisRequestForm" autocomplete="off" action="hepatitis-edit-request-helper.php">
                    <div class="box-body">
                        <div class="box box-default">
                            <div class="box-body">
                                <div class="box-header with-border">
                                    <h3 class="box-title">SITE INFORMATION</h3>
                                </div>
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-size:1em;">To be filled by requesting Clinician/Nurse</h3>
                                </div>
                                <table class="table" style="width:100%">
                                    <?php if ($hepatitisInfo['remote_sample'] == 'yes') { ?>
                                        <tr>
                                            <?php
                                            if ($hepatitisInfo['sample_code'] != '') {
                                            ?>
                                                <td colspan="4"> <label for="sampleSuggest" class="text-danger">&nbsp;&nbsp;&nbsp;Please note that this Remote Sample has already been imported with VLSM Sample ID </td>
                                                <td colspan="2" align="left"> <?php echo $hepatitisInfo['sample_code']; ?></label> </td>
                                            <?php
                                            } else {
                                            ?>
                                                <td colspan="4"> <label for="sampleSuggest">Sample ID (might change while submitting the form)</label></td>
                                                <td colspan="2" align="left"> <?php echo $sampleSuggestion; ?></td>
                                            <?php } ?>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                            <td><label for="sampleCode">Sample ID </label> </td>
                                            <td colspan="5">
                                                <span id="sampleCodeInText" style="width:30%;border-bottom:1px solid #333;"><?php echo ($sCode != '') ? $sCode : $hepatitisInfo[$sampleCode]; ?></span>
                                                <input type="hidden" class="<?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" value="<?php echo ($sCode != '') ? $sCode : $hepatitisInfo[$sampleCode]; ?>" />
                                            </td>
                                        <?php } else { ?>
                                            <td><label for="sampleCode">Sample ID </label><span class="mandatory">*</span> </td>
                                            <td colspan="5">
                                                <input type="text" readonly value="<?php echo ($sCode != '') ? $sCode : $hepatitisInfo[$sampleCode]; ?>" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="Sample ID" title="Please enter Sample ID" style="width:30%;" onchange="" />
                                            </td>
                                        <?php } ?>
                                    </tr>
                                    <tr>
                                        <td><label for="province">Province </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="province" id="province" title="Please choose province" onchange="getfacilityDetails(this);" style="width:100%;">
                                                <?php echo $province; ?>
                                            </select>
                                        </td>
                                        <td><label for="district">District </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="district" id="district" title="Please choose district" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                                <option value=""> -- Select -- </option>
                                            </select>
                                        </td>
                                        <td><label for="facilityId">Health Facility </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired " name="facilityId" id="facilityId" title="Please choose service provider" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                                <?php echo $facility; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="supportPartner">Implementing Partner </label></td>
                                        <td>

                                            <select class="form-control" name="implementingPartner" id="implementingPartner" title="Please choose implementing partner" style="width:100%;">
                                                <option value=""> -- Select -- </option>
                                                <?php
                                                foreach ($implementingPartnerList as $implementingPartner) {
                                                ?>
                                                    <option value="<?php echo ($implementingPartner['i_partner_id']); ?>" <?php echo ($hepatitisInfo['implementing_partner'] == $implementingPartner['i_partner_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($implementingPartner['i_partner_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td><label for="fundingSource">Funding Partner</label></td>
                                        <td>
                                            <select class="form-control" name="fundingSource" id="fundingSource" title="Please choose funding source" style="width:100%;">
                                                <option value=""> -- Select -- </option>
                                                <?php
                                                foreach ($fundingSourceList as $fundingSource) {
                                                ?>
                                                    <option value="<?php echo ($fundingSource['funding_source_id']); ?>" <?php echo ($hepatitisInfo['funding_source'] == $fundingSource['funding_source_id']) ? "selected='selected'" : ""; ?>><?php echo ucwords($fundingSource['funding_source_name']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <th style="width:15% !important">Sample Collection Date <span class="mandatory">*</span> </th>
                                        <td style="width:35% !important;">
                                            <input value="<?php echo ($hepatitisInfo['sample_collection_date']); ?>" class="form-control isRequired" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Sample Collection Date" onchange="sampleCodeGeneration();" />
                                        </td>
                                    </tr>
                                    <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                        <tr>
                                            <td><label for="labId">Lab Name <span class="mandatory">*</span></label> </td>
                                            <td>
                                                <select name="labId" id="labId" class="form-control isRequired" title="Please select Testing Lab name" style="width:100%;">
                                                    <?= $general->generateSelectOptions($testingLabs, $hepatitisInfo['lab_id'], '-- Select --'); ?>
                                                </select>
                                            </td>
                                            <th></th>
                                            <td></td>
                                            <th></th>
                                            <td></td>
                                        </tr>
                                    <?php } ?>
                                    </tr>
                                </table>
                                <br>
                                <hr style="border: 1px solid #ccc;">

                                <div class="box-header with-border">
                                    <h3 class="box-title">PATIENT INFORMATION</h3>
                                </div>
                                <table class="table" style="width:100%">

                                    <tr>
                                        <th style="width:15% !important"><label for="firstName">First Name <span class="mandatory">*</span> </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control isRequired" id="firstName" name="firstName" placeholder="First Name" title="Please enter patient first name" style="width:100%;" value="<?php echo $hepatitisInfo['patient_name']; ?>" />
                                        </td>
                                        <th style="width:15% !important"><label for="lastName">Last name </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control " id="lastName" name="lastName" placeholder="Last name" title="Please enter patient last name" style="width:100%;" value="<?php echo $hepatitisInfo['patient_surname']; ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="patientId">Patient ID <span class="mandatory">*</span> </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control isRequired" id="patientId" name="patientId" placeholder="Patient Identification" title="Please enter Patient ID" style="width:100%;" value="<?php echo $hepatitisInfo['patient_id']; ?>" />
                                        </td>
                                        <th><label for="patientDob">Date of Birth <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <input type="text" class="form-control isRequired" id="patientDob" name="patientDob" placeholder="Date of Birth" title="Please enter Date of birth" style="width:100%;" onchange="calculateAgeInYears();" value="<?php echo $general->humanDateFormat($hepatitisInfo['patient_dob']); ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Patient Age (years)</th>
                                        <td><input type="number" max="150" maxlength="3" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="patientAge" name="patientAge" placeholder="Patient Age (in years)" title="Patient Age" style="width:100%;" value="<?php echo $hepatitisInfo['patient_age']; ?>" /></td>
                                        <th><label for="patientGender">Gender <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <select class="form-control isRequired" name="patientGender" id="patientGender">
                                                <option value=''> -- Select -- </option>
                                                <option value='male' <?php echo ($hepatitisInfo['patient_gender'] == 'male') ? "selected='selected'" : ""; ?>> Male </option>
                                                <option value='female' <?php echo ($hepatitisInfo['patient_gender'] == 'female') ? "selected='selected'" : ""; ?>> Female </option>
                                                <option value='other' <?php echo ($hepatitisInfo['patient_gender'] == 'other') ? "selected='selected'" : ""; ?>> Other </option>

                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="patientGender">Marital Status</label></th>
                                        <td>
                                            <select class="form-control isRequired" name="maritalStatus" id="maritalStatus" title="Please select the Marital Status">
                                                <option value=''> -- Select -- </option>
                                                <option value='married' <?php echo ($hepatitisInfo['patient_marital_status'] == 'married') ? "selected='selected'" : ""; ?>> Married </option>
                                                <option value='single' <?php echo ($hepatitisInfo['patient_marital_status'] == 'single') ? "selected='selected'" : ""; ?>> Single </option>
                                                <option value='widow' <?php echo ($hepatitisInfo['patient_marital_status'] == 'widow') ? "selected='selected'" : ""; ?>> Widow </option>
                                                <option value='divorced' <?php echo ($hepatitisInfo['patient_marital_status'] == 'divorced') ? "selected='selected'" : ""; ?>> Divorced </option>
                                                <option value='separated' <?php echo ($hepatitisInfo['patient_marital_status'] == 'separated') ? "selected='selected'" : ""; ?>> Separated </option>

                                            </select>
                                        </td>
                                        <th><label for="patientGender">Insurance</label></th>
                                        <td>
                                            <select class="form-control isRequired" name="insurance" id="insurance" title="Please select the Insurance">
                                                <option value=''> -- Select -- </option>
                                                <option value='mutuelle' <?php echo ($hepatitisInfo['patient_insurance'] == 'mutuelle') ? "selected='selected'" : ""; ?>> Mutuelle </option>
                                                <option value='RAMA' <?php echo ($hepatitisInfo['patient_insurance'] == 'RAMA') ? "selected='selected'" : ""; ?>> RAMA </option>
                                                <option value='MMI' <?php echo ($hepatitisInfo['patient_insurance'] == 'MMI') ? "selected='selected'" : ""; ?>> MMI </option>
                                                <option value='private' <?php echo ($hepatitisInfo['patient_insurance'] == 'private') ? "selected='selected'" : ""; ?>> Private </option>
                                                <option value='none' <?php echo ($hepatitisInfo['patient_insurance'] == 'none') ? "selected='selected'" : ""; ?>> None </option>

                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Phone number</th>
                                        <td><input type="text" class="form-control " id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Patient Phone Number" title="Patient Phone Number" style="width:100%;" value="<?php echo $hepatitisInfo['patient_phone_number']; ?>" /></td>

                                        <th>Patient address</th>
                                        <td><textarea class="form-control " id="patientAddress" name="patientAddress" placeholder="Patient Address" title="Patient Address" style="width:100%;" onchange=""><?php echo $hepatitisInfo['patient_address']; ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <th>Province</th>
                                        <td><input type="text" value="<?php echo $hepatitisInfo['patient_province']; ?>" class="form-control " id="patientProvince" name="patientProvince" placeholder="Patient Province" title="Please enter the patient province" style="width:100%;" /></td>

                                        <th>District</th>
                                        <td><input class="form-control" value="<?php echo $hepatitisInfo['patient_district']; ?>" id="patientDistrict" name="patientDistrict" placeholder="Patient District" title="Please enter the patient district" style="width:100%;"></td>
                                    </tr>
                                </table>

                                <br><br>
                                <table class="table">
                                    <tr>
                                        <th colspan=4 style="border-top:#ccc 2px solid;">
                                            <h4>COMORBIDITIES</h4>
                                        </th>
                                    </tr>
                                    <?php foreach($comorbidityData as $id=>$name){ ?>
                                        <tr>
                                            <th>
                                                <label for="riskFactors"><?php echo ucwords($name);?></label>
                                            </th>
                                            <td>
                                                <select name="comorbidity[<?php echo $id;?>]" id="comorbidity<?php echo $id;?>" class="form-control" title="Please choose <?php echo ucwords($name);?>" style="width:100%"  onchange="comorbidity(this,<?php echo $id;?>);">
                                                    <option value="">-- Select --</option>
                                                    <option value="yes" <?php echo (isset($comorbidityInfo[$id]) && $comorbidityInfo[$id] == 'yes')?"selected='selected'":"";?>>Yes</option>
                                                    <option value="no" <?php echo (isset($comorbidityInfo[$id]) && $comorbidityInfo[$id] == 'no')?"selected='selected'":"";?>>No</option>
                                                    <option value="other" <?php echo (isset($comorbidityInfo[$id]) && $comorbidityInfo[$id] != 'yes' && $comorbidityInfo[$id] != 'no')?"selected='selected'":"";?>>Others</option>
                                                </select>
                                            </td>
                                            <?php $display = (isset($comorbidityInfo[$id]) && $comorbidityInfo[$id] != 'yes' && $comorbidityInfo[$id] != 'no')?"revert":"none";?>
                                            <th class="show-comorbidity<?php echo $id;?>" style="display:<?php echo $display;?>;">
                                                <label for="comorbidityOther<?php echo $id;?>">Enter other comorbidity for <?php echo ucwords($name);?></label>
                                            </th>
                                            <td class="show-comorbidity<?php echo $id;?>" style="display:<?php echo $display;?>;">
                                                <input value="<?php echo $comorbidityInfo[$id];?>" name="comorbidityOther[<?php echo $id;?>]" id="comorbidityOther<?php echo $id;?>" placeholder="Enter other comorbidity" type="text" class="form-control" title="Please enter <?php echo ucwords($name);?> others" style="width:100%">
                                            </td>
                                        </tr>
                                    <?php }?>
                                </table>

                                <table class="table">
                                    <tr>
                                        <th colspan=4 style="border-top:#ccc 2px solid;">
                                            <h4>HEPATITIS RISK FACTORS</h4>
                                        </th>
                                    </tr>
                                    <?php foreach($riskFactorsData as $id=>$name){ ?>
                                        <tr>
                                            <th>
                                                <label for="riskFactors"><?php echo ucwords($name);?></label>
                                            </th>
                                            <td>
                                                <select name="riskFactors[<?php echo $id;?>]" id="riskFactors<?php echo $id;?>" class="form-control" title="Please choose <?php echo ucwords($name);?>" style="width:100%"  onchange="riskfactor(this,<?php echo $id;?>);">
                                                    <option value="">-- Select --</option>
                                                    <option value="yes" <?php echo (isset($riskFactorsInfo[$id]) && $riskFactorsInfo[$id] == 'yes')?"selected='selected'":"";?>>Yes</option>
                                                    <option value="no" <?php echo (isset($riskFactorsInfo[$id]) && $riskFactorsInfo[$id] == 'no')?"selected='selected'":"";?>>No</option>
                                                    <option value="other" <?php echo (isset($riskFactorsInfo[$id]) && $riskFactorsInfo[$id] != 'yes' && $riskFactorsInfo[$id] != 'no')?"selected='selected'":"";?>>Others</option>
                                                </select>
                                            </td>
                                            <?php $display = (isset($riskFactorsInfo[$id]) && $riskFactorsInfo[$id] != 'yes' && $riskFactorsInfo[$id] != 'no')?"revert":"none";?>
                                            <th class="show-riskfactor<?php echo $id;?>" style="display:<?php echo $display;?>;">
                                                <label for="riskFactors">Enter other risk factor for <?php echo ucwords($name);?></label>
                                            </th>
                                            <td class="show-riskfactor<?php echo $id;?>" style="display:<?php echo $display;?>;">
                                                <input value="<?php echo $riskFactorsInfo[$id];?>" name="riskFactorsOther[<?php echo $id;?>]" id="riskFactorsOther<?php echo $id;?>" placeholder="Enter other risk factor" type="text" class="form-control" title="Please enter <?php echo ucwords($name);?> others" style="width:100%">
                                            </td>
                                        </tr>
                                    <?php }?>
                                    <tr>
                                        <th><label for="HbvVaccination">HBV vaccination</label></th>
                                        <td>
                                            <select name="HbvVaccination" id="HbvVaccination" class="form-control isRequired" title="Please choose HBV vaccination" style="width:100%">
                                                <option value="">-- Select --</option>
                                                <option value="yes" <?php echo ($hepatitisInfo['hbv_vaccination'] == 'yes') ? "selected='selected'" : ""; ?>>Yes</option>
                                                <option value="no" <?php echo ($hepatitisInfo['hbv_vaccination'] == 'no') ? "selected='selected'" : ""; ?>>No</option>
                                                <option value="fully-vaccinated" <?php echo ($hepatitisInfo['hbv_vaccination'] == 'fully-vaccinated') ? "selected='selected'" : ""; ?>>Fully vaccinated</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="box-body">
                            <div class="box-header with-border">
                                <h3 class="box-title">TEST RESULTS FOR SCREENING BY RDTs</h3>
                            </div>
                            <table class="table" style="width:100%">
                                <tr>
                                    <th><label for="HBsAg">HBsAg Result</label></th>
                                    <td>
                                        <select class="form-control" name="HBsAg" id="HBsAg" title="Please choose HBsAg result">
                                            <option value=''> -- Select -- </option>
                                            <option value='positive' <?php echo ($hepatitisInfo['hbsag_result'] == 'positive') ? "selected='selected'" : ""; ?>>Positive</option>
                                            <option value='negative' <?php echo ($hepatitisInfo['hbsag_result'] == 'negative') ? "selected='selected'" : ""; ?>>Negative</option>
                                            <option value='interminate' <?php echo ($hepatitisInfo['hbsag_result'] == 'interminate') ? "selected='selected'" : ""; ?>>Interminate</option>
                                        </select>
                                    </td>
                                    <th><label for="antiHcv">Anti-HCV Result</label></th>
                                    <td>
                                        <select class="form-control" name="antiHcv" id="antiHcv" title="Please choose Anti-HCV result">
                                            <option value=''> -- Select -- </option>
                                            <option value='positive' <?php echo ($hepatitisInfo['anti_hcv_result'] == 'positive') ? "selected='selected'" : ""; ?>>Positive</option>
                                            <option value='negative' <?php echo ($hepatitisInfo['anti_hcv_result'] == 'negative') ? "selected='selected'" : ""; ?>>Negative</option>
                                            <option value='interminate' <?php echo ($hepatitisInfo['anti_hcv_result'] == 'interminate') ? "selected='selected'" : ""; ?>>Interminate</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>


                        <?php if ($sarr['user_type'] != 'remoteuser') { ?>
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">TO BE FILLED AT VIRAL LOAD TESTING SITE </h3>
                                    </div>
                                    <table class="table" style="width:100%">
                                        <tr>
                                            <th><label for="">Sample Received Date </label></th>
                                            <td>
                                                <input value="<?php echo $general->humanDateFormat($hepatitisInfo['sample_received_at_vl_lab_datetime']) ?>" type="text" class="form-control" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Please enter sample receipt date" style="width:100%;" />
                                            </td>
                                            <th><label for="sampleTestedDateTime">Vl Testing Date</label></th>
                                            <td>
                                                <input value="<?php echo $general->humanDateFormat($hepatitisInfo['sample_tested_datetime']) ?>" type="text" class="form-control" id="sampleTestedDateTime" name="sampleTestedDateTime" placeholder="e.g 09-Jan-1992 05:30" title="Please enter testing date" style="width:100%;" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="vlTestingSite">Vl Testing Site</label></th>
                                            <td>
                                                <input value="<?php echo $hepatitisInfo['vl_testing_site'];?>" type="text" class="form-control" id="vlTestingSite" name="vlTestingSite" placeholder="Testing Site" title="Please enter testing site" style="width:100%;" />
                                            </td>
                                            <th><label for="sampleCondition">VL test purpose</label></th>
                                            <td>
                                                <select class="form-control" name="sampleCondition" id="sampleCondition">
                                                    <option value=''> -- Select -- </option>
                                                    <option value='Initial HCV VL' <?php echo ($hepatitisInfo['sample_condition'] == 'Initial HCV VL') ? "selected='selected'" : ""; ?>>Initial HCV VL</option>
                                                    <option value='SVR12 HCV VL' <?php echo ($hepatitisInfo['sample_condition'] == 'SVR12 HCV VL') ? "selected='selected'" : ""; ?>>SVR12 HCV VL</option>
                                                    <option value='Initial HBV VL' <?php echo ($hepatitisInfo['sample_condition'] == 'Initial HBV VL') ? "selected='selected'" : ""; ?>>Initial HBV VL</option>
                                                    <option value='Follow up HBV VL' <?php echo ($hepatitisInfo['sample_condition'] == 'Follow up HBV VL') ? "selected='selected'" : ""; ?>>Follow up HBV VL</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="hcv">HCV VL</label></th>
                                            <td>
                                                <select class="form-control" name="hcv" id="hcv">
                                                    <option value=''> -- Select -- </option>
                                                    <option value='positive' <?php echo ($hepatitisInfo['hcv_vl_result'] == 'positive') ? "selected='selected'" : ""; ?>>Positive</option>
                                                    <option value='negative' <?php echo ($hepatitisInfo['hcv_vl_result'] == 'negative') ? "selected='selected'" : ""; ?>>Negative</option>
                                                    <option value='interminate' <?php echo ($hepatitisInfo['hcv_vl_result'] == 'interminate') ? "selected='selected'" : ""; ?>>Interminate</option>
                                                </select>
                                            </td>
                                            <th><label for="hbv">HBV VL</label></th>
                                            <td>
                                                <select class="form-control" name="hbv" id="hbv">
                                                    <option value=''> -- Select -- </option>
                                                    <option value='positive' <?php echo ($hepatitisInfo['hbv_vl_result'] == 'positive') ? "selected='selected'" : ""; ?>>Positive</option>
                                                    <option value='negative' <?php echo ($hepatitisInfo['hbv_vl_result'] == 'negative') ? "selected='selected'" : ""; ?>>Negative</option>
                                                    <option value='interminate' <?php echo ($hepatitisInfo['hbv_vl_result'] == 'interminate') ? "selected='selected'" : ""; ?>>Interminate</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="hcvCount">HCV VL Count</label></th>
                                            <td>
                                                <input value="<?php echo $hepatitisInfo['hcv_vl_count'];?>" type="text" class="form-control" placeholder="Enter HCV Count" title="Please enter HCV Count" name="hcvCount" id="hcvCount">
                                            </td>
                                            <th><label for="hbvCount">HBV VL Count</label></th>
                                            <td>
                                                <input value="<?php echo $hepatitisInfo['hbv_vl_count'];?>" type="text" class="form-control" placeholder="Enter HBV Count" title="Please enter HBV Count" name="hbvCount" id="hbvCount">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Is Result Authorized ?</th>
                                            <td>
                                                <select name="isResultAuthorized" id="isResultAuthorized" class="disabled-field form-control" title="Is Result authorized ?" style="width:100%">
                                                    <option value="">-- Select --</option>
                                                    <option value='yes' <?php echo ($hepatitisInfo['is_result_authorised'] == 'yes') ? "selected='selected'" : ""; ?>> Yes </option>
                                                    <option value='no' <?php echo ($hepatitisInfo['is_result_authorised'] == 'no') ? "selected='selected'" : ""; ?>> No </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Authorized By</th>
                                            <td><input value="<?php echo $hepatitisInfo['authorized_by'];?>" type="text" name="authorizedBy" id="authorizedBy" class="disabled-field form-control" placeholder="Authorized By" /></td>
                                            <th>Authorized on</td>
                                            <td><input value="<?php echo $general->humanDateFormat($hepatitisInfo['authorized_on']) ?>" type="text" name="authorizedOn" id="authorizedOn" class="disabled-field form-control date" placeholder="Authorized on" /></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <?php if ($arr['sample_code'] == 'auto' || $arr['sample_code'] == 'YY' || $arr['sample_code'] == 'MMYY') { ?>
                            <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo (isset($sFormat) && $sFormat != '') ? $sFormat : ''; ?>" />
                            <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo (isset($sKey) && $sKey != '') ? $sKey : ''; ?>" />
                        <?php } ?>
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                        <input type="hidden" name="formId" id="formId" value="7" />
                        <input type="hidden" name="deletedRow" id="deletedRow" value="" />
                        <input type="hidden" name="hepatitisSampleId" id="hepatitisSampleId" value="<?php echo $hepatitisInfo['hepatitis_id']; ?>" />
                        <input type="hidden" name="sampleCodeCol" id="sampleCodeCol" value="<?php echo $arr['sample_code']; ?>" />
                        <input type="hidden" name="oldStatus" id="oldStatus" value="<?php echo $hepatitisInfo['result_status']; ?>" />
                        <input type="hidden" name="provinceCode" id="provinceCode" />
                        <input type="hidden" name="provinceId" id="provinceId" />
                        <a href="/hepatitis/requests/hepatitis-requests.php" class="btn btn-default"> Cancel</a>
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

    function getfacilityDetails(obj) {
        $.blockUI();
        var cName = $("#facilityId").val();
        var pName = $("#province").val();
        if (pName != '' && provinceName && facilityName) {
            facilityName = false;
        }
        if ($.trim(pName) != '') {
            //if (provinceName) {
            $.post("/includes/siteInformationDropdownOptions.php", {
                    pName: pName,
                    testType: 'hepatitis'
                },
                function(data) {
                    if (data != "") {
                        details = data.split("###");
                        $("#facilityId").html(details[0]);
                        $("#district").html(details[1]);
                        $("#clinicianName").val(details[2]);
                    }
                });
            //}
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

    function getfacilityDistrictwise(obj) {
        $.blockUI();
        var dName = $("#district").val();
        var cName = $("#facilityId").val();
        if (dName != '') {
            $.post("/includes/siteInformationDropdownOptions.php", {
                    dName: dName,
                    cliName: cName,
                    testType: 'hepatitis'
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
                    testType: 'hepatitis'
                },
                function(data) {
                    console.log(data);
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
        $("#provinceCode").val($("#province").find(":selected").attr("data-code"));
        $("#provinceId").val($("#province").find(":selected").attr("data-province-id"));
        flag = deforayValidator.init({
            formId: 'editHepatitisRequestForm'
        });
        if (flag) {
            document.getElementById('editHepatitisRequestForm').submit();
        }
    }

    $(document).ready(function() {
        $('#facilityId').select2({
            placeholder: "Select Clinic/Health Center"
        });
        $('#district').select2({
            placeholder: "District"
        });
        $('#province').select2({
            placeholder: "Province"
        });
        getfacilityProvinceDetails($("#facilityId"));
        checkIsResultAuthorized();
    });

    function checkIsResultAuthorized() {
        if ($('#isResultAuthorized').val() == 'no') {
            $('#authorizedBy,#authorizedOn').val('');
            $('#authorizedBy,#authorizedOn').prop('disabled', true);
            $('#authorizedBy,#authorizedOn').addClass('disabled');
            $('#authorizedBy,#authorizedOn').removeClass('isRequired');
            return false;
        } else {
            $('#authorizedBy,#authorizedOn').prop('disabled', false);
            $('#authorizedBy,#authorizedOn').removeClass('disabled');
            $('#authorizedBy,#authorizedOn').addClass('isRequired');
        }
    }
</script>