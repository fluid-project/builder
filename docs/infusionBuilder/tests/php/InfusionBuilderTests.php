<?php
require_once ('config.php');
require_once (SIMPLETEST_PATH.'simpletest/simpletest.php');
require_once (SIMPLETEST_PATH.'/simpletest/autorun.php');
require_once (SIMPLETEST_PATH.'/simpletest/web_tester.php');
require_once('InfusionHtmlReporter.php');     //our own customized layout
SimpleTest::prefer(new InfusionHtmlReporter());

//starts here
class InfusionBuilderTests extends TestSuite {
    function __construct() {
        parent::__construct();
        $this->addFile(TESTCASE_PATH.'BuilderUtilitiesTest.php');
        $this->addFile(TESTCASE_PATH.'GroupClassTest.php');
        $this->addFile(TESTCASE_PATH.'ModuleClassTest.php');
        $this->addFile(TESTCASE_PATH.'PostClassTest.php');
        $this->addFile(TESTCASE_PATH.'BuilderDownloadTest.php');
    }
}
?>