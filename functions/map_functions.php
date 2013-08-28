<?php

include('convex_hull.php');

define('OFFSET', 268435456);
define('RADIUS', 85445659.4471); /* $offset / pi() */

function haversineDistance($site1, $site2) {
    
	$site1 = explode(', ',$site1);
	$site2 = explode(', ',$site2);
	
	$latd = deg2rad($site2[0] - $site1[0]);
    $lond = deg2rad($site2[1] - $site1[1]);
    $a = sin($latd / 2) * sin($latd / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($lond / 2) * sin($lond / 2);
         $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return 6371.0 * $c;
}
    
function lonToX($lon) {
    return round(OFFSET + RADIUS * $lon * pi() / 180);        
}

function latToY($lat) {
    return round(OFFSET - RADIUS * 
                log((1 + sin($lat * pi() / 180)) / 
                (1 - sin($lat * pi() / 180))) / 2);
}

function pixelDistance($site1, $site2, $zoom) {
    
	$site1 = explode(', ',$site1);
	$site2 = explode(', ',$site2);
	
	$x1 = lonToX($site1[1]);
    $y1 = latToY($site1[0]);

    $x2 = lonToX($site2[1]);
    $y2 = latToY($site2[0]);
        
    return sqrt(pow(($x1-$x2),2) + pow(($y1-$y2),2)) >> (21 - $zoom);
}

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

function getAllPins($zoom){
	
	// get session variables
	//$zoom = $zoom;
	$show_sites = 'yes';
	$show_districts = 'yes';
	$show_tours = 'yes';
	$show_events = 'yes';
	
	// intitialize
	$sites = array();
	$districts = array();
	$tours = array();
	$events = array();
	$largestDistance = 10000;
	$results['total_time'] = '';
	$results['zoom'] = $zoom;
	$results['queries']['sites'] = ''; 
	$results['queries']['districts'] = ''; 
	$results['queries']['tours'] = '';
	$results['queries']['events'] = '';
	$results['sites'] = array();
	$results['districts'] = array();
	$results['tours'] = array();
	$results['events'] = array();
	
	// get the start time
	$starttime = get_time_start();
	
	// get sites
	if($show_sites == 'yes'):
	
		// set up query
		$q = "SELECT 
				 'site' AS point_type,
				 concat_ws(', ',latitude,longitude) AS coordinate, 
				 latitude, 
				 longitude, 
				 name_title, 
				 category, 
				 record_id, 
				 has_podcast 
				 FROM item WHERE 
				 live = '1'
				 AND 
				 latitude != '' AND 
				 longitude != '' AND 
				 latitude != '0' AND 
				 longitude != '0'";
				 
		$results['queries']['sites'] = $q;			 		 
		$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
		$sites = array();
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
			$sites[] = array('type' => 'single',
							 'point_type' => $row['point_type'],
							 'has_podcast' => $row['has_podcast'],
							 'record_id' => $row['record_id'],
							 'name_title' => $row['name_title'],
							 'category' => $row['category'],
							 'coordinate' => $row['coordinate']);
		}
	
	endif; // end if show sites is yes
	
	/*
	// get districts
	if($show_districts == 'yes'):
	
		$q = "SELECT * FROM polygons 
			  WHERE 
			  live = '1'"; // The query		 
		$results['queries']['districts'] = $q;
		
		$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
		
		if($zoom >= 1){ // if zoom is greater than a certain point, draw the polygons.
			
			while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
				$districts[] = $row;
			} // /while mysql_fetch_array
		
		} // /if zoom
	
	endif; // /if show districts is yes
	*/
	/*
	// get tours
	if($show_tours == 'yes'):
	
		$centers_of_gravity = array();
		$new_center = TRUE;
	
		$centers_distance = $largestDistance / pow(2,$zoom); // The centers distance is based on the zoom
	
		$q = "SELECT
			  'tour' AS point_type,
			  record_id,
			  tour_name as name_title,
			  categories as category,
			  'no' as has_podcast
		      FROM tours WHERE live = '1'"; // The query		  
		$results['queries']['tours'] = $q;
		$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
	
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
				$q2 = "SELECT * 
						FROM tour_stops 
						WHERE attached_to_tour = '{$row['record_id']}' 
						ORDER BY stop_order LIMIT 1";
				$r2 = mysql_query($q2) or 
						die('unable to execute query <i>' . $q2 . '</i>: ' . mysql_error()); 
				$stop_row = mysql_fetch_array($r2, MYSQL_ASSOC);
				$row['latitude'] = $stop_row['latitude'];
				$row['longitude'] = $stop_row['longitude'];
				$row['latLng'] = $stop_row['latitude'] . ', ' . $stop_row['longitude'];
				$tours[] = $row;
		}
		
	endif; // /if show tours is yes
	*/
	// get events
	if($show_events == 'yes'):
	
		// set up query
		$q = "SELECT 
			    'event' AS point_type,
				concat_ws(', ',latitude,longitude) AS coordinate, 
				latitude, 
				longitude, 
				title as name_title, 
				'Today' as category, 
				record_id, 
				'no' as has_podcast 
				FROM single_events WHERE 
				live = '1'
				AND 
				latitude != '' AND 
				longitude != '' AND
				latitude != '0' AND 
				longitude != '0'
				";		 
		$results['queries']['events'] = $q;			 		 
		$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
		$events = array();
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
			$events[] = array('type' => 'single',
								 	    'point_type' => $row['point_type'],
									    'has_podcast' => $row['has_podcast'],
								 	    'record_id' => $row['record_id'],
								 	    'name_title' => $row['name_title'],
								   	    'category' => $row['category'],
								        'coordinate' => $row['coordinate']);
		}
	
	endif; // /if show events is yes	

	/* WORK RESULTS */
	
	$merged_set = array_merge($sites, $districts, $tours, $events);
	
	if($zoom <= 16){
		$merged_set = clusterResultsNEW($merged_set, $zoom);
	}
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['total_time'] = $total_time;
	$results['merged_set'] = $merged_set;
	
	//echo '<pre>',print_r($results),'</pre>';
	
	return $results;
	
}

