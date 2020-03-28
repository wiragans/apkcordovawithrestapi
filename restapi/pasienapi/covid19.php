<?php
        error_reporting(0);
        date_default_timezone_set('Asia/Jakarta');
        
        $url = "https://kawalcovid19.harippe.id/api/summary";

		//$payload = "";

		$ch1 = curl_init($url);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch1, CURLOPT_POST, 1);
		//curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch1, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch1, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		$resultCh1 = curl_exec($ch1);
		//echo($resultCh1);
		$decodeResultCh1 = json_decode($resultCh1, true);
		curl_close($ch1);
		
		$confirmed = $decodeResultCh1['confirmed']['value'];
		$recovered = $decodeResultCh1['recovered']['value'];
		$deaths = $decodeResultCh1['deaths']['value'];
		$activeCare = $decodeResultCh1['activeCare']['value'];
		$metadata = $decodeResultCh1['metadata']['lastUpdatedAt'];
		
		$url2 = "https://api.kawalcorona.com/positif/";
		$ch2 = curl_init($url2);
		curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch2, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		$resultCh2 = curl_exec($ch2);
		//echo($resultCh2);
		$decodeResultCh2 = json_decode($resultCh2, true);
		curl_close($ch2);
		
		$url3 = "https://api.kawalcorona.com/sembuh/";
		$ch3 = curl_init($url3);
		curl_setopt($ch3, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch3, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		$resultCh3 = curl_exec($ch3);
		//echo($resultCh3);
		$decodeResultCh3 = json_decode($resultCh3, true);
		curl_close($ch3);
		
		$url4 = "https://api.kawalcorona.com/meninggal/";
		$ch4 = curl_init($url4);
		curl_setopt($ch4, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch4, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch4, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch4, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch4, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		$resultCh4 = curl_exec($ch4);
		//echo($resultCh4);
		$decodeResultCh4 = json_decode($resultCh4, true);
		curl_close($ch4);
		
		$positif = $decodeResultCh2['value'];
		$sembuh = $decodeResultCh3['value'];
		$kematian = $decodeResultCh4['value'];
		
		$json = json_encode(array(
		    'statusCode'=>200,
		    'status'=>true,
		    'Code'=>'00',
		    'message'=>'Berhasil menampilkan data',
		    'data'=>[
		        'indonesia'=>[
		        'terkonfirmasi'=>$confirmed,
		        'sembuh'=>$recovered,
		        'kematian'=>$deaths,
		        'dalam_perawatan'=>$activeCare
		        ],
		        'world'=>[
		            'positif'=>$positif,
		            'sembuh'=>$sembuh,
		            'kematian'=>$kematian
		            ],
		        'updated_at'=>$metadata
		        ]
		      ));

        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json; charset=UTF-8');
        echo $json;
?>