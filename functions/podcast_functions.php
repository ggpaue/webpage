<?php

function getPodcastsList($start_at = 0,$rows_to_show = 25){
	
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}	
	
	$q = "SELECT * FROM podcasts WHERE live = '1' ORDER BY record_id DESC $limit";
	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$num_rows = mysql_num_rows($r);
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$results = array('query' => $q,'count' => $num_rows, 'rows' => $rows);
	
	return $results;
	
}

function getRandomPodcast(){
	$query = "SELECT 
				COUNT(record_id) AS count 
				FROM podcasts
				WHERE 
				tour_podcast = '0' AND 
				live = '1'"; 
	$result = mysql_query($query) or die(mysql_error());
	$row = mysql_fetch_array($result);
	$rand = rand(0, ($row['count'] - 1));
	$limit = "LIMIT $rand, 1";
	$query = "SELECT 
				* 
				FROM podcasts
				WHERE tour_podcast = '0' AND 
				live = '1'
				$limit"; 
	$result = mysql_query($query);
	$row = mysql_fetch_array($result,  MYSQL_ASSOC);
	return $row;
}

function podcastSearch($search_param, $order_by, $page)
{
	// Set up LIMIT, ROWS TO SHOW 
	$limit = ' LIMIT ' . (($page - 1) * 25) . ', 25';
	
	// ORDER BY
	switch($order_by){
		
		case 'title':
		$order_by = "ORDER BY TRIM(LEADING '\'' FROM TRIM(LEADING '-' FROM TRIM(LEADING '(' FROM podcasts.title))) ASC";
		break;
		
		case 'category':
		$order_by = "ORDER BY podcasts.categories ASC";
		break;
	
		case 'type':
		$order_by = "ORDER BY podcasts.podcast_type ASC";
		break;
	
		case 'podcaster':
		$order_by = "ORDER BY people.name ASC";
		break;
		
		case 'site':
		$order_by = "ORDER BY podcasts.title ASC";
		break;		
		
		case 'recently_added':
		$order_by = "ORDER BY podcasts.record_id DESC";
		break;		
	
		default:
		$order_by = "ORDER BY podcasts.title ASC";
		break;
		
	}
		
	// SET UP SEARCH PARAMETERS
	
	$search_params = explode(' ',$search_param);
	$query_string = array();

	foreach($search_params as $param){
		
		$param = mysql_real_escape_string($param);
		
		$year_query = "";
		
		if(preg_match("/^[0-9]{4}$/",$param)){
			$year_query = "OR podcasts.date_recorded = '$param'";
		}elseif(preg_match("/^[0-9]{4}[s]{1}$/",$param)){
			$param = str_replace('s','',$param);
			$next_decade = $param + 10;
			$year_query = "OR (podcasts.date_recorded >= '$param' AND podcasts.date_recorded < '$next_decade')";
		}
		
		$query_string[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR
						   podcasts.title LIKE '%$param%' OR 
						   tags.tag LIKE '%$param%' OR 
						   podcasts.write_up LIKE '%$param%' OR
						   podcasts.podcast_type LIKE '%$param%' OR
						   item.city LIKE '%$param%' 
						   $year_query)";
	}
	
	
	if($search_param == ''){
		unset($query_string);
		$query_string = "podcasts.live = '1'";
	}else{
		$query_string = join("\nAND\n",$query_string) . " AND podcasts.live = '1' " . $alpha;
	}
	
	$q = "SELECT SQL_CALC_FOUND_ROWS DISTINCT podcasts.* FROM podcasts
    			LEFT OUTER JOIN item_podcast
    				ON  podcasts.record_id = item_podcast.podcasts_id
    			LEFT OUTER JOIN item
    				ON  item.record_id = item_podcast.item_id
    			LEFT OUTER JOIN podcast_people
    				ON  podcasts.record_id = podcast_people.podcasts_id
    			LEFT OUTER JOIN people
    				ON  people.record_id = podcast_people.people_id					
				LEFT OUTER JOIN tags ON  
					tags.attached_to = podcasts.record_id AND
					tags.attached_table = 'podcasts'
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

function basicPodcastSearch($podcast_param = '',$start_at = 0,$rows_to_show = 25){
	
	$param = mysql_real_escape_string($podcast_param);
	
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}	
			
	$q = "SELECT DISTINCT 
		   'podcast' as xmltag,
			podcasts.record_id,
			podcasts.file_name,
			podcasts.mobile_url,
			podcasts.title,
			podcasts.categories,
			podcasts.write_up,
			podcasts.date_recorded,
			(MATCH podcasts.title, podcasts.categories, podcasts.write_up 
			 AGAINST ('$param')) +
			(MATCH people.name_first, people.name
			 AGAINST ('$param')) +
			((MATCH item.name_title
			 AGAINST ('$param')) / 2 ) AS score 
			FROM podcasts
			LEFT OUTER JOIN podcast_people
				ON  podcasts.record_id = podcast_people.podcasts_id
			LEFT OUTER JOIN people
				ON  people.record_id = podcast_people.people_id
			LEFT OUTER JOIN item_podcast
				ON podcasts.record_id = item_podcast.podcasts_id
			LEFT OUTER JOIN item
				ON item.record_id = item_podcast.item_id						
			WHERE 
				podcasts.live = '1' AND
		   (MATCH
			  podcasts.title, 
			  podcasts.categories, 
			  podcasts.write_up 
			AGAINST ('$param') OR
			MATCH
			  people.name_first,
			  people.name
			AGAINST ('$param') OR
			MATCH
			   item.name_title
			AGAINST ('$param') OR
			MATCH
			   item.category
			AGAINST ('$param') OR
			MATCH
			   item.city,
			   item.state
			AGAINST ('$param'))
		   HAVING score > '2'	
		   ORDER BY score DESC, date_recorded DESC
			$limit";		
	
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

function basicPodcastSearchNEW($podcast_param = '',$start_at = 0,$rows_to_show = 25){
	
	// Set up LIMIT, ROWS TO SHOW 
	
	if($rows_to_show == 'all'){
		$rows_to_show = '';
		$limit = '';
	}else{
		$limit = ' LIMIT ' . $start_at . ', ' . $rows_to_show;
	}
	
	// SET UP SEARCH PARAMETERS
	
	$podcast_params = explode(' ',$podcast_param);
	
	$query_string = array();
	

	foreach($podcast_params as $param){
		
		$param = mysql_real_escape_string($param);
		
		$year_query = "";
		
		if(preg_match("/^[0-9]{4}$/",$param)){
			$year_query = "OR podcasts.date_recorded = '$param'";
		}elseif(preg_match("/^[0-9]{4}[s]{1}$/",$param)){
			$param = str_replace('s','',$param);
			$next_decade = $param + 10;
			$year_query = "OR (podcasts.date_recorded >= '$param' AND podcasts.date_recorded < '$next_decade')";
		}
		
		$query_string[] = "(concat_ws(' ',people.name_first,people.name) LIKE '%$param%' OR 
						   podcasts.title LIKE '%$param%' OR 
						   podcasts.categories LIKE '%$param%' OR
						   podcasts.write_up LIKE '%$param%'
						   $year_query)";
	}
	
	$query_string = join("\nAND\n",$query_string);
	
	
	$q = "SELECT SQL_CALC_FOUND_ROWS DISTINCT podcasts.* FROM podcasts
    			LEFT OUTER JOIN podcast_people
    				ON  podcasts.record_id = podcast_people.podcasts_id
    			LEFT OUTER JOIN people
    				ON  people.record_id = podcast_people.people_id
				WHERE podcasts.live = '1'
				AND ( $query_string )
				ORDER BY podcasts.record_id DESC $limit";
	
	echo '<!-- query: <pre>',$q,'</pre><hr/> -->';

	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	//$num_rows = mysql_num_rows($r);
	
	$rows = array();
	
	$r_count = mysql_query("SELECT FOUND_ROWS()");
	$num_rows = mysql_fetch_array($r_count, MYSQL_ASSOC);
	$num_rows = $num_rows['FOUND_ROWS()'];
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$results = array('query' => $q,'count' => $num_rows, 'rows' => $rows);
	
	//echo '<!-- rows: ',print_r($rows),' -->';
	
	return $results;
	
}