function clusterResultsNEW($markers,$zoom)
{
	$pixel_distance = 35;
	
	$clusters = array();
    // Loop until all markers have been compared.
    while(count($markers)){
		
		// reset the counters
		$site_count = 0;
		$district_count = 0;
		$tour_count = 0;
		$event_count = 0;
		
        $marker = array_pop($markers);
		
		switch($marker['point_type']){
			case 'site':
			$site_count++;
			break;
			
			case 'district':
			$district_count++;
			break;
			
			case 'tour':
			$tour_count++;
			break;
			
			case 'event':
			$event_count++;
			break;					
		}
		
		// Compare against all markers which are left.
		foreach ($markers as $key => $target) {
            
			$pixels = pixelDistance($marker['coordinate'],
                                    $target['coordinate'],
                                    $zoom);
            // If two markers are closer than given distance combine 
            // the target and the marker.
			if($pixel_distance > $pixels){
                
				unset($marker['name']);
				unset($marker['record_id']);
				unset($marker['point_type']);
				
				$marker['type'] = 'group';
				
				if($target['has_podcast'] == '1'){
					$marker['has_podcast'] = '1';
				}
				switch($target['point_type']){
					case 'site':
					$site_count++;
					break;
					
					case 'district':
					$district_count++;
					break;
					
					case 'tour':
					$tour_count++;
					break;
					
					case 'event':
					$event_count++;
					break;					
				}
				
				unset($markers[$key]);
			}
        }
		
		if($marker['type'] == 'group'){
			$marker['site_count'] = $site_count;
			$marker['district_count'] = $district_count;
			$marker['tour_count'] = $tour_count;
			$marker['event_count'] = $event_count;
		}
		
        $clusters[] = $marker;
    
	}
	
	return $clusters;
}

