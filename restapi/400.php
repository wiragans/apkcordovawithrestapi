<?php
error_reporting(0);
header('HTTP/1.1 400 Bad Request');
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(array(
                'statusCode' => 400,
                'status'=> false,
                'Code'=>'01',
                'message' => 'Bad Request'
                ));
?>