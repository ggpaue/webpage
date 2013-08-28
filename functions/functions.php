<?php // REQUIRES

include('edit_function.php');

// FUNCTIONS


// DATA CLEANING

function clean_array($array){
	
	foreach($array as $key => $value){
		
		$value = htmlspecialchars($value);
		$value = str_replace(chr(11)," ",$value); // Vert-Tab from FM Ascii(11)
		$value = str_replace(chr(29),"",$value); // Repetition Character Ascii(29)
		$array[$key] = $value;
		
	}
	
	return $array;

}

// TIMING FUNCTIONS

function get_time_start(){

	$time = microtime(); 
	$time = explode(" ",$time); 
	$time = $time[1] + $time[0]; 
	$starttime = $time; 
	return $starttime;

}

function get_time_end($starttime,$name){

	$time = microtime(); 
	$time = explode(" ",$time); 
	$time = $time[1] + $time[0]; 
	$endtime = $time; 
	$totaltime = substr($endtime - $starttime,0,6); 
	echo '<total_time>',$name,' time elapsed: ',$totaltime,' seconds</total_time>';
		
}	

// URL CREATION

function currentPageURL() {
	$pageURL = $_SERVER['SCRIPT_NAME'];
	return $pageURL;
}

function addSearchParamToCurrentURL($add_key,$add_value){
	
	$pageURL = $_SERVER['SCRIPT_NAME'] . '?';
	if(isset($_GET)){
		foreach($_GET as $key=>$value){
			if($key != $add_key){
				$pageURL .= $key . '=' . $value  . '&';
			}
		}
	}	
	
	$pageURL .= $add_key . '=' . $add_value;
	return $pageURL;
}

function stickyForm($index,$default = ''){

	if(empty($_SESSION[basename(currentPageURL())][$index]) || $_SESSION[basename(currentPageURL())][$index] == ''){
		return $default;
	}else{
		return $_SESSION[basename(currentPageURL())][$index];
	}

}


function stickyPOST($index,$default = ''){

	if(empty($_POST[$index]) || $_POST[$index] == ''){
		return $default;
	}else{
		return $_POST[$index];
	}

}

function stickyRow($array,$index,$default = ''){

	if(empty($array[$index]) || $array[$index] == ''){
		return $default;
	}else{
		return $array[$index];
	}

}

function getValuesIntoSession($get_values = array()){

	$array = array();

	if(isset($get_values)){
		foreach($get_values as $key => $value){
			$array[$key] = $value;
		}
	}
	
	$results = $array;

	return $results;
	
}

function compareSessionToGet($session,$get){

	foreach($_GET as $key => $value){
		$session[$key] = $value;
	}
	
	return $session;
	
}

// QUERY LIMITERS

function createAlphaLimitList($active){

	$alphabet = array ("all","A","B","C","D","E","F","G","H","I","J","K","L","M",
"N","O","P","Q","R","S","T","U","V","W","X","Y","Z","_","0-9");
	
	$menu = '<ul id="database_nav">';
	
	foreach($alphabet as $letter){
		$menu .= '<li';
		
		if($active == $letter || ($active == '' && $letter == 'all')){
			$menu .= ' id="active" ';
		}
		
		$menu .= '><a href="' . addSearchParamToCurrentURL('limit_alpha',$letter) . '">' . $letter . '</a></li>';	
	}
	
	$menu .= '</ul>';
	
	return $menu;

}

function createRoleLimitList($active){

	$roles = array ("all","podcaster","artist/architect");
	
	$menu = '<ul id="database_nav">';
	
	foreach($roles as $role){
		$menu .= '<li';
		
		if($active == $role || ($active == '' && $role == 'all')){
			$menu .= ' id="active" ';
		}
		
		$menu .= '><a href="' . addSearchParamToCurrentURL('limit_role',$role) . '">' . $role . '</a></li>';	
	}
	
	$menu .= '</ul>';
	
	return $menu;

}

function createOrgLimitList($active){

	$orgs = array ("all","arts for transit");
	
	$menu = '<ul id="database_nav">';
	
	foreach($orgs as $org){
		$menu .= '<li';
		
		if($active == $org || ($active == '' && $org == 'all')){
			$menu .= ' id="active" ';
		}
		
		$menu .= '><a href="' . addSearchParamToCurrentURL('limit_org',$org) . '">' . $org . '</a></li>';	
	}
	
	$menu .= '</ul>';
	
	return $menu;

}

function createCategoryLimitList($active){

	$categories = array ("all","Artist Related","Artworks","Civic Landmarks","Civic Resources","Community Gardens","Cultural Landmarks","Cultural Resources","Educational","Film","Galleries and Art Spaces","Greenmarkets","Historic Buildings","Information Kiosks and Visitor Centers","Libraries","Museums","Parks","Performance Spaces","Performing Arts","Places of Worship","Planning Projects","Recent Architecture","Sustainable Design");
	
	$menu = '<ul id="database_nav">';
	
	foreach($categories as $category){
		$menu .= '<li';
		
		if($active == $category || ($active == '' && $category == 'all')){
			$menu .= ' id="active" ';
		}
		
		$menu .= '><a href="' . addSearchParamToCurrentURL('limit_category',$category) . '">' . $category . '</a></li>';	
	}
	
	$menu .= '</ul>';
	
	return $menu;

}

function createHasPodcastList($active){

	$podcasts = array ("all","Yes","No");
	
	$menu = '<ul id="database_nav">';
	
	foreach($podcasts as $podcast){
		$menu .= '<li';
		
		if($active == $podcast || ($active == '' && $podcast == 'all')){
			$menu .= ' id="active" ';
		}
		
		$menu .= '><a href="' . addSearchParamToCurrentURL('limit_podcast',$podcast) . '">' . $podcast . '</a></li>';	
	}
	
	$menu .= '</ul>';
	
	return $menu;

}

function createTourLimitList($active){

	$tours = array ("all","Midtown","Audubon Terrace","125th Street","Harlem: The Capital","Boat Tour","Revolutionary NYC");
	
	$menu = '<ul id="database_nav">';
	
	foreach($tours as $tour){
		$menu .= '<li';
		
		if($active == $tour || ($active == '' && $tour == 'all')){
			$menu .= ' id="active" ';
		}
		
		$menu .= '><a href="' . addSearchParamToCurrentURL('limit_tour',$tour) . '">' . $tour . '</a></li>';	
	}
	
	$menu .= '</ul>';
	
	return $menu;

}

function createOrderByList($active){

	$order_bys = array("alpha","recent");
	
	$menu = '<ul id="database_nav">';
	
	foreach($order_bys as $order_by){
		$menu .= '<li';
		
		if($active == $order_by){
			$menu .= ' id="active" ';
		}
		
		$menu .= '><a href="' . addSearchParamToCurrentURL('order_by',$order_by) . '">' . $order_by . '</a></li>';	
	}
	
	$menu .= '</ul>';
	
	return $menu;

}

// QUERY EXECUTION