function plotMainMapNEWNEW(){
	
	// get session variables
	$zoom = $_SESSION['map_zoom'];
	$show_sites = $_SESSION['map_sites_show'];
	$show_districts = $_SESSION['map_districts_show'];
	$show_tours = $_SESSION['map_tours_show'];
	$show_events = $_SESSION['map_events_show'];
	$sites_categories = $_SESSION['map_sites_categories'];
	$districts_categories = $_SESSION['map_districts_categories'];
	$tours_categories = $_SESSION['map_tours_categories'];
	$events_categories = $_SESSION['map_events_categories'];
	$NELat = $_SESSION['map_bounds_NELat']; 
	$NELng = $_SESSION['map_bounds_NELng'];
	$SWLat = $_SESSION['map_bounds_SWLat'];
	$SWLng = $_SESSION['map_bounds_SWLng'];
	
	// intitialize
	$sites = array();
	$districts = array();
	$tours = array();
	$events = array();
	$largestDistance = 10000;
	$results['total_time'] = '';
	$results['zoom'] = $zoom;
	$results['show_sites'] = $show_sites;
	$results['show_districts'] = $show_districts;
	$results['show_tours'] = $show_tours;
	$results['show_events'] = $show_events;
	$results['sites_categories'] = $sites_categories;
	$results['districts_categories'] = $districts_categories;
	$results['tours_categories'] = $tours_categories;
	$results['events_categories'] = $events_categories;
	$results['NELat'] = $NELat; 
	$results['NELng'] = $NELng;
	$results['SWLat'] = $SWLat;
	$results['SWLng'] = $SWLng;	
	$results['queries']['sites'] = ''; 
	$results['queries']['districts'] = ''; 
	$results['queries']['tours'] = '';
	$results['queries']['events'] = '';
	$results['sites'] = array();
	$results['districts'] = array();
	$results['tours'] = array();
	$results['events'] = array();
	
	// get the start time
	$starttime = get_time_start();
	
	// get sites
	if($show_sites == 'yes'):
		$sites_categories = explode('_',$sites_categories);		
		$sites_categories = join("', '" ,str_replace('-',' ',$sites_categories)); 
		
		if($NELng < $SWLng){
			$longitude_query = "(longitude <= '$NELng') OR (longitude >= '$SWLng')";
		}else{
			$longitude_query = "(longitude >= '$NELng' AND longitude <= '$SWLng')";
		}
		
		if($zoom <= 8){
			$latlng_query = "";
		}else{
			$latlng_query = "(latitude <= '$NELat' AND latitude >= '$SWLat') AND
							  $longitude_query AND";
		}
	
		// set up query
		$q = "SELECT 
				 'site' AS point_type,
				 concat_ws(', ',latitude,longitude) AS latLng, 
				 latitude, 
				 longitude, 
				 name_title, 
				 category, 
				 record_id, 
				 has_podcast 
				 FROM item WHERE 
				 live = '1' AND
				 category IN ('$sites_categories') 
				 AND 
				 $latlng_query
				 latitude != '' AND 
				 longitude != '' AND 
				 latitude != '0' AND 
				 longitude != '0'";
				 
		$results['queries']['sites'] = $q;			 		 
		$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
		$sites = array();
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
			$sites[] = $row;	
		}
	
	endif; // end if show sites is yes
	
	// get districts
	if($show_districts == 'yes'):
	
		$districts_categories = explode('_',$districts_categories);		
		$districts_categories = join("', '" ,str_replace('-',' ',$districts_categories)); 
	
		$q = "SELECT * FROM polygons 
			  WHERE 
			  type IN ('$districts_categories') AND  
			  live = '1'"; // The query		 
		$results['queries']['districts'] = $q;
		
		$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
		
		if($zoom >= 1){ // if zoom is greater than a certain point, draw the polygons.
			
			while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
				$districts[] = $row;
			} // /while mysql_fetch_array
		
		} // /if zoom
	
	endif; // /if show districts is yes
	
	// get tours
	if($show_tours == 'yes'):
	
		$centers_of_gravity = array();
		$new_center = TRUE;
	
		$centers_distance = $largestDistance / pow(2,$zoom); // The centers distance is based on the zoom
	
		$q = "SELECT
			  'tour' AS point_type,
			  record_id,
			  tour_name as name_title,
			  categories as category,
			  'no' as has_podcast
		      FROM tours WHERE live = '1'"; // The query		  
		$results['queries']['tours'] = $q;
		$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
	
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
				$q2 = "SELECT * 
						FROM tour_stops 
						WHERE attached_to_tour = '{$row['record_id']}' 
						ORDER BY stop_order LIMIT 1";
				$r2 = mysql_query($q2) or 
						die('unable to execute query <i>' . $q2 . '</i>: ' . mysql_error()); 
				$stop_row = mysql_fetch_array($r2, MYSQL_ASSOC);
				$row['latitude'] = $stop_row['latitude'];
				$row['longitude'] = $stop_row['longitude'];
				$row['latLng'] = $stop_row['latitude'] . ', ' . $stop_row['longitude'];
				$tours[] = $row;
		}
		
	endif; // /if show tours is yes
	
	// get events
	if($show_events == 'yes'):
		
		if($zoom <= 8){
			$latlng_query = "";
		}else{
			if($NELng < $SWLng){
				$longitude_query = "(longitude <= '$NELng') OR (longitude >= '$SWLng')";
			}else{
				$longitude_query = "(longitude >= '$NELng' AND longitude <= '$SWLng')";
			}
			$latlng_query = "(latitude <= '$NELat' AND latitude >= '$SWLat') AND
							  $longitude_query AND";
		}
	
		// set up query
		$q = "SELECT 
			    'event' AS point_type,
				concat_ws(', ',latitude,longitude) AS latLng, 
				latitude, 
				longitude, 
				title as name_title, 
				'Today' as category, 
				record_id, 
				'no' as has_podcast 
				FROM single_events WHERE 
				live = '1'
				AND 
				$latlng_query
				latitude != '' AND 
				longitude != '' AND
				latitude != '0' AND 
				longitude != '0'
				";		 
		$results['queries']['events'] = $q;			 		 
		$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
		$events = array();
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
			$events[] = $row;	
		}
	
	endif; // /if show events is yes	

	/* WORK RESULTS */
	
	$merged_set = array_merge($sites, $tours, $events);
	
	if($zoom <= 16){
		$merged_set = clusterResults($merged_set, $zoom);
	}else{
		$merged_set = unclusteredResults($merged_set);	
	}
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['total_time'] = $total_time;
	//$results['sites'] = $sites;
	//$results['districts'] = $districts;
	//$results['tours'] = $tours;
	//$results['events'] = $events;
	$results['merged_set'] = $merged_set;
	
	
	return $results;
	
}

