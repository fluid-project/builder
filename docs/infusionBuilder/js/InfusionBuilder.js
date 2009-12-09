/*
 Copyright 2008-2009 University of Cambridge
 Copyright 2008-2009 University of Toronto
 
 Licensed under the Educational Community License (ECL), Version 2.0 or the New
 BSD license. You may not use this file except in compliance with one these
 Licenses.
 
 You may obtain a copy of the ECL 2.0 License and BSD License at
 https://source.fluidproject.org/svn/LICENSE.txt
 
 */
/*global jQuery*/
/*global fluid*/

(function ($) {
    
    var UNSELECTED = 0; //a 'constant' value representing an unselected module
    
    //relates module values to module names - initialized in setup
    var moduleNames;
    
    var createModuleNamesObject = function (model) {
        var moduleNamesObject = {};
        for (var i = 0; i < model.moduleInfo.length; i++) {
            var modInfo = model.moduleInfo[i];
            moduleNamesObject[modInfo.moduleValue] = modInfo.moduleName;
        }
        return moduleNamesObject;
    };
    
    /**
     * Traverses through an array of objects returning an array of all the values for a specified key.
     * 
     * @param {Object} array, an array of Objects to search through
     * @param {Object} key, the key for whose value to return from each object. Will return an empty string "",
     * if the key does not exist in the any of the objects.
     */
    var extractArray = function (array, key) {
        return fluid.transform(array, function (object, index) {
            return object[key] || "";
        });
    };
    
    /**
     * Returns an object with the information needed for setting up data binding in the renderer.
     * 
     * @param {Object} id, an ID for the render to use to refer to this set of options
     * @param {Object} values, an EL path in the model to the values
     * @param {Object} names, an EL path in the model to the names
     * @param {Object} selections, an EL path in the model to the selections
     */
    var dataBindingOptions = function (id, values, names, selections) {
        return {
            ID: id,
            optionlist: values,
            optionnames: names,
            selection: {valuebinding: selections}
        };
    };
    
    /**
     * Returns an object used by the renderer for leafs that only have an ID and a value. 
     * This is just a convenience function
     * 
     * @param {Object} id
     * @param {Object} value
     */
    var treeLeafValue = function (id, value) {
        return {ID: id, value: value};  
    };
    
    /**
     * Renders the modules and associated information. All of the data needed for rendering is held
     * in the model.
     * 
     * @param {Object} that, the component
     */
    var renderModules = function (that) {
        
        var moduleNames = extractArray(that.options.model.moduleInfo, "moduleName");
        
        var selectionsRenderMap = [
            {selector: that.options.selectors.groups, id: "groups:"},
            {selector: that.options.selectors.groupName, id: "groupName"},
            {selector: that.options.selectors.module, id: "module:"},
            {selector: that.options.selectors.moduleInput, id: "moduleInput"},
            {selector: that.options.selectors.moduleInputLabel, id: "moduleLabel"},
            {selector: that.options.selectors.moduleDescription, id: "moduleDescription"}
        ];
        
        /**
         * Concatinates the module name and a hidden span containing the module description.
         * The hidden span is only read by screen readers and supplements the description that is 
         * also displayed as a title for the checkbox label.
         * 
         * @param (Object) that, the component
         * @param (Object) position, the position of the module name and description in the array
         */
        var concatModuleNameDescription = function (that, position) {
            return moduleNames[position] + " <span class='" + that.options.styles.hideModuleDescription + "'>" + that.options.model.moduleInfo[position].moduleDescription + "</span>";
        };
        
        /**
         * Programmatically generates a hydrated component tree
         * 
         * @param {Object} that, the component
         */
        var generatedTree = function (that) {
            var tree = {children: []};
            var groupInfo = that.options.model.groupInfo;
            
            //when no groups - render all modules with no group heading
            var moduleSubstitution = (groupInfo.length === 1 && groupInfo[0].groupModules.length === 0) ? that.moduleValues : null;
      
            tree.children[0] = dataBindingOptions("selections", that.moduleValues, moduleNames, "moduleSelections");
            
            tree.children = tree.children.concat(fluid.transform(groupInfo, function (group) {
                var tempTree = {
                    ID: "groups:",
                    children: [treeLeafValue("groupName", group.groupName || "")]
                };
                var modules = moduleSubstitution || group.groupModules;
                
                tempTree.children = tempTree.children.concat(fluid.transform(modules || [], function (module) {
                    var position = $.inArray(module, that.moduleValues);
                    
                    return {    //adds an id to the node with the prefix "check-" and the suffix "module number"
                        ID: "module:",
                        children: [
                            {ID: "moduleInput", choiceindex: position, parentRelativeID: "..::..::selections",
                             decorators: [{
                                    type: "identify", 
                                    key: "check-" + position 
                                }]
                            }, { ID: "moduleLabel", choiceindex: position, parentRelativeID: "..::..::selections",
                             markup: concatModuleNameDescription(that, position),
                             decorators: [{
                                    type: "attrs",
                                    attributes: {title: that.options.model.moduleInfo[position].moduleDescription || ""}
                                }]
                          } ]
                        };
                }));
                
                return tempTree;
            }));
            
            return tree;
        };
        
        var renderOptions = {
            cutpoints: selectionsRenderMap, 
            model: that.model, 
            applier: that.applier,
            idMap: that.idMap,
            autoBind: true
        };
        
        fluid.selfRender(that.locate("selectionsContainer"), generatedTree(that), renderOptions);
    };
    
    /**
     * Renders the download type controls (the defaults are minified/source), but can be overriden in the components defaults
     * 
     * @param {Object} that, the component
     */
    var renderDownloadTypeControls = function (that) {
        var typeValues = extractArray(that.options.model.typeInfo, "typeValue");
        var typeNames = extractArray(that.options.model.typeInfo, "typeName");
        
        var formControlsSelectorMap = [
            {selector: that.options.selectors.selectionModifier, id: "modifiers:"},
            {selector: that.options.selectors.selectionModifierInput, id: "modifierInput"},
            {selector: that.options.selectors.selectionModifierLabel, id: "modifierLabel"}
        ];
        
        /**
         * Programmatically generates a hydrated component tree
         * 
         * @param {Object} that, the component
         */
        var generatedTree = function (that) {
            var tree = {children: []};
            
            tree.children[0] = dataBindingOptions("modifierValues", typeValues, typeNames, "typeSelections");
            
            tree.children = tree.children.concat(fluid.transform(typeValues, function (object, index) {

                return {
                    ID: "modifiers:",
                    children: [
                        {ID: "modifierInput", choiceindex: index, parentRelativeID: "..::modifierValues",
                              decorators: [{type: "identify", key: "type-" + index }]},
                        {ID: "modifierLabel", choiceindex: index, parentRelativeID: "..::modifierValues"}
                    ]  
                };
            }));
            
            return tree;
        };
        
        var renderOptions = {
            cutpoints: formControlsSelectorMap, 
            model: that.model, 
            applier: that.applier, 
            autoBind: true,
            idMap: that.idMap
        };
        
        fluid.selfRender(that.locate("compressionControls"), generatedTree(that), renderOptions);
    };
    
    /**
     * Performs a manual update of the model.
     * If an addition is being made, it will add the value to the selected values in the model.
     * If a deletion is being performed, it will remove the value from the selected values in the model
     * 
     * @param {Object} selections, the array of selected values
     * @param {Object} value, the value that has changed in the model
     * @param {Object} newState, true if all modules should be selected, false otherwise
     */
    var manuallyUpdateModel = function (selections, value, newState) {
        var index = $.inArray(value, selections);
        
        if (newState && index === -1) {
            selections[selections.length] = value;
        } else if (!newState && index !== -1) {
            selections.splice(index, 1);
        }
    };
    
    /**
     * Resets a single module's counter to 0 (UNSELECTED). This module is 
     * specified by the key.
     * 
     * @param {Object} that, the component
     * @param {Object} key, the module to be reset
     */
    var resetSingleCounter = function (that, key) {
        if (that.selectionCounter[key]) {
            that.selectionCounter[key] = UNSELECTED;
        }
    };
              
    /**
     * Resets all the selectionCounter, returning all counters to 0.
     * 
     * @param {Object} that, the component
     */
    var resetSelectionCounter = function (that) {
        for (var key in that.selectionCounter) {
            if (that.selectionCounter.hasOwnProperty(key)) {
                resetSingleCounter(that, key);
            }
        }
    };
    
    /**
     * Selects all modules
     * 
     * @param {Object} that, the component
     */
    var selectAllModules = function (that) {
        if (that.moduleValues.length !== that.model.moduleSelections.length) {
            for (var i = 0; i < that.moduleValues.length; i++) {
                if ($.inArray(that.moduleValues[i], that.model.moduleSelections) === -1) {
                    fluid.jById(that.idMap["check-" + i]).attr("checked", true);
                    manuallyUpdateModel(that.model.moduleSelections, that.moduleValues[i], true);
                    fluid.infusionBuilder.updateItemAndDependencies(that, i, true);
                }
            }
        }
        that.events.afterModuleSelectionsChanged.fire(that);
    };
 
    /**
     * Resets the download type selection to the first option (MINIFIED).
     * 
     * @param {Object} that, the component
     */
    var resetTypeSelection = function (that) {
        that.model.typeSelections = that.options.model.typeInfo[0].typeValue;
        fluid.jById(that.idMap["type-0"]).attr("checked", true);
        
        that.events.afterModelChange.fire(that);
    };

    /**
     * Unselects all modules, and resets the selection counter back to 0
     * 
     * @param {Object} that, the component
     */
    var unselectAllModules = function (that) {
        resetTypeSelection(that);
        
        that.model.moduleSelections = [];
        resetSelectionCounter(that);
        
        for (var i = 0; i < that.moduleValues.length; ++ i) {
            fluid.infusionBuilder.updateItem(that, i, false);
            fluid.jById(that.idMap["check-" + i]).attr("checked", false);
        }
        that.events.afterModuleSelectionsChanged.fire(that);
    };    
    
    /**
     * Initializes the selection counter (that.selectionCounter) which will be used to keep track of how many selected modules
     * depend on a specific module. All modules are represented as keys in the object with counters starting at 0
     * 
     * @param {Object} that, the component
     */
    var initSelectionCounter = function (that) {
        that.selectionCounter = {};
        
        for (var i = 0; i < that.moduleValues.length; i++) {
            that.selectionCounter[that.moduleValues[i]] = UNSELECTED;
        }
    };
    
    /**
     * Determines what the extra value is in the larger array.
     * This index of this value (in the model.moduleValues array from the defaults) is passed along 
     * to the supplied function.
     * 
     * @param {Object} that, the component
     * @param {Object} largerArray, the larger of two arrays
     * @param {Object} smallerArray, the smaller of two arrays
     * @param {Object} newState, true if an input has been selected, false otherwise
     * @param {Object} func, the function to be called with the index from the extra value in the larger array, 
     * has the signature of (that, index, newState)
     */
    var updateModules = function (that, largerArray, smallerArray, newState, func) {
        var larger = fluid.copy(largerArray);
        var smaller = fluid.copy(smallerArray);
        for (var i = 0; i < larger.length; i++) {
            //there should only be one element which is not in the smaller array
            if ($.inArray(larger[i], smaller) === -1) { 
                resetSingleCounter(that, larger[i]);
                func(that, $.inArray(larger[i], that.moduleValues), newState);
                break;
            }
        }
        that.events.afterModuleSelectionsChanged.fire(that);
    };
    
    /**
     * Listens for changes to the model.
     * Additions to the model will invoke updateModules passing in the function specified at that.options.markSelection.
     * Deletions to the model will invoke updateModules passing in the function specified at that.options.unmarkSelection.
     * 
     * @param {Object} that, the component
     */
    var modelUpdated = function (that) {
        that.applier.modelChanged.addListener("*", function (newModel, oldModel) {
            var newSelections = newModel.moduleSelections || [];
            var oldSelections = oldModel.moduleSelections || [];
            var changeAmount = Math.abs(newSelections.length - oldSelections.length);
            
            if (changeAmount === 1) { //could also be 0 (change to typeSelections only)
                if (newSelections.length > oldSelections.length) { //check box was checked
                    updateModules(that, newSelections, oldSelections, true, that.options.markSelection);
                } else { //check box was unchecked
                    updateModules(that, oldSelections, newSelections, false, that.options.unmarkSelection);
                }
            }

            if (newModel.typeSelections !== oldModel.typeSelections) {
                that.events.afterModelChange.fire(that);
            }
        });
    };
    
    /**
     * Returns the ancestor to the element that has the selector specified by that.options.selectors.module
     * 
     * @param {Object} that, the component
     * @param {Object} element, the DOM element to find the ancestor of
     */
    function findRow(that, element) {
        return fluid.findAncestor(element, function (node) {
            return $(node).is(that.options.selectors.module);
        });
    }
    
    /**
     * Updates the counter for the specified module. The counter is responsible for keeping track of the number of
     * "checks" that a module has. Multiple modules may imply more than one module depends on a particular module. This
     * allows the code to keep track of when to actually "uncheck" a module once there are no other modules checked 
     * which depend on it. 
     * 
     * @param {Object} that, the component
     * @param {Object} value, the module to change the count for
     * @param {Object} newState, if true the counter is incremented, decremented otherwise
     */
    var updateSelectionCounter = function (that, value, newState) {
        if (newState) {
            ++that.selectionCounter[value];
        } else if (that.selectionCounter[value] > UNSELECTED) {
            --that.selectionCounter[value];
        }
    };
    
    /**
     * Assembles the model which tracks the selections made in the component
     * 
     * @param {Object} that, the component
     */
    var setupModel = function (that) {
        that.model = {
            typeSelections: that.options.model.typeInfo[0].typeValue || "",
            moduleSelections: []
        };
        
        that.applier = fluid.makeChangeApplier(that.model);
        that.idMap = {};
        that.moduleValues = extractArray(that.options.model.moduleInfo, "moduleValue");
        moduleNames = createModuleNamesObject(that.options.model);
    };

    /**
     * Adds a click handler func to the specified element
     * 
     * @param {Object} element, the class of the element to which the click handler should be added.
     * @param {Object} func, the click handler function
     * @param {Object} that, the component
     */
    var addClickHandler = function (element, func, that) {
        var elementArray = that.locate(element, that.container);
        if (elementArray.length > 0 && elementArray[0] !== that.container) {
            elementArray.click(function () {
                func(that);
            }
          );
        }
    };
    
    /**
     * Adds click handlers to the check all and uncheck all buttons if they exist
     * 
     * @param {Object} that, the component
     */			
    var setupQuickSelect = function (that) {
        //add click handler to check all if it exists
        addClickHandler("checkAll", selectAllModules, that);

        //add click handler to uncheck all if it exists
        addClickHandler("unCheckAll", unselectAllModules, that);
    };
    
    /**
    * Adds javascript dependent aria tags to the markup.
    *  
    * @param {Object} that, the component
    */
    var addAria = function (that) {
        var selections = that.locate("moduleSelections");
        
        selections.attr("role", "region");
        selections.attr("aria-live", "polite");
        selections.attr("aria-relevant", "all");
    };
    
    /**
    * Given a the list of the indices of the checked modules return a string
    * containing the names of the checked modules
    * 
    * @param {Object} valuesArray
    */
    var getNamesString = function (valuesArray) {
        var nameString = "Modules Selected: ";
        var arrayLength = valuesArray.length;
        for (var i = 0; i < arrayLength; i++) {
            nameString += moduleNames[valuesArray[i]];
            if (i < arrayLength - 1) {
                nameString += ", ";
            }
        }
        return nameString;
    };

        
    /**
     * Outputs a list of module names of the currently selected modules to the interface. Useful
     * for accessibility purposes.
     * 
     * @param (Object) that, the component
     * 
     */
    var updateSelectedModules = function (that) {
        var selections = that.model.moduleSelections;
        var modulesSelectedString = "Modules selected: none";
        if (selections.length > 0) {
            modulesSelectedString = getNamesString(selections);
        }
        that.locate("moduleSelections").html(modulesSelectedString);
    };

    var updateHiddenFormWithSelections = function (that) {
        //sets the hidden form inputs to the user selection to be posted back to the server.
        that.locate("formModuleSelections").val(that.model.moduleSelections);
        that.locate("formTypeSelections").val(that.model.typeSelections);
        
        // Enable or disable the download button depending on how many selections have been made.
        if (that.model.moduleSelections.length > 0) {
            that.locate("downloadButton").removeAttr("disabled");
        } else {
            that.locate("downloadButton").attr("disabled", "disabled");
        }
    };
   
    /**
     * Sets up the event handlers for listeners.
     * 
     * @param {Object} that, the component
     */
    var bindEventHandlers = function (that) {
        that.events.afterModuleSelectionsChanged.addListener(updateSelectedModules);
        that.events.afterModelChange.addListener(updateHiddenFormWithSelections);
        
        setupQuickSelect(that);

        that.locate("downloadButton").click(function () {
            that.locate("controls").hide();
            that.locate("downloadMessage").show();
        });
    };

    /**
     * Calls all of the setup functions that the component needs to run.
     * 
     * @param {Object} that, the component
     */
    var setupInfusionBuilder = function (that) {
        setupModel(that);
        addAria(that);
        bindEventHandlers(that);
        initSelectionCounter(that);
        renderModules(that);
        renderDownloadTypeControls(that);
        modelUpdated(that);
    };
    
    /**
     * The creator function for the component
     * 
     * @param {Object} container, selector representing the components container
     * @param {Object} options, the options to be passed in to the component
     */
    fluid.infusionBuilder = function (container, options) {
        var that = fluid.initView("fluid.infusionBuilder", container, options);		        
        setupInfusionBuilder(that);
        
        return that;
    };
    
    /**
     * Updates the style for a module that has been selected/deselected and fires off an event that the model has been updated.
     * 
     * @param {Object} that, the component
     * @param {Object} index, an index within the moduleValues array for the value that has been selected/deselected
     * @param {Object} newState, true if all modules should be selected, false otherwise
     */
    fluid.infusionBuilder.updateItem = function (that, index, newState) {
        var element = fluid.byId(that.idMap["check-" + index]); //this finds the rendered elements with decorators
        var container = findRow(that, element);

        if (newState || that.selectionCounter[that.moduleValues[index]] <= 1) {
            $(container)[newState ? "addClass" : "removeClass"](that.options.styles.selectedModule);
            fluid.jById(that.idMap["check-" + index]).attr("checked", newState);
            manuallyUpdateModel(that.model.moduleSelections, that.moduleValues[index], newState);
        }
        
        updateSelectionCounter(that, that.moduleValues[index], newState);
        that.events.afterModelChange.fire(that);
    };
    
    /**
     * Updates the style and presentation of all of the dependencies for a selected module
     * 
     * @param {Object} that, the component
     * @param {Object} index, an index within the moduleValues array for the value that has been selected/deselected
     * @param {Object} newState, true if all modules should be selected, false otherwise
     */
    fluid.infusionBuilder.updateItemAndDependencies = function (that, index, newState) {
        var dependencies = that.options.model.moduleInfo[index].moduleDependencies || [];
        fluid.infusionBuilder.updateItem(that, index, newState);
        
        for (var i = 0; i < dependencies.length; ++i) {
            var moduleValuesIndex = $.inArray(dependencies[i], that.moduleValues);
            if (moduleValuesIndex >= 0) {
                fluid.infusionBuilder.updateItemAndDependencies(that, moduleValuesIndex, newState);
            }    
        }
    };
    
    /**
     * The component's defaults
     */
    fluid.defaults("fluid.infusionBuilder", {
        
        selectors: {
            selectionsContainer: ".flc-infusionBuilder-selectionsContainer",
            
            moduleSelections: ".flc-infusionBuilder-moduleSelections",
            
            groups: ".flc-infusionBuilder-group",
            groupName: ".flc-infusionBuilder-groupName",
            
            modules: ".flc-infusionBuilder-modules",
            module: ".flc-infusionBuilder-module",
            moduleInput: "#flc-infusionBuilder-input",
            moduleInputLabel: ".flc-infusionBuilder-inputLabel",
            moduleDescription: ".flc-infusionBuilder-moduleDescription",
            
            compressionControls: ".flc-infusionBuilder-compressionControls",
            selectionModifier: ".flc-infusionBuilder-selectionModifier",
            selectionModifierInput: "#flc-infusionBuilder-selectionModifierInput",
            selectionModifierLabel: ".flc-infusionBuilder-selectionModifierLabel",
            
            checkAll: ".flc-infusionBuilder-checkAll",
            unCheckAll: ".flc-infusionBuilder-unCheckAll",
            
            controls: ".flc-infusionBuilder-downloadControls",
            downloadMessage: ".flc-infusionBuilder-downloadMsg",
            downloadButton: ".flc-infusionBuilder-downloadButton",
            
            formModuleSelections: "#moduleSelections",
            formTypeSelections: "#typeSelections"
        },
        strings: {},
        
        styles: {
            selectedModule: "fl-infusionBuilder-selected",
            hideModuleDescription: "fl-offScreen-hidden"
        },
        
        events: {
            afterModelChange: null,
            afterModuleSelectionsChanged: null
        },
        // TODO: Delete me!
        model: {
            groupInfo: [
                {
                    groupName: "",
                    groupModules: []
                }
            ],
            
            moduleInfo: [
                {
                    moduleValue: "",
                    moduleName: "",
                    moduleDescription: "",
                    moduleDependencies: []
                }
            ],
            
            typeInfo: [
                {
                    typeValue: "minified",
                    typeName: "Minified"
                },
                {
                    typeValue: "source",
                    typeName: "Source"
                }
            ]
        },
        
        markSelection: fluid.infusionBuilder.updateItemAndDependencies, 
        unmarkSelection: fluid.infusionBuilder.updateItemAndDependencies
    });
})(jQuery);
