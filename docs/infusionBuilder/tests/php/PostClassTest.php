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
require_once (TESTCASE_PATH.'../../../../infusionBuilder-secure/php/BuilderUtilities.php');
require_once (TESTCASE_PATH.'../../../../infusionBuilder-secure/php/PostClass.php');

class TestPostClass extends UnitTestCase
{

    private $postVariables;

    /**
     * Currently no setup required
     */
    function setUp()
    {
        $this->postVariables = new PostClass();
    }

    /**
     * Currently no teardown required
     */
    function tearDown()
    {
        unset ($this->postVariables);
    }


    /**
     * Tests the validateMinified function
     */
    function testValidateMinified1()
    {
        $min = MINIFIED;
        $this->assertTrue($this->postVariables->validateMinified($min));
    }

    function testValidateMinified2()
    {
        $min = "stuff";
        $this->assertTrue($this->postVariables->validateMinified($min));
    }

    function testValidateMinified3()
    {
        $min = "";
        $this->assertTrue($this->postVariables->validateMinified($min));
    }

    function testValidateMinified4()
    {
        $min = "&*(&^&*(^";
        $this->assertTrue($this->postVariables->validateMinified($min));
    }
    function testValidateMinified5()
    {
        $min = SOURCE;
        $this->assertFalse($this->postVariables->validateMinified($min));
    }

    /**
     * Tests the validateIncludes function
     */
    function testValidateIncludes1()
    {
        $test = "Renderer,jQuery,framework,fastXmlPull";
        $expectedIncludes = "jQuery, framework, fastXmlPull";
        $expectedKey = "0_14_17_".$this->postVariables->getFluidVersionNumber();
        
        $this->assertEqual($this->postVariables->validateIncludes($test, retrieveModuleList()), $expectedIncludes);
        $this->assertEqual($this->postVariables->getKey(), $expectedKey);
    }

    function testValidateIncludes2()
    {
        $test = "";
        $expectedIncludes = "";
        $expectedKey = "";
        $this->assertEqual($this->postVariables->validateIncludes($test, retrieveModuleList()), $expectedIncludes);
        $this->assertEqual($this->postVariables->getKey(), $expectedKey);
    }

    function testValidateIncludes3()
    {
        $test = "Renderer,jQuery,framework,fastXmlPull";
        $expectedIncludes = "jQuery, framework, fastXmlPull";
        $expectedKey = "0_14_17_".$this->postVariables->getFluidVersionNumber();
        $this->assertEqual($this->postVariables->validateIncludes($test, retrieveModuleList()), $expectedIncludes);
        $this->assertEqual($this->postVariables->getKey(), $expectedKey);
   }

    function testValidateIncludes4()
    {
        $test = " && cd hack";
        $expectedIncludes = "";
        $expectedKey = "";
        $this->assertEqual($this->postVariables->validateIncludes($test, retrieveModuleList()), $expectedIncludes);
        $this->assertEqual($this->postVariables->getKey(), $expectedKey);
    }

    function testValidateIncludes5()
    {
        $test = "framework,jQuery,jQueryUICore";
        $expectedIncludes = "framework, jQuery, jQueryUICore";
        $expectedKey = "0_17_18_".$this->postVariables->getFluidVersionNumber();
        $this->assertEqual($this->postVariables->validateIncludes($test, retrieveModuleList()), $expectedIncludes);
        $this->assertEqual($this->postVariables->getKey(), $expectedKey);
    }	
    
    function testValidateIncludes6()
    {
        $test = "framework";
        $expectedIncludes = "framework";
        $expectedKey = "0_".$this->postVariables->getFluidVersionNumber();
        $this->assertEqual($this->postVariables->validateIncludes($test, retrieveModuleList()), $expectedIncludes);
        $this->assertEqual($this->postVariables->getKey(), $expectedKey);
    }
}
?>
