<?php
require_once("./config/dmsDefaults.php");
		
	
if(checkSession()) {
	

	require_once("$default->owl_fs_root/presentation/webPageTemplate.inc");
	
$Center = "
	<br></br>
	<b> Welcome to the Administration Section:</b>
	<br></br>
	<p>Please make a selection from the sidemenu.</ul></p>
	";
	
	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml($Center); 
	$main->setCentralPayload($oPatternCustom);
	$main->render();
	$main->render();
}

?>

