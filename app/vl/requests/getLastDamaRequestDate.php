<?php

use App\Registries\ContainerRegistry;


/** @var MysqliDb $db */
$db = ContainerRegistry::get('db');


$lastRequestDate = "";

$query = "SELECT * FROM form_vl WHERE source_of_request = 'dama' ORDER BY vl_sample_id DESC LIMIT 1";
$result = $db->rawQuery($query);

foreach ($result as $list) {
	$lastRequestDate = $list['request_imported_datetime'];
}

echo $lastRequestDate;