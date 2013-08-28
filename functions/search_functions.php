<?php

/* ITEM SEARCH
********************************************************************************/

function isJson($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}

function basicItemSearchTEST($search_param = '', $order_by = 'title', $page = '1', $cat_str = '', $limit_to = ''){
	
	// Set up LIMIT, ROWS TO SHOW 
	$limit = (($page - 1) * 25);
	if($limit < 0){ $limit = 0; }
	$limit = ' LIMIT ' . $limit . ', 25';
	// ORDER BY
	switch($order_by){
		
		case 'title':
		$order_by = "ORDER BY TRIM(LEADING '\'' FROM TRIM(LEADING '-' FROM TRIM(LEADING '(' FROM item.name_title))) ASC";
		break;
		
		case 'category':
		$order_by = "ORDER BY item.category ASC, item.type ASC";
		break;
		
		case 'location_o':
		$order_by = "ORDER BY item.city ASC";
		break;		
	
		case 'type':
		$order_by = "ORDER BY item.type ASC";
		break;
	
		case 'people':
		$order_by = "ORDER BY people.name ASC";
		break;
		
		case 'recently_added':
		$order_by = "ORDER BY item.record_id DESC";
		break;		
	
		default:
		$order_by = "ORDER BY TRIM(LEADING '\'' FROM TRIM(LEADING '-' FROM TRIM(LEADING '(' FROM item.name_title))) ASC";
		break;
		
	}
		
	// SET UP SEARCH PARAMETERS
	
	// categories
	$cat_str = str_replace('-',' ',$cat_str);
	$cat_array = explode('_',$cat_str);
	$categories = "'" . join("', '",$cat_array) . "'";
	$category_condition = "\nAND item.sub_category IN ($categories) "; 
	
	$search_param = urldecode($search_param);
	$search_param = str_replace('&', '', $search_param);
	$search_params = explode(' ',$search_param);
	$query_string = array();

	foreach($search_params as $key => $param){

		if($param == ''){
			unset($search_params[$key]);	
		}
		
	}
	
	foreach($search_params as $param){

		if($param == 'Snohetta' || $param == 'snohetta'){
			$param = 'Snøhetta';	
		}
		
		$param = strtolower($param);
		$param = convert_state_to_abbreviation(strtolower($param));
		$param = mysql_real_escape_string($param);
		
		$year_query = "";
		
		if(preg_match("/^[0-9]{4}$/",$param)){
			$year_query = "OR events.end_year = '$param'";
		}elseif(preg_match("/^[0-9]{4}[s]{1}$/",$param)){
			$param = str_replace('s','',$param);
			$next_decade = $param + 10;
			$year_query = "OR (events.end_year >= '$param' AND events.end_year < '$next_decade')";
		}
		
		$query_string[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR 
						  	concat_ws(' ',item.city,item.state) LIKE '%$param%' OR 
							concat_ws(', ',item.city,item.state) LIKE '%$param%' OR 
						   	concat_ws(' ',item.city,item.country) LIKE '%$param%' OR
						    people.name_first LIKE '%$param%' OR
							people.name LIKE '%$param%' OR
							item.name_title LIKE '%$param%' OR 
						    item.city LIKE '%$param%' OR 
						    item.state LIKE '%$param%' OR 
						    item.main_category LIKE '%$param%' OR 
						    item.sub_category LIKE '%$param%' OR 							
						    item.type LIKE '%$param%' OR 
							tags.tag LIKE '%$param%' OR 
						    designations.designation LIKE '%$param%' OR 
							designations.date LIKE '%$param%'
						    $year_query)";
	}
	
	if($search_param == '' || $search_param == NULL){
		unset($query_string);
		$query_string = "item.live = '1' $category_condition";	
	}else{
		$query_string = join("\nAND\n",$query_string) . " AND item.live = '1' $category_condition";
	}
	
	if($limit_to != '' && $limit_to != ' '  && $limit_to != "''"){
		$query_string .= "AND item.record_id IN ($limit_to)";	
	}
	
	$q = "SELECT SQL_CALC_FOUND_ROWS DISTINCT item.* FROM item
    			LEFT OUTER JOIN item_people
    				ON  item.record_id = item_people.item_id
    			LEFT OUTER JOIN people
    				ON  people.record_id = item_people.people_id
				LEFT OUTER JOIN events
    				ON item.record_id = events.attached_to
				LEFT OUTER JOIN designations
    				ON item.record_id = designations.attached_to
				LEFT OUTER JOIN tags
    				ON tags.attached_to = item.record_id AND	
					   tags.attached_table = 'item'
				WHERE $query_string
				$order_by $limit";			

	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$rows = array();
	
	$r_count = mysql_query("SELECT FOUND_ROWS()");
	$num_rows = mysql_fetch_array($r_count, MYSQL_ASSOC);
	$num_rows = $num_rows['FOUND_ROWS()'];
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$results = array('query' => $q, 
					 'count' => $num_rows,  
					 'rows' => $rows);
	
	return $results;
}

