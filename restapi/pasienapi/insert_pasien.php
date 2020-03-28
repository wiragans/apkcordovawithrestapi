<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: POST");
require 'connection.php';

$contentType = explode(';', $_SERVER['CONTENT_TYPE']);
$clientId = $_SERVER['HTTP_CLIENTID'];
$accessBearerToken = $_SERVER['HTTP_AUTHORIZATION'];
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
    
    if($nih == "")
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
    
    $valid = $conn->prepare("SELECT * FROM users WHERE BINARY user_token=:user_token ORDER BY id DESC LIMIT 1");
    $valid->bindParam(':user_token', $nih);
    $valid->execute();
    
    if($valid->rowCount() <= 0)
    {
        header('WWW-Authenticate: Bearer realm="KMSPRealm"');
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
    
    if($valid->rowCount() > 0)
    {
        foreach($valid as $rowValid)
        {
            $timestamp = $rowValid['expires_at'];
        }
        
        $cekMasaAktif = time() - $timestamp;
        $remaining = $timestamp - time();
        
        if($cekMasaAktif >= 0)
        {
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array(
                    'statusCode' => 200,
                    'status'=> false,
                    'Code'=>'01',
                    'message' => 'Sesi telah berakhir',
                    'data'=>[
                        'active'=>false
                        ]
                    ));
            exit();
        }
        
        if($cekMasaAktif < 0)
        {
            $data = json_decode($rawBody);
            $nama_lengkap = $data->nama_lengkap;
            $ruang_pasien = $data->ruang_pasien;
            $alamat = $data->alamat;
            $umur = $data->umur;
            $golongan_darah = $data->golongan_darah;
            $jenis_kelamin = $data->jenis_kelamin;
            $keluhan = $data->keluhan;
            
            if($nama_lengkap == "" || $ruang_pasien == "" || $alamat == "" || $umur == "" || $golongan_darah == "" || $jenis_kelamin == "" || $keluhan == "")
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Maaf, lengkapi terlebih dahulu semua data'
                            ));
                exit();
            }
            
            //CEK INPUTAN UMUR
            if($umur < 0)
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Value umur paling sedikit harus minimal 0'
                            ));
                exit();
            }
            
            //CEK PARAMETER INPUTAN JENIS KELAMIN
            if($jenis_kelamin != 0 && $jenis_kelamin != 1 && $jenis_kelamin != 2 && $jenis_kelamin != 3)
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Invalid Parameter Value Jenis Kelamin'
                            ));
                exit();
            }
            
            //JIKA SEMUA FIX BENAR, MAKA INPUT DATA PASIEN
            $insertData = $conn->prepare("INSERT INTO pasien(namalengkap, ruangpasien, alamat, umur, golongandarah, jeniskelamin, keluhan) VALUES(:namalengkap, :ruangpasien, :alamat, :umur, :golongandarah, :jeniskelamin, :keluhan)");
            $insertData->bindParam(':namalengkap', $nama_lengkap);
            $insertData->bindParam(':ruangpasien', $ruang_pasien);
            $insertData->bindParam(':alamat', $alamat);
            $insertData->bindParam(':umur', $umur);
            $insertData->bindParam(':golongandarah', $golongan_darah);
            $insertData->bindParam(':jeniskelamin', $jenis_kelamin);
            $insertData->bindParam(':keluhan', $keluhan);
            $insertData->execute();
            
            if($insertData)
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> true,
                            'Code'=>'00',
                            'message' => 'Data sukses ditambahkan'
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
                            'message' => 'Terjadi masalah saat menambahkan data. Silakan coba lagi!'
                            ));
                exit();
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