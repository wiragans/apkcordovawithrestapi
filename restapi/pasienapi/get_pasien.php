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
            $id = $data->id;
            $filter = $data->filter;
            
            if($filter == "byid")
            {
                $getPasien = $conn->prepare("SELECT * FROM pasien WHERE BINARY id=:id ORDER BY id DESC LIMIT 1");
                $getPasien->bindParam(':id', $id);
                $getPasien->execute();
            }
            
            else if($filter == "all")
            {
                $getPasien = $conn->prepare("SELECT * FROM pasien ORDER BY id DESC");
                $getPasien->execute();
            }
            
            else
            {
                header('HTTP/1.1 200 OK');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array(
                            'statusCode' => 200,
                            'status'=> false,
                            'Code'=>'01',
                            'message' => 'Filter Invalid'
                            ));
                exit();
            }
                
                if($getPasien->rowCount() <= 0)
                {
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(array(
                                'statusCode' => 200,
                                'status'=> false,
                                'Code'=>'01',
                                'message' => 'Data pasien tidak ditemukan'
                                ));
                    exit();
                }
                
                if($getPasien->rowCount() > 0)
                {
                    $dataArray = array();
                    foreach($getPasien as $rowPasien)
                    {
                        $idpasien = (int)$rowPasien['id'];
                        $namalengkap = $rowPasien['namalengkap'];
                        $ruangpasien = $rowPasien['ruangpasien'];
                        $alamat = $rowPasien['alamat'];
                        $umur = (int)$rowPasien['umur'];
                        $golongandarah = $rowPasien['golongandarah'];
                        $jeniskelamin = (int)$rowPasien['jeniskelamin'];
                        $keluhan = $rowPasien['keluhan'];
                        
                        if($jeniskelamin == 0)
                        {
                            $jeniskelamintext = "Tidak diketahui";
                        }
                        
                        else if($jeniskelamin == 1)
                        {
                            $jeniskelamintext = "Laki-laki";
                        }
                        
                        else if($jeniskelamin == 2)
                        {
                            $jeniskelamintext = "Perempuan";
                        }
                        
                        else if($jeniskelamin == 3)
                        {
                            $jeniskelamintext = "Lainnya";
                        }
                        
                        else
                        {
                            $jeniskelamintext = "Tidak diketahui";
                        }
                        
                        $dataArray2 = [
                                    'id'=>$idpasien,
                                    'nama_lengkap'=>$namalengkap,
                                    'ruang_pasien'=>$ruangpasien,
                                    'alamat'=>$alamat,
                                    'umur'=>$umur,
                                    'golongan_darah'=>$golongandarah,
                                    'jenis_kelamin'=>$jeniskelamintext,
                                    'keluhan'=>$keluhan
                                ];
                        array_push($dataArray, $dataArray2);
                    }
                    
                    header('HTTP/1.1 200 OK');
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode(array(
                                'statusCode' => 200,
                                'status'=> true,
                                'Code'=>'00',
                                'message' => 'Berhasil menampilkan data',
                                'data'=>$dataArray
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