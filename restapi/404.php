<?php
error_reporting(0);
header('HTTP/1.1 404 Not Found');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(array(
                'statusCode' => 404,
                'status'=> false,
                'Code'=>'01',
                'message' => 'The resource could not be found'
                ));
?>