function plotMainMapNEW($zoom,$largest_distance,$NELat,$NELng,$SWLat,$SWLng,$categories = array()){
	
	/* QUERY AND RESULTS */
	
	// get the start time
	$starttime = get_time_start();
	
	foreach($categories as $key => $category){
		$categories[$key] = "'$category'";		
	}
	$categories = join(", " ,$categories); 
	
	if($NELng < $SWLng){
		$longitude_query = "(longitude <= $NELng) OR (longitude >= $SWLng)";
	}else{
		$longitude_query = "(longitude <= $NELng AND longitude >= $SWLng)";
	}

	// set up query
	$q = "SELECT 
				 'site' AS point_type,
				 concat_ws(', ',latitude,longitude) 
				 AS latLng, latitude, longitude, name_title, category, record_id, has_podcast 
				 FROM item WHERE 
				 live = '1' AND
				 category IN ($categories) 
				 AND 
				 (latitude <= $NELat AND latitude >= $SWLat) AND
				 $longitude_query AND
				 latitude != '' AND 
				 longitude != '' AND 
				 latitude != '0' AND 
				 longitude != '0'
				 ";		 
				 		 
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
	$results = array();
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
		$results[] = $row;	
	}
	
	$returned_count = count($results);
	
	/* WORK RESULTS */
	
	if($zoom <= 16){
		$results = clusterResults($results, $zoom);
	}else{
		$results = unclusteredResults($results);	
	}
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['total_time'] = $total_time;
	$results['returned_count'] = $returned_count;
	$results['query'] = $q;
	
	return $results;
	
}

function unclusteredResults($results)
{
	$final_results = array();
	foreach($results as $key => $result): // for each of the clusters
		$final_results[] = array('type' => 'single',
								 'point_type' => $result['point_type'],
								 'has_podcast' => $result['has_podcast'],
								 'record_id' => $result['record_id'],
								 'name_title' => $result['name_title'],
								 'category' => $result['category'],
								 'coordinates' => $result['latLng']);
	endforeach; // endforeach results
	return $final_results;
}

function clusterResults($markers,$zoom)
{
	$pixel_distance = 25;
	
	$clusters = array();
    // Loop until all markers have been compared.
    while (count($markers)) {
        $marker  = array_pop($markers);
        $cluster = array();
        // Compare against all markers which are left.
        foreach ($markers as $key => $target) {
            $pixels = pixelDistance($marker['latLng'],
                                    $target['latLng'],
                                    $zoom);
            // If two markers are closer than given distance remove 
            // target marker from array and add it to cluster. 
            if ($pixel_distance > $pixels) {
                unset($markers[$key]);
                $cluster[] = $target;
            }
        }

        // If a marker has been added to cluster, add also the one 
        // we were comparing to and remove the original from array.
        if (count($cluster) > 0) {
			$cluster[] = $marker;
            $clusters[] = $cluster;
        } else {
            $marker = array($marker);
			$clusters[] = $marker;
        }
    }
	
	// we now have an array, clusters, where each index contains another array
	// which contains the info of our original results.
	// now we pricess the information for each cluster, making the distinctions from
	// clusters which are made of a single point and those made of multiple points.
	
	$final_clusters = array();
	foreach($clusters as $key => $cluster): // for each of the clusters
		
		$clusters[$key]['points'] = array();
		foreach($cluster as $site){
			if(!empty($site['latLng']) && $site['latLng'] != ''){
				$clusters[$key]['points'][] = $site['latLng'];
			}
		}
		
		$site_count = count($cluster);
		
		if($site_count == 1){ // if this cluster contains a single site
			
			$final_clusters[] = array('type' => 'single',
									  'point_type' => $cluster[0]['point_type'],
									  'has_podcast' => $cluster[0]['has_podcast'],
									  'record_id' => $cluster[0]['record_id'],
									  'name_title' => $cluster[0]['name_title'],
									  'category' => $cluster[0]['category'],
									  'coordinates' => $cluster[0]['latLng']);
			
		}elseif($site_count > 1){
		
			// determine if the grouping contains a podcast
			$has_podcast = 0;
			foreach($cluster as $ckey => $cvalue){
				if(empty($cvalue['has_podcast']) || $cvalue['has_podcast'] == 0){
					// still no podcast, do nothing
				}elseif($cvalue['has_podcast'] == 1){
					$has_podcast = 1;
				}
			}
		    
			// get all of the hull points for the cluster, and determine the center of these points
			$hull = new ConvexHull($clusters[$key]['points']);
			$hull_points = $hull->getHullPoints();
		
			$hull_center = $clusters[$key]['points'][0];
			$hci = 0;
			foreach($hull_points as $key => $hull_point){
				$hull_center = getTrueCenter($hull_point,$hull_center,$hci);
				$hci++;
			}

			$radius = (1800000 / pow(2,$zoom));	

			$final_clusters[] = array('type' => 'group',
									  'point_type' => 'grouped',
									  'has_podcast' => $has_podcast,
								      'represents' => $site_count,
									  'group_id' => $key,
								      'center' => $hull_center,
								      'point_count' => $site_count,
								      'hull_points' => $hull_points,
								      'radius' => intval($radius));
		
		}
		
	endforeach; // endforeach clusters
	
	return $final_clusters;
		
}