function getPodcastImageHTTPLocation($record_id){
	
	$podcast_image = getMainImageHTTPLocation('podcasts',$record_id);
	
	if($podcast_image == '/ui/images/blank.png'){
		$items = getRelatedItems('podcasts',$record_id);
		$item_image = getMainImageHTTPLocation('item',$items['record_ids'][0]);	
	}else{
		return $podcast_image;	 
	}
	
	if($item_image == '/ui/images/blank.png'){
		$areas = getRelatedAreas('podcasts',$record_id);
		$area_image = getMainImageHTTPLocation('polygons',$areas['record_ids'][0]);	
	}else{
		return $item_image;	 
	}	
	
	if($area_image == '/ui/images/blank.png'){
		$people = getRelatedPeople('podcasts',$record_id);
		$person_image = "/ui/images/blank.png";	
	}else{
		return $area_image;	
	}
	
	if($person_image == '/ui/images/blank.png'){
		return '/ui/images/blank.png';
	}else{
		return $person_image;
	}

}

function getMainImageHTTPLocation($table,$record_id){

	$q = "SELECT * FROM new_images WHERE attached_table = '$table' AND attached_to = '$record_id' ORDER BY default_image DESC, img_order ASC LIMIT 1";
	$r = @mysql_query($q) OR die('unable to execute ' . $table . ' query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
    //echo $q,' - ',$count,'<br />';
	
	if($count > 0){
		return 'http://www.culturenow.org/media/new_images/' . $row['record_id'] . '/mini.jpg';	
	}else{
		return '/ui/images/blank.png';	
	}
	
}

function makeWhereStatement($params){

    // sanity check 
    if(!$params || !count ($params)){
        return ''; 
	}

    // get last element    
    $last = array_pop ($params); 

    // if it was the only element - return it 
    if(!count($params)){
        return $last;
	}

    return implode(', ',$params) . ' AND ' . $last; 

}

?>