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
 * Tests utility functions for the infusion builder.
 */
include_once ("../../../../infusionBuilder-secure/php/config.php");
require_once (SIMPLETEST_PATH.'/simpletest/autorun.php');
require_once ('../../../../infusionBuilder-secure/php/BuilderUtilities.php');


class TestBuilderUtilities extends UnitTestCase
{

    private $actualTestString = "fluid_version = 1.2-SNAPSHOT group_id=framework group_name-framework=Infusion Framework Modules group_description-framework=The core Infusion modules group_id=components group_name-components=Infusion Component Modules group_description-components=Fluid Infusion components group_id=lib group_name-lib=Third Party Modules group_description-lib=Third party javascript libraries required by Infusion components module_framework=framework/core module_fss=framework/fss module_fssReset=framework/fss module_fssLayout=framework/fss module_fssText=framework/fss module_fssThemes=framework/fss module_renderer=framework/renderer module_inlineEdit=components/inlineEdit module_pager=components/pager module_progress=components/progress module_reorderer=components/reorderer module_tableOfContents=components/tableOfContents module_uiOptions=components/uiOptions module_undo=components/undo module_uploader=components/uploader module_fastXmlPull=lib/fastXmlPull module_json=lib/json module_jQuery=lib/jquery/core module_jQueryUICore=lib/jquery/ui module_jQueryUIWidgets=lib/jquery/ui module_jQueryDelegatePlugin=lib/jquery/plugins/delegate module_jQueryTooltipPlugin=lib/jquery/plugins/tooltip module_jQuerybgiframePlugin=lib/jquery/plugins/bgiframe module_swfupload=lib/swfupload module_swfobject=lib/swfobject yuicompressor=lib/yuicompressor-2.3.3.jar jslint=lib/jslint4java-1.1+rhino.jar excludeFromJSLint=**/lib*/ ";
    /**
     * Tests createModuleArrayFromString on the actual build.properties contents (above)
     */
    function testCreateModuleArrayFromString1()
    {
        $expectedModuleArray = array ("framework", "fss", "fssReset", "fssLayout", "fssText", "fssThemes", "renderer", "inlineEdit", "pager", "progress", "reorderer", "tableOfContents", "uiOptions", "undo", "uploader", "fastXmlPull", "json", "jQuery", "jQueryUICore", "jQueryUIWidgets", "jQueryDelegatePlugin", "jQueryTooltipPlugin", "jQuerybgiframePlugin", "swfupload", "swfobject");
        $actualModuleArray = createModuleArrayFromString($this->actualTestString);
        $moduleCount = count($expectedModuleArray);
        $this->assertEqual($moduleCount, count($actualModuleArray));
        for ($i = 0; $i < $moduleCount; $i++)
        {
            $this->assertEqual($expectedModuleArray[$i], $actualModuleArray[$i]);
        }

    }

    /**
     * Tests createModuleArrayFromString on an empty string
     */
    function testCreateModuleArrayFromString2()
    {
        $this->assertNull($actualModuleArray = createModuleArrayFromString(""));
    }

    private $actualTestString2 = "fluid_version = 1.2-SNAPSHOT group_id=framework group_name-framework=Infusion Framework Modules group_description-framework=The core Infusion modules group_id=components group_name-components=Infusion Component Modules group_description-components=Fluid Infusion components group_id=lib group_name-lib=Third Party Modules group_description-lib=Third party javascript libraries required by Infusion components module_framework=framework/core jslint=lib/jslint4java-1.1+rhino.jar excludeFromJSLint=**/lib*/ ";
/**
 * Test on an empty string
 */
    function testCreateModuleArrayFromString3()
    {
        $expectedModuleArray = array ("framework");
        $actualModuleArray = createModuleArrayFromString($this->actualTestString2);
        $moduleCount = count($expectedModuleArray);
        $this->assertEqual($moduleCount, count($actualModuleArray));
        for ($i = 0; $i < $moduleCount; $i++)
        {
            $this->assertEqual($expectedModuleArray[$i], $actualModuleArray[$i]);
        }
    }

/**
 * Tests using actual build.properties contents
 */
    function testGetGroupIdArray()
    {
        $frameworkGroup = new GroupClass("framework");
        $componentsGroup = new GroupClass("components");
        $libGroup = new GroupClass("lib");
        $expectedGroupIdArray = array ($frameworkGroup, $componentsGroup, $libGroup);
        $groupIdCount = count($expectedGroupIdArray);

        $actualGroupIdArray = getGroupIdArray($this->actualTestString);
        $this->assertEqual($groupIdCount, count($actualGroupIdArray));
        foreach ($expectedGroupIdArray as $expectedGroupIdObject)
        {
            $groupId = $expectedGroupIdObject->getId();
            $found = false;
            foreach ($actualGroupIdArray as $actualGroupIdObject)
            {
                if ($actualGroupIdObject->getId() == $groupId)
                {
                    $found = true;
                    $this->assertEqual("", $actualGroupIdObject->getName());
                    $this->assertEqual("", $actualGroupIdObject->getDescription());
                }
            }
            if (!$found) fail();
        }
    }


    private $actualTestString3 = "fluid_version = 1.2-SNAPSHOT module_framework=framework/core module_fss=framework/fss module_fssReset=framework/fss module_fssLayout=framework/fss module_fssText=framework/fss module_fssThemes=framework/fss module_renderer=framework/renderer module_inlineEdit=components/inlineEdit module_pager=components/pager module_progress=components/progress module_reorderer=components/reorderer module_tableOfContents=components/tableOfContents module_uiOptions=components/uiOptions module_undo=components/undo module_uploader=components/uploader module_fastXmlPull=lib/fastXmlPull module_json=lib/json module_jQuery=lib/jquery/core module_jQueryUICore=lib/jquery/ui module_jQueryUIWidgets=lib/jquery/ui module_jQueryDelegatePlugin=lib/jquery/plugins/delegate module_jQueryTooltipPlugin=lib/jquery/plugins/tooltip module_jQuerybgiframePlugin=lib/jquery/plugins/bgiframe module_swfupload=lib/swfupload module_swfobject=lib/swfobject yuicompressor=lib/yuicompressor-2.3.3.jar jslint=lib/jslint4java-1.1+rhino.jar excludeFromJSLint=**/lib*/ ";
/**
 * Tests using a string that does not contain any group information
 */
    function testGetGroupIdArray2()
    {
        $this->assertNull(getGroupIdArray($this->actualTestString3));
    }

/**
 * Tests using a null string.
 */
    function testGetGroupIdArray3()
    {
        $this->assertNull(getGroupIdArray(""));
    }

}
?>
