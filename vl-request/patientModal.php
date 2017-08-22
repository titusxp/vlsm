 <?php
include('../includes/MysqliDb.php');
include('../General.php');
$general=new Deforay_Commons_General();
$artNo=$_GET['artNo'];
//global config
$cQuery="SELECT * FROM global_config";
$cResult=$db->query($cQuery);
$arr = array();
// now we create an associative array so that we can easily create view variables
for ($i = 0; $i < sizeof($cResult); $i++) {
  $arr[$cResult[$i]['name']] = $cResult[$i]['value'];
}
$pQuery="SELECT * FROM vl_request_form where patient_art_no='".$_GET['artNo']."' AND vlsm_country_id='".$arr['vl_form']."'";
$pResult = $db->rawQuery($pQuery);
?>
  <link rel="stylesheet" media="all" type="text/css" href="../assets/css/jquery-ui.1.11.0.css" />
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/css/font-awesome.min.4.5.0.css">
   <!-- DataTables -->
  <link rel="stylesheet" href=".././assets/plugins/datatables/dataTables.bootstrap.css">
  <link href="../assets/css/deforayModal.css" rel="stylesheet" />  
  <style>
    .content-wrapper{
      padding:2%;
    }
    
    .center{text-align:center;}
    body{
      overflow-x: hidden;
      /*overflow-y: hidden;*/
    }
  </style>
  <script type="text/javascript" src="../assets/js/jquery.min.2.0.2.js"></script>
  <script type="text/javascript" src="../assets/js/jquery-ui.1.11.0.js"></script>
  <script src="../assets/js/deforayModal.js"></script>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="pull-left" style="font-size:22px;padding:15px;">Matched Patient List with ART Number(<?php echo $artNo;?>)</div>
    </section>
     <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
              <table id="patientModalDataTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th style="width:10%;">Action</th>
                  <th>Patient Name</th>
                  <th>Gender</th>
                </tr>
                </thead>
                <tbody>
										<?php
										foreach($pResult as $patient){
											$patientDetails = $patient['patient_first_name']."##".$patient['patient_last_name']."##".$patient['patient_gender']."##".$general->humanDateFormat($patient['patient_dob'])."##".$patient['patient_age_in_years']."##".$patient['patient_age_in_months']."##".$patient['is_patient_pregnant']."##".$patient['is_patient_breastfeeding']."##".$patient['patient_mobile_number']."##".$patient['consent_to_receive_sms']."##".$general->humanDateFormat($patient['treatment_initiated_date'])."##".$patient['current_regimen']."##".$general->humanDateFormat($patient['last_viral_load_date'])."##".$patient['last_viral_load_result']."##".$patient['number_of_enhanced_sessions'];
											?>
												<tr>
													 <td><input type="radio" id="patient<?php echo $patient['vl_sample_id'];?>" name="patient" value="<?php echo $patientDetails;?>" onclick="getPatientDetails(this.value);"></td>
													 <td><?php echo ucfirst($patient['patient_first_name'])." ".$patient['patient_last_name'];?></td>
													 <td><?php echo $patient['patient_gender'];?></td>
												</tr>
											<?php
										}
										?>
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <div id="dDiv" class="dialog">
      <div style="text-align:center"><span onclick="closeModal();" style="float:right;clear:both;" class="closeModal"></span></div> 
      <iframe id="dFrame" src="" style="border:none;" scrolling="yes" marginwidth="0" marginheight="0" frameborder="0" vspace="0" hspace="0">some problem</iframe> 
  </div>
  <!-- Bootstrap 3.3.6 -->
  <script src="../assets/js/bootstrap.min.js"></script>
  <!-- DataTables -->
  <script src=".././assets/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src=".././assets/plugins/datatables/dataTables.bootstrap.min.js"></script>
  <script>
  
  $(document).ready(function() {
         $('#patientModalDataTable').DataTable({"aaSorting": [1,'asc']});
    } );
  
    function getPatientDetails(pDetails){
      parent.closeModal();
      window.parent.setPatientDetails(pDetails);
    }
</script>