function getAreaHeaders($search_param = ''){
		
	// SET UP SEARCH PARAMETERS
	
	$search_params = explode(' ',$search_param);
	$query_string = array();

	foreach($search_params as $param){
		
		$param = mysql_real_escape_string($param);
		
		/*
		$year_query = "";
		
		if(preg_match("/^[0-9]{4}$/",$param)){
			$year_query = "OR events.end_year = '$param'";
		}elseif(preg_match("/^[0-9]{4}[s]{1}$/",$param)){
			$param = str_replace('s','',$param);
			$next_decade = $param + 10;
			$year_query = "OR (events.end_year >= '$param' AND events.end_year < '$next_decade')";
		}
		*/
		$query_string_areas[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR  			  
						    polygons.title LIKE '%$param%' OR 
						    polygons.type LIKE '%$param%')";
	}
	
	if($search_param == ''){
		unset($query_string_areas);
		$query_string_areas = " polygons.live = '1'";
	}else{
		$query_string_areas = join("\nAND\n",$query_string_areas) . " AND polygons.live = '1' ";
	}
				
	$q_areas = "SELECT 
					type, main_category, sub_category,  
					COUNT(DISTINCT polygons.record_id) as type_count
					FROM polygons
					LEFT OUTER JOIN polygons_people
    					ON  polygons.record_id = polygons_people.polygons_id
    				LEFT OUTER JOIN people
    					ON  people.record_id = polygons_people.people_id
					WHERE $query_string_areas
					GROUP BY main_category
					ORDER BY FIELD(main_category,'Art','Architecture','Cultural','Academic','Greenspaces')";			
	
	$r_areas = mysql_query($q_areas) OR die('unable to execute query <i>' . $q_areas . '</i>: ' . mysql_error());
	
	while($row_areas = mysql_fetch_array($r_areas, MYSQL_ASSOC)){ 
		$row_areas = clean_array($row_areas);
		$rows_areas[] = $row_areas;
	}
	
	$num_rows_areas = count($rows_areas);
	
	$results = array('query_areas' => $q_areas, 
					 'count_areas' => $num_rows_areas,
					 'rows_areas' => $rows_areas);
	
	return $results;
}

function getAreas($search_param, $type)
{	
	// SET UP SEARCH PARAMETERS
	
	$search_params = explode(' ',$search_param);
	$query_string = array();

	foreach($search_params as $param){
		
		$param = convert_state_to_abbreviation(strtolower($param));
		
		$param = mysql_real_escape_string($param);
		
		$query_string_areas[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR  			  
						    polygons.title LIKE '%$param%' OR
							polygons.main_category LIKE '%$param%' OR
							polygons.sub_category LIKE '%$param%' OR 
						    polygons.type LIKE '%$param%')";
	}
	
	if($search_param == ''){
		$query_string_areas = "polygons.main_category = '$type' AND polygons.live = '1'";
	}else{
		$query_string_areas = join("\nAND\n",$query_string_areas) . " AND polygons.main_category = '$type' AND polygons.live = '1' ";
	}
				
	$q_areas = "SELECT 
					DISTINCT
					polygons.record_id,
					polygons.title,
					polygons.type,
					polygons.main_category,
					polygons.sub_category,
					polygons.city,
					polygons.state
					FROM polygons
					JOIN ( SELECT city, COUNT(polygons.record_id) AS cnt
					   FROM polygons
					   GROUP BY city
					 ) c2 ON ( c2.city = polygons.city )
					LEFT OUTER JOIN polygons_people
    					ON  polygons.record_id = polygons_people.polygons_id
    				LEFT OUTER JOIN people
    					ON  people.record_id = polygons_people.people_id
					WHERE $query_string_areas
					ORDER BY c2.cnt DESC, polygons.state, polygons.city, polygons.title ASC";			
	
	$r_areas = mysql_query($q_areas) OR die('unable to execute query <i>' . $q_areas . '</i>: ' . mysql_error());
	
	while($row_areas = mysql_fetch_array($r_areas, MYSQL_ASSOC)){ 
		$row_areas = clean_array($row_areas);
		$rows_areas[] = $row_areas;
	}
	
	$num_rows_areas = count($rows_areas);
	
	$results = array('query' => $q_areas, 
					 'count' => $num_rows_areas, 
					 'rows' => $rows_areas);
	
	return $results;
}

