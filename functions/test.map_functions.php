<?php

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
	return '<total_time>' . $name . ' time elapsed: ' . $totaltime . ' seconds</total_time>';
		
}	

function showCurrentPin($width,$letter){
	return '<img width="' . $width . '" src="http://chart.apis.google.com/chart?chst=d_map_xpin_letter&chld=pin_star|A|00FFFF|000000|FF0000" />';
}

function showNearbyPin($width,$letter){
	return '<img width="' . $width . '" src="http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' . $letter . '|BDD73C|000000" />';
}

/*******************************

plotMainMap($zoom,$largest_distance,$NELat,$NELng,$SWLat,$SWLng,$category)

$zoom - current zoom of the map

$largest_distance - the largest distance things must be from each other so they are not lumped together in a cluster. This will be further modified based onthe zoom of the map - the more zoomed in things are the closer things can be without falling into clusters.

$NELat,$NELng,$SWLat,$SWLng - bounds of the map

$category - the category to display

*******************************/

function plotMainMap($zoom,$largest_distance,$NELat,$NELng,$SWLat,$SWLng){
	
	$centers_of_gravity = array();
	$new_center = TRUE;
	
	$starttime = get_time_start(); // get the start time to display query execution time
	
	$category_query_array = array();
	
	foreach($_SESSION['map']['search_array'] as $category){
		$category_query_array[] = "category = '$category'";		
	}
	
	$category_query = join(" OR\n" ,$category_query_array);  

	$centers_distance = $largest_distance / pow(2,$zoom); // The centers distance is based on the zoom

	$q = "SELECT concat_ws(', ',latitude,longitude) 
				 AS latLng, latitude, longitude, name_title, category, record_id 
				 FROM item WHERE 
				 live = '1' AND 
				 ($category_query) AND 
				 latitude <= $NELat AND
				 longitude <= $NELng AND
				 latitude >= $SWLat AND
				 longitude >= $SWLng AND
				 latitude != '' AND 
				 longitude != '' AND 
				 latitude != '0' AND 
				 longitude != '0'"; // The query		 
				 
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results

	$i = 0;
	
	if($zoom >= 16){ // if zoom is past a certain point, then don't bother clustering.
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$row['number_at_center'] = 1;
			$results['items'][] = $row;
		}
		
	}else{ // but if you want clustering, then....
	
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ // go through all the rows, and make the clusters.
			
			if($i == 0){ // if this is the first item, then it is a new center
				$new_center = TRUE;
			}else{
			
				foreach($centers_of_gravity as $key => $value){
				
					$distance = getDistance($value,$row['latLng']);
				
					if($distance <= $centers_distance){
						$new_center = FALSE;
						break;
					}else{
						$new_center = TRUE;
					}	
				}
			}
			
			//echo '<b>new center: ',$new_center,'</b><br />';
			
			if($new_center == TRUE){
				$point_list[count($centers_of_gravity)][] = array('latLng' => $row['latLng'], 'record_id' => $row['record_id']);
				$centers_of_gravity[] = $row['latLng'];
			}elseif($new_center == FALSE){
				$point_list[$key][] = $row['latLng'];
				$centers_of_gravity[$key] = getTrueCenter($row['latLng'],$centers_of_gravity[$key],count($point_list[$key]));
			}
		
			$new_center = '';
			$i = $i + 1;
		
		}
		
		$cgi = 1;
		
		
		//echo '<pre>',print_r($centers_of_gravity),'</pre>';
		//echo '<pre>',print_r($point_list),'</pre>';
		
		foreach($centers_of_gravity as $key => $value){
			
			$value = explode(', ',$value);
			
			$latitude = $value[0];
			$longitude = $value[1];
			
			$number_at_center = count($point_list[$key]);
			
			if($number_at_center == 1){
						
				$results['items'][$key] = array('record_id' => $point_list[$key][0]['record_id'],
											'latitude' => $latitude, 
											'longitude' => $longitude, 
											'number_at_center' => $number_at_center);
			}else{
				$results['items'][$key] = array('record_id' => $cgi,
											'latitude' => $latitude, 
											'longitude' => $longitude, 
											'number_at_center' => $number_at_center);
				
			}
				
			$cgi = 1 + $cgi;
		}
	
	}
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['total_time'] = $total_time;
	$results['query'] = $q;
	
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

function getTrueCenter($point,$center,$cweight){
		
	$point = explode(', ',$point);
		
	$plat = $point[0];
	$plng = $point[1];
	
	$center = explode(', ',$center);
		
	$clat = $center[0];
	$clng = $center[1];
	
	$mlat = ($plat + ($clat * $cweight)) / ($cweight + 1);
	$mlng = ($plng + ($clng * $cweight)) / ($cweight + 1);
	
	return $mlat . ', ' . $mlng;

}

/***************************
* MAP DISPLAY
***************************/

function displayCategorySelect($item_categories){

	$selector = '';

	$count = count($item_categories);
	$width = floor(844 / $count);

	$selector .= '<div id="s-head" class="selector">' . "\n";
	$selector .= '<div id="selector-heading">' . "\n";
    $selector .= 'key' . "\n";
    $selector .= '</div>' . "\n";
	$selector .= '</div>' . "\n";

	$selector .= '<div id="s-body">' . "\n"; 
	foreach($item_categories as $category){
		 $selector .= '<div class="selector" name="' . str_replace(' ','-',$category) . '" search="yes">' . "\n";
		 $selector .= '<div class="' . str_replace(' ','-',$category) . '" style="float:right; width:16px; height:16px;">' . "\n";
		 $selector .= '<img src="ui/images/pinhead-knockout.png" />' . "\n";
		 $selector .= '</div>' . "\n";
		 $selector .= '<div id="selector-text" class="small">' . "\n";
		 $selector .= strtolower($category) . "\n";
		 $selector .= '</div>' . "\n";
		 $selector .= '</div>' . "\n";
	}
	$selector .= '</div>' . "\n";
	
	return $selector;

}

?>