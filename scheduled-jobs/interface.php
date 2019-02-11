<?php

require(__DIR__ . "/../includes/MysqliDb.php");
require(__DIR__ . "/../General.php");

$general=new General($db);
//get the value from interfacing DB
$interfaceQuery="SELECT * from orders where result_status = 1";
$interfaceInfo=$interfacedb->query($interfaceQuery);
if(count($interfaceInfo)>0)
{
    foreach($interfaceInfo as $key=>$result)
    {
        $vlQuery="SELECT vl_sample_id from vl_request_form where sample_code = '".$result['test_id']."'";
        $vlInfo=$db->query($vlQuery);
        if(isset($vlInfo[0]['sample_id'])){
            $data = array(
                        'result_approved_by'=>$result['tested_by'],
                        'result_approved_datetime'=>$result['authorised_date_time'],
                        'sample_tested_datetime'=>$result['result_accepted_date_time'],
                        'result'=>$result['results']
                        );
            
                        $db=$db->where('vl_sample_id',$vlInfo[0]['sample_id']);
                        $vlUpdateId = $db->update('vl_request_form',$data);
                        if($vlUpdateId){
error_log($key);
                            $interfaceData = array(
                                                    'lims_sync_status'=>1,
                                                    'lims_sync_date_time'=>date('Y-m-d H:i:s')
                                                    );
                                                    $db=$interfacedb->where('id',$result['id']);
                                                    $interfaceUpdateId = $interfacedb->update('orders',$interfaceData);

                        }
        }
    }
}
?>