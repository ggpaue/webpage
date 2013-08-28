<?php

function cleanData($data){

	$value = htmlspecialchars($value);
	$value = str_replace(chr(11)," ",$value); // Vert-Tab from FM Ascii(11)
	$value = str_replace(chr(29),"",$value); // Repetition Character Ascii(29)
	return $value;

}

function getRecord($table,$record_id){
	
	$q = "SELECT * FROM $table WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	$num_rows = mysql_num_rows($r);

	if($num_rows == 1){
		return $row;
	}else{
		return getRecord($table,'00001');
	}

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

	$q = "SELECT record_id, name_title, category, latitude, longitude, ROUND((((acos(sin((" . $lat . " * pi() / 180 )) * sin(( latitude * pi() / 180 ))+cos((" . $lat . " * pi() / 180 )) * cos(( latitude * pi() / 180 )) * cos((( " . $lng . " - longitude ) * pi() / 180)))) * 180 / pi()) * 60 * 1.1515 ), 2) AS distance FROM item WHERE category = '" . $category . "' AND live = '1' HAVING distance < '" . $distance . "'";
	
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

	$q = "SELECT record_id, name_title, latitude, longitude, (((acos(sin((" . $lat . "* pi()/180 )) * sin((latitude * pi()/180 ))+cos((" . $lat . " * pi()/180)) * cos((latitude * pi()/180)) * cos(((" . $long . "- longitude) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344 ) * 5280 AS distance FROM item WHERE record_id != '$current_id' AND live = '1' ORDER BY distance ASC LIMIT 10";
	
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

function getRelatedImages($table,$record_id){

	$q = "SELECT * FROM new_images WHERE attached_table = '$table' AND attached_to = '$record_id'";
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$image_info = getRecord('new_images',$row['record_id']);
			$record_ids[] = $image_info['record_id'];
		}
	}else{
		$record_ids = array();
		$related_images = array('count' => $count, 'record_ids' => $record_ids);
		return $related_images;
	}
	
	$related_images = array('count' => $count, 'record_ids' => $record_ids);
	
	return $related_images;
	
}

