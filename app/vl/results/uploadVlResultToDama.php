<?php
$title = _("VL | Upload VL Result to DAMA");

require_once APPLICATION_PATH . '/header.php';

?>
<style>
    .table-container {
        height: 275px;
        width: 1250px;
        padding: 20px;
        margin: 10px;
        overflow-x: auto;

    }

    .div-container {
        margin-left: 20px;
        margin-right: 20px;
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
        background-color: white;
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

    th {
        background-color: #F3F6FC;
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
            <table>
                <tr>
                    <td>
                        <div id="loader" style="display: none;">
                            <h4><b><?php echo _("Loading your request please wait...") ?></b></h4>
                        </div>
                    </td>
                    <td><b>
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
                            <th>VL SAMPLE ID</th>
                            <th>DAMA ID</th>
                            <th>PATIENT ART NUMBER</th>
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
        var rawData;
        var startDate;
        var showAdvanced = false;
        var savedSampleIds = [];
        $('#advanceOption').hide();
        $('#advanceOptionButton').text('<?php echo _("Show Advanced Option") ?>').removeClass('btn-danger').addClass('btn-primary');

        $('#saveButton').click(function() {
            $('#loader').show();
            $.ajax({
                url: 'uploadVlResultDamaHelper.php',
                type: 'POST',
                data: {
                    data: rawData
                },
                success: function(response) {
                    var value = JSON.parse(response);
                    if (value.message == 'success') {
                        if (savedSampleIds = []) {
                            $('#loader').hide();
                            alert("No data to save");
                        } else {
                            updateUploadStatus();
                        }
                    } else {
                        $('#loader').hide();
                        alert("Data not saved in Dama: " + value.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('#loader').hide();
                    alert('Error  ');
                }
            });
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


                    console.log(response);



                    var data = JSON.parse(response);
                    rawData = response;
                    console.log(data);
                    console.log(rawData);

                    $('#loader').hide();
                    $('#dataTable').show();
                    $('#saveButton').show();
                    alert("Data was successfully retrieved.");

                    var tbody = $('#dataTable tbody');
                    tbody.empty();

                    for (var i = 0; i < data.length; i++) {
                        var row = $('<tr></tr>');
                        row.append('<td>' + data[i].vl_sample_id + '</td>');
                        row.append('<td>' + data[i].Id + '</td>');
                        row.append('<td>' + data[i].patient_art_no + '</td>');

                        // Add more table columns as per  data

                        tbody.append(row);
                        savedSampleIds.push(data[i].vl_sample_id);

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
            console.log(savedSampleIds);
            $.ajax({
                url: 'updateDamaUploadStatus.php',
                type: 'POST',
                data: {
                    savedSampleIds: savedSampleIds
                },
                success: function(response) {
                    $('#loader').hide();
                    alert(response);
                    if (response = "1") {
                        alert('Data saved successfully to Dama');
                    } else {
                        alert('Data not Update in vlsm');
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
