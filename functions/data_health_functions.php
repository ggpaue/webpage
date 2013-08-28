<?php // DATA HEALTH FUNCTIONS

function getAllFromTable($table){
	
	$rows = array();
	
	$q = "SELECT * FROM $table";
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());

	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
		clean_array($row);
		$rows[] = $row;
		unset($row);
	}
	
	return $rows;
	
}

function prevNextRecordLink($page,$table,$current_record_id){
	
	$link = '';
	
	$q = "SELECT record_id FROM $table WHERE record_id < $current_record_id ORDER BY record_id DESC LIMIT 1";
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
	if($count > 0){
		$link .= '<a href="' . $page . '?table=' . $table . '&record_id=' . $row['record_id'] . '">< PREV</a>';
	}
	
	$link .= ' &bull; ';
	
	$q = "SELECT record_id FROM $table WHERE record_id > $current_record_id ORDER BY record_id ASC LIMIT 1";
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$count = mysql_num_rows($r);
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
	if($count > 0){
		$link .= '<a href="' . $page . '?table=' . $table . '&record_id=' . $row['record_id'] . '">NEXT ></a>';
	}
	
	return $link;
	
}

function putAllOldImagesIntoDatabase(){
	
	$q = "TRUNCATE TABLE all_old_images";
	echo $q,'<hr />';
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());

	// folders with images
	
	$images_folder = '../../media/images';
	$images_man_folder = '../../media/images/manhattanartnow';
	$images_dn_folder = '../../media/images/downtownnow';
	$images_hn_folder = '../../media/images/harlemnow';
	$orig_hn_folder = '../../HarlemNOW/images';
	$orig_man_folder = '../../ManhattanArtNOW/images';
	
	$dn_folders = array($images_folder,$images_dn_folder);
	$man_folders = array($images_folder,$images_man_folder,$orig_man_folder);
	$hn_folders = array($images_folder,$images_hn_folder,$orig_hn_folder);
	$all_folders = array($images_folder,$images_man_folder,$images_dn_folder,$images_hn_folder,$orig_hn_folder,$orig_man_folder);
	
	foreach($all_folders as $folder){
		echo $folder,'<br />';
		scanDirectoriesForAndInsertIntoOldImages($folder);
	}
	
	echo '<hr />script complete<hr />';

}

function collectPossibleBetterImages($record_id){
	
	// get orig worknum

	$image = getRecord('new_images',$record_id);
	$item = getRecord('item',$image['attached_to']);
	
	$orig_map = ereg_replace('[^A-Za-z]+','',$item['orig_worknum']);
	$orig_worknum = ereg_replace('[^0-9]+','',$item['orig_worknum']);
	
	$q = "SELECT * FROM all_old_images WHERE fullpath LIKE '%$orig_worknum%'";
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	while($row = mysql_fetch_array($r, MYSQL_ASSOC)){
		if(ereg_replace('[^0-9]+','',basename($row['fullpath'])) == $orig_worknum){
			$final_dirty_array[] = $row['fullpath'];
		}
	}
	
	$final_clean_array = array_unique($final_dirty_array);
	
	foreach($final_clean_array as $file){
		$imagesize = getimagesize($file);
		$link['fullpath'] = $file;
		$link['imagesize'] = '<i>(' . $imagesize[0] . ' x ' . $imagesize[1] . ')</i>';
		$links[] = $link;
		unset($imagesize);
	}
	
	return $links;
	
}

function lastTimeChecked($review_type,$table,$record_id){
		
	$q = "SELECT datetime_checked FROM reviewed WHERE review_type = '$review_type' AND  checked_table = '$table' AND checked_id = '$record_id' ORDER BY datetime_checked DESC";
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	$row = mysql_fetch_array($r, MYSQL_ASSOC);
	
	return $row['datetime_checked'];
		
}

function insertRecordChecked($review_type,$checked_table,$checked_id){

	$datetime_checked = date("Y-m-d H:i:s",time());

	$q = "INSERT INTO reviewed SET (review_type,checked_table,checked_id,datetime_checked) VALUES ('$review_type','$checked_table','$checked_id','$datetime_checked')";
	$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
	
}

function createEditLink($table,$record_id,$class = 'standard'){

	$link = '<a ';
	if($class != 'standard'){
		$link .= 'class="' . $class . '" ';
	}
	$link .= 'target="_blank" href="http://www.culturenow.org/admin_and_tools/databases/edit.php?table=' . $table . '&record_id=' . $record_id . '">' . getItemNameTitle($record_id) . '</a>';

	return $link;

}

