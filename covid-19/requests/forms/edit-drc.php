<?php

// imported in covid-19-edit-request.php based on country in global config

ob_start();


//Funding source list
$fundingSourceQry = "SELECT * FROM r_funding_sources WHERE funding_source_status='active' ORDER BY funding_source_name ASC";
$fundingSourceList = $db->query($fundingSourceQry);

//Implementing partner list
$implementingPartnerQry = "SELECT * FROM r_implementation_partners WHERE i_partner_status='active' ORDER BY i_partner_name ASC";
$implementingPartnerList = $db->query($implementingPartnerQry);


$covid19Obj = new \Vlsm\Models\Covid19($db);


$covid19Results = $covid19Obj->getCovid19Results();
$specimenTypeResult = $covid19Obj->getCovid19SampleTypes();

$covid19Symptoms = $covid19Obj->getCovid19SymptomsDRC();
$covid19SelectedSymptomsData = $covid19Obj->getCovid19SymptomsByFormId($covid19Info['covid19_id'], true);
$covid19SelectedSymptoms = array();
foreach ($covid19SelectedSymptomsData as $row) {
    $covid19SelectedSymptoms[$row['symptom_id']]['value'] = $row['symptom_detected'];
    $covid19SelectedSymptoms[$row['symptom_id']]['sDetails'] = json_decode($row['symptom_details'], true);
}


$covid19ReasonsForTesting = $covid19Obj->getCovid19ReasonsForTestingDRC();
$covid19SelectedReasonsForTesting = $covid19Obj->getCovid19ReasonsForTestingByFormId($covid19Info['covid19_id']);
$covid19SelectedReasonsDetailsForTesting = $covid19Obj->getCovid19ReasonsDetailsForTestingByFormId($covid19Info['covid19_id']);
// To get the reason details value
$reasonDetails = json_decode($covid19SelectedReasonsDetailsForTesting['reason_details'], true);

$covid19Comorbidities = $covid19Obj->getCovid19Comorbidities();
$covid19SelectedComorbidities = $covid19Obj->getCovid19ComorbiditiesByFormId($covid19Info['covid19_id']);


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
$province .= "<option value=''> -- Sélectionner -- </option>";
foreach ($pdResult as $provinceName) {
    $province .= "<option data-code='" . $provinceName['province_code'] . "' data-province-id='" . $provinceName['province_id'] . "' data-name='" . $provinceName['province_name'] . "' value='" . $provinceName['province_name'] . "##" . $provinceName['province_code'] . "'>" . ucwords($provinceName['province_name']) . "</option>";
}

$facility = $general->generateSelectOptions($healthFacilities, $covid19Info['facility_id'], '-- Sélectionner --');


//suggest N°EPID when lab user add request sample
$sampleSuggestion = '';
$sampleSuggestionDisplay = 'display:none;';
$sCode = (isset($_GET['c']) && $_GET['c'] != '') ? $_GET['c'] : '';
if ($sarr['user_type'] == 'vluser' && $sCode != '') {
    $vlObj = new \Vlsm\Models\Covid19($db);
    $sampleCollectionDate = explode(" ", $sampleCollectionDate);
    $sampleCollectionDate = $general->humanDateFormat($sampleCollectionDate[0]);
    $sampleSuggestionJson = $vlObj->generateCovid19SampleCode($stateResult[0]['province_code'], $sampleCollectionDate, 'png');
    $sampleCodeKeys = json_decode($sampleSuggestionJson, true);
    $sampleSuggestion = $sampleCodeKeys['sampleCode'];
    $sampleSuggestionDisplay = 'display:block;';
}

?>
<style>
    .other-comorbidities {
        display: none;
    }
