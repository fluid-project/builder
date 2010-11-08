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
 * Server configuration requirements.
 * Customize SECURE_PATH, BUILER_PATH, DB_USER, DB_PASS
 */
    
    //***On windows use 'c:\' NOT 'c:/' but you can continue to use forward slash
    // for the remainder of the directory name. i.e. C:\Documents and Settings/User/infusion-builder/
    // Do not use back slash as it get's mistaken for an escape character.
    //***Remember to end path names with a forward slash
    define ("SECURE_PATH", "/path/to/infusionBuilder-secure/");
    define ("BUILDER_PATH", "/path/to/infusionBuilder/");
    define ("SIMPLETEST_PATH", "/path/to/simpleTest/");
    define("DB_USER", "mysql_user");
    define("DB_PASS", "mysql_password");
    define("DB_NAME", "mysql_db_name");
    
    
    // Do not change these unless you know what you are doing
    define("PRETREATED_PATH", BUILDER_PATH."infusion/build/pretreated/");
    define("BUILD_SCRIPT_PATH", BUILDER_PATH."infusion/build-scripts/");
    define("BUILD_PROPS", BUILD_SCRIPT_PATH."build.properties");
    define("BUILD_FILES", BUILDER_PATH."infusion/src/webapp/");
    define("JSON_SUFFIX", "Dependencies.json"); 
    define("OUTPUT_FILE_PATH_BUILD", SECURE_PATH."tmp/build/");
    define("OUTPUT_FILE_PATH_PRODUCTS", SECURE_PATH."tmp/products/");
    define("CACHE_FILE_PATH", SECURE_PATH."cache/");
    define("CACHE_FILE", CACHE_FILE_PATH."json-cache.txt");
    define("DB_HOST", "localhost");

?>
