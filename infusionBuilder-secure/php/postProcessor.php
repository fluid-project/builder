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

function executeBuildScript($includeString, $excludeString, $uuid, $minified)
{
	$antCommand = buildAntCommand($includeString, $excludeString, $uuid);
	if (!$minified) {
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
function deliverBuildFile($key, $filepath, $filename)
{
    //increment cache download counter
    $query = "UPDATE cache SET counter = counter + 1 WHERE id='".$key."'";
    $result = mysql_query($query);
    if (!$result) return false;
	
    //deliver file
	$fp = @fopen($filepath, "r");
	if ($fp == false)
    {
        return false;
    }
    $stats = fstat($fp);
    $size = $stats[size];
        
    header("Content-Type: archive/zip");
    header("Content-Disposition: attachment; filename=$filename");
    header("Content-Length: $size");
    echo fpassthru($fp);
}

//START OF EXECUTION

//process posted values
$postVariables = new PostClass();
$successPost = processPostVariables($postVariables);
if (!$successPost)
{
    returnError("Cannot process input variables");
    exit (1);
}

//connect to database
$link = @mysql_connect(DB_NAME, DB_USER, DB_PASS);
if (!$link) {
    returnError("Cannot connect to cache database");
    exit (1);
}
$db_selected = @mysql_select_db('build_cache', $link);
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
$cacheKey = $postVariables->getKey();
$cachepath = CACHE_FILE_PATH.$cacheKey;
$tmppath = OUTPUT_FILE_PATH_PRODUCTS.$uuid;
$ver = $postVariables->getFluidVersionNumber();
$minfilename = "infusion-".$ver.".zip";
$srcfilename = "infusion-".$ver."-src.zip";
$min = $postVariables->getMinified();
$includes = $postVariables->getIncludes();
$excludes = $postVariables->getExcludes();

//check if files are already cached
if (!empty($cacheKey)) {
    $cache_query = "SELECT * FROM cache WHERE id = '{$cacheKey}'";
    $cache_result = mysql_query($cache_query);
    if (!$cache_result) {
        returnError("Cannot complete cache retrieval query");
        exit (1);
    }
	
	if (mysql_num_rows($cache_result) == 1) {
        $cache_row = mysql_fetch_assoc($cache_result);
        mysql_free_result($cache_result);
	}
}

//files are not cached - go through build process
if (empty($cache_row)) {
    //build minified file
    $successExecuteMin = executeBuildScript($includes, $excludes, $uuid, $min);
    if (!$successExecuteMin)
    {
        returnError("Cannot execute build script");
        exit (1);
    }
    
    //create cache directory
    if (!file_exists($cachepath)) {
        if (!@mkdir($cachepath, 0755, true)) {
            returnError("Cannot create cache directory");
            exit(1);
        }
    }
    
    //copy file to cache directory
    if (!@copy($tmppath."/".$minfilename, $cachepath."/".$minfilename)) {
        returnError("Cannot copy minified files to cache");
        exit(1);
    }
    
    //build source file
    $successExecuteSrc = executeBuildScript($includes, $excludes, $uuid, $min);
    if (!$successExecuteSrc)
    {
        returnError("Cannot execute source build script");
        exit (1);
    }
    
    //copy source file to cache
    //copy file to cache directory
    if (!@copy($tmppath."/".$minfilename, $cachepath."/".$srcfilename)) {
        returnError("Cannot copy source files to cache");
        exit(1);
    }
        
    //insert cache entry for this build
    $insert_query = "INSERT INTO cache (id) VALUES('$cacheKey')";
    $insert_result = mysql_query($insert_query);
    if (!$insert_result) {
        returnError("Cannot insert cache entry for this build");
        exit(1);
    }
        
}

//deliver file from cache location to user
$filename = $min ? $minfilename : $srcfilename;
$filepath = $cachepath."/".$filename;
$successDeliver = deliverBuildFile($cacheKey, $filepath, $filename);
if (!$successDeliver)
{
    returnError("Cannot deliver file");
}
mysql_close($link);
?>
