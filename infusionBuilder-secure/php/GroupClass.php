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
 * Encapsulates group info.
 */

include_once("BuilderUtilities.php");

class GroupClass
{
    private $id; //id or "short" name of group
    private $name = ""; //verbose name of group
    private $description = ""; //verbose description of group
    private $modules = array(); //array of module names (strings) which are part of this group

    public function GroupClass($inputId)
    {
        $this->id = $inputId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }
    
    public function getModules() {
        return $this->modules;
    }


/**
 * Apply's the regular expression to the string, splits the result on = and returns the
 * string the the right of the = sign.
 * 
 * @param object $regularExpression
 * @param object $inputString
 */    
    private function applyRegex($regularExpression, $inputString)
    {
        if (!preg_match($regularExpression, $inputString, $match)) return "";
        $tempArray = preg_split(GROUP_SPLITTER, $match[0]);
        return $tempArray[1];
    }

/**
 * Set's the group name by extracting the information from a string.
 * The input string hopefully contains at least one line of text which is of the form: 
 * group_name-components=Infusion Component Modules - where
 * name is the text to the right of the = sign.
 * 
 * @param object $regularExpression
 * @param object $inputString
 */
    public function setName($regularExpression, $inputString)
    {
        $this->name = $this->applyRegex($regularExpression, $inputString);
    }

/**
 * Set's the group description by extracting the information from a string.
 * The input string hopefully contains at least one line of text which is of the form: 
 * group_description-components=Fluid Infusion components - where
 * description is the text to the right of the = sign.
 * 
 * @param object $regularExpression
 * @param object $inputString
 */    public function setDescription($regularExpression, $inputString)
    {
        $this->description = $this->applyRegex($regularExpression, $inputString);
    }
    
    public function setModules($regularExpression, $inputString) 
    {
        if (!preg_match_all($regularExpression, $inputString, $matches)) return;
        foreach ($matches[0] as $moduleProperty) {
            $tempArray = preg_split(MODULES_SPLITTER, $moduleProperty);
            $this->modules[] = $tempArray[1];
        }
    }
}

?>
