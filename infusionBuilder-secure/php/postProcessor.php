<?php

/*
 Copyright 2008-2009 University of Toronto
 Licensed under the Educational Community License (ECL), Version 2.0 or the New
 BSD license. You may not use this file except in compliance with one these
 Licenses.
 You may obtain a copy of the ECL 2.0 License and BSD License at
 https://source.fluidproject.org/svn/LICENSE.txt
 */

/**
 * Processes a post from the infusionBuilder Fluid Infusion component.
 */


//create a session and establish unique directory and file names based on the session id
session_start();

// TODO users will have to change this depending on their server setup
include_once ("config.php");
include_once ("PostClass.php");
include_once ("BuilderUtilities.php");

/**
 * Ensures that the expected post variables have been posted and assigns them to the appropriate variables
 *
 * @return true if it is successful, false otherwise
 */
function processPostVariables($postVariables)
{
    if (! isset ($_POST[SELECTION_CHOICE]) || !isset ($_POST[MODULE_SELECTIONS])) {
        return false;
    }

    //these values are set to defaults if any invalid data is entered
    $minified = $postVariables->validateMinified($_POST[SELECTION_CHOICE]);
    $validModules = retrieveModuleList();
    if ($validModules == null) {
 		return false;
	}
    $includes = $postVariables->validateIncludes($_POST[MODULE_SELECTIONS], $validModules);
    return true;
}


/**
 * Builds the ant command.
 * See http://wiki.fluidproject.org/display/fluid/Custom+Build for details about
 * the command.
 *
 * @param object $incl Comma separated names representing the fluid modules to include
 * @param object $excl Comma separated names representing the fluid modules to exclude
 */
function buildAntCommand($incl, $excl, $unique_dir)
{
    $command = "cd ".BUILD_SCRIPT_PATH.
    $command .= " && ant builderBuild";
    if ($incl) $command .= " -Dinclude=\"".$incl."\"";
    if ($excl) $command .= " -Dexclude=\"".$excl."\"";
    $command .= " -Dproducts=\"".OUTPUT_FILE_PATH_PRODUCTS.$unique_dir."\"";
    $command .= " -Dbuild=\"".OUTPUT_FILE_PATH_BUILD.$unique_dir."\"";
    $command .= " -Dpretreated=\"".PRETREATED_PATH."\"";

    return $command;
}

/**
 * Builds and executes the ant command.
 * See http://wiki.fluidproject.org/display/fluid/Custom+Build for details about
 * the command.
 *
 * @return true if the build is successful and false otherwise
 * @param object $includeString Comma separated module names representing the fluid modules to include
 * @param object $excludeString Comma separated module names representing the fluid modules to exclude
 */

function executeBuildScript($includeString, $excludeString, $uuid, $doSource)
{
	$antCommand = buildAntCommand($includeString, $excludeString, $uuid);
	if ($doSource) {
	    $antCommand .= " -DnoMinify=\"true\"";
	}
    exec($antCommand, $output, $status);    
    if (in_array("BUILD SUCCESSFUL", $output))
    {
        return true;
    }
    return false;
}

/**
 * Constructs a return response of type "archive/zip" and returns the appropriate minified or src
 *  version of the custom built package.
 *
 * @param object $min A boolean indicating that the user wants to download the
 * minified package (true) or src package (false)
 */
function deliverBuildFile($key, $filepath, $filename, $intMin)
{
    //increment cache download counter
    $query = "UPDATE cache SET counter = counter + 1 WHERE id='{$key}' AND minified=".$intMin;
    $result = mysql_query($query);
    if (!$result) return false;
}

/**
 * Performs a mysql query to determine if the requested file is already cached.
 * If the file is cached already, the row is returned, otherwise FALSE.
 *
 * @param object $cacheKey      The key, which is the directory name of the stored file
 * @param object $intMin        The integer value representing minified or source download
 * @param object $error_message An error string for if the mysql query fails
 * @return object $cache_row    A row of data from the cache table if successful, FALSE otherwise
 */
