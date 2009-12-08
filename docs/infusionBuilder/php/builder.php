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
 * A utility page to allow the other php files to be placed somewhere else on the server.
 */

define("DISTANT_PATH", "../../../infusionBuilder-secure/php/");

include_once(DISTANT_PATH."PostClass.php");

if ( isset ($_GET['var']))
{
    include_once (DISTANT_PATH."jsonProcessor.php");
}
else if ( isset ($_POST[SELECTION_CHOICE]) && isset ($_POST[MODULE_SELECTIONS]))
{
    include_once (DISTANT_PATH."postProcessor.php");

}
else
{
    header("HTTP/1.0 400 Bad Request");
    header("Status: 400");
    echo "<html><body><p>Error</p></body></html>";
}

?>
