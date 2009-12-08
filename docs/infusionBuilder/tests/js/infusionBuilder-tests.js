/*
 Copyright 2008-2009 University of Toronto
 
 Licensed under the Educational Community License (ECL), Version 2.0 or the New
 BSD license. You may not use this file except in compliance with one these
 Licenses.
 
 You may obtain a copy of the ECL 2.0 License and BSD License at
 https://source.fluidproject.org/svn/LICENSE.txt
 */
/*global fluid, jQuery, jqUnit, expect  */

(function ($) {

    /** UTILITY FUNCTIONS
     *
     * A function to encapsulate the utility functions used in this test. Mostly
     * useful for code navigation and not absolutely necessary
     */
    var defineUtilityFunctions = function () {
        var that = {};
        /**
         * Returns true if the selectedValues are in the set of allValues
         * and passes the test specified in func
         *
         * @param {Object} selectedValues, the set of selected values
         * @param {Object} allValues, the complete set of all possible values
         * @param {Object} func, a function to test the selected values against
         * should have the signature (index) and return a boolean of true for a passing test
         * and false otherwise
         */
        that.testSelections = function (selectedValues, allValues, func) {
            for (var i = 0; i < selectedValues.length; i++) {
                var index = $.inArray(selectedValues[i], allValues);
                if ((index === -1) || (!func(index))) {
                    return false;
                }
            }
            return true;
        };
        
        /**
         * Concatenates arrayOne and arrayTwo and returns the result in arrayOne
         * arrayTwo is not impacted.
         *
         * The elements of arrayOne are preserved. elements of arrayTwo are added
         * to arrayOne only if they are not duplicates of elements already in arrayOne.
         *
         * @param {Object} arrayOne, the first array - returned with added elements
         * @param {Object} arrayTwo, the second array - does not change
         *
         */
        that.concatUnique = function (arrayOne, arrayTwo) {
            for (var j = 0; j < arrayTwo.length; j++) {
                var element = arrayTwo[j];
                if ($.inArray(element, arrayOne) === -1) {
                    arrayOne.push(element);
                }
            }
            return arrayOne;
        };
        
        /**
         * Combines two arrays, while removing any duplicate values from each of the two arrays.
         * Returns a separate array and does not impact either of the input arrays
         *
         * @param {Object} arrayOne, the first array
         * @param {Object} arrayTwo, the second array
         */
        that.combineArrays = function (arrayOne, arrayTwo) {
        
            var array = [];
            that.concatUnique(array, arrayOne);
            that.concatUnique(array, arrayTwo);
            return array;
            
        };
        
        /**
         * Generates what the model should look like based on the new value added and the current model.
         *
         * @param {Object} value, the value to ammend the model with
         * @param {Object} valueArray, the set of all possible values
         * @param {Object} model, an array of values representing a model
         */
        that.generatedModel = function (value, valueArray, model) {
            var array = [value];
            var dependencies = model[$.inArray(value, valueArray)].moduleDependencies || [];
            
            for (var i = 0; i < dependencies.length; i++) {
                array = that.combineArrays(array, that.generatedModel(dependencies[i], valueArray, model));
            }
            
            return array;
        };
        
        /**
         * This function is similar to jQuery's "is",
         * except that instead of returning true if any of the elements fits,
         * it will return true if all of the elements fit the passed expression.
         *
         * @param {Object} selection, the set of elements
         * @param {Object} expr, an expression to test the elements against
         */
        that.isAll = function (selection, expr) {
            var fits = false;
            
            for (var i = 0; i < selection.length; i++) {
                fits = $(selection[i]).is(expr);
                if (!fits) {
                    return fits;
                }
            }
            
            return fits;
        };
        
        /**
         * Traverses through an array of objects returning an array of all the values for a specified key.
         *
         * @param {Object} array, an array of Objects to search through
         * @param {Object} key, the key for whose value to return from each object. Will return an empty string "",
         * if the key does not exist in the any of the objects.
         */
        that.extractArray = function (array, key) {
            return fluid.transform(array, function (object, index) {
                return object[key] || "";
            });
        };
        
        /**
         * Returns true if both arrays contain the same values, in any order
         *
         * @param {Object} arrayOne, the first array
         * @param {Object} arrayTwo, the second array
         */
        that.hasSameValues = function (arrayOne, arrayTwo) {
            var arrayLength = arrayOne.length;
            if (arrayLength !== arrayTwo.length) {
                return false;
            }
            
            for (var i = 0; i < arrayLength; i++) {
                if (($.inArray(arrayOne[i], arrayTwo) === -1) || ($.inArray(arrayTwo[i], arrayOne) === -1)) {
                    return false;
                }
            }
            
            return true;
        };
                
        return that;
    };
    
    //** variables and functions used for actual tests **/
    var testComponent;
    var INFUSION_BUILDER_SELECTOR = "#infusionBuilder";
    
    /**
     * Testing objects and functions
     *
     * @param {Object} data - the data on which the tests are being performed
     */
    var defineTestingFunctions = function (data) {
        var that = {};
        
        var utilityFunctions = defineUtilityFunctions();
        
        //determine expected values for rendering
        
        //set defaults for no groups case
        var numGroups = 1;
        var groupNamesArray = [""];
        var groupDescriptionArray = [""];
        var groupModulesArray = [""];
        
        //if groups exist set values for group info
        if (data.model.groupInfo.length > 0) {
            numGroups = data.model.groupInfo.length;
            groupNamesArray = utilityFunctions.extractArray(data.model.groupInfo, "groupName");
            groupDescriptionArray = utilityFunctions.extractArray(data.model.groupInfo, "groupDescription");
            
            //get group modules listing to compare with module listing
            var modulesArray = utilityFunctions.extractArray(data.model.groupInfo, "groupModules");
            groupModulesArray = [];
            for (var mods = 0; mods < modulesArray.length; mods++) {
                groupModulesArray = utilityFunctions.combineArrays(groupModulesArray, modulesArray[mods]);
            }
        }
        
        //set defaults for no modules case
        var numModules = 1;
        var moduleNamesArray = [""];
        var moduleDescriptionArray = [""];
        var moduleValuesArray = ["true"];
        
        //if modules exist set values for module info
        if (data.model.moduleInfo.length > 0) {
            numModules = data.model.moduleInfo.length;
            moduleNamesArray = utilityFunctions.extractArray(data.model.moduleInfo, "moduleName");
            moduleDescriptionArray = utilityFunctions.extractArray(data.model.moduleInfo, "moduleDescription");
            moduleValuesArray = utilityFunctions.extractArray(data.model.moduleInfo, "moduleValue");
            
            //if groups exist then change module info based on both group and module info
            if (groupModulesArray[0] !== "") {
                //if module is not in a group drop it
                for (var modss = moduleValuesArray.length - 1; modss >= 0; modss--) {
                    if ($.inArray(moduleValuesArray[modss], groupModulesArray) === -1) {
                        moduleValuesArray.splice(modss, 1);
                        moduleNamesArray.splice(modss, 1);
                        moduleDescriptionArray.splice(modss, 1);
                    }
                }
                //reset number of modules based on modified modules array
                numModules = moduleValuesArray.length;
            }
        }
        
        //if there are no modules but groups do exist reset module information
        else {
            if (groupModulesArray[0] !== "") {
                numModules = 0;
                moduleNamesArray = [];
                moduleDescriptionArray = [];
                moduleValuesArray = [];
            }
        }
                
        /**
         * Assertions for the selection of values
         *
         * @param {Object} expectedModel, the expected model
         * @param {Object} inputs, a jQuery representing the set of inputs
         * @param {Object} modules, a jQuery representing the set of modules
         */
        var selectionTests = function (expectedModel, inputs, modules) {
            jqUnit.assertTrue("Modules selected", utilityFunctions.testSelections(expectedModel, moduleValuesArray, function (index) {
                return inputs.eq(index).attr("checked");
            }));
            jqUnit.assertTrue("Selected modules styled", utilityFunctions.testSelections(expectedModel, moduleValuesArray, function (index) {
                return modules.eq(index).is(".fl-infusionBuilder-selected");
            }));
            jqUnit.assertEquals("Number of selected styled modules", expectedModel.length, $(".fl-infusionBuilder-selected").length);
            jqUnit.assertEquals("Number of modules selected", expectedModel.length, $(".flc-infusionBuilder-module input:checked").length);
            jqUnit.assertTrue("Model has selected values", utilityFunctions.hasSameValues(expectedModel, testComponent.model.moduleSelections));
        };
        
        /**
         * Checks the rendering of groups
         */
        var groupNameClass = ".flc-infusionBuilder-groupName";
        var groupDescriptionClass = ".flc-infusionBuilder-groupDescription";
        var renderedGroupsTester = {
            numGroupTests: 3 + numGroups,
            checkRenderedGroups: function () {
            
                //check that correct number of groups are rendered
                var numRenderedGroups = $(".flc-infusionBuilder-group").length;
                jqUnit.assertEquals("Number of groups", numGroups, numRenderedGroups);
                                
                //no descriptions are rendered at this time
                jqUnit.assertEquals("Description text", "", $(groupDescriptionClass).text());
                
                //check that correct number of group name fields are rendered
                var renderedGroupNamesArray = $(groupNameClass);
                var numRenderedGroupNames = renderedGroupNamesArray.length;
                jqUnit.assertEquals("Number of group name fields", numGroups, numRenderedGroupNames);
                for (var grpNum = 0; grpNum < numRenderedGroupNames; grpNum++) {
                    var renderedGroupName = renderedGroupNamesArray[grpNum];
                    jqUnit.assertEquals("Rendered group name - " + renderedGroupName, groupNamesArray[grpNum], $(renderedGroupName).text());
                }
            }
        };
        
        /**
         * Checks the rendering of the radio buttons for 'download type'
         */
        var renderedRadioButtonTester = {
            numRadioTests: 2,
            checkRenderedRadioButtons: function () {
                var numRadio = testComponent.options.model.typeInfo.length;
                
                //check that correct number of radio buttons are rendered
                var numRenderedRadioButtons = $("input:radio").length;
                jqUnit.assertEquals("Minified/Source selection input", numRadio, numRenderedRadioButtons);
                
                //check that correct radio button is rendered as selected 
                // currently the first radio button is always selected
                var firstRenderedRadioButton = $(".flc-infusionBuilder-selectionModifier input")[0];
                var numRenderedSelectedRadioButtons = $("input:radio:checked")[0];
                jqUnit.assertEquals("Minified selection selected", firstRenderedRadioButton, numRenderedSelectedRadioButtons);
            }
        };
        
        /**
         * Checks the rendering of the modules
         */
        var moduleNameClass = ".flc-infusionBuilder-inputLabel";
        var moduleDescriptionClass = ".flc-infusionBuilder-moduleDescription";
        var moduleCheckboxClass = ".flc-infusionBuilder-module input:checkbox";
        var moduleCheckboxCheckedClass = moduleCheckboxClass + ":checked";
        var renderedModulesTester = {
            numModuleTests: 5 + 3 * numModules,
            checkRenderedModules: function () {
                //check that correct numbers of module elements are rendered
                var renderedCheckboxArray = $(moduleCheckboxClass);
                var renderedModuleNameArray = $(moduleNameClass);
                var renderedModuleDescriptionArray = $(moduleDescriptionClass);
                
                var numRenderedCheckboxes = renderedCheckboxArray.length;
                var numRenderedModules = $(".flc-infusionBuilder-module").length;
                var numRenderedModuleNames = renderedModuleNameArray.length;
                var numRenderedModuleDescriptions = renderedModuleDescriptionArray.length;
                
                jqUnit.assertEquals("Number of checkboxes", numModules, numRenderedCheckboxes);
                jqUnit.assertEquals("Number of modules", numModules, numRenderedModules);
                jqUnit.assertEquals("Number of module name fields", numModules, numRenderedModuleNames);
                jqUnit.assertEquals("Number of module description fields", numModules, numRenderedModuleDescriptions);
                
                for (var numMod = 0; numMod < numModules; numMod++) {
                    var renderedCheckboxText = $(renderedCheckboxArray[numMod]).val();
                    var renderedModuleNameText = $(renderedModuleNameArray[numMod]).text();
                    var renderedModuleDescriptionText = $(renderedModuleDescriptionArray[numMod]).text();
                    
                    jqUnit.assertEquals("Rendered module checkbox  - " + renderedCheckboxText, moduleValuesArray[numMod], renderedCheckboxText);
                    jqUnit.assertEquals("Rendered module name - " + renderedModuleNameText, moduleNamesArray[numMod] + " " + moduleDescriptionArray[numMod], renderedModuleNameText);
                    jqUnit.assertEquals("Rendered module description - " + renderedModuleDescriptionText, moduleDescriptionArray[numMod], renderedModuleDescriptionText);
                }
                
                //check that no modules are rendered as selected
                var numberRenderedSelectedModules = $(moduleCheckboxCheckedClass).length;
                jqUnit.assertEquals("Number of selected modules", 0, numberRenderedSelectedModules);
                
            }
        };
        
        
        /**
         * Performs tests to ensure the input data is rendered as expected.
         */
        that.renderingTests = function () {
            var expectedAssertions = renderedGroupsTester.numGroupTests +
            renderedRadioButtonTester.numRadioTests +
            renderedModulesTester.numModuleTests;
            
            expect(expectedAssertions);
            
            renderedGroupsTester.checkRenderedGroups();
            renderedRadioButtonTester.checkRenderedRadioButtons();
            renderedModulesTester.checkRenderedModules();
        };
        
        /**
         * Performs tests to ensure 'select all' performs as expected
         */
        that.selectAllTests = function () {
            expect(4);
            $(".flc-infusionBuilder-checkAll").simulate("click");

            jqUnit.assertEquals("Modules checked", numModules, $(".flc-infusionBuilder-module input:checkbox:checked", INFUSION_BUILDER_SELECTOR).length);
            jqUnit.assertTrue("Selections in model", utilityFunctions.hasSameValues(utilityFunctions.extractArray(data.model.moduleInfo, "moduleValue"), testComponent.model.moduleSelections));
            jqUnit.assertTrue("Selected modules have selection styling", utilityFunctions.isAll($(".flc-infusionBuilder-module", INFUSION_BUILDER_SELECTOR), ".fl-infusionBuilder-selected"));
            jqUnit.assertTrue("Selection styling only on selected modules", utilityFunctions.isAll($(".fl-infusionBuilder-selected", INFUSION_BUILDER_SELECTOR), ".flc-infusionBuilder-module"));
        };
        
        /**
         * Performs tests to ensure 'deselect all' performs as expected
         */
        that.deselectAllTests = function () {
            expect(6);          
            $(".flc-infusionBuilder-unCheckAll").simulate("click");
            
            //ensure check boxes have been unchecked and model has been cleared
            jqUnit.assertEquals("Modules checked", 0, $(".flc-infusionBuilder-module input:checkbox:checked", INFUSION_BUILDER_SELECTOR).length);
            jqUnit.assertTrue("Selections in model", utilityFunctions.hasSameValues([], testComponent.model.moduleSelections));
            jqUnit.assertEquals("Modules with selection styling", 0, $(".fl-infusionBuilder-selected", INFUSION_BUILDER_SELECTOR).length);

            //ensure download type has been reset to minified.
            var expectedTypeModel = ["minified"];
            var element = $(".flc-infusionBuilder-selectionModifier input").eq(0);
            
            jqUnit.assertTrue("Minified selected", $(element).attr("checked"));
            jqUnit.assertEquals("Number of radio buttons selected", 1, $(".flc-infusionBuilder-selectionModifier input:checked").length);
            jqUnit.assertEquals("Model", expectedTypeModel, testComponent.model.typeSelections);
        };
        
        /**
         * Generates a function to test auto-checking of a single module.
         *
         * @param {Object} checkedModuleIndex The index of the module to be checked and then tested.
         */
        that.generateAutoCheckingTestFunc = function (checkedModuleIndex) {
            return function () {
                expect(5);
                
                var expectedModel = utilityFunctions.generatedModel(data.model.moduleInfo[checkedModuleIndex].moduleValue, moduleValuesArray, data.model.moduleInfo);
                var inputs = $(".flc-infusionBuilder-module input");
                var modules = $(".flc-infusionBuilder-module");
                var element = inputs.eq(checkedModuleIndex);
                element.simulate("click");
                selectionTests(expectedModel, inputs, modules);
            };
        };
        
        /**
         * Generates a function to test auto-checking of two modules.
         * This test assumes that the first and second modules are not dependent on each other, and
         * will likely fail if this is not the case.
         *
         * @param {Object} firstCheckedModuleIndex
         * @param {Object} secondCheckedModuleIndex
         */
        that.generateAutoCheckingTwoModulesTestFunc = function (firstCheckedModuleIndex, secondCheckedModuleIndex) {
            return function () {
                expect(5);
                
                var inputs = $(".flc-infusionBuilder-module input");
                var modules = $(".flc-infusionBuilder-module");
                var expectedModel = utilityFunctions.combineArrays(utilityFunctions.generatedModel(data.model.moduleInfo[firstCheckedModuleIndex].moduleValue, moduleValuesArray, data.model.moduleInfo), utilityFunctions.generatedModel(data.model.moduleInfo[secondCheckedModuleIndex].moduleValue, moduleValuesArray, data.model.moduleInfo));
                var elementFirst = inputs.eq(firstCheckedModuleIndex);
                var elementSecond = inputs.eq(secondCheckedModuleIndex);
                
                elementFirst.simulate("click");
                elementSecond.simulate("click");
                
                selectionTests(expectedModel, inputs, modules);
            };
        };
        
        /**
         * Generates a function which tests the auto-unchecking is performing as expected
         * This test assumes that the first and second modules are not dependent on each other, but
         * is best run on two modules which have at least one common dependency modules.
         *
         * @param {Object} firstModuleIndex
         * @param {Object} secondModuleIndex
         */
        that.generateAutoUncheckingFunc = function (firstModuleIndex, secondModuleIndex) {
        
            return function () {
                expect(5);
                
                var inputs = $(".flc-infusionBuilder-module input");
                var modules = $(".flc-infusionBuilder-module");
                var elementFirst = inputs.eq(firstModuleIndex);
                var elementSecond = inputs.eq(secondModuleIndex);
                var moduleProperties = data.model.moduleInfo[secondModuleIndex];
                var expectedModel = utilityFunctions.generatedModel(moduleProperties.moduleValue, moduleValuesArray, data.model.moduleInfo);
                
                elementFirst.simulate("click");
                elementSecond.simulate("click");
                elementFirst.simulate("click");
                
                selectionTests(expectedModel, inputs, modules);
            };
        };
        
        
        /**
         * Performs tests to ensure 'select minified' performs as expected
         */
        that.selectMinified = function () {
            expect(3);
            
            var expectedModel = ["minified"];
            var element = $(".flc-infusionBuilder-selectionModifier input").eq(0);
            element.simulate("click");
            
            jqUnit.assertTrue("Minified selected", $(element).attr("checked"));
            jqUnit.assertEquals("Number of radio buttons selected", 1, $(".flc-infusionBuilder-selectionModifier input:checked").length);
            jqUnit.assertEquals("Model", expectedModel, testComponent.model.typeSelections);
        };
        
        /**
         * Performs tests to ensure 'select source' performs as expected
         */
        that.selectSource = function () {
            expect(3);
            
            var expectedModel = ["source"];
            var element = $(".flc-infusionBuilder-selectionModifier input").eq(1);
            element.simulate("click");
            
            jqUnit.assertTrue("Source selected", $(element).attr("checked"));
            jqUnit.assertEquals("Number of radio buttons selected", 1, $(".flc-infusionBuilder-selectionModifier input:checked").length);
            jqUnit.assertEquals("Model", expectedModel, testComponent.model.typeSelections);
        };
        return that;
    };
    
    //*** TESTS BEGIN HERE ***/
    var testingFunctions;
    
    /**
     * Initialize testing functions, infusionBuilder component and unit test object
     *
     * @param {Object} data - the data on which the tests will be performed
     */
    var testSetup = function (dataId, data) {
        var setUp = function () {
            testComponent = fluid.infusionBuilder(INFUSION_BUILDER_SELECTOR, data);
        };

        var testCase = jqUnit.testCase("Infusion Builder Tests - " + dataId + " - ", setUp);
        testCase.fetchTemplate("../../html/InfusionBuilder.html", INFUSION_BUILDER_SELECTOR, $("#main"));
        testingFunctions = defineTestingFunctions(data);
        
        return testCase;
    };
    
    /**
     * A function to obtain the index of a module in the data given the moduleValue
     * 
     * @param {Object} moduleValue, a string representing the module
     * @param {Object} jsonData, the data in which the module is listed
     * 
     */
    var getModuleIndex = function (moduleValue, jsonData) {
        for (var moduleIndex = 0; moduleIndex < jsonData.model.moduleInfo.length; moduleIndex++) {
            if (jsonData.model.moduleInfo[moduleIndex].moduleValue === moduleValue) {
                break;
            }
        }
        return moduleIndex;
    };

    var fullDataTests = function () {
        var data = fluid.infusionBuilder.dependencies;
    
        var INLINE_EDIT_INDEX = getModuleIndex("inlineEdit", data);
        var PAGER_INDEX = getModuleIndex("pager", data);
        var PROGRESS_INDEX = getModuleIndex("progress", data);
        var REORDERER_INDEX = getModuleIndex("reorderer", data);
        var UIOPTIONS_INDEX = getModuleIndex("uiOptions", data);
        
        //TODO: do we need to test the model initialization or does the rendering test do it sufficiently?
        //TODO: are we testing infusionBuilder external API? If so, how?
        var tests = testSetup("full data", data);
        tests.test("Rendering of Elements", testingFunctions.renderingTests);
        tests.test("Select all Modules", testingFunctions.selectAllTests);
        tests.test("Deselect all Modules", testingFunctions.deselectAllTests);
        tests.test("Autochecking single Module", testingFunctions.generateAutoCheckingTestFunc(PAGER_INDEX));
        tests.test("Autochecking multiple Modules", testingFunctions.generateAutoCheckingTwoModulesTestFunc(INLINE_EDIT_INDEX, REORDERER_INDEX));
        tests.test("Auto-unchecking", testingFunctions.generateAutoUncheckingFunc(PROGRESS_INDEX, UIOPTIONS_INDEX));
        tests.test("Select Minified", testingFunctions.selectMinified);
        tests.test("Select Source", testingFunctions.selectSource);
        
        //TODO: test event that signals when the model is changed.
    };
    
    /*
     * Performs tests on a reduced data set with a single group and two modules and minimal dependencies.
     * Note that the dependencies here are for testing purposes and do not reflect reality.
     */
    var smallDataTests = function () {
    
        var FIRST_MODULE_INDEX = 0;
        var SECOND_MODULE_INDEX = 1;
        
        var data = {
            "model": {
                "groupInfo": [{
                    "groupName": "Infusion Framework Modules",
                    "groupDescription": "The core Infusion modules",
                    "groupModules": ["framework", "fss"]
                }],
                "moduleInfo": [{
                    "moduleValue": "framework",
                    "moduleName": "Fluid Infusion Framework",
                    "moduleDescription": "The core of the Fluid Infusion framework. Required for all Fluid components",
                    "moduleDependencies": ["fss"]
                }, {
                    "moduleValue": "fss",
                    "moduleName": "Fluid Skinning System",
                    "moduleDescription": "A modular CSS framework, which allows you to add, remove, and mix classes to get the desired effect."
                }]
            }
        };
        
        var tests = testSetup("small data set", data);
        tests.test("Rendering of Elements", testingFunctions.renderingTests);
        tests.test("Select all Modules", testingFunctions.selectAllTests);
        tests.test("Deselect all Modules", testingFunctions.deselectAllTests);
        tests.test("Autochecking single module with one dependency ", testingFunctions.generateAutoCheckingTestFunc(FIRST_MODULE_INDEX));
        tests.test("Autochecking single module no dependencies", testingFunctions.generateAutoCheckingTestFunc(SECOND_MODULE_INDEX));
        tests.test("Autochecking multiple Modules", testingFunctions.generateAutoCheckingTwoModulesTestFunc(SECOND_MODULE_INDEX, FIRST_MODULE_INDEX));
        tests.test("Select Minified", testingFunctions.selectMinified);
        tests.test("Select Source", testingFunctions.selectSource);
    };
    
    
    /*
     * Performs tests on an empty data set with an empty group info model and an empty moduleInfo Model
     * This reflects the output of not clicking any modules on the web page, but somehow being able to
     * submit the request. It also tests the possibility of someone attempting to hack the page and the
     * validator filtering out any bad data.
     */
    var emptyDataTests = function () {
    
        var data = {
            "model": {
                "groupInfo": [],
                "moduleInfo": []
            }
        };
        
        var tests = testSetup("empty data set", data);
        tests.test("Rendering of Elements", testingFunctions.renderingTests);
    };
    
    var noModulesDataTests = function () {
        var data = {
            "model": {
                "groupInfo": [{
                    "groupName": "Infusion Framework Modules",
                    "groupDescription": "The core Infusion modules",
                    "groupModules": []
                }, {
                    "groupName": "Infusion Component Modules",
                    "groupDescription": "Fluid Infusion components",
                    "groupModules": []
                }, {
                    "groupName": "Third Party Modules",
                    "groupDescription": "Third party javascript libraries required by Infusion components",
                    "groupModules": []
                }],
                "moduleInfo": []
            }
        };
        
        var tests = testSetup("no modules data set", data);
        tests.test("Rendering of Elements", testingFunctions.renderingTests);
        
    };
    
    var noGroupsDataTests = function () {
        var data = {
            "model": {
                "groupInfo": [],
                "moduleInfo": [{
                    "moduleValue": "framework",
                    "moduleName": "Fluid Infusion Framework",
                    "moduleDescription": "The core of the Fluid Infusion framework. Required for all Fluid components"
                }, {
                    "moduleValue": "fss",
                    "moduleName": "Fluid Skinning System",
                    "moduleDescription": "A modular CSS framework, which allows you to add, remove, and mix classes to get the desired effect.",
                    "moduleDependencies": ["fssReset", "fssLayout", "fssText", "fssThemes"]
                }, {
                    "moduleValue": "fssReset",
                    "moduleName": "Reset",
                    "moduleDescription": "A css reset file based on the YUI reset.",
                    "moduleDependencies": []
                }, {
                    "moduleValue": "fssLayout",
                    "moduleName": "Layout",
                    "moduleDescription": "Provides css classes for layout and convenience classes for widgets.",
                    "moduleDependencies": []
                }, {
                    "moduleValue": "fssText",
                    "moduleName": "Text",
                    "moduleDescription": "Classes for text, headers, spacing and sizes.",
                    "moduleDependencies": []
                }, {
                    "moduleValue": "fssThemes",
                    "moduleName": "Themes",
                    "moduleDescription": "Colour schemes for basic markup and widgets.",
                    "moduleDependencies": []
                }, {
                    "moduleValue": "renderer",
                    "moduleName": "Renderer",
                    "moduleDescription": "Allows users to create user interface templates in pure HTML, and render the pages entirely on the client side."
                }]
            }
        };
        
        var tests = testSetup("no groups data set", data);
        tests.test("Rendering of Elements", testingFunctions.renderingTests);
        
    };
    
    var noGroupNameDataTests = function () {
        var data = {
            "model": {
                "groupInfo": [{
                    "groupName": "",
                    "groupDescription": "",
                    "groupModules": ["framework", "fss", "fssReset", "fssLayout", "fssText", "fssThemes", "renderer"]
                }],
                "moduleInfo": [{
                    "moduleValue": "framework",
                    "moduleName": "Fluid Infusion Framework",
                    "moduleDescription": "The core of the Fluid Infusion framework. Required for all Fluid components",
                    "moduleDependencies": ["jQuery", "jQueryUICore", "jQueryDelegatePlugin"]
                }, {
                    "moduleValue": "fss",
                    "moduleName": "Fluid Skinning System",
                    "moduleDescription": "A modular CSS framework, which allows you to add, remove, and mix classes to get the desired effect.",
                    "moduleDependencies": ["fssReset", "fssLayout", "fssText", "fssThemes"]
                }, {
                    "moduleValue": "fssReset",
                    "moduleName": "Reset",
                    "moduleDescription": "A css reset file based on the YUI reset.",
                    "moduleDependencies": []
                }, {
                    "moduleValue": "fssLayout",
                    "moduleName": "Layout",
                    "moduleDescription": "Provides css classes for layout and convenience classes for widgets.",
                    "moduleDependencies": []
                }, {
                    "moduleValue": "fssText",
                    "moduleName": "Text",
                    "moduleDescription": "Classes for text, headers, spacing and sizes.",
                    "moduleDependencies": []
                }, {
                    "moduleValue": "fssThemes",
                    "moduleName": "Themes",
                    "moduleDescription": "Colour schemes for basic markup and widgets.",
                    "moduleDependencies": []
                }, {
                    "moduleValue": "renderer",
                    "moduleName": "Renderer",
                    "moduleDescription": "Allows users to create user interface templates in pure HTML, and render the pages entirely on the client side.",
                    "moduleDependencies": ["jQuery", "framework", "fastXmlPull"]
                }, {
                    "moduleValue": "inlineEdit",
                    "moduleName": "Inline Edit",
                    "moduleDescription": "Allows a user to do quick edits to simple text directly on a web page. ",
                    "moduleDependencies": ["jQuery", "jQueryTooltipPlugin", "framework", "undo"]
                }, {
                    "moduleValue": "pager",
                    "moduleName": "Pager",
                    "moduleDescription": "Allows users to break up long lists of items into separate pages.",
                    "moduleDependencies": ["jQuery", "jQueryTooltipPlugin", "jQuerybgiframePlugin", "framework", "renderer"]
                }]
            }
        };
        
        var tests = testSetup("no group name data set", data);
        tests.test("Rendering of Elements", testingFunctions.renderingTests);
        
    };
    
    $(document).ready(function () {
        fullDataTests();
        smallDataTests();
        emptyDataTests();
        noModulesDataTests();
        noGroupsDataTests();
        noGroupNameDataTests();
    });
})(jQuery);