function populatePeopleList($title_search_style = 'all',$name = '',$order_by = 'recent',$start_at = '0',$rows_to_show = '50'){

	$rows = array();
	
	if($order_by == 'name'){
		$order_by = 'ORDER BY name';
	}else{
		$order_by = 'ORDER BY record_id DESC';
	}
	
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}
	
	
	if($title_search_style == 'all'){
		$q =  "SELECT * FROM people $order_by $limit";
	}else{
		if($title_search_style == 'contains'){
			$q = 'SELECT * FROM people WHERE name LIKE \'%' . $name . "%'  $order_by $limit";
		}elseif($title_search_style == 'begins with'){
			$q = 'SELECT * FROM people WHERE name LIKE \'' . $name . "%'  $order_by $limit";
		}
	}
	
	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());

	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		clean_array($row);
		$rows[] = $row;
		unset($row);
	}
	
	$results = array('query' => $q,'count' => count($rows), 'rows' => $rows);
	
	return $results;
	
}

function populateItemListNew($search_type = 'item',$title_search_style = 'all',$name_title = '',$map_name = 'all',$categories = 'all',$orgs = 'all',$order_by = 'recent',$start_at = '0',$rows_to_show = '50'){
	
	//echo '<font size="+3">search type: ' . $search_type . '</font><br />';
	
	if($search_type == 'item'){
		$order_field = 'item.record_id';
		$name_field = 'item.name_title';
	}elseif($search_type == 'artist'){
		$order_field = 'people.record_id';
		$name_field = 'people.name';
	}
	
	$rows = array();
	
	if($order_by == 'name'){
		$order_by = ' ORDER BY ' . $name_field . ' ';
	}else{
		$order_by = ' ORDER BY ' . $order_field . ' DESC ';	
	}
	
	if($orgs == 'arts for transit'){
		$orgs = " item.on_lists LIKE '%MTA Arts For Transit%' ";
	}elseif($orgs == 'schools_list'){
		$orgs = " item.schools_list = '1' ";
	}else{
		$orgs = '';
	}
	
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}
	
	if($title_search_style == 'all'){
		$title_search_style = '';
		$name_title = '';
	}else{
		if($title_search_style == 'contains'){
			$name_title = ' ' . $name_field . ' LIKE \'%' . $name_title . '%\' ';
		}else{
			$name_title = ' ' . $name_field . ' LIKE \'' . $name_title . '%\' ';
		}
	}
	
	if($categories == 'all' || $categories == ''){
		$categories = '';
	}else{
		$categories = ' item.category = \'' . $categories . '\' ';	
	}
	
	$where = '';
	if($name_title != '' || $categories != '' || $orgs != ''){
		$where .= ' WHERE ';	
	}
	
	if($name_title != ''){
		$where .= ' ' . $name_title . ' ';
	}
	
	if($name_title != '' && ($categories != '' || $orgs != '')){
		$where .= ' AND ';
	}
	
	if($categories != ''){
		$where .= ' ' . $categories . ' ';
	}
	
	if($categories != '' && $orgs != ''){
		$where .= ' AND ';
	}
	
	if($orgs != ''){
		$where .= ' ' . $orgs . ' ';
	}
	
	if($map_name != 'all'){
		$q = "SELECT item.*, map_info.*, item.record_id AS record_id, map_info.record_id AS map_info_record_id FROM item, map_info WHERE item.record_id = map_info.attached_to AND map_info.map_name = '$map_name' $order_by $limit";
	}else{
		$q = "SELECT * FROM item $where $order_by $limit";
	}
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());

	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		clean_array($row);
		$rows[] = $row;
		unset($row);
	}
	
	$results = array('query' => $q,'count' => count($rows), 'rows' => $rows);
	
	//echo '<pre>',print_r($results),'</pre>';
	
	return $results;
	
}

function populatePodcastsList($start_at = 0, $rows_to_show = 50){
	
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}
	
	$rows = array();
	
	$q = "SELECT *, record_id AS podcasts_id, write_up AS podcast_write_up FROM podcasts ORDER BY record_id DESC $limit";
	/*
	if($limit_alpha == ''){
		$q = "SELECT podcasts.*, people.*, podcast_people.*, podcasts.write_up AS podcast_write_up FROM podcasts, people, podcast_people WHERE podcasts.record_id = podcast_people.podcasts_id AND people.record_id = podcast_people.people_id ORDER BY podcasts.title";
	}elseif($limit_alpha == '0-9'){
		$q = "SELECT podcasts.*, people.*, podcast_people.*, podcasts.write_up AS podcast_write_up  FROM podcasts, people, podcast_people WHERE podcasts.record_id = podcast_people.podcasts_id AND people.record_id = podcast_people.people_id AND people.name REGEXP '^[0-9] ORDER BY people.name, podcasts.title";
		$q = "SELECT * FROM podcasts WHERE title REGEXP '^[0-9]' ORDER BY title";
	}elseif($limit_alpha == '_'){
		$q = "SELECT podcasts.*, people.*, podcast_people.*, podcasts.write_up AS podcast_write_up  FROM podcasts, people, podcast_people WHERE podcasts.record_id = podcast_people.podcasts_id AND people.record_id = podcast_people.people_id AND people.name = '' ORDER BY podcasts.title";
	}else{
		$q = "SELECT podcasts.*, people.*, podcast_people.*, podcasts.write_up AS podcast_write_up  FROM podcasts, people, podcast_people WHERE podcasts.record_id = podcast_people.podcasts_id AND people.record_id = podcast_people.people_id AND people.name LIKE '$limit_alpha%' ORDER BY podcasts.title";
	}
	*/

	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());

	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		clean_array($row);
		$rows[] = $row;
		unset($row);
	}
	
	$results = array('query' => $q,'count' => count($rows), 'rows' => $rows);
	
	//echo '<pre>',print_r($results),'</pre>';
	
	return $results;
	
}

function populateToursList($limit_tour){
	
	$rows = array();
	
	if($limit_tour == ''){
		$q = "SELECT tours.*, tour_stops.*, tours.record_id AS tour_record_id FROM tours, tour_stops WHERE tours.record_id = tour_stops.attached_to_tour ORDER BY tours.tour_name, tour_stops.stop_order";
	}else{
		$q = "SELECT tours.*, tour_stops.*, tours.record_id AS tour_record_id FROM tours, tour_stops WHERE tours.record_id = tour_stops.attached_to_tour AND tours.tour_name LIKE '%$limit_tour%' ORDER BY tours.tour_name, tour_stops.stop_order";
	}
	
	//echo $q,'<br />';

	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());

	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		clean_array($row);
		$rows[] = $row;
		unset($row);
	}
	
	//echo '<pre>',print_r($rows),'</pre>';
	
	return $rows;
	
}

function populateImageList($start_at = 0, $rows_to_show = 50){
	
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}
	
	$rows = array();
	
	$q = "SELECT * FROM new_images ORDER BY record_id DESC $limit";

	//echo $q,'<br />';

	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());

	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		clean_array($row);
		$rows[] = $row;
		unset($row);
	}
	
	//echo '<pre>',print_r($rows),'</pre>';
	
	$results = array('query' => $q,'count' => count($rows), 'rows' => $rows);
	
	return $results;
	
}

// DATA DISPLAY

function convertBOOL($value){
	if($value >= 1){
		return 'Yes';
	}else{
		return 'No';
	}
}

