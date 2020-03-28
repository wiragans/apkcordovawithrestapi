<?php
error_reporting(0);
date_default_timezone_set('Asia/Jakarta');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: PUT");
require 'connection.php';

$contentType = explode(';', $_SERVER['CONTENT_TYPE']);
$clientId = $_SERVER['HTTP_CLIENTID'];
$accessBearerToken = $_SERVER['HTTP_AUTHORIZATION'];
$rawBody = file_get_contents("php://input");
$data = array();

if(in_array('application/json', $contentType))
{
    $method = $_SERVER['REQUEST_METHOD'];
    if($method != "PUT")
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
            $request  = str_replace("/pasienapi/edit_pasien/", "", $_SERVER['REQUEST_URI']);
            $params = explode("/", $request);
            $resourceId = $params[0];
            
            $cekData = $conn->prepare("SELECT * FROM pasien WHERE BINARY id=:id ORDER BY id DESC LIMIT 1");
            $cekData->bindParam(':id', $resourceId);
            $cekData->execute();
            
            if($cekData->rowCount() <= 0)
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Data pasien tidak dapat ditemukan!'
                            ));
                exit();
            }
            
            if($cekData->rowCount() > 0)
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
                
                //JIKA SEMUA FIX BENAR, MAKA EDIT DATA PASIEN
                $update = $conn->prepare("UPDATE pasien SET namalengkap=:namalengkap, ruangpasien=:ruangpasien, alamat=:alamat, umur=:umur, golongandarah=:golongandarah, jeniskelamin=:jeniskelamin, keluhan=:keluhan WHERE BINARY id=:id");
                $update->bindParam(':namalengkap', $nama_lengkap);
                $update->bindParam(':ruangpasien', $ruang_pasien);
                $update->bindParam(':alamat', $alamat);
                $update->bindParam(':umur', $umur);
                $update->bindParam(':golongandarah', $golongan_darah);
                $update->bindParam(':jeniskelamin', $jenis_kelamin);
                $update->bindParam(':keluhan', $keluhan);
                $update->bindParam(':id', $resourceId);
                $update->execute();
                
                if($update)
                {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(array(
                                'statusCode' => 200,
                                'status'=> true,
                                'Code'=>'00',
                                'message' => 'Sukses Edit Data Pasien'
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
                                'message' => 'Terjadi masalah saat edit data pasien. Silakan coba lagi!'
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