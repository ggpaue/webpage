<?php

// GATHER INFORMATION

function toursList($tour_section = FALSE){
	
	if(empty($tour_section) || $tour_section == 'all' || $tour_section == 'map'){
		$q = "SELECT * FROM tours WHERE live = '1'
				ORDER BY tour_name ASC";
	}else{
		$q = "SELECT * FROM tours WHERE live = '1' AND section = '$tour_section' 
				ORDER BY tour_name ASC";
	}
	
	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$num_rows = mysql_num_rows($r);
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$results = array('query' => $q,'count' => $num_rows, 'rows' => $rows);
	
	return $results;
	
}

function tourStopsList($tour_id){
	
	if($tour_id == 'all'){
		$q = "SELECT * FROM tour_stops ORDER BY stop_order";
	}else{
		$q = "SELECT * FROM tour_stops WHERE attached_to_tour = '$tour_id' ORDER BY stop_order";
	}
	
	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$num_rows = mysql_num_rows($r);
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$row = clean_array($row);
		$rows[] = $row;
	}
	
	$results = array('query' => $q,'count' => $num_rows, 'rows' => $rows);
	
	return $results;
	
}

function getTourSections()
{
	$q = "SELECT 
			tours.section,
			COUNT(tours.record_id) AS tour_count
			FROM tours
			WHERE 
				live = '1' AND
				section != ''
			GROUP BY tours.section
			ORDER BY section ASC";
	if($r = mysql_query($q)){
		$rows = array();
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
			$rows[] = $row;
		}
		return $rows;	
	}else{
		return mysql_error();	
	}
}

function tourStop($tour_stop_id){
	
	$q = "SELECT * FROM tour_stops WHERE record_id = '$tour_stop_id'";
	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$num_rows = mysql_num_rows($r);
	
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
	return $row;
	
}

function getTourInfo($tour_id){
	
	$q = "SELECT * FROM tours WHERE record_id = '$tour_id'";
	$r = mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
	$num_rows = mysql_num_rows($r);
	
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
	return $row;
	
}

function displayTourPodcastsNew($podcasts_array = array()){

	if($podcasts_array['count'] > 0){ ?>
		
        <table width="100%" cellspacing="0" cellpadding="2">
        <tr>
		<td align="center" width="100%" bgcolor="#EC2A8C">
        <span class="small white"><strong>podcasts</strong></span>
        </td>
        </tr>
        <?php
		foreach($podcasts_array['record_ids'] as $podcast_id):
			$podcast = getRecord('podcasts',$podcast_id);
			$podcasters = getRelatedPeople('podcasts',$podcast_id); ?>
			<tr>
            <td align="center" valign="top" width="100%">
            <br />
            <?=$podcast['file_name']?>
            <br />
            <div id="jquery_jplayer">player</div>
            <br /><br />
			</td>
			</tr>
		<?php endforeach; ?>
		<tr>
		<td align="center" width="100%" bgcolor="#EC2A8C">
        <span class="small white"><strong>slideshow</strong></span>
        </td>
        </tr>
		</table>
	<?php	
    }else{
		echo '<div class="infoheading">There are no podcasts for this section of the tour.</div>';
	}
}


?>