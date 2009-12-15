/*
 Copyright 2008-2009 University of Toronto
 
 Licensed under the Educational Community License (ECL), Version 2.0 or the New
 BSD license. You may not use this file except in compliance with one these
 Licenses.
 
 You may obtain a copy of the ECL 2.0 License and BSD License at
 https://source.fluidproject.org/svn/LICENSE.txt
 
 */
/*global jQuery, fluid*/

fluid = fluid || {};
fluid.infusionBuilder = fluid.infusionBuilder || {};

/*
 * Note that this data is used both by the demo and by the tests, so changes to
 * this data must be checked in both places.
 * 
 */
fluid.infusionBuilder.dependencies = {
	model : {

		groupInfo : [
				{
					groupName : "Framework",
					groupDescription : "Framework Description",
					groupModules : [ "framework", "fss", "fssReset",
							"fssLayout", "fssText", "fssThemes", "renderer" ]
				},
				{
					groupName : "Components",
					groupDescription : "Infusion Component Modules Description",
					groupModules : [ "inlineEdit", "pager", "progress",
							"reorderer", "tableOfContents", "uiOptions",
							"undo", "uploader" ]
				},
				{
					groupName : "Third Party",
					groupDescription : "Third Party Modules Descriptoin",
					groupModules : [ "fastXmlPull", "json", "jQuery",
							"jQueryUICore", "jQueryUIWidgets",
							"jQueryDelegatePlugin", "jQueryTooltipPlugin",
							"jQuerybgiframePlugin", "swfupload", "Cats!" ]
				} 
			],
			moduleInfo : [ // index 0
				{
					moduleValue : "framework",
					moduleName : "Fluid Infusion Framework",
					moduleDescription : "The core of the Fluid Infusion framework. Required for all Fluid components",
					moduleDependencies : [ "jQuery", "jQueryUICore",
							"jQueryDelegatePlugin" ]
				}, // index 1
				{
					moduleValue : "fss",
					moduleName : "Fluid Skinning System",
					moduleDescription : "A modular CSS framework, which allows you to add, remove, and mix classes to get the desired effect.",
					moduleDependencies : [ "fssReset", "fssLayout", "fssText",
							"fssThemes" ]
				}, // index 2
				{
					moduleValue : "fssReset",
					moduleName : "Reset",
					moduleDescription : "A css reset file based on the YUI reset."
				}, // index 3
				{
					moduleValue : "fssLayout",
					moduleName : "Layout",
					moduleDescription : "Provides css classes for layout and convenience classes for widgets."
				}, // index 4
				{
					moduleValue : "fssText",
					moduleName : "Text",
					moduleDescription : "Classes for text, headers, spacing and sizes."
				}, // index 5
				{
					moduleValue : "fssThemes",
					moduleName : "Themes",
					moduleDescription : "Colour schemes for basic markup and widgets."
				}, // index 6
				{
					moduleValue : "renderer",
					moduleName : "Renderer",
					moduleDescription : "Allows users to create user interface templates in pure HTML, and render the pages entirely on the client side.",
					moduleDependencies : [ "jQuery", "framework", "fastXmlPull" ]
				}, // index 7
				{
					moduleValue : "inlineEdit",
					moduleName : "Inline Edit",
					moduleDescription : "Allows a user to do quick edits to simple text directly on a web page.",
					moduleDependencies : [ "jQuery", "jQueryTooltipPlugin",
							"framework", "undo" ]
				}, // index 8
				{
					moduleValue : "pager",
					moduleName : "Pager",
					moduleDescription : "Allows users to break up long lists of items into separate pages.",
					moduleDependencies : [ "jQuery", "jQueryTooltipPlugin",
							"jQuerybgiframePlugin", "framework", "renderer" ]
				}, // index 9
				{
					moduleValue : "progress",
					moduleName : "Progress",
					moduleDescription : "A linear progress display.",
					moduleDependencies : [ "jQuery", "jQueryUICore",
							"framework" ]
				}, // index 10
				{
					moduleValue : "reorderer",
					moduleName : "Reorderer",
					moduleDescription : "enables users to directly re-arrange content on the page.",
					moduleDependencies : [ "jQuery", "jQueryUICore",
							"jQueryUIWidgets", "framework" ]
				}, // index 11
				{
					moduleValue : "tableOfContents",
					moduleName : "Table of Contents",
					moduleDescription : "Constructs and displays a formatted list of links to all headers in a document.",
					moduleDependencies : [ "jQuery", "framework", "renderer" ]
				}, // index 12
				{
					moduleValue : "uiOptions",
					moduleName : "User Interface Options",
					moduleDescription : "Transforms the presentation of the user interface and content resources so that they are personalized to an individual user's needs.",
					moduleDependencies : [ "fss", "jQuery", "jQueryUICore",
							"framework", "renderer", "jQueryUIWidgets",
							"tableOfContents", "json" ]
				}, // index 13
				{
					moduleValue : "undo",
					moduleName : "Undo",
					moduleDescription : "Provides undo support for any component that bears a model.",
					moduleDependencies : [ "jQuery", "framework" ]
				}, // index 14
				{
					moduleValue : "uploader",
					moduleName : "Uploader",
					moduleDescription : "Allows users to upload files.",
					moduleDependencies : [ "jQuery", "jQueryUICore",
							"framework", "Cats!", "swfupload", "progress" ]
				}, // index 15
				{
					moduleValue : "fastXmlPull",
					moduleName : "fastXmlPull",
					moduleDescription : "A fast xml pull parser."
				}, // index 16
				{
					moduleValue : "json",
					moduleName : "JSON",
					moduleDescription : "Javascript lightweight data-interchange format."
				}, // index 17
				{
					moduleValue : "jQuery",
					moduleName : "jQuery",
					moduleDescription : "jQuery javascript library core."
				}, // index 18
				{
					moduleValue : "jQueryUICore",
					moduleName : "jQuery UI Core",
					moduleDescription : "The core of jQuery UI, required for all jQuery UI interactions and widgets.",
					moduleDependencies : [ "jQuery" ]
				}, // index 19
				{
					moduleValue : "jQueryUIWidgets",
					moduleName : "jQuery UI Widgets",
					moduleDescription : "Full-featured jQuery UI Controls - each has a range of options and is fully themeable.",
					moduleDependencies : [ "jQuery", "jQueryUICore" ]
				}, // index 20
				{
					moduleValue : "jQueryDelegatePlugin",
					moduleName : "jQuery Delegate Plugin",
					moduleDescription : "Used to simulate portable bubbling focus event",
					moduleDependencies : [ "jQuery" ]
				}, // index 21
				{
					moduleValue : "jQueryTooltipPlugin",
					moduleName : "jQuery Tooltip Plugin",
					moduleDescription : "Allows tooltip customization.",
					moduleDependencies : [ "jQuery", "jQueryUICore" ]
				}, // index 22
				{
					moduleValue : "jQuerybgiframePlugin",
					moduleName : "jQuery bgiframe Plugin",
					moduleDescription : "Used to handle zindex issues in IE6.",
					moduleDependencies : [ "jQuery", "jQueryUICore" ]
				}, // index 23
				{
					moduleValue : "swfupload",
					moduleName : "SWFUpload",
					moduleDescription : "SWFUpload is a small JavaScript/Flash library featuring the great upload capabilities of Flash and the accessibility and ease of HTML/CSS."
				}, // index 24
				{
					moduleValue : "Cats!",
					moduleName : "Cats!",
					moduleDescription : "Slash slash, bite bite."
				} 
			]
		}
	};