function plotMainMap($zoom,$largest_distance,$NELat,$NELng,$SWLat,$SWLng){
	
	$starttime = get_time_start(); // get the start time

	$q = "SELECT concat_ws(', ',latitude,longitude) 
				 AS latLng, latitude, longitude, name_title, category, record_id 
				 FROM item WHERE 
				 live = '1' AND
				 latitude <= $NELat AND
				 longitude <= $NELng AND
				 latitude >= $SWLat AND
				 longitude >= $SWLng AND
				 latitude != '' AND 
				 longitude != '' AND 
				 latitude != '0' AND 
				 longitude != '0'"; // The query		 
				 
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results

	$centers_of_gravity = array();
	$new_center = TRUE;
	$centers_distance = $largest_distance / pow(2,$zoom); // The centers distance is based on the zoom

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

function plotSearchMap($zoom,$largest_distance,$NELat,$NELng,$SWLat,$SWLng,$categories,$search_param){
	
	$centers_of_gravity = array();
	$new_center = TRUE;
	
	$starttime = get_time_start(); // get the start time to display query execution time

	$centers_distance = $largest_distance / pow(2,$zoom); // The centers distance is based on the zoom
	
	$category_query_array = array();
	
	foreach($categories as $category){
		$category_query_array[] = "category = '$category'";		
	}
	
	$category_query = join(" OR\n" ,$category_query_array);  

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
						   item.name_title LIKE '%$param%' OR 
						   item.city LIKE '%$param%' OR 
						   item.state LIKE '%$param%' OR 
						   item.category LIKE '%$param%' OR 
						   item.type LIKE '%$param%' OR 
						   item.on_lists LIKE '%$param%' OR
						   tags.tag LIKE '%$param%' OR
						   designations.designation LIKE '%$param%'
						   $year_query)";
	}
	
	$query_string = join("\nAND\n",$query_string);

		$q = "SELECT DISTINCT item.*, concat_ws(', ',latitude,longitude) 
				 AS latLng FROM item
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
			WHERE
			($category_query) AND 
				(item.live = '1')
			AND ( $query_string )";
				 
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results

	$i = 0;
	
	$count = 0;
	
	if($zoom >= 16){ // if zoom is past a certain point, then don't bother clustering.
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$row['number_at_center'] = 1;
			$results['items'][] = $row;
			$count = $count + 1;
		}
		
	}else{ // but if you want clustering, then....
	
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ // go through all the rows, and make the clusters.
			$count = $count + 1;
			
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
	
	$results['total_returned'] = $count;
	$results['total_time'] = $total_time;
	$results['query'] = $q;
	
	return $results;
	
}