function createImageEditLink($record_id,$class = 'standard'){

	$link = '<a ';
	if($class != 'standard'){
		$link .= 'class="' . $class . '" ';
	}
	$link .= 'target="_blank" href="http://www.culturenow.org/admin_and_tools/databases/edit_image.php?id=' . $record_id . '">' . $record_id . '</a>';

	return $link;

}

function createImageSuggestionLink($record_id,$class = 'standard'){

	$link = '<a ';
	if($class != 'standard'){
		$link .= 'class="' . $class . '" ';
	}
	$link .= ' href="http://www.culturenow.org/admin_and_tools/data_health/image_size_check.php?table=item&record_id=' . $record_id . '&find_similar=TRUE">search for replacements</a>';

	return $link;

}

function duplicateCheck($table,$record_id){
	
	$red_dupes = array();
	$yellow_dupes = array();
	$name_dupes = array();
	$add_dupes = array();

	$rows = getAllFromTable($table);
	
	$record = getRecord($table,$record_id);
		
	$lastChecked = lastTimeChecked('dupe',$table,$record_id);
	
	foreach($rows as $row){
		
		if($row['record_id'] != $record['record_id']){
		
			similar_text(strtoupper($record['name_title']), strtoupper($row['name_title']), $pct); 
			if (number_format($pct, 0) > 80){ 
				$name_dupes[] = $row['record_id'];
			}elseif(number_format($pct, 0) > 95){
				$red_dupes[] = $row['record_id'];
			}
			
			if($record['add_number'] . ' ' . $record['add_street'] != ' ' && $row['add_number'] . ' ' . $row['add_street'] != ' '){
			
				similar_text(strtoupper($record['add_number'] . ' ' . $record['add_street']), strtoupper($row['add_number'] . ' ' . $row['add_street']), $pct); 
				if (number_format($pct, 0) > 100){ 
					$add_dupes[] = $row['record_id'];
				}
				
			}
			
		}
			
	}
	
	foreach($name_dupes as $dupe_value){
	
		if(in_array($dupe_value,$add_dupes)){
			$red_dupes[] = $dupe_value;
		}
		
	}
	
	$orange_dupes = array_unique(array_merge($add_dupes,$name_dupes));
	
	$red_dupes = array_unique($red_dupes);
	
	$i = count($red_dupes) + count($orange_dupes);
	
	if($i == 0){
		$font_color = 'green';
	}else{
		$font_color = 'red';
	}
	
	$results['red_dupes'] = $red_dupes;
	$results['orange_dupes'] = $orange_dupes;
	
	$results['message'] = '<font color="' . $font_color . '">found <b>' . $i . '</b> possible duplicates. These are records from the ' . $table . ' table that are possible duplicates based on name and address.</font>';
	
	return $results;
	
}

function addressCheck($table){
	
	$rows = getAllFromTable($table);
	
	$i = 0;
	foreach($rows as $key => $row){
		
		if(($row['add_number'] == '' || $row['add_street'] == '') && ($row['x_street_1'] == '' || $row['x_street_2'] == '')){
			$i = $i + 1;
			$links[] = createEditLink($table,$row['record_id']);
		}elseif(($row['city'] == '' || $row['state'] == '') && $row['zip'] != ''){	
			$i = $i + 1;
			$links[] = createEditLink($table,$row['record_id']);
		}
			
	}
	
	if($i == 0){
		$font_color = 'green';
	}else{
		$font_color = 'red';
	}
	
	$results['message'] = '<font color="' . $font_color . '">found <b>' . $i . '</b> address errors. These are items with addresses that are inadequate for geocoding.</font>';
	$results['links'] = $links;
	
	return $results;
	
}

function imageCheck($table){
	
	$rows = getAllFromTable($table);
	
	$i = 0;
	foreach($rows as $key => $row){
		if(!file_exists('../../media/new_images/' . $row['record_id'] . '/original.jpg') && !file_exists('../../media/new_images/' . $row['record_id'] . '/web.jpg')){
			$i = $i + 1;
		}elseif(!file_exists('../../media/new_images/' . $row['record_id'] . '/thumb.jpg')){
			$i = $i + 1;
		}elseif(!file_exists('../../media/new_images/' . $row['record_id'] . '/mini.jpg')){
			$i = $i + 1;
		}
	}
	
	if($i == 0){
		$font_color = 'green';
	}else{
		$font_color = 'red';
	}
	
	$results = '<font color="' . $font_color . '">found <b>' . $i . '</b> image errors. These are image records that lack either an original, web, thumb, or mini image.</font>';
	
	return $results;
	
}

