<?php

use App\Registries\ContainerRegistry;
use App\Services\CommonService;

/** @var CommonService $general */
$general = ContainerRegistry::get(CommonService::class);

$artNo = $_POST['artPatientNo'];

$count = 0;
$pQuery = "SELECT * FROM form_generic where (patient_id like '%" . $artNo . "%' OR patient_first_name like '%" . $artNo . "%' OR patient_middle_name like '%" . $artNo . "%' OR patient_last_name like '%" . $artNo . "%') ORDER BY sample_tested_datetime DESC, sample_collection_date DESC LIMIT 25";
// die($pQuery)
$pResult = $db->rawQuery($pQuery);
$count = count($pResult);
echo $count;
