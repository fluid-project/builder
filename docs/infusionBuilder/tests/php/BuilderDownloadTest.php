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
 * Tests infusion package download.
 */

require_once (TESTCASE_PATH.DISTANT_PATH.'PostClass.php');

class BuilderDownloadTest extends WebTestCase {    
    var $_infusion_builder_url;
    var $_version;
    var $db;    //database object

    //constructor
    function __construct(){
        $this->_infusion_builder_url = TEST_INFUSION_BUILDER_URL;
        $pc = new PostClass();
        $this->_version = $pc->getFluidVersionNumber();

        //setup db 
        $this->db = @mysql_connect(DB_HOST, DB_USER, DB_PASS);	
        @mysql_select_db(DB_NAME, $this->db);
    }

    /**
     * Test valid post response code from builder.php
     */
    function testPostValidRespondCode(){
        $params = array('Download'           => 'Download', 
                        'moduleSelections'   => 'jQuery',
                        'typeSelections'     => 'minified');
        $this->post($this->_infusion_builder_url, $params);
        $this->assertResponse(200);
    }

    /**
     * Test invalid post response code from builder.php
     */
    function testPostInvalidRespondCode(){
        $params = array('Download'          => 'Download', 
                        'moduleSelection'   => 'jQuery',
                        'typeSelection'     => 'minified');
        $this->post($this->_infusion_builder_url, $params);
        $this->assertResponse(400);
    }
    
    /**
     * Test insert into cache table after a successful post.  
     */
    function testCacheTableEntry(){
        $params = array('Download'           => 'Download', 
                        'moduleSelections'   => 'jQuery',
                        'typeSelections'     => 'source');
        $this->post($this->_infusion_builder_url, $params);

        //downloaded, check if database has this entry
        $expected = '17_'.$this->_version;
        $sql = "SELECT id FROM cache WHERE id='$expected' AND minified=0";
        $result = mysql_query($sql, $this->db);
        list($actual) = mysql_fetch_array($result);
        $this->assertEqual($actual, $expected);
    }

    /**
     * Test database cache table after a successful post
     * After testCacheTableEntry() (testcase above), the table entry with 
     * [key=17_version, type=0] should increment by 1.
     */
    function testCacheIncrement(){        
        $version = '17_'.$this->_version;
        $sql = "SELECT counter FROM cache WHERE id='$version' AND minified=0";

        //get the current count
        $result = mysql_query($sql, $this->db);
        list($pre_counter) = mysql_fetch_array($result);
        //perform a download
        $params = array('Download'           => 'Download', 
                        'moduleSelections'   => 'jQuery',
                        'typeSelections'     => 'source');
        $this->post($this->_infusion_builder_url, $params);

        //posted, check if value is incremented by 1
        $result = mysql_query($sql, $this->db);
        list($post_counter) = mysql_fetch_array($result);

        $this->assertEqual($pre_counter+1, $post_counter);
    }

    /**
     * Test response text from builder.php with TypeSelections = Minified
     */
    function testTypeMinified(){
        $params = array('Download'           => 'Download', 
                        'moduleSelections'   => 'jQuery',
                        'typeSelections'     => 'minified');
        $this->post($this->_infusion_builder_url, $params);
        $this->assertText('["17_'.$this->_version.'",1]');
    }
    
    /**
     * Test response text from builder.php with TypeSelections = Source 
     */
    function testTypeSource(){
        $params = array('Download'           => 'Download', 
                        'moduleSelections'   => 'jQuery',
                        'typeSelections'     => 'source');
        $this->post($this->_infusion_builder_url, $params);
        $this->assertText('["17_'.$this->_version.'",0]');
    }

    /**
     * Test multiple module download - minified
     */
    function testMultipleModuleDownloadMin(){
        $params = array('Download'           => 'Download', 
                        'moduleSelections'   => 'progress,jQuery,jQueryUICore,framework',
                        'typeSelections'     => 'minified');
        $this->post($this->_infusion_builder_url, $params);
        $this->assertText('["0_9_17_18_'.$this->_version.'",1]');
    }
    
    /**
     * Test multiple module download - source
     */

    function testMultipleModuleDownloadSrc(){
        $this->setConnectionTimeout(300); 
        $params = array('Download'           => 'Download', 
                        'moduleSelections'   => 'framework,renderer,progress,undo,fastXmlPull,jQuery,jQueryUICore',
                        'typeSelections'    => 'source');
        $this->post($this->_infusion_builder_url, $params);        
        $this->assertText('["0_6_9_13_15_17_18_'.$this->_version.'",0]');
    }    
}
?>
