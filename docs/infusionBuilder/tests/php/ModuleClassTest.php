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
 * Tests non-trivial module class functions.
 */
require_once (TESTCASE_PATH.'../../../../infusionBuilder-secure/php/ModuleClass.php');

class TestModuleClass extends UnitTestCase
{

    /**
     * Test using an array input
     */
    function testSetDependencies()
    {
        $inputArray = array("dependency1", "dependency2");
        $moduleObject = new ModuleClass("module");
        $expectedArray = array("dependency1", "dependency2");
        $moduleObject->setDependencies($inputArray);
        $dependencies = $moduleObject->getDependencies();
        $this->assertEqual(count($expectedArray), count($dependencies));
        foreach ($expectedArray as $expectedDependency)
        {
            $this->assertTrue(in_array($expectedDependency, $dependencies));
        }
   }
  
      /**
     * Test using a single string
     */
    function testSetDependencies2()
    {
        $inputArray = "dependency1";
        $moduleObject = new ModuleClass("module");
        $expectedArray = array("dependency1");
        $moduleObject->setDependencies($inputArray);
        $dependencies = $moduleObject->getDependencies();
        $this->assertEqual(count($expectedArray), count($dependencies));
        foreach ($expectedArray as $expectedDependency)
        {
            $this->assertTrue(in_array($expectedDependency, $dependencies));
        }
   }

      /**
     * Test using a null string
     */
    function testSetDependencies3()
    {
        $inputArray = null;
        $moduleObject = new ModuleClass("module");
        $expectedArray = array();
        $moduleObject->setDependencies($inputArray);
        $dependencies = $moduleObject->getDependencies();
        $this->assertEqual(count($expectedArray), count($dependencies));
        foreach ($expectedArray as $expectedDependency)
        {
            $this->assertTrue(in_array($expectedDependency, $dependencies));
        }
   }
 
   


}
?>