function basicItemSearch($search_param = '', $order_by = 'title', $page = '1'){
	
	// Set up LIMIT, ROWS TO SHOW 
	$limit = ' LIMIT ' . (($page - 1) * 25) . ', 25';
	
	// ORDER BY
	switch($order_by){
		
		case 'title':
		$order_by = "ORDER BY item.name_title ASC";
		break;
		
		case 'category':
		$order_by = "ORDER BY item.category ASC, item.type ASC";
		break;
		
		case 'location_o':
		$order_by = "ORDER BY item.city ASC";
		break;		
	
		case 'type':
		$order_by = "ORDER BY item.type ASC";
		break;
	
		case 'people':
		$order_by = "ORDER BY people.name ASC";
		break;
		
		case 'recently_added':
		$order_by = "ORDER BY item.record_id DESC";
		break;		
	
		default:
		$order_by = "ORDER BY item.name_title ASC";
		break;
		
	}
		
	// SET UP SEARCH PARAMETERS
	
	$search_params = explode(' ',$search_param);
	$query_string = array();

	foreach($search_params as $param){
		
		$param = mysql_real_escape_string($param);
		
		$year_query = "";
		
		if(preg_match("/^[0-9]{4}$/",$param)){
			$year_query = "OR events.end_year = '$param'";
		}elseif(preg_match("/^[0-9]{4}[s]{1}$/",$param)){
			$param = str_replace('s','',$param);
			$next_decade = $param + 10;
			$year_query = "OR (events.end_year >= '$param' AND events.end_year < '$next_decade')";
		}
		
		$query_string[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR 
						  	concat_ws(' ',item.city,item.state) LIKE '%$param%' OR 
						   	concat_ws(' ',item.city,item.country) LIKE '%$param%' OR 			  
						   item.name_title LIKE '%$param%' OR 
						   item.city LIKE '%$param%' OR 
						   item.state LIKE '%$param%' OR 
						   item.category LIKE '%$param%' OR 
						   item.type LIKE '%$param%' OR 
						   item.on_lists LIKE '%$param%' OR
						   designations.designation LIKE '%$param%'
						   $year_query)";
	}
	
	
	if($search_param == ''){
		unset($query_string);
		$query_string = "item.live = '1'";
	}else{
		$query_string = join("\nAND\n",$query_string) . " AND item.live = '1' " . $alpha;
	}
	
	
	$q = "SELECT SQL_CALC_FOUND_ROWS DISTINCT item.* FROM item
    			LEFT OUTER JOIN item_people
    				ON  item.record_id = item_people.item_id
    			LEFT OUTER JOIN people
    				ON  people.record_id = item_people.people_id
				LEFT OUTER JOIN events
    				ON item.record_id = events.attached_to
				LEFT OUTER JOIN designations
    				ON item.record_id = designations.attached_to	
				WHERE $query_string
				$order_by $limit";

	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$rows = array();
	
	$r_count = mysql_query("SELECT FOUND_ROWS()");
	$num_rows = mysql_fetch_array($r_count, MYSQL_ASSOC);
	$num_rows = $num_rows['FOUND_ROWS()'];
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$results = array('query' => $q, 'count' => $num_rows, 'rows' => $rows);
	
	return $results;
}

/* ITEM SEARCH
********************************************************************************/

function currentPageURL() {
	$pageURL = $_SERVER['SCRIPT_NAME'];
	return $pageURL;
}

function compareSessionToReceived($session,$received){

	foreach($received as $key => $value){
		$value = cleanInput($value);
		$session[$key] = $value;
	}
	return $session;	
}

function stickyForm($index,$default = ''){

	if(empty($_SESSION[$index]) || $_SESSION[$index] == ''){
		return $default;
	}else{
		return $_SESSION[$index];
	}
}

// DROP DOWNS

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

function highlight($string,$search_param)
{
	$h_string = ''; // initialize a variable to hold the highlighted string
	
	if(stripos($string, $search_param) === FALSE){ // if the string DOES NOT contain the text to highlight
	
		$h_string = $string;	
	
	}else{ // if the string DOES contain the text to highlight
		
		$start_pos = stripos($string, $search_param);
		//echo '<!-- start position ',$start_pos,'-->';
		$end_pos = strlen($search_param) + $start_pos;
		//echo '<!-- end position ',$end_pos,'-->';
		$string = substr_replace( $string , '</span>' , $end_pos , 0 );
		$h_string = substr_replace( $string , '<span class="highlight">' , $start_pos , 0 );
	}
	
	return $h_string;
}

