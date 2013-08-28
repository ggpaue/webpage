<?php require('../../../secure/connect_admin.php'); ?>
<?php require('functions.php'); ?>
<?php require('data_health_functions.php'); ?>
<?php set_time_limit(600); ?>
<?php ini_set ('session.gc_maxlifetime',3600) ?>

<body>

<?php

$q = "SELECT * FROM item";
$r = @mysql_query($q) OR die('unable to execute query <i>' . $q . '</i>: ' . mysql_error());

while($row = mysql_fetch_array($r, MYSQL_ASSOC)){ 
	if($row['arts_for_transit'] == 1){
		echo $row['name_title'],'<br />';
		$qu1 = "UPDATE item SET on_lists = 'MTA Arts For Transit' WHERE record_id = '{$row['record_id']}'";
		$ru1 = @mysql_query($qu1) OR die('unable to execute query <i>' . $qu1 . '</i>: ' . mysql_error());
		$qu2 = "INSERT INTO permissions (table_id, record_id, user_id, created_timestamp) VALUES ('item', '{$row['record_id']}', 'arts_for_transit', NOW())";
		$ru2 = @mysql_query($qu2) OR die('unable to execute query <i>' . $qu2 . '</i>: ' . mysql_error());
		
	}
}


?>

</body>
</html>