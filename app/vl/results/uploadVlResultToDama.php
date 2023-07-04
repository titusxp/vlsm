<?php
$title = _("VL | Upload VL Result to DAMA");

require_once APPLICATION_PATH . '/header.php';

?>
<style>
    .table-container {
        height: 275px;
        padding: 20px;
        margin: 10px;
        overflow-x: auto;
        overflow-y: auto;

    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-family: Arial, sans-serif;
        font-size: 10px;
        border: 15px solid white;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-top: 5px;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #3C8DBC;
        color: #ffffff;
        font-weight: bold;
    }

    tbody tr:nth-child(even) {
        background-color: #F2F2F2;
    }

    tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }

    tbody tr:hover {
        background-color: #f9f9f9;
    }

    .success {
        color: green;
    }

    .error {
        color: red;
    }
</style>
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
        <div class="row">
            <div id="loader" style="display: none;" class="dataTables_empty">
                <h4><b><?php echo _("Loading your request please wait...") ?></b></h4>
            </div>
            <table>
                <tr>
                    <td>
                        <b>
                            <div id="notification" style="display: none;"></div>
                        </b>
                    </td>
                    <td>
                        <div id="advanceOption">
                            <label for="startDate" class="form-control"><?php echo _("Select Date and Time") ?></label>
                            <input type="datetime-local" id="startDate" name="startDate" class="form-control">
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm pull-right" style="margin-right:5px;" id="fetchButton"><?php echo _("Fetch Results From Vlsm") ?></button>
                        <button class="btn btn-primary btn-sm pull-right" style="margin-right:5px;" id="advanceOptionButton"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="5"><?php echo _("You can specify a date to load record from. Only Approved results will be loaded.") ?> </td>
                </tr>
            </table>
        </div>
        <div class="table-container">
            <div class="row">
                <table id="dataTable" style="display: none;" border=1>
                    <thead class>
                        <tr>
                            <th><?php echo _("VL SAMPLE ID") ?></th>
                            <th><?php echo _("DAMA ID") ?></th>
                            <th><?php echo _("TEST PLATFORM") ?></th>
                            <th><?php echo _("SAMPLE TESTING DATE") ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data rows will be dynamically populated here -->
                    </tbody>
                </table>
            </div>
        </div>
        <button class="btn btn-success btn-sm pull-right" id="saveButton" style="display: none;"><?php echo _("Send Data To DAMA") ?></button>
    </section>
</div>
<script type="text/javascript" src="/assets/js/moment.min.js"></script>
<script>
    $(document).ready(function() {
        var rawJson;
        var startDate;
        var showAdvanced = false;
        var savedSampleIds = [];
        $('#advanceOption').hide();
        $('#advanceOptionButton').text('<?php echo _("Show Advanced Option") ?>').removeClass('btn-danger').addClass('btn-primary');

        $('#saveButton').click(function() {
            if (savedSampleIds.length == 0) {
                $('#loader').hide();
                alert("No data to save");
            } else {
                console.log(rawJson);
                $('#loader').show();
                $.ajax({
                    url: 'uploadVlResultDamaHelper.php',
                    type: 'POST',
                    data: {
                        data: rawJson
                    },
                    success: function(response) {
                        var value = JSON.parse(response);
                        if (value.message == 'success') {
                            updateUploadStatus();
                        } else {
                            $('#loader').hide();
                            alert("Data not saved to Dama");
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loader').hide();
                        alert('Error  ');
                    }
                });
            }
        });
        $('#fetchButton').click(function() {
            $('#loader').show();
            _startDate = $('#startDate').val().toString().replace("T", " ");
            if (_startDate == "") {
                startDate = startDate;
            } else {
                startDate = _startDate;
            }
            $.ajax({
                url: 'getResultsToUploadDama.php',
                type: 'POST',
                data: {
                    startDate: startDate
                },
                success: function(response) {
                    $('#loader').hide();
                    //console.log(response);
                    var parsedJson = JSON.parse(response);
                    rawJson = response;

                    $('#loader').hide();
                    $('#dataTable').show();
                    $('#saveButton').show();
                    alert("Data was successfully retrieved.");

                    var tbody = $('#dataTable tbody');
                    tbody.empty();

                    for (var i = 0; i < parsedJson.length; i++) {
                        var row = $('<tr></tr>');
                        row.append('<td>' + parsedJson[i].vl_sample_id + '</td>');
                        row.append('<td>' + parsedJson[i].unique_id + '</td>');
                        row.append('<td>' + parsedJson[i].vl_test_platform + '</td>');
                        row.append('<td>' + parsedJson[i].sample_testing_date + '</td>');
                        // Add more table columns as per  data

                        tbody.append(row);
                        savedSampleIds.push(parsedJson[i].vl_sample_id);
                    }
                },
                error: function(xhr, status, error) {
                    $('#loader').hide();
                    alert('Error  ');
                }
            });
        });
        $('#advanceOptionButton').click(function() {
            showAdvanced = !showAdvanced;
            if (showAdvanced) {
                $('#advanceOption').show();
                $('#advanceOptionButton').text('<?php echo _("Hide Advanced Option") ?>').removeClass('btn-primary').addClass('btn-danger');
            } else {
                $('#advanceOption').hide();
                $('#advanceOptionButton').text('<?php echo _("Show Advanced Option") ?>').removeClass('btn-danger').addClass('btn-primary');
            }
        });

        function updateUploadStatus() {
            $.ajax({
                url: 'updateDamaUploadStatus.php',
                type: 'POST',
                data: {
                    savedSampleIds: savedSampleIds
                },
                success: function(response) {
                    $('#loader').hide();
                    if (response = "1") {
                        alert('Data saved successfully to Dama and updated in vlsm ');
                    } else {
                        alert('Data not updated in vlsm');
                    }
                },
                error: function() {
                    $('#loader').hide();
                    alert('Error updating status in vlsm');
                }
            });
        }
    });
</script>
<?php
include APPLICATION_PATH . '/footer.php';