function plotSearchMapNEW($zoom,$largest_distance,$NELat,$NELng,$SWLat,$SWLng,$categories,$search_param)
{
	/* QUERY AND RESULTS */
	
	// get the start time
	$starttime = get_time_start();
	
	foreach($categories as $key => $category){
		$categories[$key] = "'$category'";		
	}
	$categories = join(", " ,$categories); 
	
	if($NELng < $SWLng){
		$longitude_query = "(longitude <= $NELng) OR (longitude >= $SWLng)";
	}else{
		$longitude_query = "(longitude <= $NELng AND longitude >= $SWLng)";
	}
	
	$category_query_array = array();
	/*
	foreach($categories as $category){
		$category_query_array[] = "category = '$category'";		
	}
	*/
	$category_query = join(" OR\n" ,$category_query_array);  
	
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
						   item.name_title LIKE '%$param%' OR 
						   item.city LIKE '%$param%' OR 
						   item.state LIKE '%$param%' OR 
						   item.category LIKE '%$param%' OR 
						   item.type LIKE '%$param%' OR 
						   item.on_lists LIKE '%$param%' OR
						   designations.designation LIKE '%$param%'
						   $year_query)";
	}
	
	$query_string = join("\nAND\n",$query_string);

	$q = "SELECT DISTINCT 
			'site' AS point_type, 
			item.*, 
			concat_ws(', ',latitude,longitude) AS latLng
			FROM item
			LEFT OUTER JOIN item_people
				ON  item.record_id = item_people.item_id
			LEFT OUTER JOIN people
				ON  people.record_id = item_people.people_id
			LEFT OUTER JOIN events
				ON item.record_id = events.attached_to
			LEFT OUTER JOIN designations
				ON item.record_id = designations.attached_to	
			WHERE
		
			(item.live = '1') AND
			( $query_string )"; 
	
	if($search_param == '' || $search_param == 'undefined'){
		$q = "SELECT DISTINCT 
			'site' AS point_type, 
			item.*, 
			concat_ws(', ',latitude,longitude) AS latLng
			FROM item
			LEFT OUTER JOIN item_people
				ON  item.record_id = item_people.item_id
			LEFT OUTER JOIN people
				ON  people.record_id = item_people.people_id
			LEFT OUTER JOIN events
				ON item.record_id = events.attached_to
			LEFT OUTER JOIN designations
				ON item.record_id = designations.attached_to	
			WHERE
			(item.live = '1')";
	}else{
		// use the original query	
	}
				 		 
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results
	$results = array();
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
		$results[] = $row;	
	}
	
	$returned_count = count($results);
	
	/* WORK RESULTS */
	
	if($zoom <= 16){
		$results = clusterResults($results, $zoom);
	}else{
		$results = unclusteredResults($results);	
	}
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['returned_count'] = $returned_count;
	$results['total_time'] = $total_time;
	$results['query'] = $q;
	
	return $results;	
	
	
}

function plotPodcastMap($zoom,$largest_distance){
	
	$centers_of_gravity = array();
	$new_center = TRUE;
	
	$starttime = get_time_start(); // get the start time to display query execution time

	$centers_distance = $largest_distance / pow(2,$zoom); // The centers distance is based on the zoom

	$q = "SELECT * FROM podcasts WHERE live = '1'"; // The query		  
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results

	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$q2 = "SELECT 
			        podcasts_id, 
					item_id,
					item.record_id as item_record_id,
					item.name_title,
					item.latitude,
					item.longitude
					FROM item_podcast
					LEFT OUTER JOIN item ON
						item_podcast.item_id = item.record_id
					WHERE item_podcast.podcasts_id = '{$row['record_id']}'";
	  		$r2 = mysql_query($q2) or die('unable to execute query <i>' . $q2 . '</i>: ' . mysql_error()); 
			
	  		$item_row = mysql_fetch_array($r2, MYSQL_ASSOC);
			
			if($item_row['latitude'] != '' && $item_row['longitude'] != ''){
				$row['title'] = $item_row['name_title'];
				$row['latitude'] = $item_row['latitude'];
				$row['longitude'] = $item_row['longitude'];
				$row['latLng'] = $item_row['latitude'] . ', ' . $item_row['longitude'];
				$rows[] = $row;
			}
	}

	$i = 0;
	
	if($zoom >= 16){ // if zoom is past a certain point, then don't bother clustering.
		
		foreach($rows as $row){ 
			$row['number_at_center'] = 1;
			$results['items'][] = $row;
		}
		
	}else{ // but if you want clustering, then....
	
		foreach($rows as $row){ // go through all the rows, and make the clusters.
			
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
				$point_list[count($centers_of_gravity)][] = array('latLng' => $row['latLng'], 'record_id' => $row['record_id'], 'title' => $row['title'], 'map_name' => 'podcast');
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
											'title' => $point_list[$key][0]['title'],
											'map_name' => $point_list[$key][0]['map_name'],
											'latitude' => $latitude, 
											'longitude' => $longitude, 
											'number_at_center' => $number_at_center,
											'all_points' => $point_list[$key]);
			}else{
				$results['items'][$key] = array('record_id' => $cgi,
											'latitude' => $latitude, 
											'longitude' => $longitude, 
											'number_at_center' => $number_at_center,
											'all_points' => $point_list[$key]);
				
			}
				
			$cgi = 1 + $cgi;
		}
	
	}
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['total_time'] = $total_time;
	$results['query'] = $q;
	
	return $results;

}


