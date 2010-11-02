<?php
/**
 * Config file 
 * Variables that are used by PHP test cases
 * 
 */
define('DISTANT_PATH',  '../../../../infusionBuilder-secure/php/');  //path to the other folder which contains the PHP server config file

require_once (DISTANT_PATH.'config.php');

define('TEST_BASE_PATH',            'http://localhost/fluid-infusionBuilder/'); //infusion builder base URL
define('TEST_INFUSION_BUILDER_URL', 'docs/infusionBuilder/php/builder.php');    //Infusion Builder PHP server script
define('TEST_INFUSION_GET_URL',     'docs/infusionBuilder/php/get.php');        //URL of get.php which is used to fetch the zip products
define('TEST_INFUSION_VERSION',     '1.1.2');                                   //infusion version
define('TESTCASE_PATH',             BUILDER_PATH.'tests/php/');                 //folder of the php test cases. BUILDER_PATH is from general builder config file (DISTANT_PATH.'config.php')

?>