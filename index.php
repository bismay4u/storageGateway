<?php
include_once __DIR__."/config.php";
include_once __DIR__."/s3.php";

if(!is_dir($tempDir)) @mkdir($tempDir);

//replace photo with S3 URI

if(isset($_GET['s3key']) && isset($S3Config[$_GET['s3key']])) {
  if(!isset($_GET['forward'])) {
    echo "Forward Server Missing";
    exit();
  }
  $_GET['forward']=urldecode($_GET['forward']);
  
  $S3Params=$S3Config[$_GET['s3key']];
  
  if(isset($_POST[$POSTDATAPARAM])) {
    $fname=md5(time().rand());
    $finalFile = save_base64_image($_POST[$POSTDATAPARAM],$fname,$tempDir);
    
    S3::$useSSL = false;
    $s3 = new S3($S3Params['accessKeyId'], $S3Params['secretAccessKey']);
    
    if(!$s3->putBucket($S3Params['bucket'], S3::ACL_PUBLIC_READ)) {
        exit("Sorry, Storage Initiation failed");
    }
    $uploadPath="{$S3Params['folder']}/".time()."_".$finalFile;
    $b=$s3->putObjectFile($tempDir.$finalFile, $S3Params['bucket'], $uploadPath, S3::ACL_PUBLIC_READ);
    
    $photoURI='https://s3.amazonaws.com/'.$S3Params['bucket'].'/'.$uploadPath;
   
    unlink($tempDir.$finalFile);
    
    if($b) {
      $_POST[$POSTDATAPARAM]=$photoURI;

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $_GET['forward'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($_POST),
        CURLOPT_HTTPHEADER => array(
          "Cache-Control: no-cache",
          "Content-Type: application/x-www-form-urlencoded",
          "token: pixys3gateway",
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      //var_dump([$response,$err]);
      
      echo $response;
    } else {
      echo "Error uploading image.";
    }
  } else {
    echo "Image Missing";
  }
} else {
  echo "Upload Key Missing";
}

function save_base64_image($base64_image_string, $output_file_without_extension, $path_with_end_slash="" ) {
    //usage:  if( substr( $img_src, 0, 5 ) === "data:" ) {  $filename=save_base64_image($base64_image_string, $output_file_without_extentnion, getcwd() . "/application/assets/pins/$user_id/"); }      
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
?>