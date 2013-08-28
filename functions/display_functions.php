<?php

function fixChars($text){

	// First, replace UTF-8 characters.
   $text = str_replace(
   array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
   array("'", "'", '"', '"', '-', '--', '...'),
   $text);
   // Next, replace their Windows-1252 equivalents.
   $text = str_replace(
   array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
   array("'", "'", '"', '"', '-', '--', '...'),
   $text);	
   
   return $text;
   
}

function cleanData($data){

	$data = htmlspecialchars($data);
	$data = str_replace(chr(11)," ",$data); // Vert-Tab from FM Ascii(11)
	$data = str_replace(chr(29),"",$data); // Repetition Character Ascii(29)
	//$data = utf8_encode($data);
	return $data;

}

function getRecord($table,$record_id,$order_by=''){
	
	$q = "SELECT * FROM $table WHERE record_id = '$record_id' $order_by";
	$r = @mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	$num_rows = mysql_num_rows($r);

	if($num_rows == 1){
		return $row;
	}

}

function noteView($table,$record_id){
	
	return '';

}

function getItemByPermalink($permalink){
	
	$q = "SELECT * FROM item WHERE record_id = '$permalink'";
	$r = @mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	$num_rows = mysql_num_rows($r);

	if($num_rows == 1){
		return $row;
	}else{
		return getRecord('item','00001');
	}

}

function getYear($record_id){
	
	$q = "SELECT end_year FROM events WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return $row['end_year'];

}

function getItemsByDistance($lat,$lng,$category,$distance){

	$q = "SELECT record_id, name_title, main_category, sub_category, category, latitude, longitude, ROUND((((acos(sin((" . $lat . " * pi() / 180 )) * sin(( latitude * pi() / 180 ))+cos((" . $lat . " * pi() / 180 )) * cos(( latitude * pi() / 180 )) * cos((( " . $lng . " - longitude ) * pi() / 180)))) * 180 / pi()) * 60 * 1.1515 ), 2) AS distance FROM item WHERE sub_category = '" . $category . "' AND live = '1' HAVING distance < '" . $distance . "'";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$items[] = $row;
		}
	}else{
		$items = array();
		$count = 0;
		$items_near_point = array('query' => $q, 'count' => $count, 'items' => $items);
		return $items_near_point;
	}
	
	$items_near_point = array('query' => $q, 'count' => $count, 'items' => $items);
	
	return $items_near_point;
	
}

function getNearbyItems($lat,$long,$current_id){

	$q = "SELECT record_id, name_title, latitude, longitude, (((acos(sin((" . $lat . "* pi()/180 )) * sin((latitude * pi()/180 ))+cos((" . $lat . " * pi()/180)) * cos((latitude * pi()/180)) * cos(((" . $long . "- longitude) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344 ) * 5280 AS distance FROM item WHERE record_id != '$current_id' AND live = '1' ORDER BY distance ASC LIMIT 7";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$nearby_info[] = $row;
		}
	}else{
		$nearby_info = array();
		$nearby_items = array('count' => $count, 'nearby_info' => $nearby_info);
		return $nearby_items;
	}
	
	$nearby_items = array('count' => $count, 'nearby_info' => $nearby_info);
	
	return $nearby_items;
	
}

function getNearbyAreas($point,$area_id){

	$point = explode(',',$point);
	$lat = trim($point[0]);
	$lng = trim($point[1]);

	$q = "SELECT 
			record_id,
			name_title,
			center,
			type,
			SUBSTRING_INDEX( `center`, ', ', 1 ) AS latitude,
			SUBSTRING_INDEX( `center`, ', ', -1 ) AS longitude,
			(((acos(sin((" . $lat . "* pi()/180 )) * sin((SUBSTRING_INDEX( center, ', ', 1 ) * 
			pi()/180 ))+cos((" . $lat . " * pi()/180)) * cos((SUBSTRING_INDEX( center, ', ', 1 ) * pi()/180)) * 
			cos(((" . $lng . "- SUBSTRING_INDEX( center, ', ', -1 )) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344 ) 
			* 5280 AS distance 
			FROM 
			item 
			WHERE
			center != '' AND
			record_id != '$area_id' AND 
			live = '1' 
			ORDER BY distance ASC 
			LIMIT 7";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$nearby_info[] = $row;
		}
	}else{
		$nearby_info = array();
		$nearby_areas = array('count' => $count, 'nearby_info' => $nearby_info);
		return $nearby_areas;
	}
	
	$nearby_areas = array('count' => $count, 'nearby_info' => $nearby_info);
	
	//echo '<pre>',print_r($nearby_info),'</pre>';
	
	return $nearby_areas;
	
}

function getRelatedAreas($table,$record_id){
	
	if($table == 'people'){
		$q = "SELECT * FROM polygons_people WHERE people_id = '$record_id'";
	}elseif($table == 'podcasts'){
		$q = "SELECT * FROM polygons_podcasts WHERE podcasts_id = '$record_id'";
	}else{
		return FALSE;	
	}
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$area_info = getRecord('polygons',$row['polygons_id']);
			$record_ids[] = $area_info['record_id'];
		}
	}else{
		$record_ids = array();
		$related_areas = array('count' => $count, 'record_ids' => $record_ids);
		return $related_areas;
	}
	
	$related_areas = array('count' => $count, 'record_ids' => $record_ids);
	
	return $related_areas;
	
}

function getRelatedItems($table,$record_id){
	
	if($table == 'people'){
		$q = "SELECT * FROM item_people WHERE people_id = '$record_id'";
	}elseif($table == 'podcasts'){
		$q = "SELECT * FROM item_podcast WHERE podcasts_id = '$record_id'";
	}elseif($table == 'tour_stops'){
		$q = "SELECT * FROM tour_stop_item WHERE tour_stops_id = '$record_id' ORDER BY item_order";
	}elseif($table == 'item'){
		return 'Items cannot be related to items';
		$related_items = array('count' => 0, 'record_ids' => array());
		return $related_items;
	}
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$item_info = getRecord('item',$row['item_id']);
			$record_ids[] = $item_info['record_id'];
		}
	}else{
		$record_ids = array();
		$related_items = array('count' => $count, 'record_ids' => $record_ids);
		return $related_items;
	}
	
	$related_items = array('count' => $count, 'record_ids' => $record_ids);
	
	return $related_items;
	
}