// GATHER INFORMATION

function getRelatedTags($table,$record_id,$return_as = 'IDS'){

	$q = "SELECT * FROM tags WHERE attached_table = '$table' AND attached_to = '$record_id' ORDER BY tag ASC";
	
	$r = mysql_query($q) OR die('getRelatedTags: unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	$tags = array();
	$record_ids = array();
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$record_ids[] = $row['record_id'];
		$tags[] = $row['tag'];
	}
	
	$related_tags = array('count' => $count, 'record_ids' => $record_ids);
	
	$csv_list = join(', ',$tags);
	
	switch($return_as){
		
		case 'IDS':
		return $related_tags;
		break;
		
		case 'CSV_LIST':
		return $csv_list;
		break;
		
		default:
		return $related_tags;
		break;
	}
	
	return $related_tags;
	
}

// IMAGES

function getAndShowMainImage($table,$record_id,$image_type){
	
	$row = getRelatedImages($table,$record_id);
	
	if($row['count'] >= 1){
		if($image_type == 'mini'){
			$image = displayMiniImage($row['record_ids'][0]);
		}elseif($image_type == 'thumb'){
			$image = displayThumbImage($row['record_ids'][0]);
		}elseif($image_type == 'web'){
			$image = displayWebImage($row['record_ids'][0]);
		}elseif($image_type == '40'){
			$image = display40Image($row['record_ids'][0]);			
		}elseif($image_type == '72'){
			$image = display72Image($row['record_ids'][0]);
		}
	}else{
		if($image_type == 'mini'){
			$image = '<div class="miniimage"></div>';
		}elseif($image_type == 'thumb'){
			$image = '<div class="thumbnail"></div>';
		}elseif($image_type == '40'){
			$image = '<div style="width:40px; height:40px;"></div>';	
		}elseif($image_type == '72'){
			$image = '<div style="width:72px; height:72px;"></div>';		
		}else{
			$image = '<img src="" />';	
		}
	}
	
	return $image;
	
}

function display72Image($record_id){
	$html = '<img width="72" height="72" src="http://www.culturenow.org/media/new_images/' . $record_id . '/thumb.jpg" />';
	return $html;
}

function display40Image($record_id){
	$html = '<img width="40" height="40" src="http://www.culturenow.org/media/new_images/' . $record_id . '/mini.jpg" />';
	return $html;
}

function displayMiniImage($record_id){
	$html = '<img width="67" height="67" src="http://www.culturenow.org/media/new_images/' . $record_id . '/mini.jpg" />';
	return $html;
}

function displayThumbImage($record_id){
	$html = '<img width="100" height="100" src="http://www.culturenow.org/media/new_images/' . $record_id . '/thumb.jpg" />';
	return $html;
}

function displayWebImage($record_id,$width = ''){
	$html = '<img ';
	if($width != ''){ $html .= 'width="' . $width . '" '; }
	$html .= ' src="http://www.culturenow.org/media/new_images/' . $record_id . '/web.jpg" />';
	return $html;
}

// NAVIGATION

function alphabeticalGroup($search_param, $page, $order_by, $alpha){
	
	echo '<a class="fg-button ' . ($alpha == 'A-E' ? 'bold' : 'ui-state-default') . ' ui-corner-all small" href="index.php?page=' . $page .  '&search_param=' . $search_param . '&order_by=' . $order_by . '&alpha=A-E">A - E</a>';
	
	echo '<a class="fg-button ' . ($alpha == 'F-J' ? 'bold' : 'ui-state-default') . ' ui-corner-all small" href="index.php?page=' . $page .  '&search_param=' . $search_param . '&order_by=' . $order_by . '&alpha=F-J">F - J</a>';
	
	echo '<a class="fg-button ' . ($alpha == 'K-O' ? 'bold' : 'ui-state-default') . ' ui-corner-all small" href="index.php?page=' . $page .  '&search_param=' . $search_param . '&order_by=' . $order_by . '&alpha=K-O">K - O</a>';
	
	echo '<a class="fg-button ' . ($alpha == 'P-T' ? 'bold' : 'ui-state-default') . ' ui-corner-all small" href="index.php?page=' . $page .  '&search_param=' . $search_param . '&order_by=' . $order_by . '&alpha=P-T">P - T</a>';
	
	echo '<a class="fg-button ' . ($alpha == 'V-Z' ? 'bold' : 'ui-state-default') . ' ui-corner-all small" href="index.php?page=' . $page .  '&search_param=' . $search_param . '&order_by=' . $order_by . '&alpha=V-Z">V - Z</a>';
	
}

