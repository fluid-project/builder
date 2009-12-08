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
 * Encapsulates module info.
 */

class ModuleClass
{
    private $value; //basically a non-null id
    private $name = ""; //verbose name 
    private $description = ""; //verbose description
    private $dependencies = array(); //an array of module names as dependencies

    public function ModuleClass($inputValue)
    {
        $this->value= $inputValue;
    }

    public function getValue()
    {
        return $this->value;
    }
    
    public function setName($inputName) {
        $this->name = $inputName;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setDescription($inputDescription) {
        $this->description = $inputDescription;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
/**
 * Handles the case where the dependency list is an array or a single element
 * 
 * @param object $inputDependencies
 */
    public function setDependencies($inputDependencies) {
        if (is_array($inputDependencies)) $this->dependencies = $inputDependencies;
        elseif ($inputDependencies) $this->dependencies[] = $inputDependencies;
    }
    
    public function getDependencies() {
        return $this->dependencies;
    }

}

?>