function getRelatedImages($table,$record_id,$inc_comp = FALSE){

	$q = "SELECT * FROM new_images WHERE attached_table = '$table' AND attached_to = '$record_id' ORDER BY default_image DESC, img_order ASC";
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$image_info = getRecord('new_images',$row['record_id']);
			$record_ids[] = $image_info['record_id'];
		}
	}else{
		$record_ids = array();
		$related_images = array('count' => $count, 'inc_comp' => $inc_comp, 'record_ids' => $record_ids);
		return $related_images;
	}
	
	$related_images = array('count' => $count, 'inc_comp' => $inc_comp, 'record_ids' => $record_ids);
	
	return $related_images;
	
}

function getComparisonImages($table,$record_id){

	$q = "SELECT * FROM new_images 
			WHERE 
			attached_table = '$table' AND 
			attached_to = '$record_id' AND
			img_compare = '1'
			ORDER BY img_order ASC";
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count != 2){
		return FALSE;
	}else{
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$row['filename_php'] = '/home/culture/public_html/media/new_images/' . $row['record_id'] . '/original.jpg';
			$row['filename_html'] = '/media/new_images/' . $row['record_id'] . '/original.jpg';
			$imgsize = getimagesize($row['filename_php']);
			$row['orientation'] = ($imgsize[0] >= $imgsize[1] ? 'landscape' : 'portrait');
			$row['width'] = $imgsize[0];
			$row['height'] = $imgsize[1];
			$rows[] = $row;
		}
		// calculate differences
		$rows[0]['min_dim'] = ( $row['orientation'] == 'landscape' ? 'height' : 'width' ); 
		
		// return
		return $rows;
	}
	
}

function getPodcastCount($count_for,$record_id){
	
	if($count_for == 'item'){
		$q = "SELECT * FROM item_podcast WHERE item_id = '$record_id'";
	}else if($count_for == 'people'){
		$q = "SELECT * FROM podcast_people WHERE people_id = '$record_id'";
	}
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	return $count;
	
}

function getRelatedPodcasts($table,$record_id){
	
	if($table == 'people'){
		$q = "SELECT * FROM podcast_people WHERE people_id = '$record_id'";
	}elseif($table == 'item'){
		$q = "SELECT * FROM item_podcast WHERE item_id = '$record_id'";
	}elseif($table == 'tour_stops'){
		$q = "SELECT * FROM tour_stop_podcast WHERE tour_stops_id = '$record_id'";
	}elseif($table == 'polygons'){
		$q = "SELECT * FROM polygons_podcasts WHERE polygons_id = '$record_id'";
	}elseif($table == 'podcasts'){
		return 'Podcasts cannot be related to podcasts';
		$related_podcasts = array('count' => 0, 'record_ids' => array());
		return $related_podcasts;
	}
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$podcast_info = getRecord('podcasts',$row['podcasts_id']);
			$podcasts[] = $podcast_info['record_id'];
		}
	}else{
		$podcasts = array();
		$related_podcasts = array('count' => $count, 'record_ids' => $podcasts);
		return $related_podcasts;
	}
	
	$related_podcasts = array('count' => $count, 'record_ids' => $podcasts);
	
	return $related_podcasts;
	
}