function constrainLongText($text,$length){
	
	$cut_text = '';
	
	if($text != ''){
		$cut_text = substr($text,0,$length);
		if(strlen($text) > $length){
			$cut_text .= '...';
		}
	}
	
	return $cut_text;
}

function yearsArray(){
	
	$years[] = '';
	
	$i = date('Y',time());
	while($i >= 1500){
		$years[] = $i;
		$i = $i - 1;
	}
	return $years;

}

function decadesArray(){
	
	$years[] = '';
	
	$i = date('Y',time());
	while($i >= 1500){
		$years[] = $i;
		$i = $i - 10;
	}
	return $years;

}

function ddFromArray($array,$name,$select = FALSE){
	
	$menu = '';
	
	$menu .= '<select class="search" name="' . $name . '">';
	
	foreach($array as $value){
		
		$menu .= '<option';
		if($select == $value){
			$menu .= ' selected';
		}
		$menu .= '>' . $value . '</option>';
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

function dropDownMenu($valuelist,$value,$name){
	
	$menu = '';
	
	$menu .= '<select name="' . $name . '">';
	$menu .= '<option></option>';
	
	foreach($valuelist as $listvalue){
		
		$menu .= '<option';
		if($value == $listvalue){
			$menu .= ' selected';
		}
		$menu .= '>' . $listvalue . '</option>';
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

function dropDownMenuWithKeys($valuelist,$value,$name){
	
	$menu = '';
	
	$menu .= '<select name="' . $name . '">';
	$menu .= '<option></option>';
	
	foreach($valuelist as $listkey => $listvalue){
		
		$menu .= '<option value="' . $listkey . '" ';
		if($value == $listkey){
			$menu .= ' selected';
		}
		$menu .= '>' . $listvalue . '</option>';
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

// GET RELATED INFO

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

function getImageCount($count_for,$record_id){
	
	$q = "SELECT * FROM new_images WHERE attached_table = '$count_for' AND attached_to = '$record_id'";
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	return $count;
	
}

function getItemCount($count_for,$record_id){
	
	if($count_for == 'podcast'){
		$q = "SELECT * FROM item_podcast WHERE podcasts_id = '$record_id'";
	}else if($count_for == 'people'){
		$q = "SELECT * FROM item_people WHERE people_id = '$record_id'";
	}
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	return $count;
	
}

function getPersonRole($item_id,$person_id){
	
	$q = "SELECT * FROM item_people WHERE item_id = '$item_id' AND people_id = '$person_id'";
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
	return $row;

}

function getRelatedPeople($table,$record_id){
	
	if($table == 'item'){
		$q = "SELECT * FROM item_people WHERE item_id = '$record_id' ORDER BY is_primary";
	}elseif($table == 'podcasts'){
		$q = "SELECT * FROM podcast_people WHERE podcasts_id = '$record_id'";
	}elseif($table == 'people'){
		echo 'People cannot be related to people';
		$people = array('count' => 0, 'names' => array());
		return $people;
	}
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$person_info = getRecord('people',$row['people_id']);
			$record_ids[] = $row['people_id'];
		}
	}else{
		$record_ids = array();
		$record_ids = array('count' => $count, 'record_ids' => $record_ids);
		return $record_ids;
	}
	
	$record_ids = array('count' => $count, 'record_ids' => $record_ids);
	
	return $record_ids;
	
}

function getRelatedMapInfo($table,$record_id){

	$q = "SELECT * FROM map_info WHERE attached_to = '$record_id'";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$map_info = getRecord('map_info',$row['record_id']);
			$record_ids[] = $row['record_id'];
		}
	}else{
		$record_ids = array();
		$related_maps = array('count' => $count, 'record_ids' => $record_ids);
		return $related_maps;
	}
	
	$related_maps = array('count' => $count, 'record_ids' => $record_ids);
	
	return $related_maps;
	
}

function getRelatedEvents($table,$record_id){

	$q = "SELECT * FROM events WHERE attached_to = '$record_id' ORDER BY end_year ASC";
	
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$event = getRecord('events',$row['record_id']);
			$record_ids[] = $row['record_id'];
		}
	}else{
		$record_ids = array();
		$related_events = array('count' => $count, 'record_ids' => $record_ids);
		return $related_events;
	}
	
	$related_events = array('count' => $count, 'record_ids' => $record_ids);
	
	return $related_events;
	
}

function getRelatedImages($table,$record_id){

	$q = "SELECT * FROM new_images WHERE attached_table = '$table' AND attached_to = '$record_id' ORDER BY default_image DESC";
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$image_info = getRecord('new_images',$row['record_id']);
			$record_ids[] = $row['record_id'];
		}
	}else{
		$record_ids = array();
		$related_images = array('count' => $count, 'record_ids' => $record_ids);
		return $related_images;
	}
	
	$related_images = array('count' => $count, 'record_ids' => $record_ids);
	
	return $related_images;
	
}

function getPodcastImageHTTPLocation($record_id){
	
	$podcast_image = getMainImageHTTPLocation('podcasts',$record_id);
	
	if($podcast_image == ''){
		$items = getRelatedItems('podcasts',$record_id);
		$item_image = '';
		foreach($items['record_ids'] as $key => $value){
			$item_image = getMainImageHTTPLocation('item',$value);	
		}
	}else{
		return $podcast_image;	
	}
	
	if($item_image == ''){
		$people = getRelatedPeople('podcasts',$record_id);
		$person_image = '';
		foreach($people['record_ids'] as $key => $value){
			$person_image = getMainImageHTTPLocation('people',$value);	
		}
	}else{
		return $item_image;	
	}
	
	if($person_image == ''){
		return 'http://www.culturenow.org/new_images/podcast_mini.png';
	}	

}

function getMainImageHTTPLocation($table,$record_id){

	$q = "SELECT * FROM new_images WHERE attached_table = '$table' AND attached_to = '$record_id' LIMIT 1";
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
	echo $q,' - ',$count,'<br />';
	
	if($count > 0){
		return 'http://www.culturenow.org/media/new_images/' . $row['record_id'] . '/mini.jpg';	
	}else{
		return '/ui/images/blank.png';	
	}
	
}

function getAndShowMainImage($table,$record_id,$image_type){
	
	$row = getRelatedImages($table,$record_id);
	
	if($count == 1){
		if($image_type == 'mini'){
			$image = displayMiniImage($row['record_id']);
		}elseif($image_type == 'thumb'){
			$image = displayThumbImage($row['record_id']);
		}elseif($image_type == 'web'){
			$image = displayWebImage($row['record_id']);
		}
	}else{
		if($image_type == 'mini'){
			$image = '<img src="http://www.culturenow.org/images/blank_person_mini.png" />';
		}elseif($image_type == 'thumb'){
			$image = '<img src="http://www.culturenow.org/images/blank_person_thumb.png" />';
		}else{
			$image = '<img src="" />';	
		}
	}
	
	return $image;
	
}

