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
 * Gathers the JSON files and concatenates them into the form required for the Infusion Builder
 */

include_once ("config.php");
include_once ("BuilderUtilities.php");
include_once ("GroupClass.php");
include_once ("ModuleClass.php");

if (! isset ($_GET["var"]))
{
    returnError("Cannot process input variables");
    exit (1);
}

$varName = $_GET["var"];

//get file contents of build.properties
$buildPropertiesContents = file_get_contents(BUILD_PROPS);
if ($buildPropertiesContents == false)
{
    returnError("Cannot open build file");
    exit (1);
}

//extract the group information from the build.properties string
$groupIdArray = getGroupIdArray($buildPropertiesContents);
$groupArray = array();
if ($groupIdArray != null) 
{
    foreach ($groupIdArray as $groupObject)
    {
        $groupId = $groupObject->getId();
        $name_regex = '/group_name-'.$groupId.'=[\w ]*/';
        $groupObject->setName($name_regex, $buildPropertiesContents);
        
        $description_regex = '/group_description-'.$groupId.'=[\w ]*/';
        $groupObject->setDescription($description_regex, $buildPropertiesContents);
        
        $dependencies_regex = '/module_[\w]*='.$groupId.'\/[\w]*/';
        $groupObject->setModules($dependencies_regex, $buildPropertiesContents);
        
        $groupArray[] = array("groupName"=>$groupObject->getName(), 
            "groupDescription"=>$groupObject->getDescription(),
            "groupModules"=>$groupObject->getModules());
    }
}

//extract the module information from the build.properties string
$moduleArray = array();
preg_match_all(MODULES_REGEX, $buildPropertiesContents, $modulePropertiesArray);
foreach ($modulePropertiesArray[0] as $moduleProperty)
{
    $tempArray = preg_split(MODULES_SPLITTER, $moduleProperty);      
    $moduleValue = $tempArray[1];
    
    $moduleObject = new ModuleClass($moduleValue);
    $jsonPath = $tempArray[2].'/'.$moduleValue.JSON_SUFFIX;
    $jsonContent = json_decode(file_get_contents(BUILD_FILES.$jsonPath), true);
    
    //note that it is not really necessary to store the module information
    // in the module object - it is under used - however for dependencies it
    // does simplify the code just a little.
    $moduleObject->setName($jsonContent[$moduleValue]["name"]);
    $moduleName = $moduleObject->getName();
    $moduleObject->setDescription($jsonContent[$moduleValue]["description"]);
    $moduleDescription = $moduleObject->getDescription();
    $moduleObject->setDependencies($jsonContent[$moduleValue]["dependencies"]);     
    $moduleDependencies = $moduleObject->getDependencies();

    $moduleArray[] = array("moduleValue"=>$moduleValue,
        "moduleName"=>$moduleName, 
        "moduleDescription"=>$moduleDescription,
        "moduleDependencies"=>$moduleDependencies);

}

/**
 * A function to save the json data to a file so that this code does not have to be run every time.
 * 
 * @param object $data, the data to save in the file
 * @param object $file, the name and path of the file
 * 
 */
function cacheOutput($data, $file)
{
    $filePointer = fopen($file, "w");
    if ($filePointer == false)
    {
        returnError("Cannot open cache file");
        exit (1);
    }
    else {
        $writeSuccess = fwrite($filePointer, $data);
		if ($writeSuccess == false) {
			returnError("Cannot write to cache file");
            exit(1);
		}
		fclose($filePointer);
	}
}

if (file_exists(CACHE_FILE))
{
    $setBeanValueCommand = file_get_contents(CACHE_FILE);
}
else
{
    //create the json information from the group and module arrays
    $modelArray = array("model"=>array("groupInfo"=>$groupArray, "moduleInfo"=>$moduleArray));

    $outputJson = json_encode($modelArray);
    $setBeanValueCommand = "fluid.model.setBeanValue(window, \"".$varName."\", ".$outputJson.")";
    cacheOutput($setBeanValueCommand, CACHE_FILE);
}

echo $setBeanValueCommand;
?>