function getRelatedPodcastsPPV($record_id, $city){
	

	$q = "SELECT * 
			FROM podcast_people 
			LEFT OUTER JOIN podcasts
				ON  podcast_people.podcasts_id = podcasts.record_id							
			LEFT OUTER JOIN tags ON  
				tags.attached_to = podcasts.record_id AND
				tags.attached_table = 'podcasts'
			WHERE 
			people_id = '$record_id' AND
			tags.tag LIKE 'The Podcast Project: $city%' AND
			live = '1'
			ORDER BY podcasts.title";
	
	$r = mysql_query($q) OR die('<strong>[ ERROR: ' . __FUNCTION__ . ' ]</strong> unable to execute query <br /><br /><i>' . $q . '</i>:<br /><br />' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$podcast_info = getRecord('podcasts',$row['podcasts_id']);
			$podcasts[] = $podcast_info['record_id'];
		}
	}else{
		$podcasts = array();
		$related_podcasts = array('count' => $count, 'record_ids' => $podcasts);
		return $related_podcasts;
	}
	
	$related_podcasts = array('count' => $count, 'record_ids' => $podcasts);
	
	return $related_podcasts;
	
}

function getRelatedEvents($table,$record_id){

	$q = "SELECT * 
			FROM events 
			WHERE 
			attached_table = '$table' AND 
			attached_to = '$record_id' 
			ORDER BY end_year ASC";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$event = getRecord('events',$row['record_id']);
			$record_ids[] = $event['record_id'];
		}
	}else{
		$record_ids = array();
		$related_events = array('count' => $count, 'record_ids' => $record_ids);
		return $related_events;
	}
	
	$related_events = array('count' => $count, 'record_ids' => $record_ids);
	
	return $related_events;
	
}

function getRelatedDesignations($table,$record_id){

	$q = "SELECT record_id FROM designations WHERE attached_table = '$table' AND attached_to = '$record_id' ORDER BY desig_type DESC, date ASC";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$record_ids[] = $row['record_id'];
		}
	}else{
		$record_ids = array();
		$related_designations = array('count' => $count, 'record_ids' => $record_ids);
		return $related_designations;
	}
	
	$related_designations = array('count' => $count, 'record_ids' => $record_ids);
	
	return $related_designations;
	
}

/******* Name Title Functions  *******/

function displayNameTitle($item_row){

	if($item_row['name_title'] != ''){
		echo '<span class="title">',$item_row['name_title'],'</span>';	
	}
	
}

function displayInfo($item_row,$people_array,$events_array,$designations_array){
	
	echo '<div class="small">';
	
	if($item_row['sub_category'] == 'Artworks'){
		
		if($item_row['materials'] != ''){
			echo '<div class="infoheading">Materials</div>';
			if(isset($need_comma) && $need_comma == 1){
				echo ', ';
			}
			echo $item_row['materials'];
			$need_comma = 1;
		}
		
		displayEvents($events_array);
		displayDesignations($designations_array);
		
	}else{
		
        displayEvents($events_array);
		displayDesignations($designations_array);
		
	}
	
	echo '</div>';
	
}

function displayAreaInfo($item_row,$people_array,$events_array,$designations_array){
	
	echo '<div class="small">';
	displayEvents($events_array);
	displayDesignations($designations_array);
	echo '</div>';
	
}


function getItemNameTitle($record_id){
	
	$q = "SELECT name_title FROM item WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable execute <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return $row['name_title'];

}

/******* People Functions  *******/

function getRelatedPeople($table,$record_id,$role = 'all',$is_primary = 2){
	
	if($role == 'all'){
		$role_q = "";
	}else{
		$role_q = " AND role = '$role' ";
	}
	
	if($is_primary == 2){
		$is_primary_q = '';
	}else{
		$is_primary_q = " AND is_primary = '$is_primary' ";
	}
	
	if($table == 'item'){
		$q = "SELECT * 
				FROM item_people
    			LEFT OUTER JOIN people
    				ON  people.record_id = item_people.people_id
				WHERE 
				item_id = '$record_id' $role_q $is_primary_q 
				ORDER BY ppl_order ASC, role ASC, people.name ASC";
	}elseif($table == 'podcasts'){
		$q = "SELECT * FROM podcast_people WHERE podcasts_id = '$record_id' ORDER BY ppl_order";
	}elseif($table == 'polygons'){
		$q = "SELECT * FROM polygons_people WHERE polygons_id = '$record_id' $role_q $is_primary_q ORDER BY ppl_order ASC, role ASC";			
	}else{
		die('error - people can only be related to items or podcasts'); 
	}
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0 && ($table == 'item' || $table == 'polygons')){
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			
			$person_info = getRecord('people',$row['people_id']);
			$record_ids[] = $person_info['record_id'];
			
		}
		
	}elseif($count > 0 && $table == 'podcasts'){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			
			$person_info = getRecord('people',$row['people_id']);
			$record_ids[] = $person_info['record_id'];
			
		}
	}else{
		$record_ids = array();
		$primary_ids = array();
		$non_primary_ids = array();
		$record_ids = array('count' => $count, 'record_ids' => $record_ids);
		return $record_ids;
	}
	
	if(empty($non_primary_ids)){
		$non_primary_ids = array();
	}
	
	if(empty($primary_ids)){
		$primary_ids = array();
	}
	
	$record_ids = array('count' => $count, 'record_ids' => $record_ids);
	
	return $record_ids;

}

