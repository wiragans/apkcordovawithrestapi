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
        $validateBasicToken = $conn->prepare("SELECT username, password FROM authorization_grant WHERE BINARY username=:username AND password=:password ORDER BY id DESC LIMIT 1");
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
            $nama_lengkap = $data->nama_lengkap;
            $username = $data->username;
            $password = $data->password;
            $secretkey = $data->secret_key;
            
            if($nama_lengkap == "" || $username == "" || $password == "" || $secretkey == "")
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Data ada yang kosong. Lengkapi semua terlebih dahulu!'
                            ));
                exit();
            }
            
            if($secretkey != "okelanjut12345")
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Secret Key Salah!'
                            ));
                exit();
            }
            
            $cek = $conn->prepare("SELECT username FROM users WHERE username=:username");
            $cek->bindParam(':username', $username);
            $cek->execute();
            
            if($cek->rowCount() > 0)
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Username telah digunakan. Silakan coba yang lainnya!'
                            ));
                exit();
            }
            
            if($cek->rowCount() <= 0)
            {
                $insert = $conn->prepare("INSERT INTO users(namalengkap, username, password) VALUES(:namalengkap, :username, :password)");
                $insert->bindParam(':namalengkap', $nama_lengkap);
                $insert->bindParam(':username', $username);
                $insert->bindParam(':password', $password);
                $insert->execute();
                
                if($insert)
                {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(array(
                                'statusCode' => 200,
                                'status'=> true,
                                'Code'=>'00',
                                'message' => 'Akun sukses didaftarkan!'
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
                                'message' => 'Terjadi masalah saat registrasi. Silakan coba lagi!'
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
?>