function prevNextRecordsGroup($page,$start_at,$limit,$showing_count){
	
	$link = '';
	
	$back = $start_at - $limit;
	if($back < 0){ $back = 0; }
	
	if($start_at > 0){
		$link .= '<a class="fg-button ui-state-default ui-corner-all small" href="index.php?page=' . $page .  '&start_at=' . $back . '&limit=' . $limit . '">< PREV ' . $limit . '</a>';
	}
	
	if($showing_count >= $limit){
		$link .= '<a class="fg-button ui-state-default ui-corner-all small" href="index.php?page=' . $page .  '&start_at=' . ($start_at + $limit) . '&limit=' . $limit . '">NEXT ' . $limit . ' ></a>';
	}
	
	if($limit != 'all'){
		return $link;
	}else{
		return '';
	}
	
}

/* DEPRECATED FUNCTIONS, FOR REFERENCE
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************
***************************************************************/

function basicItemSearchDEP1($search_param = '',$start_at = 0,$rows_to_show = 25){
	
	$search_param = mysql_real_escape_string($search_param);
	
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}	
	
	
	$q = "SELECT DISTINCT item.* FROM item
    LEFT OUTER JOIN item_people
    ON  item.record_id = item_people.item_id
    LEFT OUTER JOIN people
    ON  people.record_id = item_people.people_id
WHERE item.live = '1'
AND
    (
        concat_ws(' ',people.name_first,people.name) LIKE '%$search_param%'
    OR  item.name_title LIKE '%$search_param%'
    OR  item.city = '%$search_param%'
    OR  item.category LIKE '%$search_param%'
    OR  item.on_lists LIKE '%$search_param%'
    )
    ORDER BY item.name_title $limit";
	
	//echo 'query: ',$q,'<hr/>';

	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$num_rows = mysql_num_rows($r);
	
	$rows = array();
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$results = array('query' => $q,'count' => $num_rows, 'rows' => $rows);
	
	return $results;
	
}

function basicItemSearchDEP2($search_param = '', $order_by = FALSE, $start_at = 0, $rows_to_show = 25, $alpha = 'a-zA-Z'){
	
	/*
	$search_param = '';
	$order_by = '';
	$start_at = '';
	$rows_to_show = ''; 
	$alpha = '';
	*/
	
	// Set up LIMIT, ROWS TO SHOW 
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}
	
	// GET ALPHABETICAL SECTION
	if(isset($alpha)){
		$alpha = strip_tags($alpha);
	}else{
		$alpha = 'A-E';	
	}
	
	// ORDER BY
	switch($order_by){
		
		case 'title':
		$order_by = "ORDER BY item.name_title ASC";
		$alpha = "AND name_title REGEXP '^[" . $alpha . "]'";
		$limit = '';
		break;
		
		case 'category':
		$order_by = "ORDER BY item.category ASC";
		$alpha = "AND category REGEXP '^[" . $alpha . "]'";
		$limit = '';
		break;
	
		case 'type':
		$order_by = "ORDER BY item.type ASC";
		$alpha = "AND type REGEXP '^[" . $alpha . "]'";
		$limit = '';
		break;
	
		case 'people':
		$order_by = "ORDER BY people.name ASC";
		$alpha = "AND people.name REGEXP '^[" . $alpha . "]'";
		$limit = '';
		break;
		
		case 'recently_added':
		$order_by = "ORDER BY item.record_id DESC";
		$alpha = "AND people.name REGEXP '^[" . $alpha . "]'";
		break;		
		
		case '':
		$order_by = "ORDER BY item.name_title DESC";
		$alpha = "AND people.name REGEXP '^[" . $alpha . "]'";
		break;
		
		default:
		$order_by = "ORDER BY item.name_title DESC";
		$alpha = "AND people.name REGEXP '^[" . $alpha . "]'";
		break;
		
	}
		
	// SET UP SEARCH PARAMETERS
	
	$search_params = explode(' ',$search_param);
	$query_string = array();

	foreach($search_params as $param){
		
		$param = mysql_real_escape_string($param);
		
		$year_query = "";
		
		if(preg_match("/^[0-9]{4}$/",$param)){
			$year_query = "OR events.end_year = '$param'";
		}elseif(preg_match("/^[0-9]{4}[s]{1}$/",$param)){
			$param = str_replace('s','',$param);
			$next_decade = $param + 10;
			$year_query = "OR (events.end_year >= '$param' AND events.end_year < '$next_decade')";
		}
		
		$query_string[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR 
						  	concat_ws(' ',item.city,item.state) LIKE '%$param%' OR 
						   	concat_ws(' ',item.city,item.country) LIKE '%$param%' OR 			  
						   item.name_title LIKE '%$param%' OR 
						   item.city LIKE '%$param%' OR 
						   item.state LIKE '%$param%' OR 
						   item.category LIKE '%$param%' OR 
						   item.type LIKE '%$param%' OR 
						   item.on_lists LIKE '%$param%' OR
						   designations.designation LIKE '%$param%'
						   $year_query)";
	}
	
	
	if($search_param == ''){
		unset($query_string);
		$query_string = "item.live = '1'" . $alpha;
	}else{
		$query_string = join("\nAND\n",$query_string) . " AND item.live = '1' " . $alpha;
	}
	
	
	$q = "SELECT SQL_CALC_FOUND_ROWS DISTINCT item.* FROM item
    			LEFT OUTER JOIN item_people
    				ON  item.record_id = item_people.item_id
    			LEFT OUTER JOIN people
    				ON  people.record_id = item_people.people_id
				LEFT OUTER JOIN events
    				ON item.record_id = events.attached_to
				LEFT OUTER JOIN designations
    				ON item.record_id = designations.attached_to	
				WHERE $query_string
				$order_by $limit";

	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$rows = array();
	
	$r_count = mysql_query("SELECT FOUND_ROWS()");
	$num_rows = mysql_fetch_array($r_count, MYSQL_ASSOC);
	$num_rows = $num_rows['FOUND_ROWS()'];
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$results = array('query' => $q,'count' => $num_rows, 'rows' => $rows);
	
	return $results;
	
}