function getPrimaryRelatedPeople($table,$record_id){
	
	$count = 0;
	$record_ids = array();
	
	$q = "SELECT * FROM item_people WHERE item_id = '$record_id' AND is_primary = '1' ORDER BY role";
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$person_info = getRecord('people',$row['people_id']);
		$record_ids[] = $person_info['record_id'];
	}

	$record_ids = array('count' => $count, 'record_ids' => $record_ids);
	
	return $record_ids;
	
}

function displayPrimaryPeopleList($people_array = array()){
	
	if(count($people_array['primary_ids']) > 0){
		foreach($people_array['primary_ids'] as $primary_person_id){
			$primary_people[] = getPersonName($primary_person_id,'FL');
		}
		
		echo implodeToEnglish($primary_people);
	}else{
		// Do nothing
	}
	
}

function displayPodcastersList($people_array,$podcast_id){
	if(count($people_array['record_ids']) > 0){
		foreach($people_array['record_ids'] as $person_id){
			$role = getPersonRole('podcasts',$person_id,$podcast_id);
			$name = getPersonName($person_id,'FL');
			$listing = $name;
			if($role != ''){
				 $listing .= ', ' . $role;	
			}
			$people[] = $listing;
		}
		echo implodeToEnglish($people);
	}else{
		// Do nothing
	}
}

function displayPeopleList($people_array){
	if(count($people_array['record_ids']) > 0){
		foreach($people_array['record_ids'] as $person_id){
			$people[] = getPersonName($person_id,'FL');
		}
		echo implodeToEnglish($people);
	}else{
		// Do nothing
	}
}

function returnPeopleList($people_array){
	if(count($people_array['record_ids']) > 0){
		foreach($people_array['record_ids'] as $person_id){
			$people[] = getPersonName($person_id,'FL');
		}
		$podcasters = implodeToEnglish($people);
		return $podcasters;
	}else{
		//do nothing
	}
}

function displayOtherPeople($people_array){
	if(count($people_array['non_primary_ids']) > 0){
		foreach($people_array['non_primary_ids'] as $record_id){
			echo '<div class="mediumsmall">',displayPersonLong($record_id),'</div>';
		}
	}
}

function displayPersonLong($person_id){
	
	return 'First and last';
	
}

function displayCategory($item_row){
	
	if($item_row['sub_category'] != ''){
		echo '<span class="small"><i>',$item_row['sub_category'];
		if($item_row['type'] != ''){
			echo ': ',$item_row['type'];
		}
		echo '</i></span>';
	}
}

function displayLocationInformation($item_row){

	$address = fixAddress($item_row);

	if($address != ''){
		echo '<div class="infoheading">Location</div>';
		echo '<div class="mediumsmall">',$address,'</div><br />';	
	}
	
	if($item_row['detailed_loc'] != ''){
		echo '<div class="small">',$item_row['detailed_loc'],'</div>';
	}
	
}

function displayEvents($events_array = array()){

	if($events_array['count'] > 0){
		echo '<div class="infoheading">Dates</div><ul>';
		foreach($events_array['record_ids'] as $record_id){
			echo '<li class="small">',displayLongEvent($record_id),'</li>';
		}
		echo '</ul>';
	}
}

function displayDesignations($designations_array = array()){

	if($designations_array['count'] > 0){
		$current_type = 'xxxxxx';
		foreach($designations_array['record_ids'] as $record_id){
			$designation = getRecord('designations',$record_id);
			if($current_type != $designation['desig_type']){
				if($current_type != 'xxxxxx'){
					echo '</ul>';
				}
				switch($designation['desig_type']){
					
					case 'landmark':
					echo '<div class="infoheading">Landmark Designations</div><ul>';
					break;
					
					case 'award':
					echo '<div class="infoheading">Awards</div><ul>';
					break;
					
					default:
					echo '<div class="infoheading">Designations</div><ul>';
					break;
				}
				$current_type = $designation['desig_type'];	
			}
			echo '<li>';
			echo '<div class="small">',$designation['designation'];
			if($designation['date'] != '0000'){
				echo ' ',$designation['date'];	
			}
			if($designation['extra'] != ''){
				echo '<div style="margin-left:5px;" class="extrasmall">',$designation['extra'],'</div>';	
			}
			echo '</div>';
			echo '</li>';
		}
	}
}

function returnPodcasters($id)
{
	$people = getRelatedPeople('podcasts',$id);
	$podcasters = returnPeopleList($people);
	return $podcasters;
}

function showPrimaryYear($events_array = array()){

	if($events_array['count'] > 0){
		echo ', <span class="mediumsmall">';
		$primary_year = getRecord('events',$events_array['record_ids'][0]);
		echo $primary_year['end_year'];
		echo '</span>';
	}
}

function displayRemarks($remarks = '',$remarks_attribution = ''){

	$display_remarks = '';

	if($remarks != ''){
		$display_remarks .= '<div class="infoheading">Description</div>';
		$display_remarks .= '<div class="small">' . nl2br(constrainLongText($remarks,500)) . '</div>';
		$display_remarks .= '<div class="small gray text-r"><em>' . $remarks_attribution . '</em></div>';
		if(strlen($remarks) > 500){
			$display_remarks .= '<div class="rightalign small"><a class="pointer extrasmall" id="open_remarks"><em>read more</em></a></div>';
		}
	}
	
	return $display_remarks;
}