function plotToursMap($zoom,$largest_distance){
	
	$centers_of_gravity = array();
	$new_center = TRUE;
	
	$starttime = get_time_start(); // get the start time to display query execution time

	$centers_distance = $largest_distance / pow(2,$zoom); // The centers distance is based on the zoom

	$q = "SELECT * FROM tours WHERE live = '1'"; // The query		  
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results

	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$q2 = "SELECT * 
					FROM tour_stops 
					WHERE attached_to_tour = '{$row['record_id']}' 
					ORDER BY stop_order LIMIT 1";
	  		$r2 = mysql_query($q2) or 
					die('unable to execute query <i>' . $q2 . '</i>: ' . mysql_error()); 
	  		$stop_row = mysql_fetch_array($r2, MYSQL_ASSOC);
			$row['latitude'] = $stop_row['latitude'];
			$row['longitude'] = $stop_row['longitude'];
			$row['latLng'] = $stop_row['latitude'] . ', ' . $stop_row['longitude'];
			$rows[] = $row;
	}

	$i = 0;
	
	if($zoom >= 16){ // if zoom is past a certain point, then don't bother clustering.
		
		foreach($rows as $row){ 
			$row['number_at_center'] = 1;
			$results['items'][] = $row;
		}
		
	}else{ // but if you want clustering, then....
	
		foreach($rows as $row){ // go through all the rows, and make the clusters.
			
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
				$point_list[count($centers_of_gravity)][] = array('latLng' => $row['latLng'], 'record_id' => $row['record_id'], 'tour_name' => $row['tour_name'], 'map_name' => $row['map_name']);
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
											'tour_name' => $point_list[$key][0]['tour_name'],
											'map_name' => $point_list[$key][0]['map_name'],
											'latitude' => $latitude, 
											'longitude' => $longitude, 
											'number_at_center' => $number_at_center,
											'all_points' => $point_list[$key]);
			}else{
				$results['items'][$key] = array('record_id' => $cgi,
											'latitude' => $latitude, 
											'longitude' => $longitude, 
											'number_at_center' => $number_at_center,
											'all_points' => $point_list[$key]);
				
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
		break;
		
		case 'm':
		$distance = $distance * 1.609344 * 1000;
		break;
	
	}
		
	return (round($distance,2));
	
}

function centroid($polygon,$n) {
   
    $new_polygon = array();
	$i = 0;
	$n = 2 * $n;
	while($i < $n){
		$index = floor(($i + 1.0)/2);
		if($index > (($n / 2) - 1)){
			$new_polygon[$i] = $polygon[0];
		}else{
			$new_polygon[$i] = $polygon[$index];
		}
		$i++;
	}
   	
	$polygon = $new_polygon;
	$n = $n/2;
	 
	//echo '<pre>',print_r($polygon),'</pre>'; 
	  
    $a = area($polygon,$n);
 
    $cx = 0;
    $cy = 0;
   
    $polygon=array_chunk($polygon,2);
   
    for ($i=0;$i<$n;$i++) {
		
		if(($i + 1) >= $n){
			$j = 0;
		}else{
			$j = ($i + 1);
		}
		
        $cx = $cx + ($polygon[$i][0] + $polygon[$j][0]) * ( ($polygon[$i][0]*$polygon[$j][1]) - ($polygon[$j][0]*$polygon[$i][1]) );
        $cy = $cy + ($polygon[$i][1] + $polygon[$j][1]) * ( ($polygon[$i][0]*$polygon[$j][1]) - ($polygon[$j][0]*$polygon[$i][1]) );
		//echo '<hr />',$cx,', ',$cy,'<hr />';
    }
 
 	if($a == 0){
		return array(0,0); //getDistance($point1,$point2,$unit = 'Mi')
	}else{
		return(array( (1/(6*$a))*$cx,(1/(6*$a))*$cy));
	}
   
}