function getRelatedPodcasts($table,$record_id){
	
	if($table == 'people'){
		$q = "SELECT * FROM podcast_people WHERE people_id = '$record_id'";
	}elseif($table == 'item'){
		$q = "SELECT * FROM item_podcast WHERE item_id = '$record_id'";
	}elseif($table == 'tour_stops'){
		$q = "SELECT * FROM tour_stop_podcast WHERE tour_stops_id = '$record_id'";
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

function getRelatedEvents($table,$record_id){

	$q = "SELECT * FROM events WHERE attached_to = '$record_id' ORDER BY end_year ASC";
	
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

/******* Name Title Functions  *******/

function displayNameTitle($item_row){

	if(count($item_row['name_title']) != ''){
		echo '<div class="title">',$item_row['name_title'],'</div>';	
	}
	
}

function displayInfo($item_row,$people_array,$events_array){
	
	/*
	print_r($item_row);
	print_r($people_array);
	print_r($events_array);
	*/
	
	echo '<div class="mediumsmall">';
	
	if($item_row['category'] == 'Artworks'){
		
		if($people_array['count'] > 0){
			echo displayPrimaryPeopleList($people_array);
			$need_comma = 1;
		}
		
		if($events_array['count'] > 0){
			if(isset($need_comma) && $need_comma == 1){
				echo ', ';
			}
			echo getYear($events_array['record_ids'][0]);	
			$need_comma = 1;
		}
		
		if($item_row['materials'] != ''){
			if(isset($need_comma) && $need_comma == 1){
				echo ', ';
			}
			echo $item_row['materials'];
			$need_comma = 1;
		}
		
		if($item_row['on_lists'] != ''){
			if(isset($need_comma) && $need_comma == 1){
				echo '<br />';
			}
			echo $item_row['on_lists'];	
		}
		
	}else{
		
        displayPeopleList($people_array);
		displayCategory($item_row);
		
		if($item_row['on_lists'] != ''){
			$item_row['on_lists'] = str_replace(', ','<br />',$item_row['on_lists']);
			echo $item_row['on_lists'];	
		}
		
        displayEvents($events_array);
		
	}
	
	echo '</div>';
	
}

/******* People Functions  *******/

function getRelatedPeople($table,$record_id){
	
	if($table == 'item'){
		$q = "SELECT * FROM item_people WHERE item_id = '$record_id'";
	}elseif($table == 'podcasts'){
		$q = "SELECT * FROM podcast_people WHERE podcasts_id = '$record_id'";
	}elseif($table == 'people'){
		echo 'People cannot be related to people';
		$people = array('count' => 0, 'names' => array());
		return $people;
	}
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0 && $table == 'item'){
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			
			$person_info = getRecord('people',$row['people_id']);
			
			//print_r($person_info);
			
			if(isset($row['is_primary'])){
				$person_info['is_primary'] = $row['is_primary'];
				$person_info['role'] = $row['role'];
			}else{
				$person_info['is_primary'] = '1';
				$person_info['role'] = '';
			}
			
			$record_ids[] = $person_info['record_id'];
			
			if($person_info['is_primary'] == '1'){
				$primary_ids[] = $person_info['record_id'];
			}else{
				$non_primary_ids[] = $person_info['record_id'];
			}
			
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
		$record_ids = array('count' => $count, 'record_ids' => $record_ids, 'primary_ids' => $primary_ids, 'non_primary_ids' => $non_primary_ids);
		return $record_ids;
	}
	
	if(empty($non_primary_ids)){
		$non_primary_ids = array();
	}
	
	if(empty($primary_ids)){
		$primary_ids = array();
	}
	
	$record_ids = array('count' => $count, 'record_ids' => $record_ids, 'primary_ids' => $primary_ids, 'non_primary_ids' => $non_primary_ids);
	
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

function displayPeopleList($people_array){
	
	if(count($people_array['record_ids']) > 0){
		foreach($people_array['record_ids'] as $person_id){
			$primary_people[] = getPersonName($person_id,'FL');
		}
		
		echo implodeToEnglish($primary_people);
	}else{
		// Do nothing
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
	
	if($item_row['category'] != ''){
		echo '<div class="infoheading">Category</div>';
		echo '<div class="mediumsmall">',$item_row['category'];
		if($item_row['type'] != ''){
			echo ': ',$item_row['type'];
		}
		echo '</div>';
	}
}

function displayLocationInformation($item_row){

	$address = fixAddress($item_row);

	if($address != ''){
		echo '<div class="infoheading">Location</div>';
		echo '<div class="mediumsmall">',$address,'</div><br />';	
	}
	
	if($item_row['detailed_loc'] != ''){
		echo '<div class="mediumsmall">',$item_row['detailed_loc'],'</div>';
	}
	
}

function displayEvents($events_array = array()){

	if($events_array['count'] > 0){
		echo '<div class="infoheading">Dates</div>';
		foreach($events_array['record_ids'] as $record_id){
			echo '<div class="mediumsmall">',displayLongEvent($record_id),'</div>';
		}
	}
}

function displayRemarks($remarks = ''){

	$display_remarks = '';

	if($remarks != ''){
		$display_remarks .= '<div class="infoheading">Description</div>';
		$display_remarks .= '<div class="small">' . constrainLongText($remarks,1000) . '</div>';
		if(strlen($remarks) > 1000){
			$display_remarks .= '<div class="rightalign small"><a id="read_more">read more</a></div>';
		}
	}
	
	return $display_remarks;
}

function displayPodcasts($podcasts_array = array()){

	if($podcasts_array['count'] > 0){ ?>
		
		<div class="infoheading"><img height="12" src="ui/images/headphones.png" />&nbsp;Podcasts</div>
		
        <table class="nearby" width="100%" cellspacing="0" cellpadding="0">
        
        <?php
		
		foreach($podcasts_array['record_ids'] as $podcast_id):
			$podcast = getRecord('podcasts',$podcast_id);
			$podcasters = getRelatedPeople('podcasts',$podcast_id); ?>
			<tr>
			<td align="left" width="35">
			<div id="mp3"><?=$podcast['file_name']?></div>
			</td>
			<td>
            <div class="small">
			<?=displayPeopleList($podcasters);?>
            </div>
            <div class="extrasmall rightalign">
			about this podcast
            </div>
            
			</td>
			</tr>
		<?php endforeach; ?>
		
		</table>
	<?php	
    }else{
		echo '<div class="infoheading">There are no podcasts available for this item.</div>';
	}
}

function displayNearby($nearby_array = array()){

	if(count($nearby_array['count']) > 0){
		
		echo '<div class="infoheading">Nearby Items</div>';
		echo '<table class="nearby" width="100%" cellpadding="0" cellspacing="0">';
		
		$i = 1;
		foreach($nearby_array['nearby_info'] as $nearby_item){
		
			echo '<tr>';
			echo '<td width="10">',showNearbyPin(15,$i),'</td>';
			echo '<td width="10"></td>';
			echo '<td width="46%">';
			echo '<span class="small">',$nearby_item['name_title'],'</span>';
			echo '</td>';
			echo '<td width="10"></td>';
			echo '<td align="right"  width="23%">';
			echo '<span class="small">',readableDistance($nearby_item['distance']),'</span></div>';
			echo '</td>';
			echo '<td width="10"></td>';
			echo '<td width="10" align="right"><a href="index.php?page=entry&id=',$nearby_item['record_id'],'"><img height="10" src="ui/images/arrow-icon.png"></a></td>';
			echo '</tr>';
			$i = $i + 1;
			
		}
		echo '</table>';
	}else{
		echo '';
	}
}

function displayThumbnails($images_array = array()){

	$spacer_positions = array('1','2','3','4','6','7','8','9');

	$i = 0;

	foreach($images_array['record_ids'] as $record_id){ 				 

		$i = $i + 1;	
		echo '<div id="thumb" image_id="',$record_id,'" class="thumbnail image-state-chosen">',displayThumbImageForItemPage($record_id),'</div>';
   		if(in_array($i,$spacer_positions)){
			echo '<div class="spacer"></div>';			
		}
		
	}
	
	while($i < 10){
		$i = $i + 1;
		echo '<div class="emptythumbnail image-state-chosen"></div>';
   		if(in_array($i,$spacer_positions)){
			echo '<div class="spacer"></div>';			
		}
	}
	
}

function displayThumbImageForItemPage($record_id){
	$html = '<img width="78" height="78" src="http://www.culturenow.org/media/new_images/' . $record_id . '/thumb.jpg">';
	return $html;
}

function displayWebImageForItemPage($record_id){
	
	if($record_id == ''){
		$img = 'http://www.culturenow.org/media/new_images/blank/web.jpg';
	}else{
		$img = 'http://www.culturenow.org/media/new_images/' . $record_id . '/web.jpg';	
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

function showCurrentPin($width,$letter){
	return '<img width="' . $width . '" src="http://chart.apis.google.com/chart?chst=d_map_xpin_letter&chld=pin_star|A|00FFFF|000000|FF0000" />';
}

function showNearbyPin($width,$letter){
	return '<img width="' . $width . '" src="http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' . $letter . '|BDD73C|000000" />';
}

function plotMainMap(){

	$centers_distance = 100;

	$q = "SELECT DISTINCT concat_ws(', ',latitude,longitude) AS latLng, record_id FROM item WHERE live = '1' AND latitude != '' AND longitude != '' AND latitude != '0' AND longitude != '0'";
	$r = @mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error());
	
	$i = 0;
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		
		if($i == 0){
			$centers_of_gravity[] = $row['latLng']; 
		}
		
		foreach($centers_of_gravity as $key => $value){
		
			$distance = getDistance($value,$row['latLng']);
		
			if($distance <= $centers_distance){
				$new_center = FALSE;
				break;
			}else{
				$new_center = TRUE;
			}
		}
		
		if($new_center == TRUE){
			//echo '<font color="red">',$value,' -> ',$row['latLng'],'</font><br />';
			$centers_of_gravity[] = $row['latLng'];
			$point_list[count($centers_of_gravity) - 1][] = $row['latLng'];
		}else{
			$point_list[$key][] = $row['latLng'];
		}
	
		$new_center = '';
		$i = $i + 1;
	
	}
	
	foreach($centers_of_gravity as $key => $value){
	
		$number_at_center = count($point_list[$key]);
	
		$true_center = getTrueCenter($point_list[$key]);
	
		//echo $true_center;
	
		$results[] = array('center' => $true_center, 'number_at_center' => $number_at_center);
		//echo $value,': ',$number_at_center,'<br />'; 
	}
	
	//echo 'centers: <pre>',print_r($centers_of_gravity),'</pre>';
	//echo 'points: <pre>',print_r($point_list),'</pre>';
	
	return $results;
	
}

function getDistance($point1,$point2,$unit = 'Mi') {
	
	$point1 = explode(', ',$point1);
	$point2 = explode(', ',$point2);
	
	$lat1 = $point1[0]; $lat2 = $point2[0];
	$lng1 = $point1[1]; $lng2 = $point2[1];

	$theta = $lng1 - $lng2;
	$distance = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) +
	(cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
	cos(deg2rad($theta)));
	$distance = acos($distance);
	$distance = rad2deg($distance);
	$distance = $distance * 60 * 1.1515;

	switch($unit) {
	
		case 'Mi': 
		break;
		
		case 'Km':
		$distance = $distance * 1.609344;
	
	}
		
	return (round($distance,2));
	
}

function getTrueCenter($points = array()){

	foreach($points as $point){
		
		$point = explode(', ',$point);
		
		$lats[] = $point[0];
		$lngs[] = $point[1];
	
	}
	
	$count_of_lats = count($lats);
	$sum_of_lats = 0;
	foreach($lats as $lat){
		$sum_of_lats = $sum_of_lats + $lat;	
	}
	
	$count_of_lngs = count($lats);
	$sum_of_lngs = 0;
	foreach($lngs as $lng){
		$sum_of_lngs = $sum_of_lngs + $lng;	
	}
	
	$midpoint_lat = $sum_of_lats / $count_of_lats;
	$midpoint_lng = $sum_of_lngs / $count_of_lngs;
	
	return $midpoint_lat . ', ' . $midpoint_lng;

}

function numberToCategory($number){
	
	switch($number){
	
		case 1:
		return 'Artworks';
		break;
		
		case 2:
		return 'Historic Buildings';
		break;
		
		default:
		return 'Artworks';
		break;
		
	}
	
}

?>