</style>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-edit"></i> COVID-19 VIRUS LABORATORY TEST DRC REQUEST FORM</h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Accueil</a></li>
            <li class="active">Ajouter une nouvelle demande</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indique un champ obligatoire &nbsp;</div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- form start -->
                <form class="form-horizontal" method="post" name="editCovid19RequestForm" id="editCovid19RequestForm" autocomplete="off" action="covid-19-edit-request-helper.php">
                    <div class="box-body">
                        <div class="box box-default">
                            <div class="box-body">
                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">INFORMATIONS SUR LE SITE</h3>
                                </div>
                                <div class="box-header with-border">
                                    <h3 class="box-title" style="font-size:1em;">À remplir par le clinicien / infirmier demandeur</h3>
                                </div>
                                <table class="table" style="width:100%">
                                    <?php if ($covid19Info['remote_sample'] == 'yes') { ?>
                                        <tr>
                                            <?php
                                            if ($covid19Info['sample_code'] != '') {
                                            ?>
                                                <td> <label for="sampleSuggest" class="text-danger">&nbsp;&nbsp;&nbsp;Veuillez noter que cet exemple distant a déjà été importé avec VLSM N°EPID </td>
                                                <td align="left"> <?php echo $covid19Info['sample_code']; ?></label> </td>
                                            <?php
                                            } else {
                                            ?>
                                                <td> <label for="sampleSuggest">N°EPID (peut changer lors de la soumission du formulaire)</label></td>
                                                <td align="left"> <?php echo $sampleSuggestion; ?></td>
                                            <?php } ?>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                            <td><label for="sampleCode">N°EPID </label> </td>
                                            <td>
                                                <span id="sampleCodeInText" style="width:30%;border-bottom:1px solid #333;"><?php echo ($sCode != '') ? $sCode : $covid19Info[$sampleCode]; ?></span>
                                                <input type="hidden" class="<?php echo $sampleClass; ?>" id="sampleCode" name="sampleCode" value="<?php echo ($sCode != '') ? $sCode : $covid19Info[$sampleCode]; ?>" />
                                            </td>
                                        <?php } else { ?>
                                            <td><label for="sampleCode">N°EPID </label><span class="mandatory">*</span> </td>
                                            <td>
                                                <input type="text" readonly value="<?php echo ($sCode != '') ? $sCode : $covid19Info[$sampleCode]; ?>" class="form-control isRequired" id="sampleCode" name="sampleCode" placeholder="N°EPID" title="N°EPID" style="width:100%;" onchange="" />
                                            </td>
                                        <?php } ?>
                                        <th><label for="testNumber">Prélévement</label></th>
                                        <td>
                                            <select class="form-control" name="testNumber" id="testNumber" title="Prélévement" style="width:100%;">
                                                <option value="">--Select--</option>
                                                <?php foreach (range(1, 5) as $element) {
                                                    $selected = (isset($covid19Info['test_number']) && $covid19Info['test_number'] == $element) ? "selected='selected'" : "";
                                                    echo '<option value="' . $element . '" ' . $selected . '>' . $element . '</option>';
                                                } ?>
                                            </select>
                                        </td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><label for="province">Province </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="province" id="province" title="Province" onchange="getfacilityDetails(this);" style="width:100%;">
                                                <?php echo $province; ?>
                                            </select>
                                        </td>
                                        <td><label for="district">Zone de Santé </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired" name="district" id="district" title="Zone de Santé " style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                                <option value=""> -- Sélectionner -- </option>
                                            </select>
                                        </td>
                                        <td><label for="facilityId">Nom de l'installation </label><span class="mandatory">*</span></td>
                                        <td>
                                            <select class="form-control isRequired " name="facilityId" id="facilityId" title="Nom de l'installation" style="width:100%;" onchange="getfacilityProvinceDetails(this);">
                                                <?php echo $facility; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php if ($sarr['user_type'] == 'remoteuser') { ?>
                                            <!-- <tr> -->
                                            <td><label for="labId">LAB ID <span class="mandatory">*</span></label> </td>
                                            <td>
                                                <select name="labId" id="labId" class="form-control isRequired" title="Please select Testing Lab name" style="width:100%;">
                                                    <?= $general->generateSelectOptions($testingLabs, $covid19Info['lab_id'], '-- Sélectionner --'); ?>
                                                </select>
                                            </td>
                                            <!-- </tr> -->
                                        <?php } ?>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">INFORMATION PATIENT</h3>
                                </div>
                                <table class="table" style="width:100%">

                                    <tr>
                                        <th style="width:15% !important"><label for="firstName">Prénom <span class="mandatory">*</span> </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control isRequired" id="firstName" name="firstName" placeholder="Prénom" title="Prénom" style="width:100%;" value="<?php echo $covid19Info['patient_name']; ?>" />
                                        </td>
                                        <th style="width:15% !important"><label for="lastName">Nom de famille</label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control " id="lastName" name="lastName" placeholder="Nom de famille" title="Nom de famille" style="width:100%;" value="<?php echo $covid19Info['patient_surname']; ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="patientId">Code Patient <span class="mandatory">*</span> </label></th>
                                        <td style="width:35% !important">
                                            <input type="text" class="form-control isRequired" id="patientId" name="patientId" placeholder="Code Patient" title="Code Patient" style="width:100%;" value="<?php echo $covid19Info['patient_id']; ?>" />
                                        </td>
                                        <th><label for="patientDob">Date de naissance <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <input type="text" class="form-control isRequired" id="patientDob" name="patientDob" placeholder="Date de naissance" title="Date de naissance" style="width:100%;" onchange="calculateAgeInYears();" value="<?php echo $general->humanDateFormat($covid19Info['patient_dob']); ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Age (years)</th>
                                        <td><input type="number" max="150" maxlength="3" oninput="this.value=this.value.slice(0,$(this).attr('maxlength'))" class="form-control " id="patientAge" name="patientAge" placeholder="Age (years)" title="Age (years)" style="width:100%;" value="<?php echo $covid19Info['patient_age']; ?>" /></td>
                                        <th><label for="patientGender">Sexe <span class="mandatory">*</span> </label></th>
                                        <td>
                                            <select class="form-control isRequired" name="patientGender" id="patientGender">
                                                <option value=''> -- Sélectionner -- </option>
                                                <option value='male' <?php echo ($covid19Info['patient_gender'] == 'male') ? "selected='selected'" : ""; ?>> Homme </option>
                                                <option value='female' <?php echo ($covid19Info['patient_gender'] == 'female') ? "selected='selected'" : ""; ?>> Femme </option>
                                                <option value='other' <?php echo ($covid19Info['patient_gender'] == 'other') ? "selected='selected'" : ""; ?>> Other </option>

                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="isPatientPregnant">Enceinte</label></th>
                                        <td>
                                            <select class="form-control" name="isPatientPregnant" id="isPatientPregnant">
                                                <option value=''> -- Sélectionner -- </option>
                                                <option value='yes' <?php echo ($covid19Info['is_patient_pregnant'] == 'yes') ? "selected='selected'" : ""; ?>> Enceinte </option>
                                                <option value='no' <?php echo ($covid19Info['is_patient_pregnant'] == 'no') ? "selected='selected'" : ""; ?>> Pas Enceinte </option>
                                                <option value='unknown' <?php echo ($covid19Info['is_patient_pregnant'] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnue </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Numéro de téléphone</th>
                                        <td><input type="text" class="form-control " id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Numéro de téléphone" title="Numéro de téléphone" style="width:100%;" value="<?php echo $covid19Info['patient_phone_number']; ?>" /></td>

                                        <th>Adresse du patient</th>
                                        <td><textarea class="form-control " id="patientAddress" name="patientAddress" placeholder="Adresse du patient" title="Adresse du patient" style="width:100%;" onchange=""><?php echo $covid19Info['patient_address']; ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <th>Province du patient</th>
                                        <td><input type="text" value="<?php echo $covid19Info['patient_province']; ?>" class="form-control " id="patientProvince" name="patientProvince" placeholder="Province du patient" title="Province du patient" style="width:100%;" /></td>

                                        <th>District des patients</th>
                                        <td><input class="form-control" value="<?php echo $covid19Info['patient_district']; ?>" id="patientDistrict" name="patientDistrict" placeholder="District des patients" title="District des patients" style="width:100%;"></td>
                                    </tr>
                                    <tr>
                                        <th>Pays de résidence</th>
                                        <td><input type="text" class="form-control" value="<?php echo $covid19Info['patient_nationality']; ?>" id="patientNationality" name="patientNationality" placeholder="Pays de résidence" title="Pays de résidence" style="width:100%;" /></td>

                                        <th></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <h4>Les détails du vol</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Compagnie aérienne</th>
                                        <td><input type="text" class="form-control " value="<?php echo $covid19Info['flight_airline']; ?>" id="airline" name="airline" placeholder="Compagnie aérienne" title="Compagnie aérienne" style="width:100%;" /></td>

                                        <th>Numéro de siège</th>
                                        <td><input type="text" class="form-control " value="<?php echo $covid19Info['flight_seat_no']; ?>" id="seatNo" name="seatNo" placeholder="Numéro de siège" title="Numéro de siège" style="width:100%;" /></td>
                                    </tr>
                                    <tr>
                                        <th>Date et heure d'arrivée</th>
                                        <td><input type="text" class="form-control dateTime" value="<?php echo $general->humanDateFormat($covid19Info['flight_arrival_datetime']); ?>" id="arrivalDateTime" name="arrivalDateTime" placeholder="Date et heure d'arrivée" title="Date et heure d'arrivée" style="width:100%;" /></td>

                                        <th>Aeroport DE DEPART</th>
                                        <td><input type="text" class="form-control" value="<?php echo $covid19Info['flight_airport_of_departure']; ?>" id="airportOfDeparture" name="airportOfDeparture" placeholder="Aeroport DE DEPART" title="Aeroport DE DEPART" style="width:100%;" /></td>
                                    </tr>
                                    <tr>
                                        <th>Transit</th>
                                        <td><input type="text" class="form-control" value="<?php echo $covid19Info['flight_transit']; ?>" id="transit" name="transit" placeholder="Transit" title="Transit" style="width:100%;" /></td>
                                        <th>Raison de la visite (le cas échéant)</th>
                                        <td><input type="text" class="form-control" value="<?php echo $covid19Info['reason_of_visit']; ?>" id="reasonOfVisit" name="reasonOfVisit" placeholder="Raison de la visite (le cas échéant)" title="Raison de la visite (le cas échéant)" style="width:100%;" /></td>

                                    </tr>
                                </table>
                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">
                                        Signes vitaux du patient
                                    </h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width: 15%;"><label for="specimenType"> Type(s) d' échantillon(s) dans le tube (cochez au moins une des cases suivants) <span class="mandatory">*</span></label></th>
                                        <td style="width: 35%;">
                                            <select class="form-control isRequired" id="specimenType" name="specimenType" title="Type(s) d' échantillon(s) dans le tube">
                                                <option value="">--Select--</option>
                                                <!-- <option value="oropharyngeal">Oropharyngée</option>
                                                <option value="nasal-both">Nasale / Les Deux</option>
                                                <option value="sputum">Expectorations</option>
                                                <option value="alveolar-broncho-wash">Lavage broncho alvéolaire</option>
                                                <option value="tracheal-aspiration">Aspiration trachéale</option>
                                                <option value="serum">Sérum</option> -->
                                                <?php echo $general->generateSelectOptions($specimenTypeResult, $covid19Info['specimen_type']); ?>
                                            </select>
                                        </td>
                                        <th style="width:15% !important"><label for="specimenType">Date de prélèvement de l'échantillon <span class="mandatory">*</span></label></th>
                                        <td style="width:35% !important;">
                                            <input class="form-control isRequired" value="<?php echo date('d-M-Y H:i:s', strtotime($covid19Info['sample_collection_date'])); ?>" type="text" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Date de prélèvement de l'échantillon" title="Date de prélèvement de l'échantillon" onchange="sampleCodeGeneration();" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" style="width:15% !important">Symptômes <span class="mandatory">*</span> </th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <table id="symptomsTable" class="table table-bordered">
                                                <?php $index = 0;
                                                foreach ($covid19Symptoms as $symptomId => $symptomName) { ?>
                                                    <tr class="row<?php echo $index; ?>">
                                                        <!-- <td style="display: flex;">
                                                            <label class="radio-inline" style="width:4%;margin-left:0;">
                                                                <input type="checkbox" class="checkSymptoms" id="xsymptom<?php echo $symptomId; ?>" name="symptom[]" value="<?php echo $symptomId; ?>" title="Veuillez choisir la valeur pour <?php echo $symptomName; ?>" onclick="checkSubSymptoms(this.value,<?php echo $symptomId; ?>,<?php echo $index; ?>);">
                                                            </label>
                                                            <label class="radio-inline" for="symptom<?php echo $symptomId; ?>" style="padding-left:17px !important;margin-left:0;"><b><?php echo $symptomName; ?></b></label>
                                                        </td> -->
                                                        <th style="width:50%;"><?php echo $symptomName; ?></th>
                                                        <td style="width:50%;">
                                                            <input name="symptomId[]" type="hidden" value="<?php echo $symptomId; ?>">
                                                            <select name="symptomDetected[]" id="symptomDetected<?php echo $symptomId; ?>" class="form-control" title="Veuillez choisir la valeur pour <?php echo $symptomName; ?>" style="width:100%">
                                                                <option value="">-- Sélectionner --</option>
                                                                <option value='yes' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['value']) && $covid19SelectedSymptoms[$symptomId]['value'] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                                <option value='no' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['value']) && $covid19SelectedSymptoms[$symptomId]['value'] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                                <option value='unknown' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['value']) && $covid19SelectedSymptoms[$symptomId]['value'] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnu </option>
                                                            </select>

                                                            <br>
                                                            <?php
                                                            if ($symptomId == 13) {
                                                            ?>
                                                                <label class="" for="" style="margin-left:0;">Si oui:<br> Sanglante?</label>
                                                                <select name="symptomDetails[13][]" class="form-control" style="width:100%">
                                                                    <option value="">-- Sélectionner --</option>
                                                                    <option value='yes' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['sDetails'][0]) && $covid19SelectedSymptoms[$symptomId]['sDetails'][0] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                                    <option value='no' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['sDetails'][0]) && $covid19SelectedSymptoms[$symptomId]['sDetails'][0] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                                    <option value='unknown' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['sDetails'][0]) && $covid19SelectedSymptoms[$symptomId]['sDetails'][0] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnu </option>
                                                                </select>
                                                                <label class="" for="" style="margin-left:0;">Aqueuse?</label>
                                                                <select name="symptomDetails[13][]" class="form-control" style="width:100%">
                                                                    <option value="">-- Sélectionner --</option>
                                                                    <option value='yes' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['sDetails'][1]) && $covid19SelectedSymptoms[$symptomId]['sDetails'][1] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                                    <option value='no' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['sDetails'][1]) && $covid19SelectedSymptoms[$symptomId]['sDetails'][1] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                                    <option value='unknown' <?php echo (isset($covid19SelectedSymptoms[$symptomId]['sDetails'][1]) && $covid19SelectedSymptoms[$symptomId]['sDetails'][1] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnu </option>
                                                                </select>
                                                                <label class="" for="" style="margin-left:0;">Nombre De Selles Par /24h</label>
                                                                <input type="text" class="form-control reason-checkbox symptoms-checkbox" id="" name="symptomDetails[13][]" placeholder="Nombre de selles par /24h" title="Nombre de selles par /24h" value="<?php echo $covid19SelectedSymptoms[$symptomId]['sDetails'][2]; ?>">

                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php $index++;
                                                } ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="medicalHistory"></label>Antécédents Médicaux</th>
                                        <td style="width:35% !important;">
                                            <select name="medicalHistory" id="medicalHistory" class="form-control" title="Antécédents Médicaux">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['medical_history'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['medical_history'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['medical_history'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="comorbidities-row" style="<?php echo ($covid19Info['medical_history'] != 'yes') ? 'display:none' : ''; ?>">
                                        <td colspan="4">
                                            <table id="comorbiditiesTable" class="table table-bordered">
                                                <?php $index = 0;
                                                foreach ($covid19Comorbidities as $comorbiditiesId => $comorbiditiesName) { ?>
                                                    <tr>
                                                        <th style="width:50%;"><?php echo $comorbiditiesName; ?></th>
                                                        <td style="width:50%;">
                                                            <input name="comorbidityId[]" type="hidden" value="<?php echo $comorbiditiesId; ?>">
                                                            <select name="comorbidityDetected[]" class="form-control comorbidity-select" title="<?php echo $comorbiditiesName; ?>" style="width:100%">
                                                                <option value="">-- Sélectionner --</option>
                                                                <option value='yes' <?php echo (isset($covid19SelectedComorbidities[$comorbiditiesId]) && $covid19SelectedComorbidities[$comorbiditiesId] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                                <option value='no' <?php echo (isset($covid19SelectedComorbidities[$comorbiditiesId]) && $covid19SelectedComorbidities[$comorbiditiesId] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                                <option value='unknown' <?php echo (isset($covid19SelectedComorbidities[$comorbiditiesId]) && $covid19SelectedComorbidities[$comorbiditiesId] == 'unknown') ? "selected='selected'" : ""; ?>> Inconnu </option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php $index++;
                                                } ?>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4" style="width:15% !important">Définition de cas <span class="mandatory">*</span> </th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <table id="responseTable" class="table table-bordered">
                                                <tr>
                                                    <td colspan="2">
                                                        <label class="radio-inline" style="margin-left:0;">
                                                            <input type="radio" class="" id="reason1" name="reasonForCovid19Test" value="1" title="Please check response" onchange="checkSubReason(this,'Cas_suspect_de_COVID_19');" <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "checked" : ""; ?>>
                                                            <strong>Cas suspect de COVID-19</strong>
                                                        </label>

                                                    </td>
                                                </tr>
                                                <tr class="Cas_suspect_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="suspect1" name="reasonDetails[]" value="Fièvre d'accès brutal (Inferieur ou égale à 38°C, vérifié à la salle d'urgence, la consultation externe, ou l'hôpital) ET(cochez une ou deux des cases suivantes)" title="Please check response" <?php echo (in_array("Fièvre d'accès brutal (Inferieur ou égale à 38°C, vérifié à la salle d'urgence, la consultation externe, ou l'hôpital) ET(cochez une ou deux des cases suivantes)", $reasonDetails)) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="suspect1" style="padding-left:17px !important;margin-left:0;">Fièvre d'accès brutal (Inferieur ou égale à 38°C, vérifié à la salle d'urgence, la consultation externe, ou l'hôpital) ET(cochez une ou deux des cases suivantes)</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_suspect_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="suspect2" name="reasonDetails[]" value="Toux" title="Please check response" <?php echo (in_array("Toux", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="suspect2" style="padding-left:17px !important;margin-left:0;">Toux</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_suspect_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="suspect3" name="reasonDetails[]" value="Rhume" title="Please check response" <?php echo (in_array("Rhume", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="suspect3" style="padding-left:17px !important;margin-left:0;">Rhume</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_suspect_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="suspect4" name="reasonDetails[]" value="Mal de gorge" title="Please check response" <?php echo (in_array("Mal de gorge", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="suspect4" style="padding-left:17px !important;margin-left:0;">Mal de gorge</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_suspect_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="suspect5" name="reasonDetails[]" value="Difficulté respiratoire" title="Please check response" <?php echo (in_array("Difficulté respiratoire", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="suspect5" style="padding-left:17px !important;margin-left:0;">Difficulté respiratoire</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_suspect_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="suspect6" name="reasonDetails[]" value="Notion de séjour ou voyage dans les zones a épidémie a COVID-19 dans les 14 jours précédant les symptômes ci-dessous." title="Please check response" <?php echo (in_array("Notion de séjour ou voyage dans les zones a épidémie a COVID-19 dans les 14 jours précédant les symptômes ci-dessous.", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="suspect6" style="padding-left:17px !important;margin-left:0;">Notion de séjour ou voyage dans les zones a épidémie a COVID-19 dans les 14 jours précédant les symptômes ci-dessous.</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_suspect_de_COVID_19 hide-reasons text-center" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td>
                                                        <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">OU</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_suspect_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="suspect7" name="reasonDetails[]" value="IRA d'intensité variable (simple a sévère) ayant été en contact étroite avec cas probable ou un cas confirmé de la maladie a COVID-19" title="Please check response" <?php echo (in_array("IRA d'intensité variable (simple a sévère) ayant été en contact étroite avec cas probable ou un cas confirmé de la maladie a COVID-19", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="suspect7" style="padding-left:17px !important;margin-left:0;">IRA d'intensité variable (simple a sévère) ayant été en contact étroite avec cas probable ou un cas confirmé de la maladie a COVID-19</label>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="2">
                                                        <label class="radio-inline" style="margin-left:0;">
                                                            <input type="radio" id="reason2" name="reasonForCovid19Test" value="2" title="Please check response" onchange="checkSubReason(this,'Cas_probable_de_COVID_19');" <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 2) ? "checked" : ""; ?>>
                                                            <strong>Cas probable de COVID-19</strong>
                                                        </label>

                                                    </td>
                                                </tr>
                                                <tr class="Cas_probable_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 2) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="probable1" name="reasonDetails[]" value="Tout cas suspects dont le résultat de laboratoire pour le diagnostic de COVID-19 n'est pas concluant (indéterminé)" title="Please check response" <?php echo (in_array("Tout cas suspects dont le résultat de laboratoire pour le diagnostic de COVID-19 n'est pas concluant (indéterminé)", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 2) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="probable1" style="padding-left:17px !important;margin-left:0;">Tout cas suspects dont le résultat de laboratoire pour le diagnostic de COVID-19 n'est pas concluant (indéterminé)</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_probable_de_COVID_19 hide-reasons text-center" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 2) ? "grid" : "none"; ?>;">
                                                    <td>
                                                        <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">OU</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_probable_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 2) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="probable2" name="reasonDetails[]" value="Tout décès dans un tableau d'IRA pour lequel il n'a pas été possible d'obtenir des échantillons biologiques pour confirmation au laboratoire mais dont les investigations ont révélé un lien épidémiologique avec un cas confirmé ou probable" title="Please check response" <?php echo (in_array("Tout décès dans un tableau d'IRA pour lequel il n'a pas été possible d'obtenir des échantillons biologiques pour confirmation au laboratoire mais dont les investigations ont révélé un lien épidémiologique avec un cas confirmé ou probable", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 2) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="probable2" style="padding-left:17px !important;margin-left:0;">Tout décès dans un tableau d'IRA pour lequel il n'a pas été possible d'obtenir des échantillons biologiques pour confirmation au laboratoire mais dont les investigations ont révélé un lien épidémiologique avec un cas confirmé ou probable</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_probable_de_COVID_19 hide-reasons text-center" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 1) ? "grid" : "none"; ?>;">
                                                    <td>
                                                        <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">OU</label>
                                                    </td>
                                                </tr>
                                                <tr class="Cas_probable_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 2) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="probable4" name="reasonDetails[]" value="Notion de séjour ou voyage dans les 14 jours précédant le décès dans les zones a épidémie de la maladie a COVID-19" title="Please check response" <?php echo (in_array("Notion de séjour ou voyage dans les 14 jours précédant le décès dans les zones a épidémie de la maladie a COVID-19", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 2) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="probable4" style="padding-left:17px !important;margin-left:0;">Notion de séjour ou voyage dans les 14 jours précédant le décès dans les zones a épidémie de la maladie a COVID-19</label>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="2">
                                                        <label class="radio-inline" style="margin-left:0;">
                                                            <input type="radio" id="reason3" name="reasonForCovid19Test" value="3" title="Please check response" onchange="checkSubReason(this,'Cas_confirme_de_COVID_19');" <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 3) ? "checked" : ""; ?>>
                                                            <strong>Cas confirme de covid-19</strong>
                                                        </label>

                                                    </td>
                                                </tr>
                                                <tr class="Cas_confirme_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 3) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="confirme1" name="reasonDetails[]" value="Toute personne avec une confirmation en laboratoire de l'infection au COVID-19, quelles que soient les signes et symptômes cliniques" title="Please check response" <?php echo (in_array("Toute personne avec une confirmation en laboratoire de l'infection au COVID-19, quelles que soient les signes et symptômes cliniques", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 3) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="confirme1" style="padding-left:17px !important;margin-left:0;">Toute personne avec une confirmation en laboratoire de l'infection au COVID-19, quelles que soient les signes et symptômes cliniques</label>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="2">
                                                        <label class="radio-inline" style="margin-left:0;">
                                                            <input type="radio" id="reason4" name="reasonForCovid19Test" value="4" title="Please check response" onchange="checkSubReason(this,'Non_cas_contact_de_COVID_19');" <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 4) ? "checked" : ""; ?>>
                                                            <strong>Non cas contact de COVID-19</strong>
                                                        </label>

                                                    </td>
                                                </tr>
                                                <tr class="Non_cas_contact_de_COVID_19 hide-reasons" style="display: <?php echo (isset($covid19SelectedReasonsDetailsForTesting['reasons_id']) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 4) ? "grid" : "none"; ?>;">
                                                    <td colspan="2" style="padding-left: 70px;display: flex;">
                                                        <label class="radio-inline" style="width:4%;margin-left:0;">
                                                            <input type="checkbox" class="checkbox" id="contact1" name="reasonDetails[]" value="Tout cas suspects avec deux résultats de laboratoire négatifs au COVID-19 a au moins 48 heures d'intervalle" title="Please check response" <?php echo (in_array("Tout cas suspects avec deux résultats de laboratoire négatifs au COVID-19 a au moins 48 heures d'intervalle", $reasonDetails) && $covid19SelectedReasonsDetailsForTesting['reasons_id'] == 4) ? "checked" : ""; ?>>
                                                        </label>
                                                        <label class="radio-inline" for="contact1" style="padding-left:17px !important;margin-left:0;">Tout cas suspects avec deux résultats de laboratoire négatifs au COVID-19 a au moins 48 heures d'intervalle</label>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">
                                        VOYAGE ET CONTACT
                                    </h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width: 15% !important;"><label for="hasRecentTravelHistory">Avez-vous voyagé au cours des 14 derniers jours ? </label></th>
                                        <td style="width:35% !important;">
                                            <select class="form-control" id="hasRecentTravelHistory" name="hasRecentTravelHistory" title="Avez-vous voyagé au cours des 14 derniers jours ?">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['has_recent_travel_history'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['has_recent_travel_history'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['has_recent_travel_history'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width: 15% !important;"><label for="countryName">Si oui, dans quels pays?</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" value="<?php echo $covid19Info['travel_country_names']; ?>" class="form-control" id="countryName" name="countryName" placeholder="Si oui, dans quels pays ?" title="Si oui, dans quels pays?" />
                                        </td>
                                    </tr>

                                    <tr>
                                        <th style="width: 15% !important;"><label for="returnDate">Date de retour</label></th>
                                        <td style="width:35% !important;">
                                            <input type="text" value="<?php echo $general->humanDateFormat($covid19Info['travel_return_date']); ?>" class="form-control date" id="returnDate" name="returnDate" placeholder="e.g 09-Jan-1992" title="Date de retour" />
                                        </td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">SIGNES ET SYMPTÔMES CLINIQUES</h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width:15% !important">Date d'apparition des symptômes <span class="mandatory">*</span> </th>
                                        <td style="width:35% !important;">
                                            <input class="form-control date isRequired" type="text" name="dateOfSymptomOnset" id="dateOfSymptomOnset" placeholder="Date d'apparition des symptômes" value="<?php echo $general->humanDateFormat($covid19Info['date_of_symptom_onset']); ?> " />
                                        </td>
                                        <th style="width:15% !important">Date de la consultation initiale</th>
                                        <td style="width:35% !important;">
                                            <input class="form-control date" type="text" name="dateOfInitialConsultation" id="dateOfInitialConsultation" placeholder="Date of Initial Consultation" value="<?php echo $general->humanDateFormat($covid19Info['date_of_initial_consultation']); ?> " />
                                        </td>
                                    </tr>
                                    <tr>

                                        <th style="width:15% !important"><label for="recentHospitalization"></label>Avez-vous été hospitalisé durant les 12 derniers mois ? Have you been hospitalized in the past 12 months ? </th>
                                        <td style="width:35% !important;">
                                            <select name="recentHospitalization" id="recentHospitalization" class="form-control" title="Avez-vous été hospitalisé durant les 12 derniers mois ? Have you been hospitalized in the past 12 months ? ">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['recent_hospitalization'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['recent_hospitalization'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['recent_hospitalization'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width:15% !important"><label for="patientLivesWithChildren"></label>Habitez-vous avec les enfants ?</th>
                                        <td style="width:35% !important;">
                                            <select name="patientLivesWithChildren" id="patientLivesWithChildren" class="form-control" title="Habitez-vous avec les enfants ?">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['patient_lives_with_children'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['patient_lives_with_children'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['patient_lives_with_children'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th style="width:15% !important"><label for="patientCaresForChildren"></label>prenez-vous soins des enfants ?</th>
                                        <td style="width:35% !important;">
                                            <select name="patientCaresForChildren" id="patientCaresForChildren" class="form-control" title="prenez-vous soins des enfants ?">
                                                <option value="">--Select--</option>
                                                <option value="yes" <?php echo ($covid19Info['patient_cares_for_children'] == 'yes') ? "selected='selected'" : ""; ?>>Oui</option>
                                                <option value="no" <?php echo ($covid19Info['patient_cares_for_children'] == 'no') ? "selected='selected'" : ""; ?>>Non</option>
                                                <option value="unknown" <?php echo ($covid19Info['patient_cares_for_children'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width:15% !important">Fever/Temperature (&deg;C) <span class="mandatory">*</span> </th>
                                        <td style="width:35% !important;">
                                            <input class="form-control isRequired" type="number" value="<?php echo $covid19Info['fever_temp']; ?>" name="feverTemp" id="feverTemp" placeholder="Fever/Temperature (in &deg;Celcius)" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="temperatureMeasurementMethod">Température</label></th>
                                        <td style="width:35% !important;">
                                            <select name="temperatureMeasurementMethod" id="temperatureMeasurementMethod" class="form-control" title="Température">
                                                <option value="">--Select--</option>
                                                <option value="auxillary" <?php echo ($covid19Info['temperature_measurement_method'] == 'auxillary') ? "selected='selected'" : ""; ?>>Axillaire</option>
                                                <option value="oral" <?php echo ($covid19Info['temperature_measurement_method'] == 'oral') ? "selected='selected'" : ""; ?>>Orale</option>
                                                <option value="rectal" <?php echo ($covid19Info['temperature_measurement_method'] == 'rectal') ? "selected='selected'" : ""; ?>>Rectale</option>
                                                <option value="unknown" <?php echo ($covid19Info['temperature_measurement_method'] == 'unknown') ? "selected='selected'" : ""; ?>>Inconnu</option>
                                            </select>
                                        </td>
                                        <th style="width:15% !important"><label for="respiratoryRate"> Fréquence Respiratoire</label></th>
                                        <td style="width:35% !important;">
                                            <input class="form-control" type="number" value="<?php echo $covid19Info['respiratory_rate']; ?>" name="respiratoryRate" id="respiratoryRate" placeholder="Fréquence Respiratoire" title="Fréquence Respiratoire" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width:15% !important"><label for="oxygenSaturation"> Saturation en oxygène</label></th>
                                        <td style="width:35% !important;">
                                            <input class="form-control" type="number" value="<?php echo $covid19Info['oxygen_saturation']; ?>" name="oxygenSaturation" id="oxygenSaturation" placeholder="Saturation en oxygène" title="Saturation en oxygène" />
                                        </td>
                                        <th></th>
                                        <td></td>
                                    </tr>
                                </table>

                                <div class="box-header with-border sectionHeader">
                                    <h3 class="box-title">FACTEURS DE RISQUE ÉPIDÉMIOLOGIQUE ET EXPOSITIONS</h3>
                                </div>
                                <table class="table">
                                    <tr>
                                        <th style="width:15% !important">Contacts étroits du patient <span class="mandatory">*</span></th>
                                        <td colspan="3">
                                            <textarea name="closeContacts" class="form-control" placeholder="Contacts étroits du patient" title="Contacts étroits du patient" style="width:100%;min-height:100px;"><?php echo $covid19Info['close_contacts']; ?></textarea>
                                            <span class="text-danger">
                                                Ajoutez les noms et numéros de téléphone des contacts proches (foyer, membres de la famille, amis avec lesquels vous avez été en contact au cours des 14 derniers jours)
                                            </span>
                                        </td>

                                    </tr>
                                    <tr>
                                        <th>Occupation du patient</th>
                                        <td>
                                            <input class="form-control" value="<?php echo $covid19Info['patient_occupation']; ?>" type="text" name="patientOccupation" id="patientOccupation" placeholder="Occupation du patient" title="Occupation du patient" />
                                        </td>
                                        <th></th>
                                        <td></td>

                                    </tr>

                                </table>
                            </div>
                        </div>
                        <?php if ($sarr['user_type'] != 'remoteuser') { ?>
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Réservé à une utilisation en laboratoire </h3>
                                    </div>
                                    <table class="table" style="width:100%">
                                        <tr>
                                            <th><label for="">Date de réception de l'échantillon </label></th>
                                            <td>
                                                <input type="text" class="form-control" id="sampleReceivedDate" name="sampleReceivedDate" placeholder="e.g 09-Jan-1992 05:30" title="Date de réception de l'échantillon" value="<?php echo $general->humanDateFormat($covid19Info['sample_received_at_vl_lab_datetime']) ?>" onchange="" style="width:100%;" />
                                            </td>
                                            <th><label for="sampleCondition">Condition de l'échantillon</label></th>
                                            <td>
                                                <select class="form-control" name="sampleCondition" id="sampleCondition" title="Condition de l'échantillon">
                                                    <option value=''> -- Sélectionner -- </option>
                                                    <option value="adequate" <?php echo ($covid19Info['sample_condition'] == 'adequate') ? "selected='selected'" : ""; ?>> Adéquat </option>
                                                    <option value="not-adequate" <?php echo ($covid19Info['sample_condition'] == 'not-adequate') ? "selected='selected'" : ""; ?>> Non Adéquat </option>
                                                    <option value="autres" <?php echo ($covid19Info['sample_condition'] == 'autres') ? "selected='selected'" : ""; ?>> Autres </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="lab-show"><label for="labId">Nom du laboratoire</label> </td>
                                            <td class="lab-show">
                                                <select name="labId" id="labId" class="form-control" title="Nom du laboratoire" style="width:100%;">
                                                    <?= $general->generateSelectOptions($testingLabs, $covid19Info['lab_id'], '-- Sélectionner --'); ?>
                                                </select>
                                            </td>
                                            <th>L'échantillon est-il rejeté?</th>
                                            <td>
                                                <select class="form-control result-focus" name="isSampleRejected" id="isSampleRejected" title="L'échantillon est-il rejeté?">
                                                    <option value=''> -- Sélectionner -- </option>
                                                    <option value="yes" <?php echo ($covid19Info['is_sample_rejected'] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                    <option value="no" <?php echo ($covid19Info['is_sample_rejected'] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="show-rejection" style="display:none;">Raison du rejet</th>
                                            <td class="show-rejection" style="display:none;">
                                                <select class="form-control" name="sampleRejectionReason" id="sampleRejectionReason" title="Raison du rejet">
                                                    <option value="">-- Sélectionner --</option>
                                                    <?php foreach ($rejectionTypeResult as $type) { ?>
                                                        <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                                            <?php
                                                            foreach ($rejectionResult as $reject) {
                                                                if ($type['rejection_type'] == $reject['rejection_type']) { ?>
                                                                    <option value="<?php echo $reject['rejection_reason_id']; ?>" <?php echo ($covid19Info['reason_for_sample_rejection'] == $reject['rejection_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo ucwords($reject['rejection_reason_name']); ?></option>
                                                            <?php }
                                                            } ?>
                                                        </optgroup>
                                                    <?php }  ?>
                                                </select>
                                            </td>
                                            <th class="show-rejection" style="display: none;">Date de rejet<span class="mandatory">*</span></th>
                                            <td class="show-rejection" style="display: none;"><input value="<?php echo $general->humanDateFormat($covid19Info['rejection_on']); ?>" class="form-control date rejection-date" type="text" name="rejectionDate" id="rejectionDate" placeholder="Date de rejet" title="Date de rejet" /></td>
                                        </tr>
                                        <tr>
                                            <!-- <th style="width: 15% !important;"><label for="resultPcr">Date de Result PCR</label></th>
                                            <td style="width:35% !important;">
                                                <input type="text" class="form-control date" id="resultPcr" name="resultPcr" placeholder="e.g 09-Jan-1992" title="Date de Result PCR"/>
                                            </td> -->
                                            <th style="width: 15% !important;"><label for="numberOfDaysSick">Depuis combien de jours êtes-vous malade?</label></th>
                                            <td style="width:35% !important;">
                                                <input type="text" value="<?php echo $covid19Info['number_of_days_sick']; ?>" class="form-control" id="numberOfDaysSick" name="numberOfDaysSick" placeholder="Depuis combien de jours êtes-vous malade?" title="Date de Result PCR" />
                                            </td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4">
                                                <table class="table table-bordered table-striped" id="testNameTable">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Test non</th>
                                                            <th class="text-center">Nom du Testkit (ou) Méthode de test utilisée</th>
                                                            <th class="text-center">Date de l'analyse</th>
                                                            <th class="text-center">Résultat du test</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="testKitNameTable">
                                                        <?php if (isset($covid19TestInfo) && count($covid19TestInfo) > 0) {
                                                            foreach ($covid19TestInfo as $indexKey => $rows) { ?>
                                                                <tr>
                                                                    <td class="text-center"><?php echo ($indexKey + 1); ?><input type="hidden" name="testId[]" value="<?php echo base64_encode($covid19TestInfo[$indexKey]['test_id']); ?>"></td>
                                                                    <td>
                                                                        <select onchange="changeDRCTestName(this.value,<?php echo ($indexKey + 1); ?>)" class="form-control test-name-table-input" id="testName<?php echo ($indexKey + 1); ?>" name="testName[]" title="Veuillez saisir le nom du test pour les lignes <?php echo ($indexKey + 1); ?>">
                                                                            <option value="">--Select--</option>
                                                                            <option value="PCR/RT-PCR" <?php echo (isset($covid19TestInfo[$indexKey]['test_name']) && $covid19TestInfo[$indexKey]['test_name'] == 'PCR/RT-PCR') ? "selected='selected'" : ""; ?>>PCR/RT-PCR</option>
                                                                            <option value="RdRp-SARS Cov-2" <?php echo (isset($covid19TestInfo[$indexKey]['test_name']) && $covid19TestInfo[$indexKey]['test_name'] == 'RdRp-SARS Cov-2') ? "selected='selected'" : ""; ?>>RdRp-SARS Cov-2</option>
                                                                            <option value="other" <?php echo (isset($covid19TestInfo[$indexKey]['test_name']) && $covid19TestInfo[$indexKey]['test_name'] == 'other') ? "selected='selected'" : ""; ?>>Others</option>
                                                                        </select>
                                                                        <?php
                                                                        $value = '';
                                                                        if ($covid19TestInfo[$indexKey]['test_name'] != 'PCR/RT-PCR' && $covid19TestInfo[$indexKey]['test_name'] != 'RdRp-SARS Cov-2' && $covid19TestInfo[$indexKey]['test_name'] != 'other') {
                                                                            $value = 'value="' . $covid19TestInfo[$indexKey]['test_name'] . '"';
                                                                            $show =  "block";
                                                                        } else {
                                                                            $show =  "none";
                                                                        } ?>
                                                                        <input <?php echo $value; ?> type="text" name="testNameOther[]" id="testNameOther<?php echo ($indexKey + 1); ?>" class="form-control testInputOther<?php echo ($indexKey + 1); ?>" title="Veuillez saisir le nom du test pour les lignes <?php echo ($indexKey + 1); ?>" placeholder="Entrez le nom du test <?php echo ($indexKey + 1); ?>" style="display: <?php echo $show; ?>;margin-top: 10px;" />
                                                                    </td>
                                                                    <!-- <td><input type="text" value="<?php echo $covid19TestInfo[$indexKey]['test_name']; ?>" name="testName[]" id="testName<?php echo ($indexKey + 1); ?>" class="form-control test-name-table-input" placeholder="Nom du test" title="Veuillez saisir le nom du test pour la ligne<?php echo ($indexKey + 1); ?>" /></td> -->
                                                                    <td><input type="text" value="<?php echo $general->humanDateFormat($covid19TestInfo[$indexKey]['sample_tested_datetime']); ?>" name="testDate[]" id="testDate<?php echo ($indexKey + 1); ?>" class="form-control test-name-table-input dateTime" placeholder="Testé sur" title="Veuillez sélectionner la Date de l analyse pour la ligne <?php echo ($indexKey + 1); ?>" /></td>
                                                                    <td><select class="form-control test-result test-name-table-input result-focus" name="testResult[]" id="testResult<?php echo ($indexKey + 1); ?>" title="Veuillez sélectionner le résultat pour la ligne<?php echo ($indexKey + 1); ?>">
                                                                            <option value=''> -- Sélectionner -- </option>
                                                                            <?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?>
                                                                                <option value="<?php echo $c19ResultKey; ?>" <?php echo ($covid19TestInfo[$indexKey]['result'] == $c19ResultKey) ? "selected='selected'" : ""; ?>> <?php echo $c19ResultValue; ?> </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </td>
                                                                    <td style="vertical-align:middle;text-align: center;">
                                                                        <a class="btn btn-xs btn-primary test-name-table" href="javascript:void(0);" onclick="insRow();"><i class="fa fa-plus"></i></a>&nbsp;
                                                                        <a class="btn btn-xs btn-default test-name-table" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);deleteRow('<?php echo base64_encode($covid19TestInfo[$indexKey]['test_id']); ?>');"><i class="fa fa-minus"></i></a>
                                                                    </td>
                                                                </tr>
                                                            <?php }
                                                        } else { ?>
                                                            <tr>
                                                                <td class="text-center">1</td>
                                                                <td>
                                                                    <select onchange="changeDRCTestName(this.value,1)" class="form-control test-name-table-input" id="testName1" name="testName[]" title="Veuillez saisir le nom du test pour les lignes 1">
                                                                        <option value="">--Select--</option>
                                                                        <option value="PCR/RT-PCR">PCR/RT-PCR</option>
                                                                        <option value="RdRp-SARS Cov-2">RdRp-SARS Cov-2</option>
                                                                        <option value="other">Others</option>
                                                                    </select>
                                                                    <input type="text" name="testNameOther[]" id="testNameOther1" class="form-control testInputOther1 test-name-table-input" title="Veuillez saisir le nom du test pour les lignes 1" placeholder="Entrez le nom du test 1" style="display: none;margin-top: 10px;" />
                                                                </td>
                                                                <!-- <td><input type="text" name="testName[]" id="testName1" class="form-control test-name-table-input" placeholder="Nom du test" title="Veuillez saisir le nom du test pour les lignes 1" /></td> -->
                                                                <td><input type="text" name="testDate[]" id="testDate1" class="form-control test-name-table-input dateTime" placeholder="Testé sur" title="Veuillez saisir le test pour la ligne 1" /></td>
                                                                <td><select class="form-control test-result test-name-table-input" name="testResult[]" id="testResult1" title="Veuillez sélectionner le résultat pour la ligne 1">
                                                                        <option value=''> -- Sélectionner -- </option>
                                                                        <?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?>
                                                                            <option value="<?php echo $c19ResultKey; ?>"> <?php echo $c19ResultValue; ?> </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </td>
                                                                <td style="vertical-align:middle;text-align: center;">
                                                                    <a class="btn btn-xs btn-primary test-name-table" href="javascript:void(0);" onclick="insRow();"><i class="fa fa-plus"></i></a>&nbsp;
                                                                    <a class="btn btn-xs btn-default test-name-table" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><i class="fa fa-minus"></i></a>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="3" class="text-right">Résultat final</th>
                                                            <td>
                                                                <select class="form-control result-focus" name="result" id="result" title="Résultat final">
                                                                    <option value=''> -- Sélectionner -- </option>
                                                                    <?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?>
                                                                        <option value="<?php echo $c19ResultKey; ?>" <?php echo ($covid19Info['result'] == $c19ResultKey) ? "selected='selected'" : ""; ?>> <?php echo $c19ResultValue; ?> </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </td>
                                        </tr>
                                        <?php $otherDiseases = (isset($covid19Info['result']) && $covid19Info['result'] != 'positive') ? 'display' : 'none'; ?>
                                        <tr>
                                            <th class="other-diseases" style="display: <?php echo $otherDiseases; ?>;"><label for="otherDiseases">Autres maladies<span class="mandatory">*</span></label></th>
                                            <td class="other-diseases" style="display: <?php echo $otherDiseases; ?>;">
                                                <select name="otherDiseases" id="otherDiseases" class="form-control" title="Autres maladies">
                                                    <option value="">--Select--</option>
                                                    <optgroup label="Coronavirus">
                                                        <option value="E-Sars-CoV" <?php echo ($covid19Info['other_diseases'] == 'E-Sars-CoV') ? "selected='selected'" : ""; ?>>E-Sars-CoV</option>
                                                        <option value="N-Sars-Cov" <?php echo ($covid19Info['other_diseases'] == 'N-Sars-Cov') ? "selected='selected'" : ""; ?>>N-Sars-Cov</option>
                                                        <option value="Other respiratory pathogens" <?php echo ($covid19Info['other_diseases'] == 'Other respiratory pathogens') ? "selected='selected'" : ""; ?>>Autres Pathogens Respiratories</option>
                                                        <option value="Other Coronavirus" <?php echo ($covid19Info['other_diseases'] == 'Other Coronavirus') ? "selected='selected'" : ""; ?>>Autres Coronavirus</option>
                                                    </optgroup>
                                                    <optgroup label="Influenza">
                                                        <option value="A/H1N1pdm09" <?php echo ($covid19Info['other_diseases'] == 'A/H1N1pdm09') ? "selected='selected'" : ""; ?>>A/H1N1pdm09</option>
                                                        <option value="A/H3N2" <?php echo ($covid19Info['other_diseases'] == 'A/H3N2') ? "selected='selected'" : ""; ?>>A/H3N2</option>
                                                        <option value="A/H5N1" <?php echo ($covid19Info['other_diseases'] == 'A/H5N1') ? "selected='selected'" : ""; ?>>A/H5N1</option>
                                                        <option value="B/Yan" <?php echo ($covid19Info['other_diseases'] == 'B/Yan') ? "selected='selected'" : ""; ?>>B/Yan</option>
                                                        <option value="B/Vic" <?php echo ($covid19Info['other_diseases'] == 'B/Vic') ? "selected='selected'" : ""; ?>>B/Vic</option>
                                                    </optgroup>
                                                </select>
                                            </td>
                                            <th class="change-reason" style="display: none;">Raison du changement<span class="mandatory">*</span></td>
                                            <td class="change-reason" style="display: none;"><textarea type="text" name="reasonForChanging" id="reasonForChanging" class="form-control date" placeholder="Raison du changement" title="Raison du changement"></textarea></td>
                                        </tr>
                                        <tr>
                                            <th>Le résultat est-il autorisé?</th>
                                            <td>
                                                <select name="isResultAuthorized" id="isResultAuthorized" class="disabled-field form-control" title="Le résultat est-il autorisé?" style="width:100%">
                                                    <option value="">-- Sélectionner --</option>
                                                    <option value='yes' <?php echo ($covid19Info['is_result_authorised'] == 'yes') ? "selected='selected'" : ""; ?>> Oui </option>
                                                    <option value='no' <?php echo ($covid19Info['is_result_authorised'] == 'no') ? "selected='selected'" : ""; ?>> Non </option>
                                                </select>
                                            </td>
                                            <th>Autorisé par</th>
                                            <td><input type="text" value="<?php echo $covid19Info['authorized_by']; ?>" name="authorizedBy" id="authorizedBy" class="disabled-field form-control" placeholder="Autorisé par" title="Autorisé par" /></td>
                                        </tr>
                                        <tr>
                                            <th>Autorisé le</td>
                                            <td><input type="text" value="<?php echo $general->humanDateFormat($covid19Info['authorized_on']); ?>" name="authorizedOn" id="authorizedOn" class="disabled-field form-control date" placeholder="Autorisé le" title="Autorisé le" /></td>
                                            <th></th>
                                            <td></td>
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
                        <input type="hidden" name="covid19SampleId" id="covid19SampleId" value="<?php echo $covid19Info['covid19_id']; ?>" />
                        <input type="hidden" name="sampleCodeCol" id="sampleCodeCol" value="<?php echo $arr['sample_code']; ?>" />
                        <input type="hidden" name="oldStatus" id="oldStatus" value="<?php echo $covid19Info['result_status']; ?>" />
                        <input type="hidden" name="provinceCode" id="provinceCode" />
                        <input type="hidden" name="provinceId" id="provinceId" />
                        <a href="/covid-19/requests/covid-19-requests.php" class="btn btn-default"> Cancel</a>
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
    tableRowId = <?php echo (isset($covid19TestInfo) && count($covid19TestInfo) > 0) ? (count($covid19TestInfo) + 1) : 2; ?>;
    deletedRow = [];

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
                    testType: 'covid19'
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
            sampleCodeGeneration();
        } else if (pName == '') {
            provinceName = true;
            facilityName = true;
            $("#province").html("<?php echo $province; ?>");
            $("#facilityId").html("<?php echo $facility; ?>");
            $("#facilityId").select2("val", "");
            $("#district").html("<option value=''> -- Sélectionner -- </option>");
        }
        $.unblockUI();
    }

    function sampleCodeGeneration() {
        var pName = $("#province").val();
        var sDate = $("#sampleCollectionDate").val();
        if (pName != '' && sDate != '') {
            $.post("/covid-19/requests/generateSampleCode.php", {
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
                    testType: 'covid19'
                },
                function(data) {
                    if (data != "") {
                        details = data.split("###");
                        $("#facilityId").html(details[0]);
                    }
                });
        } else {
            $("#facilityId").html("<option value=''> -- Sélectionner -- </option>");
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
                    testType: 'covid19'
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
        if ($('#medicalHistory').val() == "yes") {
            if ($('.comorbidity-select :selected').length > 0) {
                alert("Veuillez sélectionner au moins une option sous Antécédents médicaux");
                return false;
            }
        }
        if ($('input[name="symptom[]"]:checked').length > 0) {
            alert("Veuillez sélectionner au moins une option sous Symptômes");
            return false;
        }
        $("#provinceCode").val($("#province").find(":selected").attr("data-code"));
        $("#provinceId").val($("#province").find(":selected").attr("data-province-id"));
        flag = deforayValidator.init({
            formId: 'editCovid19RequestForm'
        });
        if (flag) {
            document.getElementById('editCovid19RequestForm').submit();
        }
    }

    function updateMotherViralLoad() {
        var motherVl = $("#motherViralLoadCopiesPerMl").val();
        var motherVlText = $("#motherViralLoadText").val();
        if (motherVlText != '') {
            $("#motherViralLoadCopiesPerMl").val('');
        }
    }



    $(document).ready(function() {
        $('.result-focus').change(function(e) {
            $('.change-reason').show(500);
            $('#reasonForChanging').addClass('isRequired');
        });

        $('#facilityId').select2({
            placeholder: "Select Clinic/Health Center"
        });
        $('#district').select2({
            placeholder: "District"
        });
        $('#province').select2({
            placeholder: "Province"
        });
        getfacilityProvinceDetails($("#facilityId").val());
        <?php if (isset($covid19Info['mother_treatment']) && in_array('Other', $covid19Info['mother_treatment'])) { ?>
            $('#motherTreatmentOther').prop('disabled', false);
        <?php } ?>

        <?php if (isset($covid19Info['mother_vl_result']) && !empty($covid19Info['mother_vl_result'])) { ?>
            updateMotherViralLoad();
        <?php } ?>

        $('#medicalHistory').change(function(e) {
            if ($(this).val() == "yes") {
                $('.comorbidities-row').show();
            } else {
                $('.comorbidities-row').hide();
            }
        });

        $("#motherViralLoadCopiesPerMl").on("change keyup paste", function() {
            var motherVl = $("#motherViralLoadCopiesPerMl").val();
            //var motherVlText = $("#motherViralLoadText").val();
            if (motherVl != '') {
                $("#motherViralLoadText").val('');
            }
        });
        $('#isResultAuthorized').change(function(e) {
            checkIsResultAuthorized();
        });
        $('#result').change(function(e) {

            if (this.value == 'positive') {
                $('.other-diseases').hide();
                $('#otherDiseases').removeClass('isRequired');
            } else {
                $('.other-diseases').show();
                $('#otherDiseases').addClass('isRequired');
            }
        });

        <?php if (isset($arr['covid19_positive_confirmatory_tests_required_by_central_lab']) && $arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes') { ?>
            $('.test-result,#result').change(function(e) {
                checkPostive();
            });
            checkPostive();
        <?php } ?>

        checkIsResultAuthorized();

        <?php $index = 0;
        if (isset($covid19Symptoms) && count($covid19Symptoms) > 0) {
            foreach ($covid19Symptoms as $symptomId => $symptomName) {
                if ($covid19SelectedSymptoms[$symptomId] == "yes") { ?>
                    checkSubSymptoms($('#symptom<?php echo $symptomId; ?>').val(), <?php echo $symptomId; ?>, <?php echo $index; ?>);
        <?php }
                $index++;
            }
        } ?>

    });

    function checkSubSymptoms(obj, parent, row, sub = "") {
        if ($(obj).val() == 'yes') {
            $.post("getSymptomsByParentId.php", {
                    symptomParent: parent,
                    covid19Id: <?php echo $covid19Info['covid19_id']; ?>
                },
                function(data) {
                    if (data != "") {
                        if ($('.hide-symptoms').hasClass('symptomRow' + parent)) {
                            $('.symptomRow' + parent).remove();
                        }
                        $(".row" + row).after(data);
                    }
                });
        } else {

            $('.symptomRow' + parent).remove();
        }
    }

    function insRow() {
        rl = document.getElementById("testKitNameTable").rows.length;
        var a = document.getElementById("testKitNameTable").insertRow(rl);
        a.setAttribute("style", "display:none");
        var b = a.insertCell(0);
        var c = a.insertCell(1);
        var d = a.insertCell(2);
        var e = a.insertCell(3);
        var f = a.insertCell(4);
        f.setAttribute("align", "center");
        b.setAttribute("align", "center");
        f.setAttribute("style", "vertical-align:middle");

        b.innerHTML = tableRowId;
        c.innerHTML = '<select onchange="changeDRCTestName(this.value,' + tableRowId + ')" class="form-control test-name-table-input" id="testName' + tableRowId + '" name="testName[]" title="Veuillez saisir le nom du test pour les lignes ' + tableRowId + '"> <option value="">--Select--</option> <option value="PCR/RT-PCR">PCR/RT-PCR</option> <option value="RdRp-SARS Cov-2">RdRp-SARS Cov-2</option> <option value="other">Others</option> </select> <input type="text" name="testNameOther[]" id="testNameOther' + tableRowId + '" class="form-control testInputOther' + tableRowId + ' test-name-table-input" placeholder="Entrez le nom du test ' + tableRowId + '" title="Veuillez saisir le nom du test pour les lignes ' + tableRowId + '" style="display: none;margin-top: 10px;"/>';
        d.innerHTML = '<input type="text" name="testDate[]" id="testDate' + tableRowId + '" class="form-control test-name-table-input dateTime" placeholder="Tested on"  title="Please enter the tested on for row ' + tableRowId + '"/>';
        e.innerHTML = '<select class="form-control test-result test-name-table-input" name="testResult[]" id="testResult' + tableRowId + '" title="Please select the result for row ' + tableRowId + '"><option value=""> -- Sélectionner -- </option><?php foreach ($covid19Results as $c19ResultKey => $c19ResultValue) { ?> <option value="<?php echo $c19ResultKey; ?>"> <?php echo $c19ResultValue; ?> </option> <?php } ?> </select>';
        f.innerHTML = '<a class="btn btn-xs btn-primary test-name-table" href="javascript:void(0);" onclick="insRow();"><i class="fa fa-plus"></i></a>&nbsp;<a class="btn btn-xs btn-default test-name-table" href="javascript:void(0);" onclick="removeAttributeRow(this.parentNode.parentNode);"><i class="fa fa-minus"></i></a>';
        $(a).fadeIn(800);
        $('.dateTime').datetimepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd-M-yy',
            timeFormat: "HH:mm",
            maxDate: "Today",
            onChangeMonthYear: function(year, month, widget) {
                setTimeout(function() {
                    $('.ui-datepicker-calendar').show();
                });
            },
            yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
        }).click(function() {
            $('.ui-datepicker-calendar').show();
        });
        tableRowId++;

        <?php if (isset($arr['covid19_positive_confirmatory_tests_required_by_central_lab']) && $arr['covid19_positive_confirmatory_tests_required_by_central_lab'] == 'yes') { ?>
            $('.test-result,#result').change(function(e) {
                checkPostive();
            });
        <?php } ?>
        showcomorbidities();
    }

    function removeAttributeRow(el) {
        $(el).fadeOut("slow", function() {
            el.parentNode.removeChild(el);
            rl = document.getElementById("testKitNameTable").rows.length;
            if (rl == 0) {
                insRow();
            }
        });
    }

    function deleteRow(id) {
        deletedRow.push(id);
        $('#deletedRow').val(deletedRow);
    }

    function checkPostive() {
        var itemLength = document.getElementsByName("testResult[]");
        for (i = 0; i < itemLength.length; i++) {

            if (itemLength[i].value == 'positive') {
                $('.disabled-field').val('');
                $('.disabled-field').prop('disabled', true);
                $('.disabled-field').addClass('disabled');
                $('.disabled-field').removeClass('isRequired');
                return false;
            } else {
                $('.disabled-field').prop('disabled', false);
                $('.disabled-field').removeClass('disabled');
                $('.disabled-field').addClass('isRequired');
            }
            if (itemLength[i].value != '') {
                $('#labId').addClass('isRequired');
            }
        }
    }

    function checkIsResultAuthorized() {
        if ($('#isResultAuthorized').val() == 'yes') {
            $('#authorizedBy,#authorizedOn').prop('disabled', false);
            $('#authorizedBy,#authorizedOn').removeClass('disabled');
            $('#authorizedBy,#authorizedOn').addClass('isRequired');
        } else {
            $('#authorizedBy,#authorizedOn').val('');
            $('#authorizedBy,#authorizedOn').prop('disabled', true);
            $('#authorizedBy,#authorizedOn').addClass('disabled');
            $('#authorizedBy,#authorizedOn').removeClass('isRequired');
        }
    }

    function changeDRCTestName(val, id) {
        if (val == 'other') {
            $('.testInputOther' + id).show();
        } else {
            $('.testInputOther' + id).hide();
        }
    }

    function checkSubReason(obj, show) {
        $('.checkbox').prop("checked", false);
        if ($(obj).prop("checked", true)) {
            $('.' + show).show();
            $('.' + show).removeClass('hide-reasons');
            $('.hide-reasons').hide();
            $('.' + show).addClass('hide-reasons');
        }
    }
</script>