function imageSizeCheck($table,$record_id){
	
	$i = 0;
	$rows = getAllFromTable($table);
	$record = getRecord($table,$record_id);
	$images = getRelatedImages($table,$record_id);
	$lastChecked = lastTimeChecked('dupe',$table,$record_id);
	$image_count = count($images['record_ids']);
	
	$problems = array();
	$critical_problems = array();
	
	foreach($images['record_ids'] as $image){
	
		if(file_exists('../../media/new_images/' . $image . '/original.jpg')){
			$imagesize = getimagesize('../../media/new_images/' . $image . '/original.jpg');
			if($imagesize[0] < 420 && $imagesize[1] < 420){
				$problem['issue'] = 'original image is too small';
				$problem['fix'] = 'replace_original';
				$problem['importance'] = 'critical';
				$problem['id'] = $image;
				$problems[] = $problem;
			}
		}else{
			$problem['issue'] = 'original image does not exist';
			$problem['fix'] = 'replace_original';
			$problem['importance'] = 'non-critical';
			$problem['id'] = $image;
			$problems[] = $problem;
		}
		
		if(file_exists('../../media/new_images/' . $image . '/web.jpg')){
			$imagesize = getimagesize('../../media/new_images/' . $image . '/web.jpg');
			if($imagesize[0] < 420 && $imagesize[1] < 420){
				$problem['issue'] = 'web image is too small';
				$problem['fix'] = 'replace_web';
				$problem['importance'] = 'critical';
				$problem['id'] = $image;
				$problems[] = $problem;
			}
		}else{
			$problem['issue'] = 'web image does not exist';
			$problem['fix'] = 'replace_web';
			$problem['importance'] = 'critical';
			$problem['id'] = $image;
			$problems[] = $problem;
		}
		
		if(file_exists('../../media/new_images/' . $image . '/crop.jpg')){
			$imagesize = getimagesize('../../media/new_images/' . $image . '/crop.jpg');
			if($imagesize[0] != $imagesize[1]){
				$problem['issue'] = 'crop image is not the right size';
				$problem['fix'] = 'go_to_crop';
				$problem['importance'] = 'non-critical';
				$problem['id'] = $image;
				$problems[] = $problem;
			}
		}else{
			$problem['issue'] = 'crop image does not exist';
			$problem['fix'] = 'go_to_edit';
			$problem['importance'] = 'non-critical';
			$problem['id'] = $image;
			$problems[] = $problem;
		}
		
		if(file_exists('../../media/new_images/' . $image . '/thumb.jpg')){
			$imagesize = getimagesize('../../media/new_images/' . $image . '/thumb.jpg');
			if($imagesize[0] != 100 && $imagesize[1] != 100){
				$problem['issue'] = 'thumb image is not the right size';
				$problem['fix'] = 'go_to_edit';
				$problem['importance'] = 'critical';
				$problem['id'] = $image;
				$problems[] = $problem;
			}
		}else{
			$problem['issue'] = 'thumb image does not exist';
			$problem['fix'] = 'go_to_edit';
			$problem['importance'] = 'critical';
			$problem['id'] = $image;
			$problems[] = $problem;
		}
		
		if(file_exists('../../media/new_images/' . $image . '/mini.jpg')){
			$imagesize = getimagesize('../../media/new_images/' . $image . '/mini.jpg');
			if($imagesize[0] != 67 && $imagesize[1] != 67){
				$problem['issue'] = 'mini image is not the right size';
				$problem['fix'] = 'go_to_edit';
				$problem['importance'] = 'critical';
				$problem['id'] = $image;
				$problems[] = $problem;
			}
		}else{
			$problem['issue'] = 'mini image does not exist';
			$problem['fix'] = 'go_to_edit';
			$problem['importance'] = 'critical';
			$problem['id'] = $image;
			$problems[] = $problem;
		}
		
		unset($problem);
		unset($imagesize);
		
	}
	
	$critical_count = 0;
	$non_critical_count = 0;
	foreach($problems as $problem){
		if($problem['importance'] == 'critical'){
			$critical_count = $critical_count + 1;
		}elseif($problem['importance'] == 'non-critical'){
			$non_critical_count = $non_critical_count + 1;
		}
	}
	
	if($critical_count == 0){
		$font_color = 'green';
	}else{
		$font_color = 'red';
	}
	
	$results['critical_message'] = '<font color="' . $font_color . '">found <b>' . $critical_count . '</b> critial errors in <b>' . $image_count . '</b> images. These are display images that either don\'t exist, or are too small to properly display on the web.</font><br />';
	
	if($non_critical_count == 0){
		$font_color = 'green';
	}else{
		$font_color = 'orange';
	}
	
	$results['non-critical_message'] = '<font color="' . $font_color . '">found <b>' . $non_critical_count . '</b> non-critial errors in <b>' . $image_count . '</b> images. These are non-display images that either don\'t exist, or are too small.</font>';
	
	$results['problems'] = $problems;
	
	return $results;

}

