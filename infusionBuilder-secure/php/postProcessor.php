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
    $command .= " && ant customBuild";
    if ($incl) $command .= " -Dinclude=\"".$incl."\"";
    if ($excl) $command .= " -Dexclude=\"".$excl."\"";
    $command .= " -Dproducts=\"".OUTPUT_FILE_PATH_PRODUCTS.$unique_dir."\"";
    $command .= " -Dbuild=\"".OUTPUT_FILE_PATH_BUILD.$unique_dir."\"";

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

function executeBuildScript($includeString, $excludeString, $uuid)
{
	$antCommand = buildAntCommand($includeString, $excludeString, $uuid);
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
function deliverBuildFile($ver, $min, $key, $not_cached, $uuid)
{
	$minfilename = "infusion-".$ver.".zip";
    $srcfilename = "infusion-".$ver."-src.zip";
	
    $mincachepath = CACHE_FILE_PATH.$key."/".$minfilename;
    $srccachepath = CACHE_FILE_PATH.$key."/".$srcfilename;
	if ($not_cached) {
        if (!file_exists(CACHE_FILE_PATH.$key)) {
        	if (!@mkdir(CACHE_FILE_PATH.$key, 0755, true)) return false;
		}
        $minfilepath = OUTPUT_FILE_PATH_PRODUCTS.$uuid."/".$minfilename;
        if (!@copy($minfilepath, $mincachepath)) return false;

        $srcfilepath = OUTPUT_FILE_PATH_PRODUCTS.$uuid."/".$srcfilename;
        if (!@copy($srcfilepath, $srccachepath)) return false;  
		
		if (!empty($key)) {
            $query = "INSERT INTO cache (id) VALUES('$key')";
            $result = mysql_query($query);
            if (!$result) return false;
		}
	}
	if (!empty($key)) {
	   $query = "UPDATE cache SET counter = counter + 1 WHERE id='".$key."'";
       $result = mysql_query($query);
       if (!$result) return false;
	}
	
	$filename = $min ? $minfilename : $srcfilename;
    $filepath = $min ? $mincachepath : $srccachepath;
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

//process posted values
$postVariables = new PostClass();
$successPost = processPostVariables($postVariables);
if (!$successPost)
{
    returnError("Cannot process input variables");
    exit (1);
}

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

//get UUID
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

$cacheKey = $postVariables->getKey();
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

if (empty($cache_row)) {
		
    $successExecute = executeBuildScript($postVariables->getIncludes(), $postVariables->getExcludes(), $uuid);
    if (!$successExecute)
    {
        returnError("Cannot execute build script");
        exit (1);
    }
}
$successDeliver = deliverBuildFile($postVariables->getFluidVersionNumber(), $postVariables->getMinified(), $cacheKey, empty($cache_row), $uuid);
if (!$successDeliver)
{
    returnError("Cannot deliver file");
}
mysql_close($link);


?>
