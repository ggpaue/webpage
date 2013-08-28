<?php

function dropDownMenu($valuelist,$value,$name,$class=FALSE)
{
	$menu = '';
	
	$menu .= '<select class="' . $class . '" name="' . $name . '">';
	$menu .= '<option></option>';
	
	foreach($valuelist as $listvalue){
		
		$menu .= '<option';
		if($value == $listvalue['list_item']){
			$menu .= ' selected';
		}
		$menu .= '>' . $listvalue['list_item'] . '</option>';
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

function getLists(){

	$q = "SELECT DISTINCT list FROM lists ORDER BY list";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$lists = array();
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$lists[] = array('list_item' => $row['list']);
	}
	
	return $lists;
	
}

function getUsersList(){

	$q = "SELECT * FROM users";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$users = array();
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$users[] = array('list_item' => $row['user_id']);
	}
	
	return $users;
	
}

function getListItems($list){

	$q = "SELECT * FROM lists WHERE list = '$list' ORDER BY list_item_order, list_item_key + 0, list_item_key";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$list_items = array();
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$list_items[] = $row;
	}
	
	return $list_items;
	
}

?>