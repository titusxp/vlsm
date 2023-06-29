<?php
$title = _("VL | Upload VL Result to DAMA");

require_once APPLICATION_PATH . '/header.php';

?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-satellite"></em> <?php echo _("Upload Result to DAMA"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _("Home"); ?></a></li>
            <li class="active"><?php echo _("Upload Result to DAMA Online"); ?></li>
        </ol>
    </section>
    <section class="content">
		
						<button class="btn btn-primary btn-sm pull-right" style="margin-right:5px;" id="postResult"><?php echo _("PostResult") ?></button>
						
	</section>
</div>
<script type="text/javascript" src="/assets/js/moment.min.js"></script>
<script>
    $(document).ready(function() {

        data = {
facilityCode: '0000',
password: '0000'
        }
       $('#postResult').click(function (){
        $.ajax({
            url: 'uploadVlResultDamaHelper.php',
            type: 'POST',
            data: { data: data},
            success: function(response){
                var esponse = JSON.parse(response); 
               alert('success   ' + esponse.message);
            },
            error: function(xhr, status, error){
                console.log(xhr);
                
                alert('Error  ' + error);
            }
        });
       });
    });
</script>
<?php
include APPLICATION_PATH . '/footer.php';
