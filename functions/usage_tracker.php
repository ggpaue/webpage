<?php

function usageTracker()
{

	$adbc = mysql_connect ('localhost', 'culture_friend', '62frd32') OR die ('Could not connect to the database: ' . mysql_error() );
	mysql_select_db ('culture_culturenow') OR die ('Could not select the database: ' . mysql_error() );
	mysql_query("SET NAMES UTF8");
	
	$q = "INSERT 
			INTO usage_tracker 
			(request_time,
			 unique_id,
			 status_code,
			 remote_addr,
			 user_agent,
			 script_name,
			 query_string) 
			VALUES 
			('" . date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']) . "',
			 '" . session_id() . "',
			 '{$_SERVER['REDIRECT_STATUS']}',
			 '{$_SERVER['REMOTE_ADDR']}',
			 '{$_SERVER['HTTP_USER_AGENT']}',
			 '{$_SERVER['SCRIPT_FILENAME']}',
			 '{$_SERVER['QUERY_STRING']}')";
	$r = mysql_query($q) OR die('unable to execute query: ' . mysql_error());

	mysql_close($adbc);

}

?>