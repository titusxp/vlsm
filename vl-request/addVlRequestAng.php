  <?php
    ob_start();
    $rKey = '';
    if(USERTYPE=='remoteuser'){
      $sampleCodeKey = 'remote_sample_code_key';
      $sampleCode = 'remote_sample_code';
      $pdQuery="SELECT * from province_details as pd JOIN facility_details as fd ON fd.facility_state=pd.province_name JOIN vl_user_facility_map as vlfm ON vlfm.facility_id=fd.facility_id where user_id='".$_SESSION['userId']."'";
      $rKey = 'R';
    }else{
      $sampleCodeKey = 'sample_code_key';
      $sampleCode = 'sample_code';
      $pdQuery="SELECT * from province_details";
    }
    $artRegimenQuery="SELECT DISTINCT headings FROM r_art_code_details WHERE nation_identifier ='ang'";
    $artRegimenResult = $db->rawQuery($artRegimenQuery);
    $province = "";
    $province.="<option value=''> -- Selecione -- </option>";
    foreach($pdResult as $provinceName){
      $province .= "<option value='".$provinceName['province_name']."##".$provinceName['province_code']."'>".ucwords($provinceName['province_name'])."</option>";
    }
    $facility = "";
    $facility.="<option value=''> -- Selecione -- </option>";
    foreach($fResult as $fDetails){
      $facility .= "<option value='".$fDetails['facility_id']."'>".ucwords($fDetails['facility_name'])."</option>";
    }
    
    //get ART list
    $aQuery="SELECT * from r_art_code_details";// where nation_identifier='drc'";
    $aResult=$db->query($aQuery);
    
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
  if($arr['sample_code']=='MMYY'){
    $mnthYr = date('my');
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-31');
  }else if($arr['sample_code']=='YY'){
    $mnthYr = date('y');
    $start_date = date('Y-01-01');
    $end_date = date('Y-12-31');
  }
  
  //$svlQuery='select MAX(sample_code_key) FROM vl_request_form as vl where vl.vlsm_country_id="3" AND DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'"';
  $svlQuery='SELECT '.$sampleCodeKey.' FROM vl_request_form as vl WHERE DATE(vl.request_created_datetime) >= "'.$start_date.'" AND DATE(vl.request_created_datetime) <= "'.$end_date.'" AND '.$sampleCode.'!="" ORDER BY vl_sample_id DESC LIMIT 1';
  $svlResult=$db->query($svlQuery);
  $prefix = $arr['sample_code_prefix'];
  if($svlResult[0][$sampleCodeKey]!='' && $svlResult[0][$sampleCodeKey]!=NULL){
   $maxId = $svlResult[0][$sampleCodeKey]+1;
   $strparam = strlen($maxId);
   $zeros = substr("000", $strparam);
   $maxId = $zeros.$maxId;
  }else{
   $maxId = '001';
  }
  $sKey = '';
  $sFormat = '';
  ?>
  <style>
   .translate-content{ color:#0000FF; font-size:12.5px; }
  </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><i class="fa fa-edit"></i> VIRAL LOAD LABORATORY REQUEST FORM</h1>
      <ol class="breadcrumb">
        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Add Vl Request</li>
      </ol> 
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
          <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> indicates required field &nbsp;</div>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <!-- form start -->
            <form class="form-inline" method="post" name="addVlRequestForm" id="addVlRequestForm" autocomplete="off" action="addVlRequestHelperAng.php">
              <div class="box-body">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="box-header with-border">
                          <h3 class="box-title">SOLICITAÇÃO DE QUANTIFICAÇÃO DE CARGA VIRAL DE VIH</h3>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">A.UNIDADE DE</h3>
                            </div>
                            <table class="table" style="width:100%">
                                <tr>
                                    <td><label for="province">Província </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="province" id="province" title="Please choose província" onchange="getfacilityDetails(this);" style="width:100%;">
                                            <?php echo $province; ?>
                                        </select>
                                    </td>
                                    <td><label for="district">Município </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="district" id="district" title="Please choose município" style="width:100%;" onchange="getfacilityDistrictwise(this);">
                                          <option value=""> -- Selecione -- </option>
                                        </select>
                                    </td>
                                    <td><label for="clinicName">Nome da Unidade </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control isRequired" name="clinicName" id="clinicName" title="Please choose Nome da Unidade" style="width:100%;" onchange="getfacilityProvinceDetails(this)">
                                          <?php echo $facility;  ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="sector">Serviço/Sector </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control" name="sector" id="sector" placeholder="Serviço/Sector" title="Please enter Serviço/Sector"/>
                                    </td>
                                    <td><label for="reqClinician">Nome do solicitante </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control" name="reqClinician" id="reqClinician" placeholder="Nome do solicitante" title="Please enter Nome do solicitante"/>
                                    </td>
                                    <td><label for="category">Categoria </label><span class="mandatory">*</span></td>
                                    <td>
                                        <select class="form-control" name="category" id="category" title="Please choose Categoria" style="width:100%;">
                                          <option value="">-- Selecione --</option>
                                          <option value="nurse">Enfermeiro/a</option>
                                          <option value="clinician">Médico/a</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="profNumber">Nº da Ordem </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control" name="profNumber" id="profNumber" placeholder="Nº da Ordem" title="Please enter Nº da Ordem"/>
                                    </td>
                                    <td><label for="contactNo">Contacto </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control" name="contactNo" id="contactNo" placeholder="Contacto" title="Please enter Contacto"/>
                                    </td>
                                    <td><label for="requestingDate">Data da solicitação </label><span class="mandatory">*</span></td>
                                    <td>
                                        <input type="text" class="form-control date" name="requestingDate" id="requestingDate" placeholder="Data da solicitação" title="Please choose Data da solicitação"/>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="box box-primary">
                            <div class="box-header with-border">
                               <!-- <h3 class="box-title">Information sur le patient </h3>&nbsp;&nbsp;&nbsp;
                            <input style="width:30%;" type="text" name="artPatientNo" id="artPatientNo" class="" placeholder="Code du patient" title="Please enter code du patient"/>&nbsp;&nbsp;
                            <a style="margin-top:-0.35%;" href="javascript:void(0);" class="btn btn-default btn-sm" onclick="showPatientList();"><i class="fa fa-search">&nbsp;</i>Search</a><span id="showEmptyResult" style="display:none;color: #ff0000;font-size: 15px;"><b>&nbsp;No Patient Found</b></span>-->
                            <h4>B. DADOS DO PACIENTE</h4>
                            </div>
                            <table class="table" style="width:100%">
                                <tr>
                                    <td style="width:14%;"><label for="patientFirstName">Nome completo </label></td>
                                    <td style="width:14%;">
                                        <input type="text" class="form-control " id="patientFirstName" name="patientFirstName" placeholder="Nome completo" title="Please enter Nome completo" style="width:100%;"/>
                                    </td>
                                    <td style="width:14%;"><label for="patientArtNo">Nº Processo Clínico </label></td>
                                    <td style="width:14%;">
                                        <input type="text" class="form-control " id="patientArtNo" name="patientArtNo" placeholder="Nº Processo Clínico" title="Please enter Nº Processo Clínico" style="width:100%;" onchange="checkNameValidation('vl_request_form','patient_art_no',this,null)"/>
                                    </td>
                                    <td><label for="sex">Género </label></td>
                                    <td style="width:16%;">
                                        <label class="radio-inline" style="padding-left:10px !important;margin-left:0;">Masculino</label>
                                        <label class="radio-inline" style="width:2%;padding-bottom:22px;margin-left:0;">
                                            <input type="radio" class="" id="genderMale" name="gender" value="male" title="Please check sexe">
                                        </label>
                                        <label class="radio-inline" style="padding-left:10px !important;margin-left:0;">Feminino</label>
                                        <label class="radio-inline" style="width:2%;padding-bottom:22px;margin-left:0;">
                                            <input type="radio" class="" id="genderFemale" name="gender" value="female" title="Please check sexe">
                                        </label>
                                    </td>
                                    <td style="width:14%;"><label for="ageInMonths">Data de nascimento </label></td>
                                    <td style="width:14%;">
                                        <input type="text" class="form-control date" id="dob" name="dob" placeholder="Data de nascimento" title="Please enter Data de nascimento" onchange="setDobMonthYear();" style="width:100%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="ageInMonths"> Idade (em meses se < 1 ano) </label></td>
                                    <td>
                                        <input type="text" class="form-control checkNum" id="ageInMonths" name="ageInMonths" placeholder="Mois" title="Please enter àge en mois" style="width:100%;"/>
                                    </td>
                                    <td colspan="3"><label for="responsiblePersonName">Nome da Mãe/ Pai/ Familiar responsáve </label></td>
                                    <td>
                                        <input type="text" class="form-control" id="responsiblePersonName" name="responsiblePersonName" placeholder="Nome da Mãe/ Pai/ Familiar responsáve" title="Please enter Nome da Mãe/ Pai/ Familiar responsáve" style="width:100%;" />
                                    </td>
                                    <td><label for="patientDistrict">Município </label></td>
                                    <td>
                                        <input type="text" class="form-control" id="patientDistrict" name="patientDistrict" placeholder="Município" title="Please enter Município" style="width:100%;" />
                                    </td>
                                </tr>
                                <tr>
                                    <td ><label for="patientProvince">Província </label></td>
                                    <td>
                                        <input type="text" class="form-control" id="patientProvince" name="patientProvince" placeholder="Província" title="Please enter Província" style="width:100%;" />
                                    </td>
                                    <td><label for="patientPhoneNumber">Contacto </label></td>
                                    <td>
                                        <input type="text" class="form-control" id="patientPhoneNumber" name="patientPhoneNumber" placeholder="Contacto" title="Please enter Contacto" style="width:100%;"/>
                                    </td>
                                    <td><label for="consentReceiveSms">Autoriza contacto </label></td>
                                    <td style="width:16%;">
                                        <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Sim</label>
                                        <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                            <input type="radio" class="" id="consentReceiveSmsYes" name="consentReceiveSms" value="yes" title="Please check Autoriza contacto">
                                        </label>
                                        <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Não</label>
                                        <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                            <input type="radio" class="" id="consentReceiveSmsNo" name="consentReceiveSms" value="no" title="Please check Autoriza contacto">
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">C. INFORMAÇÃO DE TRATAMENTO</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:14%;"><label for="">Data de início de TARV </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control date" id="dateOfArtInitiation" name="dateOfArtInitiation" placeholder="e.g 09-Jan-1992" title="Please select Data de início de TARV"  style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="artRegimen"> Esquema de TARV actual </label></td>
                                <td style="width:14%;">
                                    <select class="form-control " id="artRegimen" name="artRegimen" placeholder="Esquema de TARV actual" title="Please enter Esquema de TARV actual" style="width:100%;"  onchange="checkARTValue();">
                                        <option value="">-- Select --</option>
                                        <?php foreach($artRegimenResult as $heading) { ?>
                                        <optgroup label="<?php echo ucwords($heading['headings']); ?>">
                                          <?php
                                          foreach($aResult as $regimen){
                                            if($heading['headings'] == $regimen['headings']){
                                            ?>
                                            <option value="<?php echo $regimen['art_code']; ?>"><?php echo $regimen['art_code']; ?></option>
                                            <?php
                                            }
                                          }
                                          ?>
                                        </optgroup>
                                        <?php } ?>
                                        <option value="other">Other</option>
                                    </select>
                                  <input type="text" class="form-control newArtRegimen" name="newArtRegimen" id="newArtRegimen" placeholder="ART Regimen" title="Please enter art regimen" style="width:100%;display:none;margin-top:2px;" >
                                </td>
                                <td><label for="lineTreatment">Linha de TARV actua </label></td>
                                <td style="width:32%;">
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Primeira</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="lineTrtFirst" name="lineTreatment" value="1" title="Please check Linha de TARV actua">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Segunda</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="lineTrtSecond" name="lineTreatment" value="2" title="Please check Linha de TARV actua">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Terceira</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="lineTrtThird" name="lineTreatment" value="3" title="Please check Linha de TARV actua">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><label for="sex">Se o paciente está em 2ª ou 3ª linha de TARV, indique o tipo de falência </label></td>
                                <td colspan="3">
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">N/A</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="lineTreatmentRefType" id="lineTreatmentNoResult" name="lineTreatmentRefType" value="na" title="Please check indique o tipo de falência">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Virológica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="lineTreatmentRefType" id="lineTreatmentVirological" name="lineTreatmentRefType" value="virological" title="Please check indique o tipo de falência">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Imunológica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="lineTreatmentRefType" id="lineTreatmentimmunological" name="lineTreatmentRefType" value="immunological" title="Please check indique o tipo de falência">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Clínica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="lineTreatmentRefType" id="lineTreatmentClinical" name="lineTreatmentRefType" value="clinical" title="Please check indique o tipo de falência">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">Refira em que grupo(s) o paciente se enquadra</td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="width:1%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientGeneralPopulation" name="patientGroup" value="general_population" title="Please check População geral">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">População geral (adulto, criança ou mulheres não grávidas)</label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="width:1%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientKeyPopulation" name="patientGroup" value="key_population" title="Please check População chave – especifique">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">População chave – especifique</label>
                                    
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">HSH/Trans</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientGroupKeyMSM" name="patientGroupKeyOption" value="msm" title="Please check HSH/Trans">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">TS</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientGroupKeySW" name="patientGroupKeyOption" value="sw" title="Please check TS">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Outro</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientGroupKeyOther" name="patientGroupKeyOption" value="other" title="Please check Outro">
                                    </label>
                                    <input type="text" class="form-control" name="patientGroupKeyOtherText" id="patientGroupKeyOtherText" title="Please enter value"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="width:1%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="patientPregnantWoman" name="patientGroup" value="pregnant" title="Please check Mulher gestante">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Mulher gestante – indique a data provável do parto</label>
                                    <input type="text" class="form-control date" name="patientPregnantWomanDate" id="patientPregnantWomanDate" placeholder="e.g 09-Jan-1992" title="Please enter data provável do parto"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="width:1%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="breastFeeding" name="patientGroup" value="breast_feeding" title="Please check Mulher lactante">
                                    </label>
                                    <label class="radio-inline" style="padding-left:0px !important;margin-left:0;">Mulher lactante</label>
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">D. INDICAÇÃO PARA SOLICITAÇÃO DE CARGA VIRAL</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td colspan="6">
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Monitoria de rotina</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="routineMonitoring" name="indicateVlTesing" value="routine" title="Please check Monitoria de rotina">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Diagnóstico de criança exposta </label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="exposeChild" name="indicateVlTesing" value="expose" title="Please check Diagnóstico de criança exposta">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Suspeita de falência de tratamento</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="suspectedTreatment" name="indicateVlTesing" value="suspect" title="Please check Suspeita de falência de tratamento">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Repetição após CV≥ 1000 cp/mL</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="repetition" name="indicateVlTesing" value="repetition" title="Please check Repetição após CV≥ 1000 cp/mL">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Falência clínica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="clinicalFailure" name="indicateVlTesing" value="clinical" title="Please check Falência clínica">
                                    </label>
                                    <label class="radio-inline" style="padding-left:17px !important;margin-left:0;">Falência imunológica</label>
                                    <label class="radio-inline" style="width:4%;padding-bottom:22px;margin-left:0;">
                                        <input type="radio" class="" id="immunologicalFailure" name="indicateVlTesing" value="immunological" title="Please check Falência imunológica">
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:14%;"><label for="">Se aplicável: data da última carga viral </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control date" id="lastVlDate" name="lastVlDate" placeholder="e.g 09-Jan-1992" title="Please select data da última carga viral" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="lastVlResult"> Resultado da última carga vira </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control" id="lastVlResult" name="lastVlResult" placeholder="Resultado da última carga vira" title="Please enter Resultado da última carga vira" style="width:100%;"/>
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">E. UNIDADE DE COLHEITA</h3>
                        </div>
                        <table class="table" style="width:100%">
                            <tr>
                                <td style="width:14%;"><label for="fName">Nome da Unidade de colheita (se diferente da Unidade de solicitação) </label></td>
                                <td style="width:14%;">
                                    <select class="form-control isRequired" name="fName" id="fName" title="Please choose Nome de colheita" style="width:100%;" >
                                          <?php echo $facility;  ?>
                                    </select>
                                </td>
                                <td style="width:14%;"><label for="collectionSite"> Local de colheita </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control " id="collectionSite" name="collectionSite" placeholder="Local de colheita" title="Please enter Local de colheita" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="sampleCollectionDate"> Data Hora de colheita </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control dateTime" name="sampleCollectionDate" id="sampleCollectionDate" placeholder="Data Hora de colheita" title="Please enter Data Hora de colheita" style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:14%;"><label for="requestingPerson">Responsável pela colheita </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control" id="requestingPerson" name="requestingPerson" placeholder="Responsável pela colheita" title="Please select Responsável pela colheita" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="requestingContactNo"> Contacto </label></td>
                                <td style="width:14%;">
                                    <input type="text" class="form-control" id="requestingContactNo" name="requestingContactNo" placeholder="Contacto" title="Please enter Contacto" style="width:100%;"/>
                                </td>
                                <td style="width:14%;"><label for="sampleType"> Tipo de amostra </label></td>
                                <td style="width:14%;">
                                    <select name="specimenType" id="specimenType" class="form-control" title="Please choose Tipo de amostra" style="width:100%">
                                      <option value="">-- Selecione --</option>
                                      <?php
                                        foreach($sResult as $name){
                                         ?>
                                         <option value="<?php echo $name['sample_id'];?>"><?php echo ucwords($name['sample_name']);?></option>
                                         <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        </div>
                        <div class="box box-primary">
                    <div class="box-header with-border">
                      <h3 class="box-title">Informações laboratoriais</h3>
                    </div>
                    <table class="table" style="width:100%">
                      <tr>
                        <td style="width:14%;"><label for="sampleCode">  Nº de amostra </label></td>
                        <td style="width:14%;">
                          <input type="text" class="form-control" id="sampleCode" name="sampleCode" placeholder="Nº de amostra" title="Please enter Nº de amostra" style="width:100%;" onblur="checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>',this.id,null,'This sample number already exists.Try another number',null)"/>
                        </td>
                      </tr>
                      <tr>
                          <td style="width:14%;"><label for="">Nome do laboratório</label></td>
                          <td style="width:14%;">
                              <select name="labId" id="labId" class="form-control" title="Please choose Nome do laboratório" style="width: 100%;">
                                <option value="">-- Select --</option>
                                <?php
                                foreach($lResult as $labName){
                                  ?>
                                  <option value="<?php echo $labName['facility_id'];?>"><?php echo ucwords($labName['facility_name']);?></option>
                                  <?php
                                }
                                ?>
                              </select>
                          </td>
                          <td style="width:14%;"><label for="testingPlatform"> Plataforma de teste VL </label></td>
                          <td style="width:14%;">
                              <select name="testingPlatform" id="testingPlatform" class="form-control" title="Please choose Plataforma de teste VL" style="width: 100%;">
                                <option value="">-- Select --</option>
                                <?php foreach($importResult as $mName) { ?>
                                  <option value="<?php echo $mName['machine_name'].'##'.$mName['lower_limit'].'##'.$mName['higher_limit'];?>"><?php echo $mName['machine_name'];?></option>
                                  <?php
                                }
                                ?>
                              </select>
                          </td>
                          <td style="width:14%;"><label for="vlFocalPerson"> Responsável da recepção </label></td>
                          <td style="width:14%;">
                              <input type="text" class="form-control checkNum" id="vlFocalPerson" name="vlFocalPerson" placeholder="Responsável da recepção" title="Please enter Responsável da recepção" style="width:100%;"/>
                          </td>
                      </tr>
                      <tr>
                        <td style="width:14%;"><label for="sampleReceivedOn">  Amostra de Data Recebida no Laboratório de Teste </label></td>
                          <td style="width:14%;">
                              <input type="text" class="form-control dateTime" id="sampleReceivedOn" name="sampleReceivedOn" placeholder="Amostra de data recebida" title="Please select Amostra de data recebida"/>
                          </td>
                          <td style="width:14%;"><label for="">Data de Teste de Amostras</label></td>
                          <td style="width:14%;">
                              <input type="text" class="form-control dateTime" id="sampleTestingDateAtLab" name="sampleTestingDateAtLab" placeholder="Data de Teste de Amostras" title="Please select Data de Teste de Amostras"/>
                          </td>
                          <td style="width:14%;"><label for="resultDispatchedOn"> Data de Resultados Despachados </label></td>
                          <td style="width:14%;">
                              <input type="text" class="form-control dateTime" id="resultDispatchedOn" name="resultDispatchedOn" placeholder="Data de Resultados Despachados" title="Please select Data de Resultados Despachados"/>
                          </td>
                      </tr>
                      <tr>
                        <td style="width:14%;"><label for="noResult"> Rejeição da amostra</label></td>
                          <td style="width:14%;">
                              <label class="radio-inline">
                               <input class="" id="noResultYes" name="noResult" value="yes" title="Rejeição da amostra" type="radio"> Yes
                              </label>
                              <label class="radio-inline">
                               <input class="" id="noResultNo" name="noResult" value="no" title="Rejeição da amostra" type="radio"> No
                              </label>
                          </td>
                          <td class=" rejectionReason" style="display:none;">
                            <label for="rejectionReason">Razão de rejeição </label>
                          </td>
                          <td class="rejectionReason" style="display:none;">
                            <select name="rejectionReason" id="rejectionReason" class="form-control" title="Please choose Razão de rejeição" onchange="checkRejectionReason();" style="width: 193px;">
                              <option value="">-- Select --</option>
                              <?php foreach($rejectionTypeResult as $type) { ?>
                              <optgroup label="<?php echo ucwords($type['rejection_type']); ?>">
                                <?php
                                foreach($rejectionResult as $reject){
                                  if($type['rejection_type'] == $reject['rejection_type']){
                                  ?>
                                  <option value="<?php echo $reject['rejection_reason_id'];?>"><?php echo ucwords($reject['rejection_reason_name']);?></option>
                                  <?php
                                  }
                                }
                                ?>
                              </optgroup>
                              <?php } ?>
                              <option value="other">Outro (por favor, especifique) </option>
                            </select>
                            <input type="text" class="form-control newRejectionReason" name="newRejectionReason" id="newRejectionReason" placeholder="Razão de rejeição" title="Please enter Razão de rejeição" style="width:100%;display:none;margin-top:2px;">
                          </td>
                        <td class="vlResult">
                          <label for="vlResult">Resultado da carga viral (cópias / ml) </label>
                        </td>
                        <td class="vlResult">
                              <input type="text" class="form-control" id="vlResult" name="vlResult" placeholder="resultado da carga viral" title="Please enter viral load result" style="width:100%;" onchange="calculateLogValue(this)"/>
                              <input type="checkbox" class="" id="tnd" name="tnd" value="yes" title="Please check tnd"> Target não detectado<br>
                              <input type="checkbox" class="" id="bdl" name="bdl" value="yes" title="Please check bdl"> Abaixo do nível de detecção
                        </td>
                        <td class="vlResult">
                          <label for="vlLog">Registro de carga viral </label>
                        </td>
                            <td class="vlResult">
                              <input type="text" class="form-control" id="vlLog" name="vlLog" placeholder="Registro de carga viral" title="Please enter Registro de carga viral" style="width:100%;" onchange="calculateLogValue(this);"/>
                            </td>
                      </tr>
                      
                      <tr>
                        <td>
                          <label for="approvedBy">Aprovado por </label>
                        </td>
                        <td>
                          <select name="approvedBy" id="approvedBy" class="form-control" title="Please choose Aprovado por" style="width:100%;">
                            <option value="">-- Select --</option>
                            <?php
                            foreach($userResult as $uName){
                              ?>
                              <option value="<?php echo $uName['user_id'];?>" <?php echo ($uName['user_id']==$_SESSION['userId'])?"selected=selected":""; ?>><?php echo ucwords($uName['user_name']);?></option>
                              <?php
                            }
                            ?>
                          </select>
                        </td>
                        <td>
                          <label for="labComments">Comentários do cientista de laboratório </label>
                        </td>
                            <td colspan="3">
                              <textarea class="form-control" name="labComments" id="labComments" placeholder="Comentários do laboratório" style="width:100%"></textarea>
                            </td>
                      </tr>
                    </table>
                  </div>
                    </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                <input type="hidden" name="sampleCodeTitle" id="sampleCodeTitle" value="<?php echo $arr['sample_code'];?>"/>
                <?php if($arr['sample_code']=='auto' || $arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
                  <input type="hidden" name="sampleCodeFormat" id="sampleCodeFormat" value="<?php echo $sFormat;?>"/>
                  <input type="hidden" name="sampleCodeKey" id="sampleCodeKey" value="<?php echo $sKey;?>"/>
                <?php } ?>
                <a class="btn btn-primary" href="javascript:void(0);" onclick="validateNow();return false;">Save</a>
                <input type="hidden" name="formId" id="formId" value="8"/>
                <a href="vlRequest.php" class="btn btn-default"> Cancel</a>
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
      provinceName = true;
      facilityName = true;
     $(document).ready(function() {
        $('.date').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd-M-yy',
        yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
       }).click(function(){
           $('.ui-datepicker-calendar').show();
        });
        
        $('.dateTime').datetimepicker({
          changeMonth: true,
          changeYear: true,
          dateFormat: 'dd-M-yy',
          timeFormat: "HH:mm",
          yearRange: <?php echo (date('Y') - 100); ?> + ":" + "<?php echo (date('Y')) ?>"
          }).click(function(){
   	    $('.ui-datepicker-calendar').show();
          });
        
        $('.date').mask('99-aaa-9999');
        $('.dateTime').mask('99-aaa-9999 99:99');
     });
     
    function getfacilityDetails(obj){
       $.blockUI();
       var cName = $("#clinicName").val();
      var pName = $("#province").val();
      if(pName!='' && provinceName && facilityName){
        facilityName = false;
      }
      if($.trim(pName)!=''){
        if(provinceName){
            $.post("../includes/getFacilityForClinic.php", { pName : pName},
            function(data){
                if(data!= ""){
                  details = data.split("###");
                  $("#clinicName").html(details[0]);
                  $("#district").html(details[1]);
                  //$("#clinicianName").val(details[2]);
                }
            });
        }
        <?php if($arr['sample_code']=='auto'){ ?>
        pNameVal = pName.split("##");
        sCode = '<?php echo date('ymd');?>';
        sCodeKey = '<?php echo $maxId;?>';
        $("#sampleCode").val('<?php echo $rKey;?>'+pNameVal[1]+sCode+sCodeKey);
        $("#sampleCodeFormat").val('<?php echo $rKey;?>'+pNameVal[1]+sCode);
        $("#sampleCodeKey").val(sCodeKey);
        checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>','sampleCode',null,'This sample number already exists.Try another number',null);
        <?php
      }else if($arr['sample_code']=='YY' || $arr['sample_code']=='MMYY'){ ?>
        $("#sampleCode").val('<?php echo $rKey.$prefix.$mnthYr.$maxId;?>');
        $("#sampleCodeFormat").val('<?php echo $rKey.$prefix.$mnthYr;?>');
        $("#sampleCodeKey").val('<?php echo $maxId;?>');
        checkSampleNameValidation('vl_request_form','<?php echo $sampleCode;?>','sampleCode',null,'This sample number already exists.Try another number',null)
        <?php
      }
      ?>
      }else if(pName=='' && cName==''){
        provinceName = true;
        facilityName = true;
        $("#province").html("<?php echo $province;?>");
        $("#clinicName").html("<?php echo $facility;?>");
      }else{
        $("#district").html("<option value=''> -- Selecione -- </option>");
      }
       $.unblockUI();
    }
    
    function getfacilityDistrictwise(obj){
      $.blockUI();
      var dName = $("#district").val();
      var cName = $("#clinicName").val();
      if(dName!=''){
        $.post("../includes/getFacilityForClinic.php", {dName:dName,cliName:cName},
        function(data){
            if(data != ""){
              $("#clinicName").html(data);
            }
        });
      }else{
         $("#clinicName").html("<option value=''> -- Selecione -- </option>");
      }
      $.unblockUI();
    }
    function getfacilityProvinceDetails(obj)
    {
      $.blockUI();
       //check facility name
        var cName = $("#clinicName").val();
        var pName = $("#province").val();
        if(cName!='' && provinceName && facilityName){
          provinceName = false;
        }
      if(cName!='' && facilityName){
        $.post("../includes/getFacilityForClinic.php", { cName : cName},
        function(data){
            if(data != ""){
              details = data.split("###");
              $("#province").html(details[0]);
              $("#district").html(details[1]);
              //$("#clinicianName").val(details[2]);
            }
        });
      }else if(pName=='' && cName==''){
        provinceName = true;
        facilityName = true;
        $("#province").html("<?php echo $province;?>");
        $("#clinicName").html("<?php echo $facility;?>");
      }
      $.unblockUI();
    }

    function checkRejectionReason(){
      var rejectionReason = $("#rejectionReason").val();
      if(rejectionReason == "other"){
        $(".newRejectionReason").show();
      }else{
        $(".newRejectionReason").hide();
      }
    }
    
    function setDobMonthYear(){
      var today = new Date();
      var dob = $("#dob").val();
      if($.trim(dob) == ""){
        $("#ageInMonths").val("");
        $("#ageInYears").val("");
        return false;
      }
      var dd = today.getDate();
      var mm = today.getMonth();
      var yyyy = today.getFullYear();
      if(dd<10) {
        dd='0'+dd
      } 
      
      if(mm<10) {
        mm='0'+mm
      }
      
      splitDob = dob.split("-");
      var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
      var monthDigit = dobDate.getMonth();
      var dobYear = splitDob[2];
      var dobMonth = isNaN(monthDigit) ? 0 : (monthDigit);
      dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
      var dobDate = (splitDob[0]<10) ? '0'+splitDob[0]: splitDob[0];
      
      var date1 = new Date(yyyy,mm,dd);
      var date2 = new Date(dobYear,dobMonth,dobDate);
      var diff = new Date(date1.getTime() - date2.getTime());
      if((diff.getUTCFullYear() - 1970) == 0){
        $("#ageInMonths").val((diff.getUTCMonth() > 0)? diff.getUTCMonth(): ''); // Gives month count of difference
      }else{
        $("#ageInMonths").val("");
      }
      $("#ageInYears").val((diff.getUTCFullYear() - 1970 > 0)? (diff.getUTCFullYear() - 1970) : ''); // Gives difference as year
    }
    function validateNow(){
      flag = deforayValidator.init({
        formId: 'addVlRequestForm'
      });
      if(flag){
        $.blockUI();
        document.getElementById('addVlRequestForm').submit();
      }
    }
    function checkNameValidation(tableName,fieldName,obj,fnct)
    {
      if($.trim(obj.value)!=''){
        $.post("../includes/checkDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : obj.value,fnct : fnct, format: "html"},
        function(data){
            if(data==='1'){
                showModal('patientModal.php?artNo='+obj.value,900,520);
            }
        });
      }
    }
    function checkSampleNameValidation(tableName,fieldName,id,fnct,alrt)
    {
      if($.trim($("#"+id).val())!=''){
        $.blockUI();
        $.post("../includes/checkSampleDuplicate.php", { tableName: tableName,fieldName : fieldName ,value : $("#"+id).val(),fnct : fnct, format: "html"},
        function(data){
            if(data!=0){
              <?php if(USERTYPE=='remoteuser' || USERTYPE=='standalone'){ ?>
                  alert(alrt);
                  $("#"+id).val('');
                <?php } else { ?>
                   data = data.split("##");
                  document.location.href = "editVlRequest.php?id="+data[0]+"&c="+data[1];
                <?php } ?>
            }
        });
        $.unblockUI();
      }
    }
  function getAge(){
    var dob = $("#dob").val();
    if($.trim(dob) == ""){
      $("#ageInMonths").val("");
      $("#ageInYears").val("");
      return false;
    }
    //calculate age
    splitDob = dob.split("-");
    var dobDate = new Date(splitDob[1] + splitDob[2]+", "+splitDob[0]);
    var monthDigit = dobDate.getMonth();
    var dobMonth = isNaN(monthDigit) ? 1 : (parseInt(monthDigit)+parseInt(1));
    dobMonth = (dobMonth<10) ? '0'+dobMonth: dobMonth;
    dob = splitDob[2]+'-'+dobMonth+'-'+splitDob[0];
    var years = moment().diff(dob, 'years',false);
    var months = (years == 0)?moment().diff(dob, 'months',false):'';
    $("#ageInMonths").val(months); // Gives difference as months
  }
  
  function checkARTValue(){
    var artRegimen = $("#artRegimen").val();
    if(artRegimen=='other'){
      $("#newArtRegimen").show();
      $("#newArtRegimen").addClass("isRequired");
    }else{
      $("#newArtRegimen").hide();
      $("#newArtRegimen").removeClass("isRequired");
      $('#newArtRegimen').val("");
    }
  }
  function calculateLogValue(obj){
    if(obj.id=="vlResult") {
      absValue = $("#vlResult").val();
      if(absValue!='' && absValue!=0 && !isNaN(absValue)){
        $("#vlLog").val(Math.round(Math.log10(absValue) * 100) / 100);
      }else{
        $("#vlLog").val('');
      }
    }
    if(obj.id=="vlLog") {
      logValue = $("#vlLog").val();
      if(logValue!='' && logValue!=0 && !isNaN(logValue)){
        var absVal = Math.round(Math.pow(10,logValue) * 100) / 100;
        if(absVal!='Infinity'){
          $("#vlResult").val(Math.round(Math.pow(10,logValue) * 100) / 100);
        }
      }else{
        $("#vlResult").val('');
      }
    }
  }
  $("input:radio[name=noResult]").click(function() {
    if($(this).val() == 'yes'){
      $('.rejectionReason').show();
      $('.vlResult').css('visibility','hidden');
      $('#rejectionReason').addClass('isRequired');
    }else{
      $('.vlResult').css('visibility','visible');
      $('.rejectionReason').hide();
      $('#rejectionReason').removeClass('isRequired');
      $('#rejectionReason').val('');
    }
  });
  $("input:radio[name=lineTreatment]").click(function() {
    if($(this).val() == '1'){
      $('.lineTreatmentRefType').attr("disabled",true);
    }else{
      $('.lineTreatmentRefType').attr("disabled",false);
    }
  });
  
  $('#tnd').change(function() {
    if($('#tnd').is(':checked')){
      $('#vlResult,#vlLog').attr('readonly',true);
      $('#bdl').attr('disabled',true);
    }else{
      $('#vlResult,#vlLog').attr('readonly',false);
      $('#bdl').attr('disabled',false);
    }
  });
  $('#bdl').change(function() {
    if($('#bdl').is(':checked')){
      $('#vlResult,#vlLog').attr('readonly',true);
      $('#tnd').attr('disabled',true);
    }else{
      $('#vlResult,#vlLog').attr('readonly',false);
      $('#tnd').attr('disabled',false);
    }
  });
  
  $('#vlResult,#vlLog').on('input',function(e){
    if(this.value != ''){
      $('#tnd').attr('disabled',true);
      $('#bdl').attr('disabled',true);
    }else{
      $('#tnd').attr('disabled',false);
      $('#bdl').attr('disabled',false);
    }
  });
  </script>
  
 <?php
 //include('../footer.php');
 ?>