function displayPodcasts($podcasts_array = array()){

	if($podcasts_array['count'] > 0){ ?>
		
        <script language="javascript">
		function popUp(podcast_id) {
		var jPlayer = window.open('http://culturenow.org/ui/jPlayer/audioPlayer.php?podcast_id=' + podcast_id, 'jPlayer', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=480,height=440,left = 25,top = 0');
		if(jPlayer) jPlayer.focus();
		return false;
	}
	</script>
        
		<div class="infoheading"><img height="12" src="/ui/images/headphones.png" />&nbsp;Podcasts</div>
        
        <table class="nearby" width="100%" cellspacing="0" cellpadding="0">
        
        <?php
		
		foreach($podcasts_array['record_ids'] as $podcast_id):
			$podcast = getRecord('podcasts',$podcast_id);
			$podcasters = getRelatedPeople('podcasts',$podcast_id); ?>
			<tr>
			<td valign="top" align="left" width="35">
			<a class="pointer" onClick="popUp('<?php echo $podcast_id; ?>')">
			<img src="ui/images/button-play.png" />
			</a>
			</td>
			<td>
            <div class="small">
            <strong><?=$podcast['title'];?></strong> - <?=displayPeopleList($podcasters);?>
            </div>
            </td>
            <td align="right" style="padding-left:5px;">
            <div class="extrasmall pointer">
			<a id="open_podcast" record_id="<?=$podcast_id?>">about</a>
            </div>
			</td>
			</tr>
            
            <div id="full_podcast_<?=$podcast_id?>" title="podcast information" style="display:none">
            
            <span class="small"><?=$podcast['write_up']?></span><hr />
            <span class="small">
            <?php
            foreach($podcasters['record_ids'] as $value){
          
				$podcaster_record = getRecord('people',$value);
				
				echo '<strong>',fixName($podcaster_record['name_first'],$podcaster_record['name'],'FL'),'</strong> ',$podcaster_record['write_up'],'<br /><br />'; 
			
			}
			?>
			</span>
            </div>
            
		<?php endforeach; ?>
		
		</table>
        
	<?php	
    }else{
		echo '<div class="infoheading">There are no podcasts available.</div>';
	}
}

function displayInlinePodcasts($podcast_id){ ?>
        
        <?php
		
		$podcast = getRecord('podcasts',$podcast_id);
		$podcasters = getRelatedPeople('podcasts',$podcast_id); ?>
		
		<img height="12" src="ui/images/headphones.png" />
		<span id="mp3"><?=$podcast['file_name']?></span>
		<div class="small">
		<?=displayPeopleList($podcasters);?>
		</div>
	
	<?php 
}

function displayTourPodcasts($podcasts_array = array()){

	if($podcasts_array['count'] > 0){ ?>
		
        <table class="nearby" width="100%" cellspacing="0" cellpadding="0">
        
        <?php
		
		foreach($podcasts_array['record_ids'] as $podcast_id):
			$podcast = getRecord('podcasts',$podcast_id);
			$podcasters = getRelatedPeople('podcasts',$podcast_id); ?>
			<tr>
			<td align="left" valign="top" width="25">
			<img height="12" src="ui/images/headphones.png" />
            </td>
            <td align="left" valign="top" width="40">
            <span id="mp3"><?=$podcast['file_name']?></span>
			</td>
			<td valign="top" align="left">
            <div class="small">
			<?=displayPeopleList($podcasters);?>, <i><?=$podcast['title'];?></i>
            </div>
            </td>
			</tr>
		<?php endforeach; ?>
		
		</table>
	<?php	
    }else{
		echo '<div class="infoheading">There are no podcasts for this section of the tour.</div>';
	}
}

function makeSEO($table,$record_id){
	
	$row = getRecord($table,$record_id);
	$people = getRelatedPeople($table,$record_id);
	$people_list = returnPeopleList($people);
	
	$name_title =  preg_replace("/[^a-zA-Z0-9\s]/","",$row['name_title']);
	$people_list = preg_replace("/[^a-zA-Z0-9\s]/","",$people_list);
	
	$seo_string = str_replace(' ','-',$name_title . '_' . $people_list);
	$seo_string = str_replace('--','-',$seo_string);
	
	return $seo_string;
	
}

function makePageTitle($table,$record_id){
	
	$row = getRecord($table,$record_id);
	$people = getRelatedPeople($table,$record_id);
	$people_list = returnPeopleList($people);

	$title = $row['name_title'] . ': ' . $people_list;
	
	return $title;
	
}

function displayNearby($nearby_array = array()){

	if(count($nearby_array['count']) > 0){
		
		echo '<div class="infoheading">Nearby Items</div>';
		echo '<table class="nearby" width="100%" cellpadding="0" cellspacing="0">';
		
		$i = 1;
		foreach($nearby_array['nearby_info'] as $nearby_item){
		
			echo '<tr class="alt2" onClick="Link(\'entry&permalink=',$nearby_item['record_id'],'&seo=',makeSEO('item',$nearby_item['record_id']),'\')">';
			echo '<td width="56%">';
			echo '<span class="small">',$nearby_item['name_title'],'</span>';
			echo '</td>';
			echo '<td width="10"></td>';
			echo '<td align="right"  width="23%">';
			echo '<span class="small">',readableDistance($nearby_item['distance']),'</span></div>';
			echo '</td>';
			echo '<td width="10"></td>';
			echo '<td width="10" align="right"><img height="10" src="ui/images/arrow-icon.png" /></td>';
			echo '</tr>';
			$i = $i + 1;
			
		}
		echo '</table>';
	}else{
		echo '';
	}
}

function displayNearbyAreas($nearby_array = array()){

	if(count($nearby_array['count']) > 0){
		
		echo '<div class="infoheading">Nearby Areas</div>';
		echo '<table class="nearby" width="100%" cellpadding="0" cellspacing="0">';
		
		$i = 1;
		foreach($nearby_array['nearby_info'] as $nearby_item){
		
			echo '<tr class="alt2" onClick="Link(\'areas&area_id=',$nearby_item['record_id'],'\')">';
			echo '<td width="56%">';
			echo '<span class="small">',$nearby_item['name_title'];
			if($nearby_item['type'] == 'Historic Districts'){
				echo ' Historic District';	
			}
			echo '</span>';
			echo '</td>';
			echo '<td width="10"></td>';
			echo '<td align="right"  width="23%">';
			echo '<span class="small">',readableDistance($nearby_item['distance']),'</span></div>';
			echo '</td>';
			echo '<td width="10"></td>';
			echo '<td width="10" align="right"><img height="10" src="ui/images/arrow-icon.png" /></td>';
			echo '</tr>';
			$i = $i + 1;
			
		}
		echo '</table>';
	}else{
		echo '';
	}
}

function displayThumbnails($images_array = array()){
	
	$space = 1;
	while($space <= 100){
		if($space % 5 != 0){
			$spacer_positions[] = $space;
		}
		$space++;
	}
	
	$i = 0;

	foreach($images_array['record_ids'] as $record_id){ 				 

		$i++;	
		echo '<div id="thumb" image_id="',$record_id,'" class="thumbnail image-state-chosen">',displayThumbImageForItemPage($record_id),'</div>';
   		if(in_array($i,$spacer_positions)){
			echo '<div class="spacer"></div>';			
		}
		
	}
	
	if($images_array['inc_comp'] != FALSE){ 				 

		$i++;	
		echo '<div class="pointer enlarge-cityscape"><img src="/ui/images/enlarge-cityscape.png" height="77" width="77" /></div>';
   		if(in_array($i,$spacer_positions)){
			echo '<div class="spacer"></div>';			
		}
		
	}	

	while($i < count($images_array['record_ids'])){
		$i++;
		echo '<div class="thumbnail image-state-chosen"></div>';
   		if(in_array($i,$spacer_positions)){
			echo '<div class="spacer"></div>';			
		}
	}

}

function displayThumbnailsColumn($images_array = array()){
	
	$i = 1;
	foreach($images_array['record_ids'] as $record_id){ 				 
		$image_block[] = '<div id="thumb" image_id="' . $record_id . '" class="thumbnail_col image-state-chosen">' . displayThumbImageForItemPage($record_id) . '</div>';
		if($i == 5){ break; }
		$i++;
	}
	
	$glue = '<div style="height:13px;"></div>';
	echo join($glue,$image_block);
}

function displayAreaThumbnails($images_array = array()){

	$spacer_positions = array('1','2','3','4','6','7','8','9','11','12','13','14','16','17','18','19');

	$i = 0;

	foreach($images_array['record_ids'] as $record_id){ 				 

		$img_info = getRecord("new_images",$record_id);

		$i++;	
		echo '<div id="thumb" image_id="',$record_id,'" class="thumbnail image-state-chosen"><a rel="lightbox" title="&copy; ',$img_info['photo_credit'],'" href="http://www.culturenow.org/media/new_images/' . $record_id . '/web.jpg">',displayThumbImageForItemPage($record_id),'</a></div>';
   		if(in_array($i,$spacer_positions)){
			echo '<div class="spacer"></div>';			
		}
		
	}

	while($i < count($images_array['record_ids'])){
		$i++;
		echo '<div class="thumbnail image-state-chosen"></div>';
   		if(in_array($i,$spacer_positions)){
			echo '<div class="spacer"></div>';			
		}
	}

}

function displayThumbImageForItemPage($record_id){
	$html = '';
	if($record_id != ''){
		$html = '<img width="76" height="76" src="/media/new_images/' . $record_id . '/thumb.jpg">';
	}else{
		//$html = '<img width="76" height="76" src="/ui/images/blank.png">';
	}
		
	return $html;
}

function lightboxImages($image_array = array()){
	
	$html = '';
	
	$i = 1;
	foreach($image_array as $image_id){
		$html .= '<a rel="lightbox" href="http://www.culturenow.org/media/new_images/' . $image_id . '/web.jpg">';
		if($i == 1){
			$html .= displayThumbImageForItemPage($image_id);
		}
		$html .= '</a>';
		$i = $i + 1;
	}
	
	return $html;
}

function displayWebImageForItemPage($record_id){
	
	if($record_id == ''){
		$img = '../media/new_images/blank/web.jpg';	
	}else{
		$img = '../media/new_images/' . $record_id . '/web.jpg';
	}
	
	if(!file_exists($img)){
		echo '<div style="text-align:center">could not find image ' . $record_id . '</div>';
	}
	
	$size = getimagesize($img);
	
	$oldwidth = $size[0];
	$oldheight = $size[1];
	
	if($oldwidth >= $oldheight){ // landscape or square
		
		if($oldwidth > 410){
			$newwidth = 410;
			$newheight = ceil((410 / $oldwidth) * $oldheight);
		}else{
			$newwidth = $oldwidth;
			$newheight = $oldheight;
		}
		
	}elseif($oldheight > $oldwidth){ // portrait
		
		if($oldheight > 410){
			$newheight = 410;
			$newwidth = ceil((410 / $oldheight) * $oldwidth);
		}else{
			$newwidth = $oldwidth;
			$newheight = $oldheight;
		}
		
	}
	
	$margin_top = ceil((440 - $newheight) / 2) - 10  . 'px';
	$margin_bottom = ceil((440 - $newheight) / 2) + 10 . 'px';
	$margin_left_right = ceil((440 - $newwidth) / 2)  . 'px';
	
	/*
	echo $oldwidth,'<br />';
	echo $oldheight,'<br />';
	echo $newwidth,'<br />';
	echo $newheight,'<br />';
	echo $margin_top_bottom,'<br />';
	echo $margin_left_right,'<br />';
	*/
	
	echo '<div class="image" style="height:',$newheight,'px; width:',$newwidth,'px; margin: ',$margin_top,' ',$margin_left_right,' ',$margin_bottom,' ',$margin_left_right,';">
	<img width="',$newwidth,'" height="',$newheight,'" src="',$img,'" />
	</div>';

}

function displayLongEvent($event_id){
	
	$row = getRecord('events',$event_id);
	
	$event = '';
	
	if($row['end_year'] == '' && $row['circa'] == ''){
		return 'unknown year';
	}else{
	
		if($row['event_type'] != ''){	
			$event .= $row['event_type'] . ', ';
		}
		
		if($row['circa'] != ''){	
			$event .= 'ca. ';
		}
		if($row['end_year'] != '' && $row['end_year'] != '0000'){	
			$event .= $row['end_year'];
		}
		
	}
	
	return $event;
	
}

function readableDistance($distance){

	if($distance == 0){
		echo 'at this location';
	}elseif($distance > 1000){
		echo round(($distance / 5280),1) . ' miles';
	}else{
		echo round($distance) . ' feet';
	}

}

function fixAddress($array = array()){
	
	$address = '';
	
	if($array['loc_name'] != ''){
		$address .= '<b>' . $array['loc_name'] . '</b><br />';
	}
	
	if($array['add_number'] != '' && $array['add_street'] != ''){
		$address .= $array['add_number'] . ' ';
		$address .= $array['add_street'] . '<br />';
	}elseif($array['x_street_1'] != '' && $array['x_street_2'] != ''){
		$address .= $array['x_street_1'] . ' &amp; ';
		$address .= $array['x_street_2'] . '<br />';
	}
	
	if($array['add_two'] != ''){	
		$address .= $array['add_two'] . '<br />';
	}
	
	if($array['city'] != ''){	
		$address .= $array['city'] . ', ';
	}
	
	if($array['state'] != ''){	
		$address .= $array['state'];
	}
	
	if($array['zip'] != '' && $array['zip'] != '00000'){	
		$address .= ' ' . $array['zip'];
	}
	
	if($array['country'] != ''){	
		$address .= '<br />' . $array['country'];
	}
	
	return $address;
}

function fixAddressOneLine($array = array()){
	
	$address = '';
	
	if($array['add_number'] != '' && $array['add_street'] != ''){
		$address .= $array['add_number'] . ' ';
		$address .= $array['add_street'] . ', ';
	}elseif($array['x_street_1'] != '' && $array['x_street_2'] != ''){
		$address .= $array['x_street_1'] . ' &amp; ';
		$address .= $array['x_street_2'] . ', ';
	}
	
	if($array['add_two'] != ''){	
		$address .= $array['add_two'] . ' ';
	}
	
	if($array['city'] != ''){	
		$address .= $array['city'] . ', ';
	}
	
	if($array['state'] != ''){	
		$address .= $array['state'];
	}
	
	if($array['zip'] != '' && $array['zip'] != '00000'){	
		$address .= ' ' . $array['zip'];
	}

	return $address;
}

function shortLocation($array = array()){
	
	$address = '';
	
	if($array['loc_name'] != ''){
		$address .= $array['loc_name'];
	}
	
	if(($array['loc_name'] != '' && $array['add_number'] != '' && $array['add_street'] != '') || ($array['loc_name'] != '' && $array['x_street_1'] != '' && $array['x_street_2'] != '')){
		$address .= ' at ';
	}
	
	if($array['add_number'] != '' && $array['add_street'] != ''){
		$address .= $array['add_number'] . ' ';
		$address .= $array['add_street'];
	}elseif($array['x_street_1'] != '' && $array['x_street_2'] != ''){
		$address .= $array['x_street_1'] . ' &amp; ';
		$address .= $array['x_street_2'];
	}

	return $address;
}

function shortLocationFields($array = array()){
	
	$address = '';
	
	if($array['loc_name'] != ''){
		$address .= '<input type="text" field="loc_name" value="' . $array['loc_name'] . '" autocomplete="off" />';
		$address .= '<span class="loading hidden"><img src="/ui/images/loading.gif" height="12" /></span>';
	}
	
	if(($array['loc_name'] != '' && $array['add_number'] != '' && $array['add_street'] != '') || ($array['loc_name'] != '' && $array['x_street_1'] != '' && $array['x_street_2'] != '')){
		$address .= ' at ';
	}
	
	if($array['add_number'] != '' && $array['add_street'] != ''){
		$address .= '<input type="text" field="add_number" value="' . $array['add_number'] . '"  autocomplete="off" />';
		$address .= '<span class="loading hidden"><img src="/ui/images/loading.gif" height="12" /></span>';
		$address .= '<input type="text" field="add_street" value="' . $array['add_street'] . '"  autocomplete="off" />';
		$address .= '<span class="loading hidden"><img src="/ui/images/loading.gif" height="12" /></span>';
	}elseif($array['x_street_1'] != '' && $array['x_street_2'] != ''){
		$address .= '<input type="text" field="x_street_1" value="' . $array['x_street_1'] . '"  autocomplete="off" /> &amp; ';
		$address .= '<span class="loading hidden"><img src="/ui/images/loading.gif" height="12" /></span>';
		$address .= '<input type="text" field="x_street_2" value="' . $array['x_street_2'] . '"  autocomplete="off" />';	
		$address .= '<span class="loading hidden"><img src="/ui/images/loading.gif" height="12" /></span>';
	}

	return $address;
}

function implodeToEnglish ($array) { 
    // sanity check 
    if (!$array || !count ($array)) 
        return ''; 

    // get last element    
    $last = array_pop ($array); 

    // if it was the only element - return it 
    if (!count ($array)) 
        return $last;    

    return implode (', ', $array).' and '.$last; 
} 

function implodeToEnglishOr ($array) { 
    // sanity check 
    if (!$array || !count ($array)) 
        return ''; 

    // get last element    
    $last = array_pop ($array); 

    // if it was the only element - return it 
    if (!count ($array)) 
        return $last;    

    return implode (', ', $array).' or '.$last; 
} 

function implodeToCS($array) { 
    // sanity check 
    if (!$array || !count ($array)) 
        return '';   

    return implode (', ', $array); 
} 

function fixName($name_first,$name,$order = 'LF'){
	
	if($order == 'LF'){
		if($name_first == ''){	
			$fixed_name = $name;
		}elseif($name == ''){
			$fixed_name = $name_first;
		}else{
			$fixed_name = $name . ', ' . $name_first;
		}
	}elseif($order == 'FL'){
		if($name_first == ''){	
			$fixed_name = $name;
		}elseif($name == ''){
			$fixed_name = $name_first;
		}else{
			$fixed_name = $name_first . ' ' . $name;
		}
	}
	
	return $fixed_name;
}

function getPersonName($record_id,$order = 'LF'){
	
	$q = "SELECT name, name_first FROM people WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable execute <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return fixName($row['name_first'],$row['name'],$order);

}

function getPersonRole($table = 'item',$person_id,$item_id){
	
	if($table == 'item'){
		$q = "SELECT * FROM item_people WHERE people_id = '$person_id' AND item_id = '$item_id'";
	}elseif($table == 'podcasts'){
		$q = "SELECT podcaster_title FROM podcast_people WHERE people_id = '$person_id' AND podcasts_id = '$item_id'";
	}elseif($table == 'polygons'){
		$q = "SELECT * FROM polygons_people WHERE people_id = '$person_id' AND polygons_id = '$item_id'";
	}
	
	$r = mysql_query($q);
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	if($table == 'item'){
		return $row['role'];
	}elseif($table == 'podcasts'){
		return $row['podcaster_title'];
	}elseif($table == 'polygons'){
		return $row['role'];
	}
	

}

function constrainLongText($text,$length){
	
	$cut_text = '';
	
	if($text != ''){
		$cut_text = substr($text,0,$length);
		if(strlen($text) > $length){
			$cut_text = trim($cut_text) . '...';
		}
	}
	
	return $cut_text;
}

?>