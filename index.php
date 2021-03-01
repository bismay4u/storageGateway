<?php
define("ROOT", __DIR__."/");

include_once __DIR__."/s3.php";
include_once __DIR__."/config.php";
include_once __DIR__."/api.php";

if(!isset($_GET['s3key']) || !isset($S3Config[$_GET['s3key']])) {
  die("Upload Key Missing or not configured");
}

if(!is_dir($_ENV['TEMPDIR'])) @mkdir($_ENV['TEMPDIR']);
if(!is_dir($_ENV['TEMPDIR'])) die("Temp Dir Missing ...");

if(isset($_POST)) {
  //replace photo with S3 URI
  $photoURI = uploadPhotoToS3($S3Config);
} else {
  echo "S3 Storage Service";
}


if(isset($_GET['forward']) && strlen($_GET['forward'])>0) {
  echo forwardPhoto($_GET['forward'], $photoURI);
} else {
  echo $photoURI;
}
?>
