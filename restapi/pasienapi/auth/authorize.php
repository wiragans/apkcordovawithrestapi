<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: POST");
require '../connection.php';

$contentType = explode(';', $_SERVER['CONTENT_TYPE']);
$clientId = $_SERVER['HTTP_CLIENTID'];
$accessBearerToken = $_SERVER['HTTP_AUTHORIZATION'];
$authUsername = $_SERVER['PHP_AUTH_USER'];
$authPassword = $_SERVER['PHP_AUTH_PW'];
$rawBody = file_get_contents("php://input");
$data = array();

if(in_array('application/json', $contentType))
{
    $method = $_SERVER['REQUEST_METHOD'];
    if($method != "POST")
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
    
    $clientIdUri = $_GET['client_id'];
        
    if($clientIdUri != $clientId)
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
    
    if($validClientId->rowCount() > 0)
    {
        $validateBasicToken = $conn->prepare("SELECT username, password FROM authorization_grant WHERE BINARY username=:username and password=:password ORDER BY id DESC LIMIT 1");
        $validateBasicToken->bindParam(':username', $authUsername);
        $validateBasicToken->bindParam(':password', $authPassword);
        $validateBasicToken->execute();
        
        if($validateBasicToken->rowCount() <= 0)
        {
            header('WWW-Authenticate: Basic realm="KMSPRealm"');
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
        
        if($validateBasicToken->rowCount() > 0)
        {
            $data = json_decode($rawBody);
            $username = $data->username;
            $password = $data->password;
            
            $valid = $conn->prepare("SELECT username, password FROM users WHERE BINARY username=:username AND password=:password ORDER BY id DESC LIMIT 1");
            $valid->bindParam(':username', $username);
            $valid->bindParam(':password', $password);
            $valid->execute();
            
            if($valid->rowCount() <= 0)
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Username atau password salah!'
                            ));
                exit();
            }
            
            if($valid->rowCount() > 0)
            {
                $time = time();
                $authIdExpTime = time()+60;
                $sub = md5(time() . $username . "mVhdHVyZSI6IldlYkNvae");
                
                $payload = json_encode(
                        	[
                        	'sub'=>$sub,
                        	'iss'=>'https://api.kmsp-store.com/',
                        	'aud'=>$clientId,
                        	'iat'=>time(),
                        	'exp'=>$authIdExpTime
                        	]
                        );
                        
                $header = json_encode(['typ'=>'JWT','alg'=>'HS256']);
                $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
                $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
                $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, '4UNeVi12lIAbCnpxJrbnLxb8I9O7jGMEQdnH', true);
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
                $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
                
                $updateAuthId = $conn->prepare("UPDATE users SET authId=:authId, authIdExpires=:authIdExpires WHERE BINARY username=:username");
                $updateAuthId->bindParam(':authId', $jwt);
                $updateAuthId->bindParam(':authIdExpires', $authIdExpTime);
                $updateAuthId->bindParam(':username', $username);
                $updateAuthId->execute();
                
                if($updateAuthId)
                {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(array(
                                'statusCode' => 200,
                                'status'=> true,
                                'Code'=>'00',
                                'message' => 'SUCCESS',
                                'authId'=>$jwt
                                ));
                    exit();
                }
                
                else
                {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(array(
                                'statusCode' => 200,
                                'status'=> false,
                                'Code'=>'01',
                                'message' => 'Terjadi masalah saat login. Silakan coba lagi!',
                                ));
                    exit();
                }
            }
        }
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