function populateItemListPublicDEP($search_type = 'item',$title_search_style = 'all',$name_title = '',$map_name = 'all',$categories = 'all',$orgs = 'all',$order_by = 'recent',$start_at = '0',$rows_to_show = '50'){
	
	//echo '<font size="+3">search type: ' . $search_type . '</font><br />';
	
	$name_title = mysql_real_escape_string($name_title);
	
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

function rowDisplayDEP($row){

	$display = array();

    $podcasts = getPodcastCount('item',$row['record_id']);
    $main_image = getAndShowMainImage('item',$row['record_id'],'mini');
    $color_class = str_replace(' ','-',$row['category']);
    //$people = getRelatedPeople('item',$row['record_id']);
    
    $display[] = '<table width="100%" cellspacing="0" cellpadding="0">';
    $display[] = '<tr>';
    $display[] = '<td class="' . $color_class . '">';
    $display[] = $main_image;
    $display[] = '</td>';
	$display[] = '<div style="float:right">';
	if($podcasts['count'] > 0){
        $display[] = '<img src="ui/images/headphones.png" />';
    }
	$display[] = '</div>';
	$display[] = '</td>';
	$display[] = '</tr>';
	$display[] = '<tr>';
    $display[] = '<td>';
    $display[] = '<br /><font class="title-medium">';
	$display[] = cleanData($row['name_title']);
    $display[] = '</font><br />';
    $display[] = '<br /><span class="small">';
    
	$display[] = $row['city'] . ', ' . $row['state']; 
    $display[] = ' &bull; ' . $row['category'];
    $display[] = '</span><br />';
    $display[] = '</td>';
	$display[] = '</tr>';

	$display[] = '<tr>';
    $display[] = '<td>';
    $display[] = '<br /><ul class="small">';
 
    foreach($people['record_ids'] as $key => $person){
        $display[] = '<li>' . getPersonName($person,'FL') . '</li>';
    }
	
    $display[] = '</ul><br />';
    $display[] = '</td>';
	$display[] = '</tr>';
	$display[] = '<tr>';
    $display[] = '<td class="small">' . constrainLongText($row['remarks'],150) . '</td>';
	$display[] = '</tr>';
	$display[] = '<tr>';
    $display[] = '<td class="small" align="right"><br />';
	$display[] = '<a href="index.php?page=entry&permalink=' . $row['record_id'] . '">go to entry &rarr;</a>';
	$display[] = '</td>';
	$display[] = '</tr>';
	
    $display[] = '</table>';

	return join("\n",$display);
}

function basicItemSearchTESTDEP($search_param = '', $order_by = 'title', $page = '1', $cat_str = ''){
	
	// Set up LIMIT, ROWS TO SHOW 
	$limit = (($page - 1) * 15);
	if($limit < 0){ $limit = 0; }
	$limit = ' LIMIT ' . $limit . ', 15';
	// ORDER BY
	switch($order_by){
		
		case 'title':
		$order_by = "ORDER BY TRIM(LEADING '\'' FROM TRIM(LEADING '-' FROM TRIM(LEADING '(' FROM item.name_title))) ASC";
		$order_by_areas = "ORDER BY polygons.type ASC, polygons.name_title ASC";
		break;
		
		case 'category':
		$order_by = "ORDER BY item.category ASC, item.type ASC";
		$order_by_areas = "ORDER BY polygons.type ASC, polygons.type ASC";
		break;
		
		case 'location_o':
		$order_by = "ORDER BY item.city ASC";
		$order_by_areas = "ORDER BY polygons.type ASC, polygons.name_title ASC";
		break;		
	
		case 'type':
		$order_by = "ORDER BY item.type ASC";
		$order_by_areas = "ORDER BY polygons.type ASC";
		break;
	
		case 'people':
		$order_by = "ORDER BY people.name ASC";
		$order_by_areas = "ORDER BY polygons.type ASC, people.name  ASC";
		break;
		
		case 'recently_added':
		$order_by = "ORDER BY item.record_id DESC";
		$order_by_areas = "ORDER BY polygons.type ASC, polygons.record_id  ASC";
		break;		
	
		default:
		$order_by = "ORDER BY TRIM(LEADING '\'' FROM TRIM(LEADING '-' FROM TRIM(LEADING '(' FROM item.name_title))) ASC";
		$order_by_areas = "ORDER BY polygons.type ASC, polygons.name_title  ASC";
		break;
		
	}
		
	// SET UP SEARCH PARAMETERS
	
		// categories
	$cat_str = str_replace('-',' ',$cat_str);
	$cat_array = explode('_',$cat_str);
	$categories = "'" . join("', '",$cat_array) . "'";
	$category_condition = "\nAND item.sub_category IN ($categories) "; 
	
	$search_params = urldecode($search_params);
	$search_params = explode(' ',$search_param);
	$query_string = array();

	foreach($search_params as $param){

		if($param == 'Snohetta' || $param == 'snohetta'){
			$param = 'Snøhetta';	
		}

		$param = mysql_real_escape_string($param);
		
		$year_query = "";
		
		if(preg_match("/^[0-9]{4}$/",$param)){
			$year_query = "OR events.end_year = '$param'";
		}elseif(preg_match("/^[0-9]{4}[s]{1}$/",$param)){
			$param = str_replace('s','',$param);
			$next_decade = $param + 10;
			$year_query = "OR (events.end_year >= '$param' AND events.end_year < '$next_decade')";
		}
		
		$query_string[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR 
						  	concat_ws(' ',item.city,item.state) LIKE '%$param%' OR 
							concat_ws(', ',item.city,item.state) LIKE '%$param%' OR 
						   	concat_ws(' ',item.city,item.country) LIKE '%$param%' OR 			  
						    people.name_first LIKE '%$param%' OR
							people.name LIKE '%$param%' OR
							item.name_title LIKE '%$param%' OR 
						    item.city LIKE '%$param%' OR 
						    item.state LIKE '%$param%' OR 
						    item.main_category LIKE '%$param%' OR 
						    item.sub_category LIKE '%$param%' OR 							
						    item.type LIKE '%$param%' OR 
							tags.tag LIKE '%$param%' OR 
						    designations.designation LIKE '%$param%'
						    $year_query)";
		
		$query_string_areas[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR  			  
						    polygons.name_title LIKE '%$param%' OR 
						    polygons.main_category LIKE '%$param%' OR 
							polygons.sub_category LIKE '%$param%' OR
							polygons.type LIKE '%$param%')";
	}
	
	if($search_param == '' || $search_param == NULL){
		unset($query_string);
		$query_string = "item.live = '1' $category_condition";	
		$query_string_areas = "polygons.live = '1'";
	}else{
		$query_string = join("\nAND\n",$query_string) . " AND item.live = '1' $category_condition";
		$query_string_areas = join("\nAND\n",$query_string_areas) . " AND polygons.live = '1' ";
	}
	
	$q = "SELECT SQL_CALC_FOUND_ROWS DISTINCT item.* FROM item
    			LEFT OUTER JOIN item_people
    				ON  item.record_id = item_people.item_id
    			LEFT OUTER JOIN people
    				ON  people.record_id = item_people.people_id
				LEFT OUTER JOIN events
    				ON item.record_id = events.attached_to
				LEFT OUTER JOIN designations
    				ON item.record_id = designations.attached_to
				LEFT OUTER JOIN tags
    				ON tags.attached_to = item.record_id AND	
					   tags.attached_table = 'item'
				WHERE $query_string
				$order_by $limit";
				
	$q_areas = "SELECT 
					type, 
					COUNT(polygons.record_id) as type_count
					FROM polygons
					LEFT OUTER JOIN polygons_people
    					ON  polygons.record_id = polygons_people.polygons_id
    				LEFT OUTER JOIN people
    					ON  people.record_id = polygons_people.people_id
					WHERE $query_string_areas
					GROUP BY type
					$order_by_areas";			

	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$rows = array();
	
	$r_count = mysql_query("SELECT FOUND_ROWS()");
	$num_rows = mysql_fetch_array($r_count, MYSQL_ASSOC);
	$num_rows = $num_rows['FOUND_ROWS()'];
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$r_areas = mysql_query($q_areas) OR die('unable to execute query <i>' . $q_areas . '</i>: ' . mysql_error());
	
	while($row_areas = mysql_fetch_array($r_areas, MYSQL_ASSOC)){ 
		$row_areas = clean_array($row_areas);
		$rows_areas[] = $row_areas;
	}
	
	$num_rows_areas = count($rows_areas);
	
	$results = array('query' => $q, 
					 'query_areas' => $q_areas, 
					 'count' => $num_rows, 
					 'count_areas' => $num_rows_areas, 
					 'rows' => $rows,
					 'rows_areas' => $rows_areas);
	
	return $results;
}

function convert_state_to_abbreviation($state_name) {
    switch ($state_name) {
    case "alabama":
    return "AL";
    break;
    case "alaska":
    return "AK";
    break;
    case "arizona":
    return "AZ";
    break;
    case "arkansas":
    return "AR";
    break;
    case "california":
    return "CA";
    break;
    case "colorado":
    return "CO";
    break;
    case "connecticut":
    return "CT";
    break;
    case "delaware":
    return "DE";
    break;
    case "florida":
    return "FL";
    break;
    case "georgia":
    return "GA";
    break;
    case "hawaii":
    return "HI";
    break;
    case "idaho":
    return "ID";
    break;
    case "illinois":
    return "IL";
    break;
    case "indiana":
    return "IN";
    break;
    case "iowa":
    return "IA";
    break;
    case "kansas":
    return "KS";
    break;
    case "kentucky":
    return "KY";
    break;
    case "louisana":
    return "LA";
    break;
    case "maine":
    return "ME";
    break;
    case "maryland":
    return "MD";
    break;
    case "massachusetts":
    return "MA";
    break;
    case "michigan":
    return "MI";
    break;
    case "minnesota":
    return "MN";
    break;
    case "mississippi":
    return "MS";
    break;
    case "missouri":
    return "MO";
    break;
    case "montana":
    return "MT";
    break;
    case "nebraska":
    return "NE";
    break;
    case "nevada":
    return "NV";
    break;
    case "new hampshire":
    return "NH";
    break;
    case "new jersey":
    return "NJ";
    break;
    case "new mexico":
    return "NM";
    break;
    case "new york":
    return "NY";
    break;
    case "north carolina":
    return "NC";
    break;
    case "north dakota":
    return "ND";
    break;
    case "ohio":
    return "oh";
    break;
    case "oklahoma":
    return "OK";
    break;
    case "oregon":
    return "OR";
    break;
    case "pennsylvania":
    return "PA";
    break;
    case "rhode island":
    return "RI";
    break;
    case "south carolina":
    return "SC";
    break;
    case "south dakota":
    return "SD";
    break;
    case "tennessee":
    return "TN";
    break;
    case "texas":
    return "TX";
    break;
    case "utah":
    return "UT";
    break;
    case "vermont":
    return "VT";
    break;
    case "virginia":
    return "VA";
    break;
    case "washington":
    return "WA";
    break;
    case "washington d.c.":
    return "DC";
    break;
    case "washington dc":
    return "DC";
    break;	
    case "west virginia":
    return "WV";
    break;
    case "wisconsin":
    return "WI";
    break;
    case "wyoming":
    return "WY";
    break;
    case "alberta":
    return "AB";
    break;
    case "british columbia":
    return "BC";
    break;
    case "manitoba":
    return "MB";
    break;
    case "new brunswick":
    return "NB";
    break;
    case "newfoundland & labrador":
    return "NL";
    break;
    case "newfoundland and labrador":
    return "NL";
    break;	
    case "newfoundland":
    return "NL";
    break;	
    case "labrador":
    return "NL";
    break;		
    case "northwest territories":
    return "NT";
    break;
    case "nova scotia":
    return "NS";
    break;
    case "nunavut":
    return "NU";
    break;
    case "ontario":
    return "ON";
    break;
    case "prince edward island":
    return "PE";
    break;
    case "pei":
    return "PE";
    break;	
    case "quebec":
    return "QC";
    break;
    case "saskatchewan":
    return "SK";
    break;
    case "yukon territory":
    return "YT";
    break;
    default:
    return $state_name;
    }
}

?>