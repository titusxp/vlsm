<?php

use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;


$title = "Covid-19 | Add Batch";

require_once APPLICATION_PATH . '/header.php';

/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);
$healthFacilites = $facilitiesService->getHealthFacilities('covid19');

$facilitiesDropdown = $general->generateSelectOptions($healthFacilites, null, "-- Select --");


//Get active machines
$testPlatformResult = $general->getTestingPlatforms('covid19');
// $query = "SELECT vl.sample_code,vl.covid19_id,vl.facility_id,f.facility_name,f.facility_code FROM form_covid19 as vl INNER JOIN facility_details as f ON vl.facility_id=f.facility_id where sample_batch_id is NULL OR sample_batch_id='' ORDER BY f.facility_name ASC";
// $result = $db->rawQuery($query);

$start_date = date('Y-m-d');
$end_date = date('Y-m-d');
$maxId = $general->createBatchCode();
//Set last machine label order
$machinesLabelOrder = [];
foreach ($testPlatformResult as $machine) {
    $lastOrderQuery = "SELECT label_order from batch_details WHERE machine ='" . $machine['config_id'] . "' ORDER BY request_created_datetime DESC";
    $lastOrderInfo = $db->query($lastOrderQuery);
    if (isset($lastOrderInfo[0]['label_order']) && trim($lastOrderInfo[0]['label_order']) != '') {
        $machinesLabelOrder[$machine['config_id']] = implode(",", json_decode($lastOrderInfo[0]['label_order'], true));
    } else {
        $machinesLabelOrder[$machine['config_id']] = '';
    }
}
//print_r($machinesLabelOrder);
?>
<link href="/assets/css/multi-select.css" rel="stylesheet" />
<style>
    .select2-selection__choice {
        color: #000000 !important;
    }

    #ms-sampleCode {
        width: 110%;
    }

    .showFemaleSection {
        display: none;
    }

    #sortableRow {
        list-style-type: none;
        margin: 30px 0px 30px 0px;
        padding: 0;
        width: 100%;
        text-align: center;
    }

    #sortableRow li {
        color: #333 !important;
        font-size: 16px;
    }

    #alertText {
        text-shadow: 1px 1px #eee;
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-pen-to-square"></em> <?php echo _("Create Batch"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _("Home"); ?></a></li>
            <li class="active"><?php echo _("Batch"); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="pull-right" style="font-size:15px;"><span class="mandatory">*</span> <?php echo _("indicates required field"); ?> &nbsp;</div>
            </div>
            <table aria-describedby="table" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width: 100%;">
                <tr>
                    <th scope="col"><?php echo _("Testing Platform"); ?>&nbsp;<span class="mandatory">*</span> </th>
                    <td>
                        <select name="machine" id="machine" class="form-control isRequired" title="<?php echo _('Please choose machine'); ?>" style="width:280px;">
                            <option value=""> <?php echo _("-- Select --"); ?> </option>
                            <?php
                            foreach ($testPlatformResult as $machine) {
                                $labelOrder = $machinesLabelOrder[$machine['config_id']];
                            ?>
                                <option value="<?php echo $machine['config_id']; ?>" data-no-of-samples="<?php echo $machine['max_no_of_samples_in_a_batch']; ?>"><?= $machine['machine_name']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <th scope="col"><?php echo _("Facility"); ?></th>
                    <td>
                        <select style="width: 275px;" class="form-control" id="facilityName" name="facilityName" title="<?php echo _('Please select facility name'); ?>" multiple="multiple">
                            <?= $facilitiesDropdown; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="col"><?php echo _("Sample Collection Date"); ?></th>
                    <td>
                        <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control daterange" placeholder="<?php echo _('Select Collection Date'); ?>" readonly style="width:275px;background:#fff;" />
                    </td>
                    <th scope="col"><?php echo _("Date Sample Receieved at Lab"); ?></th>
                    <td>
                        <input type="text" id="sampleReceivedAtLab" name="sampleReceivedAtLab" class="form-control daterange" placeholder="<?php echo _('Select Received at Lab Date'); ?>" readonly style="width:275px;background:#fff;" />
                    </td>
                </tr>
                <tr>
                    <th scope="col"><?php echo _("Positions"); ?></th>
                    <td>
                        <select id="positions-type" class="form-control" title="<?php echo _('Please select the postion'); ?>">
                            <option value="numeric"><?php echo _("Numeric"); ?></option>
                            <option value="alpha-numeric"><?php echo _("Alpha Numeric"); ?></option>
                        </select>
                    </td>
                    <th scope="col"></th>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="4">&nbsp;<input type="button" onclick="getSampleCodeDetails();" value="<?php echo _('Filter Samples'); ?>" class="btn btn-success btn-sm">
                        &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?php echo _("Reset Filters"); ?></span></button>
                    </td>
                </tr>
            </table>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- form start -->
                <form class="form-horizontal" method="post" name="addBatchForm" id="addBatchForm" autocomplete="off" action="covid-19-add-batch-helper.php">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="batchCode" class="col-lg-4 control-label"><?php echo _("Batch Code"); ?> <span class="mandatory">*</span></label>
                                    <div class="col-lg-7" style="margin-left:3%;">
                                        <input type="text" class="form-control isRequired" id="batchCode" name="batchCode" placeholder="<?php echo _('Batch Code'); ?>" title="<?php echo _('Please enter batch code'); ?>" value="<?php echo date('Ymd') . $maxId; ?>" onblur='checkNameValidation("batch_details","batch_code",this,null,"<?php echo _("This batch code already exists.Try another batch code"); ?>",null)' />
                                        <input type="hidden" name="batchCodeKey" id="batchCodeKey" value="<?php echo $maxId; ?>" />
                                        <input type="hidden" name="platform" id="platform" value="" />
                                        <input type="hidden" name="positions" id="positions" value="" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="sampleDetails">
                            <h4> <?php echo _("Sample Code"); ?></h4>
                            <div class="col-md-5">
                                <!-- <div class="col-lg-5"> -->
                                <select name="sampleCode[]" id="search" class="form-control" size="8" multiple="multiple">

                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="button" id="search_rightAll" class="btn btn-block"><em class="fa-solid fa-forward"></em></button>
                                <button type="button" id="search_rightSelected" class="btn btn-block"><em class="fa-sharp fa-solid fa-chevron-right"></em></button>
                                <button type="button" id="search_leftSelected" class="btn btn-block"><em class="fa-sharp fa-solid fa-chevron-left"></em></button>
                                <button type="button" id="search_leftAll" class="btn btn-block"><em class="fa-solid fa-backward"></em></button>
                            </div>

                            <div class="col-md-5">
                                <select name="to[]" id="search_to" class="form-control" size="8" multiple="multiple"></select>
                            </div>
                        </div>
                        <div class="row col-md-12" id="alertText" style="font-size:20px;"></div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <input type="hidden" name="selectedSample" id="selectedSample" />
                        <a id="batchSubmit" class="btn btn-primary" href="javascript:void(0);" title="<?php echo _('Please select machine'); ?>" onclick="validateNow();return false;"><?php echo _("Save and Next"); ?></a>
                        <a href="covid-19-batches.php" class="btn btn-default"> <?php echo _("Cancel"); ?></a>
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
<script type="text/javascript" src="/assets/js/multiselect.min.js"></script>
<script type="text/javascript" src="/assets/js/jasny-bootstrap.js"></script>
<script src="/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript">
    var startDate = "";
    var endDate = "";
    noOfSamples = 0;
    sortedTitle = [];
    $(document).ready(function() {
        $('#search').multiselect({
            search: {
                left: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
                right: '<input type="text" name="q" class="form-control" placeholder="<?php echo _("Search"); ?>..." />',
            },
            fireSearch: function(value) {
                return value.length > 3;
            }
        });

        $("#facilityName").select2({
            placeholder: "<?php echo _("Select Facilities"); ?>"
        });

        $('.daterange').daterangepicker({
                locale: {
                    cancelLabel: "<?= _("Clear"); ?>",
                    format: 'DD-MMM-YYYY',
                    separator: ' to ',
                },
                showDropdowns: true,
                alwaysShowCalendars: false,
                startDate: moment().subtract(28, 'days'),
                endDate: moment(),
                maxDate: moment(),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            },
            function(start, end) {
                startDate = start.format('YYYY-MM-DD');
                endDate = end.format('YYYY-MM-DD');
            });
        $('.daterange').val("");
    });

    function validateNow() {
        var selVal = [];
        $('#search_to option').each(function(i, selected) {
            selVal[i] = $(selected).val();
        });
        $("#selectedSample").val(selVal);
        var selected = $("#machine").find('option:selected');
        noOfSamples = selected.data('no-of-samples');
        if (noOfSamples < selVal.length) {
            alert("You have selected maximum number of samples");
            return false;
        }

        if (selVal == "") {
            alert("Please select sample code");
            return false;
        }

        flag = deforayValidator.init({
            formId: 'addBatchForm'
        });
        if (flag) {
            $("#positions").val($('#positions-type').val());
            $.blockUI();
            document.getElementById('addBatchForm').submit();
        }
    }

    //$("#auditRndNo").multiselect({height: 100,minWidth: 150});


    function checkNameValidation(tableName, fieldName, obj, fnct, alrt, callback) {
        let removeDots = obj.value.replace(/\./g, "");
        removeDots = removeDots.replace(/\,/g, "");
        //str=obj.value;
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
                    duplicateName = false;
                    document.getElementById(obj.id).value = "";
                }
            });
    }

    function getSampleCodeDetails() {

        var machine = $("#machine").val();
        if (machine == null || machine == '') {
            $.unblockUI();
            alert("<?php echo _("You have to choose a testing platform to proceed"); ?>");
            return false;
        }
        var fName = $("#facilityName").val();

        $.blockUI();
        $.post("/covid-19/batch/get-covid-19-samples-batch.php", {
                sampleCollectionDate: $("#sampleCollectionDate").val(),
                sampleReceivedAtLab: $("#sampleReceivedAtLab").val(),
                fName: fName
            },
            function(data) {
                if (data != "") {
                    $("#sampleDetails").html(data);
                    //$("#batchSubmit").attr("disabled", true);
                    //$("#batchSubmit").css("pointer-events", "none");
                }
            });
        $.unblockUI();
    }



    $("#machine").change(function() {
        var self = this.value;
        if (self != '') {
            getSampleCodeDetails();
            $("#platform").val($("#machine").val());
            var selected = $(this).find('option:selected');
            noOfSamples = selected.data('no-of-samples');
            $('#alertText').html("<?php echo _("You have picked"); ?> " + $("#machine option:selected").text() + " <?php echo _("testing platform and it has limit of maximum"); ?> " + noOfSamples + " <?php echo _("samples per batch"); ?>");
        } else {
            $('.ms-list').html('');
            $('#alertText').html('');
        }
    });
    $(document.body).on("change", "#search, #search_to", function() {
        countOff().then(function(count) {
            // use the result here
            if (count > 0) {
                $('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + count + '/' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
            } else {
                $('#alertText').html('<?php echo _("You have picked"); ?> ' + $("#machine option:selected").text() + ' <?php echo _("testing platform and it has limit of maximum"); ?> ' + noOfSamples + ' <?php echo _("samples per batch"); ?>');
            }
        });
    });

    function countOff() {
        return new Promise(function(resolve, reject) {
            setTimeout(function() {
                resolve();
            }, 300);
        }).then(function() {
            var count = $("#search_to option").length;
            return count;
        });
    }
</script>


<?php

require_once APPLICATION_PATH . '/footer.php';
