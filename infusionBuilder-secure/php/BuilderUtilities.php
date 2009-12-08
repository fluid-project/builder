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
 * Provides testable utility functions for the infusion builder.
 */

define("MODULES_REGEX", "/module_[\w]*=[\w\/]*/");
define("MODULES_SPLITTER", "/_|=/");
define("GROUPID_REGEX", "/group_id=[A-Za-z]*/");
define("GROUP_SPLITTER", "/=/");
 
include_once ("config.php");
include_once ("GroupClass.php");

/**
 * Returns a 400 Bad Request Error with the html body containing the error string specified.
 *
 * @param object $errString The error string to output.
 */
function returnError($errString)
{
    header("HTTP/1.0 400 Bad Request");
    header("Status: 400");
    echo "<html><body><p>".$errString."</p></body></html>";
}

/**
 * Uses regular expressions to extract module names from the build.properties file
 * 
 * @return An array of all module names for the infusion builder process.
 * @param object $inputString String representation of the build.properties file
 */

function createModuleArrayFromString($inputString)
{
	$moduleArray = null;
    preg_match_all(MODULES_REGEX, $inputString, $modulePropertiesArray);
    foreach ($modulePropertiesArray[0] as $modules)
    {
        $tempArray = preg_split(MODULES_SPLITTER, $modules);
        $moduleArray[] = $tempArray[1];
    }
    return $moduleArray;
}

/**
 * Retrieves the build properties file, parses out lines which start with "module_", pulls out
 * the module names from each line and puts it into an array.
 *
 * @return null if the file is not found or no lines are found in the file,
 * otherwise an array of modules.
 */
function retrieveModuleList()
{
    $buildPropertiesContents = @file_get_contents(BUILD_PROPS);
    if ($buildPropertiesContents == false) {
        return;    	
    }
        
    return createModuleArrayFromString($buildPropertiesContents);
}

/**
 * Retrieves the group id's from the build.properties file
 * 
 * @return returns an array of group id's
 * @param object $inputString A string representing the build.properties file contents
 */
function getGroupIdArray($inputString)
{
    preg_match_all(GROUPID_REGEX, $inputString, $groupIdProperties);
    $groupIdArray = null;
    foreach ($groupIdProperties[0] as $groupIdProperty)
    {
        $tempArray = preg_split(GROUP_SPLITTER, $groupIdProperty);
        $groupId = $tempArray[1];
        $groupIdArray[] = new GroupClass($groupId);
    }

    return $groupIdArray;
}


?>
