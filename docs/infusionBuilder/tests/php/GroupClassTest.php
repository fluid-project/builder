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
 * Tests non-trivial group class functions.
 */
require_once (TESTCASE_PATH.'../../../../infusionBuilder-secure/php/GroupClass.php');

class TestGroupClass extends UnitTestCase
{

    /**
     * Tests the setName function
     */
    private $actualTestString = "fluid_version = 1.2-SNAPSHOT group_id=framework group_name-framework=Infusion Framework Modules
         group_description-framework=The core Infusion modules
         group_id=components group_name-components=Infusion Component Modules group_description-components=Fluid Infusion components group_id=lib group_name-lib=Third Party Modules group_description-lib=Third party javascript libraries required by Infusion components module_framework=framework/core module_fss=framework/fss module_fssReset=framework/fss module_fssLayout=framework/fss module_fssText=framework/fss module_fssThemes=framework/fss module_renderer=framework/renderer module_inlineEdit=components/inlineEdit module_pager=components/pager module_progress=components/progress module_reorderer=components/reorderer module_tableOfContents=components/tableOfContents module_uiOptions=components/uiOptions module_undo=components/undo module_uploader=components/uploader module_fastXmlPull=lib/fastXmlPull module_json=lib/json module_jQuery=lib/jquery/core module_jQueryUICore=lib/jquery/ui module_jQueryUIWidgets=lib/jquery/ui module_jQueryDelegatePlugin=lib/jquery/plugins/delegate module_jQueryTooltipPlugin=lib/jquery/plugins/tooltip module_jQuerybgiframePlugin=lib/jquery/plugins/bgiframe module_swfupload=lib/swfupload module_swfobject=lib/swfobject yuicompressor=lib/yuicompressor-2.3.3.jar jslint=lib/jslint4java-1.1+rhino.jar excludeFromJSLint=**/lib*/ ";

    /**
     * Test using the real build.properties string (above)
     */
    function testSetName()
    {
        $groupObject = new GroupClass("framework");
        $regularExpression = '/group_name-framework=[\w ]*/';
        $groupObject->setName($regularExpression, $this->actualTestString);
        $expectedName = "Infusion Framework Modules";
        $this->assertEqual($groupObject->getName(), $expectedName);
    }

    /**
     * Test with empty string.
     */
    function testSetName2()
    {
        $testString = "";
        $groupObject = new GroupClass("framework");
        $regularExpression = '/group_name-framework=[\w ]*/';
        $groupObject->setName($regularExpression, $testString);
        $expectedName = "";
        $this->assertEqual($groupObject->getName(), $expectedName);
    }

    /**
     * Test where string is null.
     */
    function testSetName3()
    {
        $testString = null;
        $groupObject = new GroupClass("framework");
        $regularExpression = '/group_name-framework=[\w ]*/';
        $groupObject->setName($regularExpression, $testString);
        $expectedName = "";
        $this->assertEqual($groupObject->getName(), $expectedName);
    }

    /**
     * Test using the actual build.properties string.
     */
    function testSetDescription()
    {
        $groupObject = new GroupClass("framework");
        $regularExpression = '/group_description-framework=[\w ]*/';
        $groupObject->setDescription($regularExpression, $this->actualTestString);
        $expectedDescription = "The core Infusion modules";
        $this->assertEqual($groupObject->getDescription(), $expectedDescription);
    }

    /**
     * Test with empty string.
     */
    function testSetDescription2()
    {
        $testString = "";
        $groupObject = new GroupClass("framework");
        $regularExpression = '/group_description-framework=[\w ]*/';
        $groupObject->setDescription($regularExpression, $testString);
        $expectedDescription = "";
        $this->assertEqual($groupObject->getDescription(), $expectedDescription);
    }

    /**
     * Test with null string.
     */
    function testSetDescription3()
    {
        $testString = null;
        $groupObject = new GroupClass("framework");
        $regularExpression = '/group_description-framework=[\w ]*/';
        $groupObject->setDescription($regularExpression, $testString);
        $expectedDescription = "";
        $this->assertEqual($groupObject->getDescription(), $expectedDescription);
    }

/**
 * Test with actual build.properties string.
 */
    function testSetModules()
    {
        $groupObject = new GroupClass("framework");
        $regularExpression = '/module_[\w]*=framework\/[\w]*/';
        $groupObject->setModules($regularExpression, $this->actualTestString);
        $expectedModuleArray = array ("framework", "fss", "fssReset", "fssLayout", "fssText", "fssThemes", "renderer");
        $actualModules = $groupObject->getModules();
        $this->assertEqual(count($expectedModuleArray), count($actualModules));
        foreach ($expectedModuleArray as $expectedModule)
        {
            $this->assertTrue(in_array($expectedModule, $actualModules));
        }
    }

/**
 * Test with empty string.
 */
    function testSetModules2()
    {
        $testString = "";
        $groupObject = new GroupClass("framework");
        $regularExpression = '/module_[\w]*=framework\/[\w]*/';
        $groupObject->setModules($regularExpression, $testString);
        $expectedModuleArray = array ();
        $actualModules = $groupObject->getModules();
        $this->assertEqual(count($expectedModuleArray), count($actualModules));
        foreach ($expectedModuleArray as $expectedModule)
        {
            $this->assertTrue(in_array($expectedModule, $actualModules));
        }
    }

/**
 * Test with null string.
 */
    function testSetModules3()
    {
        $testString = null;
        $groupObject = new GroupClass("framework");
        $regularExpression = '/module_[\w]*=framework\/[\w]*/';
        $groupObject->setModules($regularExpression, $testString);
        $expectedModuleArray = array ();
        $actualModules = $groupObject->getModules();
        $this->assertEqual(count($expectedModuleArray), count($actualModules));
        foreach ($expectedModuleArray as $expectedModule)
        {
            $this->assertTrue(in_array($expectedModule, $actualModules));
        }
    }

}
?>