function getRelatedItems($table,$record_id){
	
	if($table == 'people'){
		$q = "SELECT * FROM item_people WHERE people_id = '$record_id'";
	}elseif($table == 'podcasts'){
		$q = "SELECT * FROM item_podcast WHERE podcasts_id = '$record_id'";
	}elseif($table == 'tour_stops'){
		$q = "SELECT * FROM tour_stop_item WHERE tour_stops_id = '$record_id'";
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
			$podcasts[] = $row['podcasts_id'];
		}
	}else{
		$podcasts = array();
		$related_podcasts = array('count' => $count, 'record_ids' => $podcasts);
		return $related_podcasts;
	}
	
	$related_podcasts = array('count' => $count, 'record_ids' => $podcasts);
	
	return $related_podcasts;
	
}

function getRelatedTours($table,$record_id){
	
	$q = "SELECT * FROM tour_tour_stop WHERE tour_stops_id = '$record_id'";
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$tour_info = getRecord('tours',$row['tours_id']);
			$tours[] = $tour_info['record_id'];
		}
	}else{
		$tours = array();
		$related_tours = array('count' => $count, 'record_ids' => $tours);
		return $related_tours;
	}
	
	$related_tours = array('count' => $count, 'record_ids' => $tours);
	
	return $related_tours;
	
}

function getRelatedTourStops($table,$record_id){
	
	$q = "SELECT * FROM tour_tour_stop WHERE tours_id = '$record_id'";
	
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	
	if($count > 0){
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$tour_stop_info = getRecord('tour_stops',$row['tour_stops_id']);
			$tour_stops[] = $tour_stop_info['record_id'];
		}
	}else{
		$tour_stops = array();
		$related_tour_stops = array('count' => $count, 'record_ids' => $tour_stops);
		return $related_tour_stops;
	}
	
	$related_tour_stops = array('count' => $count, 'record_ids' => $tour_stops);
	
	return $related_tour_stops;
	
}

// ARTIST/ARCH SEARCH

function searchByArtistName($query,$search_style){
	
	$q = "SELECT * FROM people WHERE name LIKE";
	if($search_style == 'contains'){
		$q .= '%$query%';
	}else{
		$q .= '$query%';
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

// GET ANY SINGLE RECORD

function getRecord($table,$record_id){
	
	$q = "SELECT * FROM $table WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return $row;

}

function getRecordField($table,$record_id,$field){
	
	$q = "SELECT $field FROM $table WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return $row;

}

function getConnectionRecordField($table,$column1,$record_id1,$column2,$record_id2,$field){
	
	$q = "SELECT $field FROM $table WHERE $column1 = '$record_id1' AND $column2 = '$record_id2'";
	$r = @mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return $row;

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

function listOfNames($record_ids,$order = 'LF'){
	
	foreach($record_ids as $record_id){
		$names[] = getPersonName($record_id,$order);	
	}
	
	return ImplodeToEnglish($names);
	
}

function ImplodeToEnglish ($array) { 
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

function getItemNameTitle($record_id){
	
	$q = "SELECT name_title FROM item WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable execute <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return $row['name_title'];

}

function getPodcastTitle($record_id){
	
	$q = "SELECT title FROM podcasts WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable execute <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return $row['title'];

}

function getTourStopName($record_id){
	
	$q = "SELECT stop_name FROM tour_stops WHERE record_id = '$record_id'";
	$r = @mysql_query($q) OR die("unable execute <i>$q</i>: " . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);

	return $row['stop_name'];

}

// GET TABLE COLUMNS

function getTableColumns($table){

	$q_columns = "SHOW COLUMNS FROM $table";
	$r_columns = @mysql_query($q_columns) OR die('unable to execute $table query: ' . mysql_error());
	while($row_columns = mysql_fetch_array($r_columns, MYSQL_ASSOC)){
		$columns[] = $row_columns;	
	}
	return $columns;

}

function embedPodcast($record_id,$width,$dlink){

	$record = getRecord('podcasts',$record_id);

	$filename = $record['file_name'];

	$embed_code = '<embed src="http://www.google.com/reader/ui/3523697345-audio-player.swf" flashvars="audioUrl=../../media/podcasts/' . $filename . '" width="' . $width . '" height="27" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>';
    
	if($dlink == TRUE){
		$embed_code .= '&nbsp;<a class="dark" target="_blank" href="../../media/podcasts/' . $filename . '"><img src="../ui/culturenow/images/download-icon.png" /></a>';
	}
	
	return $embed_code;

}

// BOOL CHECK BOX

function stickyCheck($variable,$check_value){
	if(empty($variable) || $variable == ''){
		return '';
	}elseif($variable == $check_value){
		return 'checked';
	}
}

function createCheckbox($name,$value = FALSE){
	
	$checkbox = '<input type="hidden" name="' . $name . '" value="0" />';
	$checkbox .= '<input value="1" type="checkbox" name="' . $name . '"';
	if($value == 1){
		$checkbox .= ' checked';
	}
	$checkbox .= ' />';
	
	return $checkbox;
	
}


// CREATE PEOPLE DROP DOWN

function peopleDropDown($selected = ''){
	
	$q = "SELECT record_id, name_first, name FROM people ORDER BY name";
	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());
	
	
	$menu = '';
	
	$menu .= '<select name="people_list[]">';
	$menu .= '<option></option>';
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		
		$menu .= '<option';
		if($selected == $row['record_id']){
			$menu .= ' selected';
		}
		$menu .= ' value="' . $row['record_id'] . '">' . constrainLongText(fixName($row['name_first'],$row['name']),35) . '</option>' . "\n";
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

function itemDropDown($selected = ''){
	
	$q = "SELECT record_id, name_title FROM item ORDER BY name_title";
	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());
	
	
	$menu = '';
	
	$menu .= '<select name="item_list[]">';
	$menu .= '<option></option>';
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		
		$menu .= '<option';
		if($selected == $row['record_id']){
			$menu .= ' selected';
		}
		$menu .= ' value="' . $row['record_id'] . '">' . constrainLongText($row['name_title'],35) . '</option>' . "\n";
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

function podcastDropDown($selected = ''){
	
	$q = "SELECT record_id, title FROM podcasts ORDER BY title";
	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());
	
	$menu = '';
	
	$menu .= '<select name="podcast_list[]">';
	$menu .= '<option></option>';
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		
		$menu .= '<option';
		if($selected == $row['record_id']){
			$menu .= ' selected';
		}
		$menu .= ' value="' . $row['record_id'] . '">' . constrainLongText($row['title'],35) . '</option>' . "\n";
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

function toursDropDown($selected = ''){
	
	$q = "SELECT record_id, tour_name FROM tours ORDER BY tour_name";
	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());
	
	$menu = '';
	
	$menu .= '<select name="tour_list[]">';
	$menu .= '<option></option>';
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		
		$menu .= '<option';
		if($selected == $row['record_id']){
			$menu .= ' selected';
		}
		$menu .= ' value="' . $row['record_id'] . '">' . constrainLongText($row['tour_name'],35) . '</option>' . "\n";
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

function tourStopDropDown($selected = ''){
	
	$q = "SELECT record_id, stop_name, stop_order FROM tour_stops ORDER BY stop_order";
	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());
	
	$menu = '';
	
	$menu .= '<select name="tour_stop_list[]">';
	$menu .= '<option></option>';
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		
		$menu .= '<option';
		if($selected == $row['record_id']){
			$menu .= ' selected';
		}
		$menu .= ' value="' . $row['record_id'] . '">' . constrainLongText($row['stop_name'],35) . '</option>' . "\n";
	
	}
	
	$menu .= '</select>';
	
	return $menu;
	
}

// SAVE INFO

function postInfoIntoUPDATE($table,$post_info){
	
	$value_string = '';
	
	if(isset($post_info['categories'])){
		$post_info['categories'] = implode(', ',$post_info['categories']);	
	}elseif($table == 'podcasts'){
		$post_info['categories'] = '';
	}
	
	if(isset($post_info['on_lists'])){
		$post_info['on_lists'] = implode(', ',$post_info['on_lists']);	
	}elseif($table == 'item'){
		$post_info['on_lists'] = '';
	}
	
	if($table == 'designations'){
		foreach($post_info as $key => $value){
			$fixed_key = str_replace($table . '_', '', $key);
			$post_info[$fixed_key] = $value;
			unset($post_info[$key]);
		}
	}		
	
	$blacklist = array('record_id','submit','submit_n_go','table','query_type','people_list','item_list','podcast_list','tour_list','tour_stop_list','x','y','x2','y2','w','h','podcast_category','donotuse');
	foreach($blacklist as $blacklist_value){
		unset($post_info[$blacklist_value]);
	}
	
	foreach($post_info as $key => $value){
		$value_arrays[] = $key . " = '" . mysql_real_escape_string($value) . "'";
	}
	
	$value_string = implode(', ',$value_arrays);
	
	return $value_string;
}

function postInfoIntoINSERT($table,$post_info){
	
	$value_string = '';
	
	if(isset($post_info['categories']) && $table == 'podcasts'){
		$post_info['categories'] = implode(', ',$post_info['categories']);	
	}elseif($table == 'podcasts'){
		$post_info['categories'] = '';
	}
	
	if(isset($post_info['on_lists']) && $table == 'item'){
		$post_info['on_lists'] = implode(', ',$post_info['on_lists']);	
	}elseif($table == 'item'){
		$post_info['on_lists'] = '';
	}
	
	if($table == 'designations'){
		foreach($post_info as $key => $value){
			$fixed_key = str_replace($table . '_', '', $key);
			$post_info[$fixed_key] = $value;
			unset($post_info[$key]);
		}
	}	
	
	$blacklist = array('record_id','submit','submit_n_go','table','query_type','people_list','item_list','podcast_list','tour_list','tour_stop_list','podcast_category','donotuse');
	foreach($blacklist as $blacklist_value){
		unset($post_info[$blacklist_value]);
	}
	
	foreach($post_info as $key => $value){
		if(!in_array($key,$blacklist)){
			$keys[] = $key;
			$values[] = mysql_real_escape_string($value);
		}
	}
	
	$key_values = '(' . implode(', ',$keys) . ')';
	
	$value_values = "('" . implode("', '",$values) . "')";
	
	$value_string = $key_values . ' VALUES ' . $value_values;
	
	return $value_string;
	
}

function breakConnections($hub_table,$hub,$spokes_table,$spokes){
	
	$hub_spoke_array = array($hub_table,$spokes_table);
	
	if(in_array('item',$hub_spoke_array) && in_array('people',$hub_spoke_array)){
		$table = 'item_people';
	}elseif(in_array('item',$hub_spoke_array) && in_array('podcasts',$hub_spoke_array)){
		$table = 'item_podcast';
	}elseif(in_array('podcasts',$hub_spoke_array) && in_array('people',$hub_spoke_array)){
		$table = 'podcast_people';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('podcasts',$hub_spoke_array)){
		$table = 'tour_stop_podcast';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('item',$hub_spoke_array)){
		$table = 'tour_stop_item';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('tours',$hub_spoke_array)){
		$table = 'tour_tour_stop';
	}

	$q = "DELETE FROM $table WHERE " . $hub_table . "_id = '$hub' AND " . $spokes_table . "_id = '$spokes' LIMIT 1";
	//echo $q,'<br />';
	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());
	
}

function makeConnection($hub_table,$hub,$spokes_table,$spokes){

	$hub_spoke_array = array($hub_table,$spokes_table);
	
	if(in_array('item',$hub_spoke_array) && in_array('people',$hub_spoke_array)){
		$table = 'item_people';
	}elseif(in_array('item',$hub_spoke_array) && in_array('podcasts',$hub_spoke_array)){
		$table = 'item_podcast';
	}elseif(in_array('podcasts',$hub_spoke_array) && in_array('people',$hub_spoke_array)){
		$table = 'podcast_people';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('podcasts',$hub_spoke_array)){
		$table = 'tour_stop_podcast';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('item',$hub_spoke_array)){
		$table = 'tour_stop_item';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('tours',$hub_spoke_array)){
		$table = 'tour_tour_stop';
	}
	
	
	$q = "INSERT INTO $table (" . $hub_table . "_id, " . $spokes_table . "_id) VALUES ('$hub','$spokes')";
	//echo $q;
	$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());

}

