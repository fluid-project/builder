<?php
/**
 * Config file 
 * Variables that are used by PHP test cases
 * 
 */
define('DISTANT_PATH',  '../../../../infusionBuilder-secure/php/');  //path to the other folder which contains the PHP server config file

require_once (DISTANT_PATH.'config.php');

//Infusion Builder PHP server script
define('TEST_INFUSION_BUILDER_URL', 'http://forge.fluidproject.org/infusionBuilder/php/builder.php');

//folder of the php test cases. BUILDER_PATH is from general builder config file (DISTANT_PATH.'config.php')
define('TESTCASE_PATH',             BUILDER_PATH.'tests/php/');

?>