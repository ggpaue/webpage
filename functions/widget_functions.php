<?php

function displayWidget($widget,$table,$record_id){
	
	require('../../../secure/connect_admin.php');
	require('../library/library.php');
	
	switch($widget){
		
		case 'events':
		include('../../admin_and_tools/widgets/events_widget.php');
		break;
		
		case 'image':
		include('image_widget.php');
		break;
		
		case 'item':
		include('item_widget.php');
		break;
		
		case 'location':
		include('location_widget.php');
		break;
		
		case 'map_info':
		include('map_info_widget.php');
		break;
		
		case 'people':
		include('people_widget.php');
		break;
		
		case 'podcasts':
		include('podcasts_widget.php');
		break;
		
		default:
		echo 'Error adding widget.';
		break;
		
	}
	
}


?>