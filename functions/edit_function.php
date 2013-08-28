<?php

function displayEditForm($table,$record_id,$attached_to = FALSE){
	
	//GET LIBRARY
	require('../library/library.php');
	
	// GET ALL OF THE COLUMS FROM THE TBALE PARAMETER
	$columns = getTableColumns($table);
	
	
	// MAKE FORM ADD OR EDIT, DEPENDING ON IF RECORD ID IS PROVIDED 
	if($record_id != ''){
		$record = getRecord($table,$record_id);
	}else{
		foreach($columns  as $column_key => $column){
			$record[$column['Field']] = '';
		}
	}
	
	$form = '';
	
	$form .= '<input type="hidden" name="record_id" value="' . $record_id . '" />';
	$form .= '<input type="hidden" name="table" value="' . $table . '" />';
	
	if(basename($_SERVER['SCRIPT_NAME']) == 'edit.php'){
		$form .= '<input type="hidden" name="query_type" value="UPDATE" />';
	}elseif(basename($_SERVER['SCRIPT_NAME']) == 'add.php'){
		$form .= '<input type="hidden" name="query_type" value="INSERT" />';
	}
	
	if($table == 'item'){
		include('../widgets/live_widget.php');
		include('../widgets/name_category_widget.php');
		include('../widgets/location_widget.php');
		include('../widgets/description_widget.php');
	}elseif($table == 'people'){
		include('../widgets/name_widget.php');
		include('../widgets/contact_info_widget.php');
		include('../widgets/bio_widget.php');
	}elseif($table == 'podcasts'){
		include('../widgets/live_widget.php');
		include('../widgets/podcast_title_widget.php');
		include('../widgets/podcast_embed_widget.php');
		include('../widgets/podcast_categories_widget.php');
		include('../widgets/podcast_description_widget.php');
	}elseif($table == 'tour_stops'){
		include('../widgets/tour_stop_widget.php');
		include('../widgets/location_widget.php');
	}elseif($table == 'tours'){
		include('../widgets/live_widget.php');
		include('../widgets/tour_widget.php');
	}elseif($table == 'single_events'){
		include('../widgets/live_widget.php');
		include('../widgets/events2_widget.php');
		include('../widgets/location_widget.php');
		include('../widgets/description_widget.php');
	}elseif($table == 'lists'){
		echo 'lists';
	}	
	
	
	// submit button
	$form .= '<div class="footer"><input class="fg-button ui-state-highlight ui-corner-all" name="submit" type="submit" value="save changes"></div>';
	
	return $form;

}

?>