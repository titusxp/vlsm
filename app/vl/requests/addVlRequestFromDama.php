<?php
$title = _("VL | Import VL Request From DAMA");
error_reporting(E_ALL);
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
		<h1><em class="fa-solid fa-satellite"></em> <?php echo _("Import Request From DAMA"); ?></h1>
		<ol class="breadcrumb">
			<li><a href="/"><em class="fa-solid fa-chart-pie"></em> <?php echo _("Home"); ?></a></li>
			<li class="active"><?php echo _("Import From DAMA Online"); ?></li>
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
					<!-- <td>
						<label for="endDate" class="form-control"><?php echo _("To") ?></label>
						<input type="datetime-local" id="endDate" name="endDate" class="form-control">
					</td> -->

					<td>
						<button class="btn btn-primary btn-sm pull-right" style="margin-right:5px;" id="fetchButton"><?php echo _("Fetch Request From Dama") ?></button>
						<button class="btn btn-primary btn-sm pull-right" style="margin-right:5px;" id="advanceOptionButton"></button>
					</td>
				</tr>
				<tr>
					<td colspan="5"><?php echo _("You can specify a date to start loading from by clicking on Advanced option. If you don't specify a load range we'll load everything starting from the last load date.") ?> </td>
				</tr>
			</table>
		</div>
		<div class="table-container">
			<div class="row">
				<table id="dataTable" style="display: none;" border=1>
					<thead class>
						<tr>
							<th>DAMA ID</th>
							<th>PRESCRIBER NAME</th>
							<th>SAMPLE COLLECTION DATE</th>
							<th>DATE SENT TO LAB</th>
							<th>ART CODE</th>
							<th>SAMPLE COLLECTOR NAME</th>
							<th>SEX</th>
							<th>BIRTH DATE</th>
							<th>PREGNANT</th>
						</tr>
					</thead>
					<tbody>
						<!-- Data rows will be dynamically populated here -->
					</tbody>
				</table>
			</div>
		</div>

		<button class="btn btn-success btn-sm pull-right" id="saveButton" style="display: none;"><?php echo _("Save Data To VLSM") ?></button>
	</section>
</div>
<script type="text/javascript" src="/assets/js/moment.min.js"></script>
<script>
	$(document).ready(function() {
		var data;
		var showAdvanced = false;
		var startDate;
		getLastDate();
		$('#advanceOption').hide();
		$('#advanceOptionButton').text('<?php echo _("Show Advanced Option") ?>').removeClass('btn-danger').addClass('btn-primary');

		function getLastDate() {
			$.ajax({
				url: 'getLastDamaRequestDate.php',
				type: 'POST',
				success: function(response) {
					startDate = response;
					//console.log(response);
				},
				error: function() {
					//console.log('Error fetching last request date');
				}
			});
		}
		$('#fetchButton').click(function() {
			$('#loader').show();
			$('#notification').hide();
			_startDate = $('#startDate').val().toString().replace("T", " ");
			if (_startDate == "") {
				getLastDate();
				startDate = startDate;
			} else {
				startDate = _startDate;
			}
			//var endDate = $('#endDate').val().toString().replace("T", " ");
			$.ajax({
				url: 'fetchRequestDamaHelper.php',
				type: 'POST',
				data: {
					startDate: startDate,
					//endDate: endDate
				},

				success: function(response) {
					$('#loader').hide();
					$('#dataTable').show();
					$('#saveButton').show();
					alert("Data was successfully retrieved, proceed to view.");

					data = response;
					var tbody = $('#dataTable tbody');
					tbody.empty();

					for (var i = 0; i < data.length; i++) {
						var row = $('<tr></tr>');
						row.append('<td>' + data[i].Id + '</td>');
						row.append('<td>' + data[i].PrescriberName + '</td>');
						row.append('<td>' + data[i].SampleCollectionDate + '</td>');
						row.append('<td>' + data[i].DateSentToLab + '</td>');
						row.append('<td>' + data[i].ExistingARTCode + '</td>')
						row.append('<td>' + data[i].SampleCollectorName + '</td>');
						row.append('<td>' + data[i].Sex + '</td>')
						row.append('<td>' + data[i].BirthDate + '</td>');
						row.append('<td>' + data[i].IsPregnant + '</td>');

						// Add more table columns as per  data

						tbody.append(row);
					}
				},
				error: function() {
					$('#loader').hide();
					alert('Error fetching data from DAMA. Request was rejected or no internet connection.');
					//console.log(response);
				}
			});
		});

		$('#saveButton').click(function() {
			$('#loader').show();
			$('#notification').hide();

			$.ajax({
				url: 'saveRequestDamaHelper.php',
				type: 'POST',
				data: {
					data: data
				},
				success: function(response) {
					$('#loader').hide();
					//console.log(response);
					response = JSON.parse(response);
					if (Number.isInteger(response.message) == true) {
						alert("Saved to vsml successfully.");

					} else {
						$('#notification').text('Opps You are trying to save request which already exist. Note we saved all new request and ignore request which already exist.').removeClass('success').addClass('error').show();
					}
				},
				error: function(xhr, status, error) {
					$('#loader').hide();
					//var errorMessage = error;
					$('#notification').text('We encountered some errors').removeClass('success').addClass('error').show();

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

	});
</script>
</section>

<?php
include APPLICATION_PATH . '/footer.php';