function area($polygon,$n) {
	
	//echo '<pre>',print_r($new_polygon),'</pre>';
	
    // based off my function here:
    // viewtopic.php?t=41715&highlight=vertex+ordering
    //echo '<hr />';
	//echo '<pre>',print_r($polygon),'</pre>';
	$polygon=array_chunk($polygon,2);
	//echo '<hr />CHUNK ' . $n . '<hr />';
    //echo '<pre>',print_r($polygon),'</pre>';
    //echo '<hr />';	
	$area = 0;
    for ($i=0; $i < $n; $i++) {
        if(($i + 1) >= $n){
			$j = 0;
		}else{
			$j = ($i + 1);
		}
        $area = $area + ($polygon[$i][0] * $polygon[$j][1]);
        $area = $area - ($polygon[$i][1] * $polygon[$j][0]);
		//echo '<hr />';
		//echo $area;	
		//echo '<hr />';	
    }
    $area = $area / 2;
    
	return(abs($area));
}

function getTrueCenter($point,$center,$cweight){
		
	$point = explode(',',$point);
		
	$plat = $point[0];
	$plng = $point[1];
	
	$center = explode(',',$center);
		
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
    $selector .= 'categories' . "\n";
    $selector .= '</div>' . "\n";
	$selector .= '</div>' . "\n";

	$selector .= '<div id="s-body">' . "\n"; 
	foreach($item_categories as $category){
		 $selector .= '<div class="selector" name="' . str_replace(' ','-',$category) . '" ';
		 if(in_array(str_replace('-',' ',$category),$_SESSION['map']['search_array'])){
		 	$selector .= 'search="yes"';
		 }else{
		 	$selector .= 'search="no"';
		 }
		 $selector .= '>' . "\n";
		 $selector .= '<div class="' . str_replace(' ','-',$category) . '" style="float:right; width:16px; height:16px;">' . "\n";
		 $selector .= '<img src="ui/images/pinhead-knockout.png" />' . "\n";
		 $selector .= '</div>' . "\n";
		 $selector .= '<div id="selector-text" class="small">' . "\n";
		 $selector .= '<img height="10" src="ui/images/checked.png" /> ' . "\n";
		 $selector .= strtolower($category) . "\n";
		 $selector .= '</div>' . "\n";
		 $selector .= '</div>' . "\n";
	}
	$selector .= '</div>' . "\n";
	
	return $selector;

}

/* PLOT POLYGONS */

function plotAllPolygons(){
	
	$starttime = get_time_start(); // get the start time to display query execution time

	$q = "SELECT * FROM item 
				WHERE 
				coord_string != '' AND 
				live = '1'"; // The query		 
				 
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results

	$i = 0;
	
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		$results['polygons'][] = $row;
	} // /while mysql_fetch_array
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['total_time'] = $total_time;
	$results['query'] = $q;
	
	return $results;
	
}

function plotPolygons($zoom,$largest_distance,$NELat,$NELng,$SWLat,$SWLng,$categories){
	
	$centers_of_gravity = array();
	$new_center = TRUE;
	
	$starttime = get_time_start(); // get the start time to display query execution time

	$centers_distance = $largest_distance / pow(2,$zoom); // The centers distance is based on the zoom
	
	$category_query_array = array();
	
	foreach($categories as $category){
		$category_query_array[] = "category = '$category'";		
	}
	
	$category_query = join(" OR\n" ,$category_query_array);  

	$q = "SELECT * FROM polygons WHERE live = '1'"; // The query		 
				 
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results

	$i = 0;
	
	if($zoom >= 1){ // if zoom is greater than a certain point, draw the polygons.
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$results['polygons'][] = $row;
		} // /while mysql_fetch_array
	
	} // /if zoom
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['total_time'] = $total_time;
	$results['query'] = $q;
	
	return $results;
	
}

function plotPolygonsNEW($zoom,$NELat,$NELng,$SWLat,$SWLng,$categories){
	
	$largest_distance = 10000;
	
	$centers_of_gravity = array();
	$new_center = TRUE;
	
	$starttime = get_time_start(); // get the start time to display query execution time

	$centers_distance = $largest_distance / pow(2,$zoom); // The centers distance is based on the zoom
	
	$categories = explode('_',$categories);		
	$categories = join("', '" ,str_replace('-',' ',$categories));  

	$q = "SELECT * FROM polygons WHERE type IN ('$categories') AND live = '1'"; // The query		 
				 
	$r = mysql_query($q) OR die("unable find <i>$q</i>: " . mysql_error()); // get the query results

	$i = 0;
	
	if($zoom >= 1){ // if zoom is greater than a certain point, draw the polygons.
		
		while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
			$results['polygons'][] = $row;
		} // /while mysql_fetch_array
	
	} // /if zoom
	
	$total_time = get_time_end($starttime,'plotMainMap');
	
	$results['total_time'] = $total_time;
	$results['query'] = $q;
	
	return $results;
	
}

?>