function makeConnections($hub_table,$hub,$spokes_table,$spokes){

	$hub_spoke_array = array($hub_table,$spokes_table);
	
	if(in_array('item',$hub_spoke_array) && in_array('people',$hub_spoke_array)){
		$table = 'item_people';
	}elseif(in_array('item',$hub_spoke_array) && in_array('podcasts',$hub_spoke_array)){
		$table = 'item_podcast';
	}elseif(in_array('podcasts',$hub_spoke_array) && in_array('people',$hub_spoke_array)){
		$table = 'podcast_people';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('podcasts',$hub_spoke_array)){
		$table = 'tour_stop_podcast';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('item',$hub_spoke_array)){
		$table = 'tour_stop_item';
	}elseif(in_array('tour_stops',$hub_spoke_array) && in_array('tours',$hub_spoke_array)){
		$table = 'tour_tour_stop';
	}
	
	$q_delete = "DELETE FROM $table WHERE " . $hub_table . "_id = '$hub'";
	//echo $q_delete,'<br />';
	$r_delete = @mysql_query($q_delete) OR die('unable to execute query: ' . mysql_error());
	
	foreach($spokes as $key => $value){
		if($value != ''){
			$q_check = "SELECT * FROM $table WHERE (" . $hub_table . "_id = '$hub' AND " . $spokes_table . "_id = '$value')";
			//echo $q_check,'<br />';
			$r_check = @mysql_query($q_check) OR die('unable to execute query: ' . mysql_error());
			$count = mysql_num_rows($r_check);
			
			if($count == 0){
				$q = "INSERT INTO $table (" . $hub_table . "_id, " . $spokes_table . "_id) VALUES ('$hub','$value')";
				//echo $q,'<br />';
				$r = @mysql_query($q) OR die('unable to execute query: ' . mysql_error());
			}else{
				//do nothing	
			}
		}
			
	}

}