function geolocationCheck($table){
	
	$rows = getAllFromTable($table);
	
	$i = 0;
	foreach($rows as $key => $row){
		$pointLocation = new pointLocation();
		$point = $row['latitude'] . ' ' . $row['longitude'];
		$polygon = array("40.508 -74.291", "40.659, -74.181", "40.721 -74.045", "40.9296 -73.915", "40.881 -73.754", "40.719 -73.689", "40.5675 -73.750", "40.480 -74.256");
		if($pointLocation->pointInPolygon($point, $polygon) == 'outside'){
			$links[] = createEditLink($table,$row['record_id']);
		$i = $i + 1;
		}
	}
	
	if($i == 0){
		$font_color = 'green';
	}else{
		$font_color = 'red';
	}
	
	$results['message'] = '<font color="' . $font_color . '">found <b>' . $i . '</b> geocoding errors. These are item records with coordinates outside of New York City, or with no coordinates at all.</font>';
	$results['links'] = $links;
	
	return $results;
	
}

/***************************************************
*
*	work classes
*
***************************************************/

class pointLocation {
    var $pointOnVertex = false; // Check if the point sits exactly on one of the vertices

    function pointLocation() {
    }
    
    
        function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;
        
        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = array(); 
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex); 
        }
        
        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }
        
        // Check if the point is inside the polygon or on the boundary
        $intersections = 0; 
        $vertices_count = count($vertices);
    
        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1]; 
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) { 
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x']; 
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++; 
                }
            } 
        } 
        // If the number of edges we passed through is even, then it's in the polygon. 
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }

    
    
    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    
    }
        
    
    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }
    
    
}

function scanDirectoriesForWorkNum($rootDir,$search_for,$allData=array()) {
	// set filenames invisible if you want
    $invisibleFileNames = array(".", "..", ".DS_Store");
    // run through content of root directory
    $dirContent = scandir($rootDir); 
	foreach($dirContent as $key => $content) {  
		// filter all files not accessible
        $path = $rootDir.'/'.$content;
		if(!in_array($content, $invisibleFileNames)) {
			// if content is file & readable, add to array
            if(is_file($path) && is_readable($path) && $search_for == ereg_replace('[^0-9]+','',$path)) {
				// save file name with path
				$allData[] = $path;
				//echo $path,' ---- ',$search_for,' ---- ',ereg_replace('[^0-9]+','',$path),'<br />';
			}	
			// if content is dir & readable, add to array
            if(is_dir($path) && is_readable($path)) {
				// save file name with path
                //$allData[] = $path;
			}	
			// if content is a directory and readable, add path and name
            if(is_dir($path) && is_readable($path)) {
				// recursive callback to open new directory
                $allData = scanDirectoriesForWorkNum($path,$search_for,$allData);
			}
		}
	}
	return $allData;
}  // END scanDirectories function

function scanDirectoriesForAndInsertIntoOldImages($rootDir,$allData=array()){
	
	// set filenames invisible if you want
    $invisibleFileNames = array(".", "..", ".DS_Store");
    // run through content of root directory
    $dirContent = scandir($rootDir); 
	foreach($dirContent as $key => $content) {  
		// filter all files not accessible
        $path = $rootDir.'/'.$content;
		if(!in_array($content, $invisibleFileNames)) {
			// if content is file & readable, add to array
            if(is_file($path) && is_readable($path)) {
				// save file name with path
				$allData[] = $path;
				$q = "INSERT INTO all_old_images (fullpath) VALUES ('$path')";
				$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());
			}	
			// if content is dir & readable, add to array
            if(is_dir($path) && is_readable($path)) {
				// save file name with path
                //$allData[] = $path;
			}	
			// if content is a directory and readable, add path and name
            if(is_dir($path) && is_readable($path)) {
				// recursive callback to open new directory
                $allData = scanDirectoriesForWorkNum($path,$search_for,$allData);
			}
		}
	}
	return $allData;
}  // END scanDirectories function

?>