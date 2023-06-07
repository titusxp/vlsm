<?php

use App\Services\FacilitiesService;
use App\Registries\ContainerRegistry;
use App\Services\CommonService;
use App\Services\GeoLocationsService;

$title = _("View All Requests");

require_once APPLICATION_PATH . '/header.php';


/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

/** @var FacilitiesService $facilitiesService */
$facilitiesService = ContainerRegistry::get(FacilitiesService::class);
$healthFacilites = $facilitiesService->getHealthFacilities('vl');

/** @var GeoLocationsService $geolocationService */
$geolocationService = ContainerRegistry::get(GeoLocationsService::class);
$facilitiesDropdown = $general->generateSelectOptions($healthFacilites, null, "-- Select --");
$testingLabs = $facilitiesService->getTestingLabs('vl');
$testingLabsDropdown = $general->generateSelectOptions($testingLabs, null, "-- Select --");

$sQuery = "SELECT * FROM r_vl_sample_type WHERE `status`='active'";
$sResult = $db->rawQuery($sQuery);

$batQuery = "SELECT batch_code FROM batch_details WHERE test_type = 'vl' AND batch_status='completed'";
$batResult = $db->rawQuery($batQuery);

$state = $geolocationService->getProvinces("yes");

?>
<style>
    .select2-selection__choice {
        color: black !important;
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><em class="fa-solid fa-list-check"></em> <?php echo _("Failed/Hold Samples"); ?></h1>
        <ol class="breadcrumb">
            <li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _("Home"); ?></a></li>
            <li class="active"><?php echo _("Test Request"); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <table aria-describedby="table" id="advanceFilter" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width: 98%;margin-bottom: 0px;">
                        <tr>
                            <td><strong><?php echo _("Sample Collection Date"); ?> :</strong></td>
                            <td>
                                <input type="text" id="sampleCollectionDate" name="sampleCollectionDate" class="form-control" placeholder="<?php echo _('Select Collection Date'); ?>" readonly style="background:#fff;" />
                            </td>
                            <td><strong><?php echo _("Province/State"); ?>&nbsp;:</strong></td>
                            <td>
                                <select class="form-control select2-element" id="state" onchange="getByProvince(this.value)" name="state" title="<?php echo _('Please select Province/State'); ?>">
                                    <?= $general->generateSelectOptions($state, null, _("-- Select --")); ?>
                                </select>
                            </td>
                            <td><strong><?php echo _("District/County"); ?> :</strong></td>
                            <td>
                                <select class="form-control select2-element" id="district" name="district" title="<?php echo _('Please select Province/State'); ?>" onchange="getByDistrict(this.value		)">
                                </select>
                            </td>

                        </tr>
                        <tr>
                            <td><strong><?php echo _("Facility Name"); ?> :</strong></td>
                            <td>
                                <select class="form-control" id="facilityName" name="facilityName" multiple="multiple" title="<?php echo _('Please select facility name'); ?>" style="width:100%;">
                                    <?= $facilitiesDropdown; ?>
                                </select>
                            </td>
                            <td><strong><?php echo _("Testing Lab"); ?> :</strong></td>
                            <td>
                                <select class="form-control" id="vlLab" name="vlLab" title="<?php echo _('Please select vl lab'); ?>" style="width:220px;">
                                    <?= $testingLabsDropdown; ?>
                                </select>
                            </td>
                            <td><strong><?php echo _("Sample Type"); ?> :</strong></td>
                            <td>
                                <select class="form-control" id="sampleType" name="sampleType" title="<?php echo _('Please select sample type'); ?>">
                                    <option value=""> <?php echo _("-- Select --"); ?> </option>
                                    <?php
                                    foreach ($sResult as $type) {
                                    ?>
                                        <option value="<?php echo $type['sample_id']; ?>"><?= $type['sample_name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><strong><?php echo _("Result Status"); ?>&nbsp;:</strong></td>
                            <td>
                                <select name="status" id="status" class="form-control" title="<?php echo _('Please choose status'); ?>" onchange="checkSampleCollectionDate();">
                                    <option value="1"><?php echo _("Hold"); ?></option>
                                    <option value="2"><?php echo _("Lost"); ?></option>
                                    <option value="5"><?php echo _("Failed"); ?></option>
                                    <option value="10"><?php echo _("Expired"); ?></option>
                                </select>
                            </td>
                            <td><strong><?php echo _("Patient ID"); ?>&nbsp;:</strong></td>
                            <td>
                                <input type="text" id="patientId" name="patientId" class="form-control" placeholder="<?php echo _('Enter Patient ID'); ?>" style="background:#fff;" />
                            </td>

                            <td><strong><?php echo _("Patient Name"); ?>&nbsp;:</strong></td>
                            <td>
                                <input type="text" id="patientName" name="patientName" class="form-control" placeholder="<?php echo _('Enter Patient Name'); ?>" style="background:#fff;" />
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2"><input type="button" onclick="searchVlRequestData();" value="<?php echo _('Search'); ?>" class="btn btn-default btn-sm">
                                &nbsp;<button class="btn btn-danger btn-sm" onclick="document.location.href = document.location"><span><?php echo _("Reset"); ?></span></button>
                            </td>
                            <td colspan="4">
                                &nbsp;<button class="btn btn-success btn-sm pull-right retest-btn" style="margin-right:5px;display:none;" onclick="retestSample('',true);"><span><?php echo _("Retest the selected samples"); ?></span></button>
                            </td>
                        </tr>
                    </table>
                    <table aria-describedby="table" id="filter" class="table" aria-hidden="true" style="margin-left:1%;margin-top:20px;width: 98%;margin-bottom: 0px;">
                        <tr id="">
                            <td>
                                &nbsp;<button class="btn btn-success btn-sm pull-right retest-btn" style="margin-right:5px;display:none;" onclick="retestSample('',true);"><span><?php echo _("Retest the selected samples"); ?></span></button>
                            </td>
                        </tr>
                    </table>
                    <span style="display: none;position:absolute;z-index: 9999 !important;color:#000;padding:5px;" id="showhide" class="">
                        <div class="row" style="background:#e0e0e0;float: right !important;padding: 15px;">
                            <div class="col-md-12">
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="0" id="iCol0" data-showhide="sample_code" class="showhideCheckBox" /> <label for="iCol0"><?php echo _("Sample Code"); ?></label>
                                </div>
                                <?php $i = 0;
                                if ($_SESSION['instanceType'] != 'standalone') {
                                    $i = 1; ?>
                                    <div class="col-md-3">
                                        <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i; ?>" id="iCol<?php echo $i; ?>" data-showhide="remote_sample_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Remote Sample Code"); ?></label>
                                    </div>
                                <?php } ?>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="sample_collection_date" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Sample Collection Date"); ?></label> <br>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="batch_code" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Batch Code"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="patient_art_no" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Art No"); ?></label> <br>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="patient_first_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Patient's Name"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="facility_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Facility Name"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="state" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Province/State"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="district" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("District/County"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="sample_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Sample Type"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="result" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Result"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="last_modified_datetime" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Last Modified Date"); ?></label>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" onclick="fnShowHide(this.value);" value="<?php echo $i = $i + 1; ?>" id="iCol<?php echo $i; ?>" data-showhide="status_name" class="showhideCheckBox" /> <label for="iCol<?php echo $i; ?>"><?php echo _("Status"); ?></label>
                                </div>
                            </div>
                        </div>
                    </span>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <input type="hidden" name="checkedTests" id="checkedTests" />
                        <table aria-describedby="table" id="vlFailedResultDataTable" class="table table-bordered table-striped" aria-hidden="true">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="checkTestsData" onclick="toggleAllVisible()" /></th>
                                    <th><?php echo _("Sample Code"); ?></th>
                                    <?php if ($_SESSION['instanceType'] != 'standalone') { ?>
                                        <th><?php echo _("Remote Sample"); ?> <br /><?php echo _("Code"); ?></th>
                                    <?php } ?>
                                    <th><?php echo _("Sample Collection"); ?><br /> <?php echo _("Date"); ?></th>
                                    <th><?php echo _("Batch Code"); ?></th>
                                    <th><?php echo _("Unique ART No"); ?></th>
                                    <th><?php echo _("Patient's Name"); ?></th>
                                    <th scope="row"><?php echo _("Facility Name"); ?></th>
                                    <th><?php echo _("Province/State"); ?></th>
                                    <th><?php echo _("District/County"); ?></th>
                                    <th><?php echo _("Sample Type"); ?></th>
                                    <th><?php echo _("Result"); ?></th>
                                    <th><?php echo _("Last Modified Date"); ?></th>
                                    <th scope="row"><?php echo _("Status"); ?></th>
                                    <?php if (isset($_SESSION['privileges']) && (in_array("editVlRequest.php", $_SESSION['privileges']))) { ?>
                                        <th><?php echo _("Action"); ?></th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="16" class="dataTables_empty"><?php echo _("Loading data from server"); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php
                        if (isset($global['bar_code_printing']) && $global['bar_code_printing'] == 'zebra-printer') {
                        ?>

                            <div id="printer_data_loading" style="display:none"><span id="loading_message"><?php echo _("Loading Printer Details"); ?>...</span><br />
                                <div class="progress" style="width:100%">
                                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                                    </div>
                                </div>
                            </div> <!-- /printer_data_loading -->
                            <div id="printer_details" style="display:none">
                                <span id="selected_printer"><?php echo _("No printer selected"); ?>!</span>
                                <button type="button" class="btn btn-success" onclick="changePrinter()"><?php echo _("Change/Retry"); ?></button>
                            </div><br /> <!-- /printer_details -->
                            <div id="printer_select" style="display:none">
                                <?php echo _("Zebra Printer Options"); ?><br />
                                <?php echo _("Printer"); ?>: <select id="printers"></select>
                            </div> <!-- /printer_select -->

                        <?php
                        }
                        ?>

                    </div>

                </div>
                <!-- /.box -->

            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<script src="/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/daterangepicker/daterangepicker.js"></script>

<?php
if (isset($global['bar_code_printing']) && $global['bar_code_printing'] != "off") {
    if ($global['bar_code_printing'] == 'dymo-labelwriter-450') {
?>
        <script src="/assets/js/DYMO.Label.Framework.js"></script>
        <script src="/configs/dymo-format.js"></script>
        <script src="/assets/js/dymo-print.js"></script>
    <?php
    } else if ($global['bar_code_printing'] == 'zebra-printer') {
    ?>
        <script src="/assets/js/zebra-browserprint.js.js"></script>
        <script src="/configs/zebra-format.js"></script>
        <script src="/assets/js/zebra-print.js"></script>
<?php
    }
}
?>



<script type="text/javascript">
    var startDate = "";
    var endDate = "";
    var selectedTests = [];
    var selectedTestsId = [];
    var oTable = null;
    $(document).ready(function() {
        $("#state").select2({
            placeholder: "<?php echo _("Select Province"); ?>"
        });
        $("#district").select2({
            placeholder: "<?php echo _("Select District"); ?>"
        });
        $("#vlLab").select2({
            placeholder: "<?php echo _("Select Labs"); ?>"
        });
        $("#facilityName").select2({
            placeholder: "<?php echo _("Select Facilities"); ?>"
        });
        <?php
        if (isset($_GET['barcode']) && $_GET['barcode'] == 'true') {
            echo "printBarcodeLabel('" . htmlspecialchars($_GET['s']) . "','" . htmlspecialchars($_GET['f']) . "');";
        }
        ?>

        loadVlRequestData();
        $('#sampleCollectionDate').daterangepicker({
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
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'Last 90 Days': [moment().subtract(89, 'days'), moment()],
                    'Last 120 Days': [moment().subtract(119, 'days'), moment()],
                    'Last 180 Days': [moment().subtract(179, 'days'), moment()],
                    'Last 12 Months': [moment().subtract(12, 'month').startOf('month'), moment().endOf('month')]
                }
            },
            function(start, end) {
                startDate = start.format('YYYY-MM-DD');
                endDate = end.format('YYYY-MM-DD');
            });
        $('#sampleCollectionDate').val("");

        $(".showhideCheckBox").change(function() {
            if ($(this).attr('checked')) {
                idpart = $(this).attr('data-showhide');
                $("#" + idpart + "-sort").show();
            } else {
                idpart = $(this).attr('data-showhide');
                $("#" + idpart + "-sort").hide();
            }
        });

        $("#showhide").hover(function() {}, function() {
            $(this).fadeOut('slow')
        });
        var i = '<?php echo $i; ?>';
        for (colNo = 0; colNo <= i; colNo++) {
            $("#iCol" + colNo).attr("checked", oTable.fnSettings().aoColumns[parseInt(colNo)].bVisible);
            if (oTable.fnSettings().aoColumns[colNo].bVisible) {
                $("#iCol" + colNo + "-sort").show();
            } else {
                $("#iCol" + colNo + "-sort").hide();
            }
        }
    });

    function resetBtnShowHide() {
        var checkResult = false;
        $(".checkTests").each(function() {
            if ($(this).prop('checked')) {
                checkResult = true;
            }
        });
        if (checkResult) {
            $(".retest-btn").show();
        } else {
            $(".retest-btn").hide();
        }
    }

    function fnShowHide(iCol) {
        var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
        oTable.fnSetColumnVis(iCol, bVis ? false : true);
    }

    function loadVlRequestData() {
        $.blockUI();

        oTable = $('#vlFailedResultDataTable').dataTable({
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            },
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bInfo": true,
            "bScrollCollapse": true,
            "bStateSave": true,
            "bRetrieve": true,
            "aoColumns": [{
                    "sClass": "center",
                    "bSortable": false
                }, {
                    "sClass": "center"
                },
                <?php if ($_SESSION['instanceType'] != 'standalone') { ?> {
                        "sClass": "center"
                    },
                <?php } ?> {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                {
                    "sClass": "center"
                },
                <?php if (isset($_SESSION['privileges']) && (in_array("editVlRequest.php", $_SESSION['privileges']))) { ?> {
                        "sClass": "center",
                        "bSortable": false
                    },
                <?php } ?>
            ],
            "aaSorting": [
                [<?php echo ($sarr['sc_user_type'] == 'remoteuser' || $sarr['sc_user_type'] == 'vluser') ? 11 : 10 ?>, "desc"]
            ],
            "fnDrawCallback": function() {
                var checkBoxes = document.getElementsByName("chk[]");
                len = checkBoxes.length;
                for (c = 0; c < len; c++) {
                    if (jQuery.inArray(checkBoxes[c].id, selectedTestsId) != -1) {
                        checkBoxes[c].setAttribute("checked", true);
                    }
                }
            },
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "getVlFailedResultsDetails.php",
            "fnServerData": function(sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "sampleCollectionDate",
                    "value": $("#sampleCollectionDate").val()
                });
                aoData.push({
                    "name": "state",
                    "value": $("#state").val()
                });
                aoData.push({
                    "name": "district",
                    "value": $("#district").val()
                });
                aoData.push({
                    "name": "facilityName",
                    "value": $("#facilityName").val()
                });
                aoData.push({
                    "name": "vlLab",
                    "value": $("#vlLab").val()
                });
                aoData.push({
                    "name": "sampleType",
                    "value": $("#sampleType").val()
                });
                aoData.push({
                    "name": "status",
                    "value": $("#status").val()
                });
                aoData.push({
                    "name": "patientId",
                    "value": $("#patientId").val()
                });
                aoData.push({
                    "name": "patientName",
                    "value": $("#patientName").val()
                });
                $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": fnCallback
                });
            }
        });
        $.unblockUI();
    }

    function searchVlRequestData() {
        $.blockUI();
        oTable.fnDraw();
        $.unblockUI();
    }

    function loadVlRequestStateDistrict() {
        oTable.fnDraw();
    }

    function toggleTest(obj) {
        if ($(obj).is(':checked')) {
            if ($.inArray(obj.value, selectedTests) == -1) {
                selectedTests.push(obj.value);
                selectedTestsId.push(obj.id);
            }
        } else {
            selectedTests.splice($.inArray(obj.value, selectedTests), 1);
            selectedTestsId.splice($.inArray(obj.id, selectedTestsId), 1);
            $("#checkTestsData").attr("checked", false);
        }
        $("#checkedTests").val(selectedTests.join());
        if (selectedTests.length != 0) {
            $("#status").prop('disabled', false);
        } else {
            $("#status").prop('disabled', true);
        }
    }

    function toggleAllVisible() {
        //alert(tabStatus);
        $(".checkTests").each(function() {
            $(this).prop('checked', false);
            selectedTests.splice($.inArray(this.value, selectedTests), 1);
            selectedTestsId.splice($.inArray(this.id, selectedTestsId), 1);
            $("#status").prop('disabled', true);
        });
        if ($("#checkTestsData").is(':checked')) {
            $(".checkTests").each(function() {
                $(this).prop('checked', true);
                selectedTests.push(this.value);
                selectedTestsId.push(this.id);
            });
            $("#status").prop('disabled', false);
        } else {
            $(".checkTests").each(function() {
                $(this).prop('checked', false);
                selectedTests.splice($.inArray(this.value, selectedTests), 1);
                selectedTestsId.splice($.inArray(this.id, selectedTestsId), 1);
                $("#status").prop('disabled', true);
            });
        }
        $("#checkedTests").val(selectedTests.join());
        resetBtnShowHide();
    }

    function exportAllPendingVlRequest() {
        $.blockUI();
        var requestSampleType = $('#requestSampleType').val();
        $.post("export-vl-requests.php", {
                reqSampleType: requestSampleType
            },
            function(data) {
                $.unblockUI();
                if (data === "" || data === null || data === undefined) {
                    alert("<?php echo _("Unable to generate the excel file"); ?>");
                } else {
                    location.href = '/temporary/' + data;
                }
            });
    }

    function hideAdvanceSearch(hideId, showId) {
        $("#" + hideId).hide();
        $("#" + showId).show();
    }

    function retestSample(id, bulk = false) {
        if (bulk) {
            id = selectedTests;
        }
        if (id != "") {
            $.blockUI();
            $.post("failed-results-retest.php", {
                    vlId: id,
                    bulkIds: bulk
                },
                function(data) {
                    $.unblockUI();
                    if (data > 0) {
                        alert("<?php echo _("Retest has been submitted"); ?>.");
                        oTable.fnDraw();
                    } else {
                        alert("<?php echo _("Something went wrong. Please try again later"); ?>");
                    }
                });
        }
    }

    function getByProvince(provinceId) {
        $("#district").html('');
        $("#facilityName").html('');
        $("#vlLab").html('');
        $.post("/common/get-by-province-id.php", {
                provinceId: provinceId,
                districts: true,
                facilities: true,
                labs: true
            },
            function(data) {
                Obj = $.parseJSON(data);
                $("#district").html(Obj['districts']);
                $("#facilityName").html(Obj['facilities']);
                $("#vlLab").html(Obj['labs']);
            });
    }

    function getByDistrict(districtId) {
        $("#facilityName").html('');
        $("#vlLab").html('');
        $.post("/common/get-by-district-id.php", {
                districtId: districtId,
                facilities: true,
                labs: true
            },
            function(data) {
                Obj = $.parseJSON(data);
                $("#facilityName").html(Obj['facilities']);
                $("#vlLab").html(Obj['labs']);
            });
    }
</script>
<?php
require_once APPLICATION_PATH . '/footer.php';