function saveChanges($query_type,$table,$record_id,$post_info){ // $query_type is either UPDATE or INSERT
	
	switch($query_type){
		
		case 'UPDATE':
		$query = "UPDATE $table SET " . postInfoIntoUPDATE($table,$post_info) . " WHERE record_id = '$record_id' LIMIT 1";
		break;
		
		case 'INSERT':
		$query = "INSERT INTO $table " . postInfoIntoINSERT($table,$post_info);
		break;
		
	}
	
	if($r = @mysql_query($query) OR die('unable to execute query <i>' . $query . '</i>: ' . mysql_error())){
		$results['results'] =  'Edits saved.<br /><br />Query was: ' . nl2br($query);	
	}
	
	if($record_id == '' || empty($record_id)){
		$record_id = mysql_insert_id();
		$results['new_id'] =  $record_id;
	}
	
	//echo 'record id: ',$record_id,'<br />';
	
	if($table == 'people' && isset($post_info['item_list'])){
		makeConnections($table,$record_id,'item',$post_info['item_list']);
	}
	
	if($table == 'people' && isset($post_info['podcast_list'])){
		makeConnections($table,$record_id,'podcasts',$post_info['podcast_list']);
	}
	
	if($table == 'item' && isset($post_info['people_list'])){
		makeConnections($table,$record_id,'people',$post_info['people_list']);
	}
	
	if($table == 'item' && isset($post_info['podcast_list'])){
		makeConnections($table,$record_id,'podcasts',$post_info['podcast_list']);
	}
	
	if($table == 'podcasts' && isset($post_info['item_list'])){
		makeConnections($table,$record_id,'item',$post_info['item_list']);
	}
	
	if($table == 'podcasts' && isset($post_info['people_list'])){
		makeConnections($table,$record_id,'people',$post_info['people_list']);
	}
	
	if($table == 'tour_stops' && isset($post_info['item_list'])){
		makeConnections($table,$record_id,'item',$post_info['item_list']);
	}
	
	if($table == 'tour_stops' && isset($post_info['podcast_list'])){
		makeConnections($table,$record_id,'podcasts',$post_info['podcast_list']);
	}
	
	if($table == 'tour_stops' && isset($post_info['tour_list'])){
		makeConnections($table,$record_id,'tours',$post_info['tour_list']);
	}
	
	if($table == 'tours' && isset($post_info['tour_stop_list'])){
		makeConnections($table,$record_id,'tour_stops',$post_info['tour_stop_list']);
	}
	
	return $results;

}

//DELETE RECORD

function deleteRecord($table,$record_id){

	$query = "DELETE FROM $table WHERE record_id = '$record_id' LIMIT 1";
	$r = @mysql_query($query) OR die('unable to execute query <i>' . $query . '</i>: ' . mysql_error());
	echo 'RECORD DELETED<br /><br />';
	echo '<hr />';
	echo $query;
	

}

// DISPLAY

function displayMiniImage($record_id){
	$html = '<img src="http://www.culturenow.org/media/new_images/' . $record_id . '/mini.jpg">';
	return $html;
}

function displayWebImage($record_id,$width = ''){
	$html = '<img ';
	if($width != ''){ $html .= 'width="' . $width . '" '; }
	$html .= ' src="http://www.culturenow.org/media/new_images/' . $record_id . '/web.jpg">';
	return $html;
}

function lightboxLinkWebImage($record_id){
	$html = '<a rel="lightbox" href="http://www.culturenow.org/media/new_images/' . $record_id . '/web.jpg">';
	return $html;
}


// DATE HANDLING

function sqlDateTime($show_date=true,$show_time=true){

	$datetime = '';

	$date = date("Y-m-d"); 
	$time = date("H:i:s");
	
	if($show_date == TRUE){
		$datetime .= $date;
	}
	
	if($show_date == TRUE && $show_time == TRUE){
		$datetime .= ' ';
	}
	
	if($show_time == TRUE){
		$datetime .= $time;
	}
	
	return $datetime;

}

