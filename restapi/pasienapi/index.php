<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');

        $json = json_encode(array(
		    'statusCode'=>404,
		    'status'=>false,
		    'Code'=>'01',
		    'message'=>'The resource could not be found'
		      ));

        header('HTTP/1.1 404 Not Found');
        header('Content-Type: application/json; charset=UTF-8');
        echo $json;
?>