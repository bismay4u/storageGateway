<?php
if(!defined("ROOT")) die("This file can't be accessed directly");

if(!function_exists("save_base64_image")) {
  
  function forwardPhoto($forwardURI, $photoURI, $token = false) {
    $POSTDATAPARAM = $_ENV['POSTDATAPARAM'];

    $_POST[$POSTDATAPARAM] = $photoURI;

    $curl = curl_init();

    $header = [
      "Cache-Control: no-cache",
      "Content-Type: application/x-www-form-urlencoded",
    ];
    if($token) {
        $header[] = "token: {$token}";
    }

    curl_setopt_array($curl, array(
      CURLOPT_URL => $forwardURI,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => http_build_query($_POST),
      CURLOPT_HTTPHEADER => $header
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    //var_dump([$response,$err]);

    return $response;
  }

  function uploadPhotoToS3($S3Config) {
    $tempDir = $_ENV['TEMPDIR'];
    $POSTDATAPARAM = $_ENV['POSTDATAPARAM'];

    if(isset($_POST[$POSTDATAPARAM])) {
      $S3Params=$S3Config[$_GET['s3key']];

      if(isset($_POST['fname']) && strlen($_POST['fname'])>0) {
        $fname = $_POST['fname'];
      } else {
        $fname=md5(time().rand());
      }
      
      $finalFile = save_base64_image($_POST[$POSTDATAPARAM],$fname,$tempDir);

      S3::$useSSL = false;
      $s3 = new S3($S3Params['accessKeyId'], $S3Params['secretAccessKey']);

      // using v4 signature
      $s3->setSignatureVersion('v4');
      
      // List your buckets:
      //echo "S3::listBuckets(): ".print_r($s3->listBuckets(), 1)."\n";exit();

      $bucketList = $s3->listBuckets();
      if(!in_array($S3Params['bucket'],$bucketList)) {
        if(!$s3->putBucket($S3Params['bucket'], $S3Params['bucket_security_policy'])) {
            exit("Sorry, Storage Initiation failed");
        }
      }
      //$bucketList = $s3->listBuckets();
      //print_r($bucketList);exit();

      if(isset($_POST['folder']) && $_POST['folder'] && strlen($_POST['folder'])>0) {
        $uploadPath="{$_POST['folder']}/".$finalFile;
      } elseif(isset($S3Params['folder']) && $S3Params['folder'] && strlen($S3Params['folder'])>0) {
        $uploadPath="{$S3Params['folder']}/".$finalFile;
      } else {
        $uploadPath=$finalFile;
      }
      
      $b = $s3->putObjectFile($tempDir.$finalFile, $S3Params['bucket'], $uploadPath, $S3Params['security_policy']);

      $photoURI='https://s3.amazonaws.com/'.$S3Params['bucket'].'/'.$uploadPath;
      
      unlink($tempDir.$finalFile);

      if($b) {
        return $photoURI;
      } else {
        die("Error uploading image to cloud target");
      }
    } else {
      die("Image Data Missing or Error Configuring Post Paramters");
    }
  }
  
  function save_base64_image($base64_image_string, $output_file_without_extension, $path_with_end_slash="" ) {
      //usage:  if( substr( $img_src, 0, 5 ) === "data:" ) {  $filename=save_base64_image($base64_image_string, $output_file_without_extentnion, getcwd() .     "/application/assets/pins/$user_id/"); }      
      //
      //data is like:    data:image/png;base64,asdfasdfasdf
      $splited = explode(',', substr( $base64_image_string , 5 ) , 2);
      $mime=$splited[0];
      $data=$splited[1];

      $mime_split_without_base64=explode(';', $mime,2);
      $mime_split=explode('/', $mime_split_without_base64[0],2);
      if(count($mime_split)==2)
      {
          $extension=$mime_split[1];
          if($extension=='jpeg') $extension='jpg';
          //if($extension=='javascript')$extension='js';
          //if($extension=='text')$extension='txt';
          $output_file_with_extension=$output_file_without_extension.'.'.$extension;
      }
      file_put_contents( $path_with_end_slash . $output_file_with_extension, base64_decode($data) );
      return $output_file_with_extension;
  }
}
?>
