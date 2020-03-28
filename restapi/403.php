<?php
error_reporting(0);
header('HTTP/1.1 403 Forbidden');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(array(
                'statusCode' => 403,
                'status'=> false,
                'Code'=>'01',
                'message' => 'Forbidden'
                ));
?>