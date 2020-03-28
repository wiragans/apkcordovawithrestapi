<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET");
require '../connection.php';

$contentType = explode(';', $_SERVER['CONTENT_TYPE']);
$clientId = $_SERVER['HTTP_CLIENTID'];
$accessBearerToken = $_SERVER['HTTP_AUTHORIZATION'];

if(in_array('application/x-www-form-urlencoded', $contentType))
{
    $method = $_SERVER['REQUEST_METHOD'];
    if($method != "GET")
    {
        header('HTTP/1.1 405 Method Not Allowed');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
                        'statusCode' => 405,
                        'status'=> false,
                        'Code'=>'01',
                        'message' => 'Method Not Allowed'
                        ));
        exit();
    }
    
    $nih = substr($accessBearerToken, 7);
    $substrBasic = substr($accessBearerToken, 0, 6);
    
    $validClientId = $conn->prepare("SELECT clientId FROM authorization_grant WHERE BINARY clientId=:clientId ORDER BY id DESC LIMIT 1");
    $validClientId->bindParam(':clientId', $clientId);
    $validClientId->execute();
    
    if($validClientId->rowCount() <= 0)
    {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
                    'statusCode' => 403,
                    'status'=> false,
                    'Code'=>'01',
                    'message' => 'Invalid Client ID'
                    ));
        exit();
    }

    if($substrBasic != "Bearer")
    {
    	header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
                'statusCode' => 401,
                'status'=> false,
                'Code'=>'01',
                'message' => 'Authentication token is required and has failed or has not yet been provided'
                ));
    	exit();
    }
    
    //MAU TOKEN EXP ATAU MASIH AKTIF, TETAP DI-LOGOUT
    $logout = $conn->prepare("UPDATE users SET user_token='', expires_at='' WHERE BINARY user_token=:user_token");
    $logout->bindParam(':user_token', $nih);
    $logout->execute();
    
    if($logout->rowCount() > 0)
    {
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
                    'statusCode' => 200,
                    'status'=> true,
                    'Code'=>'00',
                    'message' => 'Logout Sukses'
                    ));
        exit();
    }
    
    if($logout->rowCount() <= 0)
    {
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
                    'statusCode' => 200,
                    'status'=> false,
                    'Code'=>'01',
                    'message' => 'Logout Gagal'
                    ));
        exit();
    }
}

else
{
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array(
                'statusCode' => 404,
                'status'=> false,
                'Code'=>'01',
                'message' => 'The resource could not be found'
                ));
    exit();
}