function addPodcastRecord($attached_table,$attached_to){
	
	$q = "INSERT INTO podcasts (title) VALUES ('')";
	if($r = @mysql_query($q)){
		$last_id = mysql_insert_id();
		if($attached_table != '' && $attached_table != ''){
			$q2 = "INSERT INTO item_podcast (item_id,podcasts_id) VALUES ('$attached_to','$last_id')";
			if($r2 = @mysql_query($q2)){
				$result = array('error' => 0, 'result' => $last_id);
			}else{
				$result = array('error' => 1, 'result' => 'unable to execute query <i>' . $q2 . '</i>: ' . mysql_error());
			}
		}else{
			$result = array('error' => 0, 'result' => $last_id);
		}
	}else{
		$result = array('error' => 1, 'result' => 'unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	}
	
	return $result;
}

// IMAGE RESIZING

function addImageRecord($attached_table,$attached_to){
	
	$q = "INSERT INTO new_images (attached_table,attached_to) VALUES ('$attached_table','$attached_to')";
	//echo $q,'<br />';
	if($r = @mysql_query($q)){
		$result = array('error' => 0 , 'result' => mysql_insert_id());
	}else{
		$result = array('error' => 1, 'result' => 'unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	}
	
	return $result;
}

function isJPG($image){

	if(strpos($image,'.jpg') != FALSE || strpos($image,'.JPG') != FALSE){
		$results = TRUE;
	}else{
		$results = FALSE;
	}
	
	return $results;

}

function makeMiniImage($image,$newname){

	$src = imagecreatefromjpeg($image);
	list($width,$height) = getimagesize($image);
	
	$tmp = imagecreatetruecolor(67,67);
	imagecopyresampled($tmp,$src,0,0,0,0,67,67,$width,$height);
	imagejpeg($tmp,$newname,100);		

}

function makeCropImage($image,$newname,$x,$y,$new_width,$new_height){

	$src = imagecreatefromjpeg($image);
	list($width,$height) = getimagesize($image);
		
	$tmp = imagecreatetruecolor($new_width,$new_width);
	imagecopyresampled($tmp,$src,0,0,$x,$y,$new_width,$new_height,$new_width,$new_height);
	imagejpeg($tmp,$newname,100);		

}

function makeThumbImage($image,$newname){

	$src = imagecreatefromjpeg($image);
	list($width,$height) = getimagesize($image);
	
	$tmp = imagecreatetruecolor(100,100);
	imagecopyresampled($tmp,$src,0,0,0,0,100,100,$width,$height);
	imagejpeg($tmp,$newname,100);		

}

function makeWebImage($image,$newname){

	$src = imagecreatefromjpeg($image);
	list($width,$height) = getimagesize($image);
	
	if($width > $height){
		$newwidth = 500;
		$newheight = ((500 / $width) * $height);
	}elseif($width <= $height){
		$newwidth = ((500 / $height) * $width);
		$newheight = 500;
	}
	
	$tmp = imagecreatetruecolor($newwidth,$newheight);
	imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);
	imagejpeg($tmp,$newname,100);		

}

function gif2jpeg($p_fl, $p_new_fl='', $bgcolor=false){
	  list($wd, $ht, $tp, $at)=getimagesize($p_fl);
	  $img_src=imagecreatefromgif($p_fl);
	  $img_dst=imagecreatetruecolor($wd,$ht);
	  $clr['red']=255;
	  $clr['green']=255;
	  $clr['blue']=255;
	  if(is_array($bgcolor)) $clr=$bgcolor;
	  $kek=imagecolorallocate($img_dst,
					  $clr['red'],$clr['green'],$clr['blue']);
	  imagefill($img_dst,0,0,$kek);
	  imagecopyresampled($img_dst, $img_src, 0, 0, 
					  0, 0, $wd, $ht, $wd, $ht);
	  $draw=true;
	  if(strlen($p_new_fl)>0){
		if($hnd=fopen($p_new_fl,'w')){
		  $draw=false;
		  fclose($hnd);
		}
	  }
	  if(true==$draw){
		//header("Content-type: image/jpeg");
		imagejpeg($img_dst);
	  }else imagejpeg($img_dst, $p_new_fl);
	  imagedestroy($img_dst);
	  imagedestroy($img_src);
}

function rotateImage($image,$degrees){
	
	$src = imagecreatefromjpeg($image);
	list($width,$height) = getimagesize($image);
	$tmp = imagecreatetruecolor($width,$height);
	imagecopyresampled($tmp,$src,0,0,0,0,$width,$height,$width,$height);
	$tmp = imagerotate($tmp,$degrees,0);
	imagejpeg($tmp,$image,100);
	
}

// ADDRESS & GEOCODING FUNCTIONS

function getBestAddressForGeocoding($table,$record_id){
	
	$row = getRecord($table,$record_id);
	
	$address = '';
	
	if($row['add_number'] != '' && $row['add_street'] != ''){
		$address .= $row['add_number'] . ' ';
		$address .= $row['add_street'] . ', ';
	}elseif($row['x_street_1'] != '' && $row['x_street_2'] != ''){
		$address .= $row['x_street_1'] . ' and ';
		$address .= $row['x_street_2'] . ', ';
	}
	
	if($row['city'] != ''){	
		$address .= $row['city'] . ' ';
	}
	
	if($row['state'] != ''){	
		$address .= $row['state'] . ' ';
	}
	
	if($row['zip'] != ''){	
		$address .= $row['zip'];
	}
	
	return $address;
	
}

function getBestAddressForDupeCheck($table,$record_id){
	
	$row = getRecord($table,$record_id);
	
	$address = '';
	
	if($row['add_number'] != '' && $row['add_street'] != ''){
		$address .= $row['add_number'] . ' ';
		$address .= $row['add_street'] . ', ';
	}elseif($row['x_street_1'] != '' && $row['x_street_2'] != ''){
		$address .= $row['x_street_1'] . ' and ';
		$address .= $row['x_street_2'] . ', ';
	}
	
	return $address;
	
}

function getBestAddressForDisplay($table,$record_id){
	
	$row = getRecord($table,$record_id);
	
	
}

function fixAddress($add_number,$add_street,$add_two,$city,$state,$zip){
	
	$address = '';
	
	if($add_number != ''){	
		$address .= $add_number . ' ';
	}
	
	if($add_street != ''){	
		$address .= $add_street . '<br />';
	}
	
	if($add_two != ''){	
		$address .= $add_two . '<br />';
	}
	
	if($city != ''){	
		$address .= $city . ', ';
	}
	
	if($state != ''){	
		$address .= $state;
	}
	
	if($zip != ''){	
		$address .= ' ' . $zip;
	}
	
	return $address;
}

function displayMapName($map_info_id){
	$row = getRecord('map_info',$map_info_id);
	return $row['map_name'];
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

function prevNextRecordsGroup($page,$start_at,$limit,$showing_count){
	
	$link = '';
	
	if($start_at > 0){
		$link .= '<a class="fg-button ui-state-default ui-corner-all" href="' . $page . '?start_at=' . ($start_at - $limit) . '&limit=' . $limit . '">< PREV ' . $limit . '</a>';
	}
	
	
	if($showing_count >= $limit){
		$link .= '<a class="fg-button ui-state-default ui-corner-all" href="' . $page . '?start_at=' . ($start_at + $limit) . '&limit=' . $limit . '">NEXT ' . $limit . ' ></a>';
	}
	
	if($limit != 'all'){
		return $link;
	}else{
		return '';
	}
	
}

function uploadImage($_POST,$_FILES){

	$attached_table = $_POST['attached_table'];
	$attached_to = $_POST['attached_to'];

	echo $_FILES['image']['type'],'<br /><br />';

	if($_FILES['image']['type'] != 'image/jpeg'){
		return array('error' => 1, 'result' => 'image is not a jpeg');
	}

	$size = getimagesize($_FILES['image']['tmp_name']);

	if($size[0] < 100 && $size[1] < 100){
		return array('error' => 1, 'result' => 'image is too small');
	}

	$add_results = addImageRecord($attached_table,$attached_to);

	if($add_results['error'] == 1){
		return array('error' => 1, 'result' => 'upload error');
	}else{
		$new_dir = '../../media/new_images/' . sprintf("%05d",$add_results['result']);
		mkdir($new_dir);
		if (move_uploaded_file($_FILES['image']['tmp_name'], $new_dir . '/' . 'original.jpg')) {
			return array('error' => 0, 'result' => 'edit_image.php?id=' . sprintf("%05d",$add_results['result']));
		} else {
			return array('error' => 1, 'result' => 'upload error');
		}
	}

}

function uploadPodcast($_POST,$_FILES){

	$attached_table = $_POST['attached_table'];
	$attached_to = $_POST['attached_to'];

	echo $_FILES['image']['type'],'<br /><br />';

	if($_FILES['image']['type'] != 'image/jpeg'){
		return array('error' => 1, 'result' => 'image is not a jpeg');
	}

	$size = getimagesize($_FILES['image']['tmp_name']);

	if($size[0] < 100 && $size[1] < 100){
		return array('error' => 1, 'result' => 'image is too small');
	}

	$add_results = addImageRecord($attached_table,$attached_to);

	if($add_results['error'] == 1){
		return array('error' => 1, 'result' => 'upload error');
	}else{
		$new_dir = '../../media/new_images/' . sprintf("%05d",$add_results['result']);
		mkdir($new_dir);
		if (move_uploaded_file($_FILES['image']['tmp_name'], $new_dir . '/' . 'original.jpg')) {
			return array('error' => 0, 'result' => 'edit_image.php?id=' . sprintf("%05d",$add_results['result']));
		} else {
			return array('error' => 1, 'result' => 'upload error');
		}
	}

}

function boolSwitch($table,$record_id,$field){

	$row = getRecordField($table,$record_id,$field);
	
	$switch_data = "boolSwitch('$table', '$record_id', '$field', '$row[$field]')"; 
	$image_id = $table . $record_id . $field;
	
	if($row[$field] == '0'){ 
		echo '<a id="' . $image_id . '" onClick="' . $switch_data . '" class="live-button ui-state-error ui-corner-all">not live</a>';
	}else{ 
		echo '<a id="' . $image_id . '" onClick="' . $switch_data . '" class="live-button ui-state-default ui-corner-all">LIVE</a>';
	}

}

function boolSwithConnection($table1,$record_id1,$table2,$record_id2,$field){
	
	$row = getConnectionRecordField($table,$column1,$record_id1,$column2,$record_id2,$field);
	
	$switch_data = "boolSwitchConnection('$table', '$column1', '$record_id1', '$column2', '$record_id2', '$field', '$row[$field]')";
	
	if($row[$field] == '0'){ 
		echo '<a id="' . $image_id . '" onClick="' . $switch_data . '">secondary</a>';
	}else{ 
		echo '<a id="' . $image_id . '" onClick="' . $switch_data . '">primary</a>';
	}
	
}

/*******************************************
*
* Log in functions
*
*******************************************/

function logInBox($logged_in_id = '00000', $return_to){
	
	if( empty($logged_in_id) || $logged_in_id == "" || $logged_in_id == "00000" ){
	?>
        
        <div class="bluebox ui-corner-all">
        <?php 
		if(isset($_SESSION['error'])){
			echo '<div class="ui-state-error ui-corner-all" style="padding:5px; width:130px"><i>' . $_SESSION['error'] . '</i></div><br />';
			unset($_SESSION['error']);
		} ?>
        <form class="nav" method="post" action="credentials/log_in.php">
        <input class="nav" type="text" name="user_id" /><br />
        <input class="nav" type="password" name="password" /><br />
        <input type="hidden" name="return_to" value="<?=$return_to?>" />
        <div class="footer">
        <input class="fg-button ui-state-highlight ui-corner-all" type="submit" name="login_submit" value="log in" /></div>
        </form>
        </div>
        
	<?php
	}else{
	?>
	
	 	<div class="bluebox ui-corner-all">
        You are logged in as <em><?=$_SESSION['logged_in_id']?></em>
        <br />
        <div class="footer">
        <form class="nav" method="post" action="credentials/log_out.php?return_to=<?=currentPageURL()?>">
        <input class="fg-button ui-state-highlight ui-corner-all" type="submit" value="log out" />
        </form>
        </div>
        </div>
	
	<?php
	}
	
}

/*
function testLogInCredentials($user_id,$password){

	$q = "SELECT * FROM users WHERE user_id = '$user_id' AND password = '$password' AND live = '1'";
	if($r = @mysql_query($q)){
		$rows_returned = mysql_num_rows($r);
		$row = mysql_fetch_array($r, MYSQL_ASSOC);
		if($rows_returned == 1){
			if($row['aggreement_signed'] == '1'){
				$result = 'success';
			}else{
				$result = 'send_to_legal';
			}
		}else{
			$result = 'user_pass_fail';
		}
	}else{
		$result = 'database_error';
	}
	
	return $result;
		
}
*/

function testLogInCredentials($logged_in_id = ''){

	if( empty($logged_in_id) || $logged_in_id == "" || $logged_in_id == "00000" ){
		$_SESSION['error'] = 'You are not logged in';
		header('Location: http://www.culturenow.org/admin_and_tools/');
		die();
	}	

}

function addPermissions($table,$record_id,$user_id){
	
	$query = "INSERT INTO permissions (table_id, record_id, user_id, created_timestamp) VALUES ('$table','$record_id','$user_id', NOW() )";
	$r = @mysql_query($query) OR die('unable to execute query <i>' . $query . '</i>: ' . mysql_error());
	
}

function showStandardEditLink($table,$record_id,$user_id){
	
	$permission = getRecord('permissions',$record_id);
	
	if($permission['user_id'] == $user_id || $user_id == 'culturenow'){
	?>
	<a href="edit.php?table=<?=$table?>&record_id=<?=$record_id?>"><img height="22" src="../ui/culturenow/images/edit-icon.png" /></a>
	<?php
	}else{
	?>
	<img height="22" src="../ui/culturenow/images/no-edit-icon.png" />	
	<?php
    }
}

function showStandardDeleteLink($table,$record_id,$user_id){
	
	$permission = getRecord('permissions',$record_id);
	
	if( ( $permission['user_id'] == $user_id && $table != 'people' ) || $user_id == 'culturenow' || $table == 'events' ){
	?>
    <a id="<?=$table . $record_id?>" onClick="return deleteRecordDialog('<?=$table?>', '<?=$record_id?>')"><img height="22" src="../ui/culturenow/images/trash-icon.gif" /></a>
	<?php
	}else{
	?>
	<img height="22" src="../ui/culturenow/images/no-trash-icon.png" />		
	<?php
    }
}

function showImageEditLink($table,$record_id,$user_id){
	?>
	<a href="http://www.culturenow.org/admin_and_tools/databases/edit_image.php?id=<?=$record_id?>&return_to=<?=urlencode(currentPageURL())?>"><img height="22" src="../ui/culturenow/images/edit-icon.png" /></a>
	<?php
}

function showImageDeleteLink($table,$record_id,$user_id){
	?>
    <a id="<?=$table . $record_id?>" onClick="return deleteRecordDialog('<?=$table?>', '<?=$record_id?>')"><img height="22" src="../ui/culturenow/images/trash-icon.gif" /></a>
	<?php
}

function showPodcastEditLink($table,$record_id,$user_id){
	
	$permission = getRecord('permissions',$record_id);
	
	if($permission['user_id'] == $user_id || $user_id == 'culturenow'){
	?>
	<a href="http://www.culturenow.org/admin_and_tools/databases/edit.php?table=podcasts&record_id=<?=$record_id?>&return_to=<?=urlencode(currentPageURL())?>"><img height="22" src="../ui/culturenow/images/edit-icon.png" /></a>
	<?php
	}else{
	?>
	<img height="22" src="../ui/culturenow/images/no-edit-icon.png" />
	<?php
    }
}

function showPodcastDeleteLink($table,$record_id,$user_id){
	
	$permission = getRecord('permissions',$record_id);
	
	if($permission['user_id'] == $user_id || $user_id == 'culturenow'){
	?>
    <a id="<?=$table . $record_id?>" onClick="return deleteRecordDialog('<?=$table?>', '<?=$record_id?>')"><img height="22" src="../ui/culturenow/images/trash-icon.gif" /></a>
	<?php
	}else{
	?>
	<img height="22" src="../ui/culturenow/images/no-trash-icon.png" />	
	<?php
    }
}

function showStandardUnLink($hub_table,$hub,$spokes_table,$spokes){ ?>
	
    <a onClick="return breakConnectionDialog(<?="'" . $hub_table . "', '" . $hub . "', '" . $spokes_table . "', '" . $spokes . "'"?>)"><img height="22" src="../ui/culturenow/images/break-icon.png" /></a>

<?php }

function createCheckboxList($list_array,$record_array,$input_name){ ?>
	
    <?php
    $array = explode(', ',$record_array["$input_name"]); 
    ?>

	<table>
    <?php
	$i = 1;
    foreach($list_array as $value):
	if($i == 1 || ($i) % 2 == 1){ echo '<tr>'; }?>
    <td class="whitebox" width="50%">
	<input type="checkbox" name="<?=$input_name?>[]" value="<?=$value?>" <?php if(in_array($value,$array)){ echo ' checked '; }?> />
	<?=$value?>
	</td>
    <?php
    if(($i == 1) || (($i) % 2 == 1)){ echo '</tr>'; };
	$i = $i + 1;
	endforeach;
	?>
	</table>
	
<?php } ?>