<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET");
require '../connection.php';

$contentType = explode(';', $_SERVER['CONTENT_TYPE']);
$clientId = $_SERVER['HTTP_CLIENTID'];
$authId = $_SERVER['HTTP_AUTHID'];
$accessBearerToken = $_SERVER['HTTP_AUTHORIZATION'];
$authUsername = $_SERVER['PHP_AUTH_USER'];
$authPassword = $_SERVER['PHP_AUTH_PW'];

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
            $validAuthId = $conn->prepare("SELECT authId, authIdExpires FROM users WHERE BINARY authId=:authId ORDER BY id DESC LIMIT 1");
            $validAuthId->bindParam(':authId', $authId);
            $validAuthId->execute();
            
            if($validAuthId->rowCount() <= 0)
            {
                header('HTTP/1.1 400 Bad Request');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 400,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Invalid or Expired Auth ID'
                            ));
                exit();
            }
            
            if($validAuthId->rowCount() > 0)
            {
                foreach($validAuthId as $row1)
                {
                    $authIdTimestamp = $row1['authIdExpires'];
                }
                
                $ts1 = $authIdTimestamp;
                $ts2 = time();
                $seconds_diff = $ts2 - $ts1;  
                $getTime = $seconds_diff;
                
                if($authIdTimestamp == "" || $getTime > 0)
                {
                    header('HTTP/1.1 400 Bad Request');
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(array(
                                'statusCode' => 400,
                                'status'=> false,
                                'Code'=>'01',
                                'message' => 'Invalid or Expired Auth ID'
                                ));
                    exit();
                }
                
                //JIKA SUKSES LANJUT
                $time = time();
                $accessTokenTimestamp = time()+259200;
                $sub = md5(time() . $username . $authId . "C3oIf0tns8XnO5fm946JEzPVEwgAAABqeyJvcmlnaW4iOiJodHRwc");
                $hashData = hash('sha256', $authId . "C3oIf0tns8XnO5fm946JEzPVEwgAAABqeyJvcmlnaW4iOiJodHRwc");
                
                $payload = json_encode(
                        	[
                        	'sub'=>$sub,
                        	'iss'=>'https://api.kmsp-store.com/',
                        	'aud'=>$clientId,
                        	'iat'=>time(),
                        	'exp'=>$accessTokenTimestamp,
                        	'session_data'=>$hashData
                        	]
                        );
                        
                $header = json_encode(['typ'=>'JWT','alg'=>'HS256']);
                $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
                $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
                $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, '4UNeVi12lIAbCnpxJrbnLxb8I9O7jGMEQdnH', true);
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
                $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
                
                $createAccessToken = $conn->prepare("UPDATE users SET user_token=:user_token, expires_at=:expires_at WHERE BINARY authId=:authId");
                $createAccessToken->bindParam(':user_token', $jwt);
                $createAccessToken->bindParam(':expires_at', $accessTokenTimestamp);
                $createAccessToken->bindParam(':authId', $authId);
                $createAccessToken->execute();
                
                if($createAccessToken)
                {
                    $kosongkanAuthId = $conn->prepare("UPDATE users SET authId='', authIdExpires='' WHERE BINARY authId=:authId");
                    $kosongkanAuthId->bindParam(':authId', $authId);
                    $kosongkanAuthId->execute();
                    
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(array(
                                'statusCode' => 200,
                                'status'=> true,
                                'Code'=>'00',
                                'message' => 'Login Berhasil',
                                'access_token'=>$jwt,
                                'token_type'=>'Bearer',
                                'expires_in'=>259200
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
                                'message' => 'Terjadi kesalahan saat login. Silakan coba lagi!'
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