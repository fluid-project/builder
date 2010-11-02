<?php
define("DISTANT_PATH", "../../../infusionBuilder-secure/php/");
include_once (DISTANT_PATH."config.php");
include_once (DISTANT_PATH."PostClass.php");
include_once (DISTANT_PATH."BuilderUtilities.php");

//process posted values
$postVariables = new PostClass();
$ver = $postVariables->getFluidVersionNumber();

if (get_magic_quotes_gpc()) {
	$path = stripcslashes($_GET['path']);
} else {
	$path = $_GET['path'];
}

list($cacheKey, $min) = json_decode($path);
$cacheKey = addslashes($cacheKey);
$min = intval($min);

//initialize some variables
$cachepath = CACHE_FILE_PATH.$cacheKey; //path to cached files

$minfilename = "infusion-".$ver.".zip"; //filename of minified zip
$srcfilename = "infusion-".$ver."-src.zip"; //filename of source zip

$filename = $min ? $minfilename : $srcfilename; //filename desired (either source or zip)
$filepath = $cachepath.DIRECTORY_SEPARATOR.$filename; //path and filename of cached file

if(!file_exists($filepath)){
    //quit if file does not exist
    returnError('Cannot deliver file: '.$filepath, 1);
    exit (1);
}
$stat     = stat($filepath);
$size     = $stat['size'];

header("Content-Type: archive/zip");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Length: $size");
echo @file_get_contents($filepath);
exit;
?>