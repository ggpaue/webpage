<?php

function cleanInput($input,$allowable_tags = NULL){
    $input = strip_tags($input,$allowable_tags);
	return $input;
} 

?>