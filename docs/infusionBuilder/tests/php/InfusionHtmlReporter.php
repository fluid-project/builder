<?php
require_once(SIMPLETEST_PATH.'/simpletest/reporter.php');
class InfusionHtmlReporter extends HtmlReporter {
    function paintHeader($test_name) {
        parent::paintHeader($test_name);        
    }
    
    function paintFooter($test_name) {  
        parent::paintFooter($test_name);
    }
    
    function paintCaseStart($test_name) {
        parent::paintCaseStart($test_name);
        $breadcrumb = $this->getTestList();
        echo "<strong><span class=\"pass_span\">$breadcrumb[1] - $breadcrumb[3]: </span></strong>";
        echo '<ol>';
    }
    
    function paintCaseEnd($test_name) {
        parent::paintCaseEnd($test_name);
        echo '</ol>';
    }

    function paintPass($message) {
        parent::paintPass($message);  //does a $this->_passes++ count.
        echo "<li><div class=\"pass_div\"><span class=\"pass_span\">";
        $breadcrumb = $this->getTestList();
        /* 0 RunTest.php
         * 1 RunTest
         * 2 Sub test path (ie.BuilderDownloadTest.php)
         * 3 sub test file name (BuilderDownloadTesT)
         * 4 test method
         */
        echo "$breadcrumb[4]</span><br/>";
        echo "<span class=\"pass_msg\">$message</span>";
        echo "</li></div>";
    }

    function paintFail($message) {
        $this->_fails++;
        echo "<li><div class=\"entry_div\"><strong><span class=\"fail_span\">";
        $breadcrumb = $this->getTestList();
        /* 0 RunTest.php
         * 1 RunTest
         * 2 Sub test path (ie.BuilderDownloadTest.php)
         * 3 sub test file name (BuilderDownloadTesT)
         * 4 test method
         */
        echo "$breadcrumb[4]</span></strong><br/>";
        echo "<span class=\"msg_span fail_span\">$message</span>";
        echo "</li></div>";
    }
 
    function _getCss() {
        return parent::_getCss() . 
            " body{
                font-family: 'trebuchet ms',verdana,arial; 
                font-size: 0.9em;
              }

              h1 {
                background-color:#0066BB;
                color:white;
                font-size:large;
                padding:15px;
              }

              div.entry_div{
                padding-bottom: 0.2em;
              }

              span.msg_span {
                padding-left: 2em;
              } 
              
              span.pass_span {
                color: green;
              }

              span.fail_span {
                color: red;
              }
            ";
    }
}
?>
