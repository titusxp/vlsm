<?php
$general = new \Vlsm\Models\General();
if (isset($_POST['sampleCollectionDate']) && trim($_POST['sampleCollectionDate']) != '') {
    $s_c_date = explode("to", $_POST['sampleCollectionDate']);
    //print_r($s_c_date);die;
    if (isset($s_c_date[0]) && trim($s_c_date[0]) != "") {
        $lastSevenDay = $general->dateFormat(trim($s_c_date[0]));
    }
    if (isset($s_c_date[1]) && trim($s_c_date[1]) != "") {
        $cDate = $general->dateFormat(trim($s_c_date[1]));
    }
}

$configFormQuery = "SELECT * FROM global_config WHERE name ='vl_form'";
$configFormResult = $db->rawQuery($configFormQuery);
$facilityId = array();
//get collection data
$table = $_POST['table'];
foreach ($_POST['facilityId'] as $facility) {
    $facilities[] = '"' . $facility . '"';
}
/* echo "<pre>";
print_r($facilities);
die; */
if ($table == "form_covid19") {
    $collectionQuery = "SELECT COUNT(covid19_id) as total, facility_name FROM " . $table . " as covid19 JOIN facility_details as f ON f.facility_id=covid19.facility_id WHERE vlsm_country_id = '" . $configFormResult[0]['value'] . "' AND DATE(covid19.sample_collection_date) <= '" . $cDate . "' AND DATE(covid19.sample_collection_date)>= '" . $lastSevenDay . "'";
    if (sizeof($facilities) > 0) {
        $collectionQuery .= " AND f.facility_name IN (" . implode(",", $facilities) . ")";
    }
    $collectionQuery .= "  GROUP BY f.facility_id ORDER BY total DESC";
    // die($collectionQuery);
    $collectionResult = $db->rawQuery($collectionQuery); //collection result
    $collectionTotal = 0;
    if (sizeof($collectionResult) > 0) {
        foreach ($collectionResult as $total) {
            $collectionTotal = $collectionTotal + $total['total'];
        }
    }
} ?>
<div id="collection" width="210" height="150" style="min-height:150px;"></div>
<script>
    <?php if ($collectionTotal > 0 && $table == "form_covid19") { ?>
        $('#collection').highcharts({
            chart: {
                type: 'column',
                height: 150
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            credits: {
                enabled: false
            },
            xAxis: {
                categories: [<?php
                                foreach ($collectionResult as $tRow) {
                                    echo "'" . ucwords($tRow['facility_name']) . "',";
                                }
                                ?>],
                crosshair: true,
                scrollbar: {
                    enabled: true
                },
            },
            yAxis: {
                min: 0,
                title: {
                    text: null
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0,
                    cursor: 'pointer',
                }
            },
            series: [{
                showInLegend: false,
                name: 'Samples',
                data: [<?php
                        foreach ($collectionResult as $tRow) {
                            echo ucwords($tRow['total']) . ",";
                        }
                        ?>]

            }],
            colors: ['#f36a5a']
        });
    <?php } ?>
</script>