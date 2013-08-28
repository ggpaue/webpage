<?php require('functions.php'); ?>
<?php require('data_health_functions.php'); ?>
<?php set_time_limit(600); ?>
<?php ini_set ('session.gc_maxlifetime',3600) ?>

<body>

<?php

$q = "SELECT * FROM item";
$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());

while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
	clean_array($row);
	$rows[] = $row;
	unset($row);
}

?>


<pre><?=print_r($functions['user'])?></pre>

</body>
</html>