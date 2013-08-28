<?php 

/*
error_reporting(E_NONE);
ini_set('display_errors', '0');
*/

function train($file) {
	$contents = file_get_contents($file);
	// get all strings of word letters
	preg_match_all('/\w+/i', $contents, $matches);
	unset($contents);
	$dictionary = array();
	foreach($matches[0] as $word) {
			//$word = strtolower($word);
			$soundex_key = metaphone($word);
			if(!isset($dictionary[$soundex_key][$word])) {
				$dictionary[$soundex_key][$word] = 0;
			}
			$dictionary[$soundex_key][$word] += 1;
	}
	unset($matches);
	return $dictionary;
}





function correct($word, $dic) {
    
	if (array_key_exists($word, $dic)) {
        return $word;
    }  
	
	//echo '<!--DICTIONARY ARRAY -->';
	//echo '<!--',print_r($dic),'-->';
 
    $search_result = $dic[metaphone($word)];
 	
    foreach ($search_result as $key => &$res) {
        
		$dist = levenshtein($key,$word);
        // consider just distance equals to 1 (the best) or 2
        if ($dist <= 4 && $dist != 0 ) {
            $res = $res / $dist;
        }
        // discard all the other candidates that have distances other than 1 and 2
        // from the original word
        else {
            unset($search_result[$key]);
        }
    }

    // reverse sorting of the words by frequence
    arsort($search_result);

 
    // return the all of the possible replacements
	return $search_result;
	
}

?>