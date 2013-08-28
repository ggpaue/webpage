<?php

function setDebugging()
{
	$debug_file = "/home/culture/public_html/admin_and_tools/database/debug_status.txt";
	$fh = fopen($debug_file, 'r');
	$debug = fread($fh, filesize($debug_file));
	fclose($fh);
	
	$debug = json_decode($debug);
	
	switch($debug->setting){
	
		case 'off':
			error_reporting(E_NONE);
			ini_set('display_errors', '0');		
		break;
	
		case 'private':
			if($_SERVER['REMOTE_ADDR'] == $debug->ip){
				error_reporting(E_ALL);
				ini_set('display_errors', '1');
			}else{
				error_reporting(E_NONE);
				ini_set('display_errors', '0');	
			}
		break;
		
		case 'public':
			error_reporting(E_ALL);
			ini_set('display_errors', '1');
		break;
		
		default:
			error_reporting(E_NONE);
			ini_set('display_errors', '0');			
		break;
	}
	
}

?>