function checkCacheMysql($cacheKey, $intMin, $error_message) { 
    $cache_query = "SELECT * FROM cache WHERE id = '{$cacheKey}' AND minified = ".$intMin;
    $cache_result = mysql_query($cache_query);
    if (!$cache_result) { //mysql_query resulted in error
        returnError($error_message);
        exit (1);
    }

    //a valid resource was obtained, but the cache may still be empty if no rows were returned
    $is_cached = false;
    if (mysql_num_rows($cache_result) == 1) { //must be 0 or 1 because of table restrictions
        $is_cached = true;
    }
    mysql_free_result($cache_result);
    return $is_cached;
}

//START OF EXECUTION

//process posted values
$postVariables = new PostClass();
$successPost = processPostVariables($postVariables);
$cacheKey = $postVariables->getKey();
$ver = $postVariables->getFluidVersionNumber();
$min = $postVariables->getMinified();
$intMin = $min ? 1 : 0;
$includes = $postVariables->getIncludes();
$excludes = $postVariables->getExcludes();

if (!$successPost)
{
    returnError("Cannot process input variables");
    exit (1);
}

//connect to database
$link = @mysql_connect(DB_HOST, DB_USER, DB_PASS);
if (!$link) {
    returnError("Cannot connect to cache database");
    exit (1);
}
$db_selected = @mysql_select_db(DB_NAME, $link);
if (!$db_selected) {
    returnError("Cannot select cache database");
    exit (1);
}

//get UUID to use as temp directory name
$uuid_query = "SELECT uuid()";
$uuid_result = mysql_query($uuid_query);
if (!$uuid_result) {
	returnError("Cannot complete cache retrieval query");
    exit (1);
}  
if (mysql_num_rows($uuid_result) == 1) {
	$uuid_row = mysql_fetch_assoc($uuid_result);
    mysql_free_result($uuid_result);
}
$uuid = $uuid_row["uuid()"];

//initialize some variables
$tmppath = OUTPUT_FILE_PATH_PRODUCTS.$uuid; //path for temporary files
$cachepath = CACHE_FILE_PATH.$cacheKey; //path to cached files

$minfilename = "infusion-".$ver.".zip"; //filename of minified zip
$srcfilename = "infusion-".$ver."-src.zip"; //filename of source zip

$tmpfilename = $tmppath."/".$minfilename; //path and filename of temporary zip file
$filename = $min ? $minfilename : $srcfilename; //filename desired (either source or zip)
$filepath = $cachepath."/".$filename; //path and filename of cached file

//check if file are already cached
if (!empty($cacheKey)) {
    $is_cached = checkCacheMysql($cacheKey, $intMin, "Cannot complete cache retrieval query");
}

//file is not cached - go through build process
if (!$is_cached) {
    
    //create cache directory
    if (!file_exists($cachepath)) {
        if (!@mkdir($cachepath, 0755, true)) {
            returnError("Cannot create cache directory");
            exit(1);
        }
    }
    
    //build file
    $successExecute = executeBuildScript($includes, $excludes, $uuid, !$min);
    if (!$successExecute)
    {
        returnError("Cannot execute build script");
        exit (1);
    }
    
    //copy file to cache directory
    if (!@copy($tmpfilename, $filepath)) {
            returnError("Cannot copy temp file to cache");
            exit(1);
     }
    
    //insert cache entry for this build
    $insert_query = "INSERT INTO cache (id, minified) VALUES('$cacheKey', ".$intMin.")";
    $insert_result = mysql_query($insert_query);
    if (!$insert_result) {
        $is_cached_again = checkCacheMysql($cacheKey, $intMin, "Error occurred inserting cache entry for this build");
        if (!$is_cached_again) {
            returnError("Cannot insert cache entry for this build");
            exit(1);
        }
    }      
}

//deliver file from cache location to user
$successDeliver = deliverBuildFile($cacheKey, $filepath, $filename, $intMin);
if ($successDeliver===false)
{
    returnError("Cannot deliver file");
} else {
    //json formatted output
    //encapsulate the actual file path, the sender should be able to use 
    //just the cacheKey and the min value to generate the filepath.
    echo json_encode(array($cacheKey, $intMin));
}
mysql_close($link);
?>
