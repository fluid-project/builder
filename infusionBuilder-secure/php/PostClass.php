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

define("SOURCE", "source");
define("MINIFIED", "minified");
define("SELECTION_CHOICE", "typeSelections");
define("MODULE_SELECTIONS", "moduleSelections");

class PostClass
{
    private $minified = true;
    private $includes = "";
    private $excludes = "";
	private $module_keys = array();
	private $fluidVersionNumber = "";
	private $key = "";


    /**
     * Extracts and validates the posted minified field.
     *
     * @return false if the field is SOURCE, otherwise true
     * @param object $model The data model which should contain a property SELECTION_CHOICE
     */
    public function validateMinified($minifiedString)
    {
        //for minified no validation required - it is either SOURCE or we default to MINIFIED
        //any attempt to hack will be ignored, including if the value is missing
        if ($minifiedString == SOURCE)
        {
            $this->minified = false;
        }
        return $this->minified;
    }

/**
 * Gets the Fluid Version number from the build properties file
 */
    private function retrieveFluidVersionNumber() {
        $buildPropertiesContents = @file_get_contents(BUILD_PROPS);
        if ($buildPropertiesContents == false) return;
        preg_match('/fluid_version = (.*)/', $buildPropertiesContents, $version);
 
        $this->fluidVersionNumber = trim($version[1]);
    }

/**
 * Generates the cache key which is a concatenation of the module indexes (delimited by underscores) and
 * the fluid infusion version number. Sets $this->key to the resulting string.
 * 
 */
    private function generateCacheKey() {
    	//create module index code
        sort($this->module_keys);
    	$moduleIndexes = implode($this->module_keys, '_');
        if ($moduleIndexes == "") return "";
        
        $this->key = $moduleIndexes."_".$this->getFluidVersionNumber();
    }
	
    /**
     * Extracts and validates the posted selected modules. Also as a side effect, the selected 
     * module indexes are recorded and a key is generated for storing in the cache.
     *
     * @return A string containing comma separated module names or an empty string if no valid choices.
     * @param object $model The data model which should contain a property "selectionChoice"
     */
    function validateIncludes($moduleString, $valid)
    {
        if ($moduleString != null)
        {
            $moduleArray = preg_split("/,/",$moduleString);

            //check that values are valid by comparing to array of valid modules
            //at same time create an array with module keys to use for caching system
            $this->module_keys = array();
            foreach ($moduleArray as $selection)
            {
            	$array_key = array_search($selection, $valid);
                if (gettype($array_key) == 'integer' && $array_key >= 0)
                {
                    $this->module_keys[] = $array_key; 
                    $this->includes .= $selection.", ";
                 }
            }
            $this->includes = trim($this->includes, ", ");
        }
		$this->generateCacheKey();
        return $this->includes;
    }


    public function getMinified()
    {
        return $this->minified;
    }

    public function getIncludes()
    {
        return $this->includes;
    }

    public function getExcludes()
    {
        return $this->excludes;
    }
	
	public function getKey()
	{
		return $this->key;
	}
	
	public function getFluidVersionNumber() 
	{		
		if ($this->fluidVersionNumber == "") {
			$this->retrieveFluidVersionNumber();
		}
		return $this->fluidVersionNumber;
	}
}

?>
