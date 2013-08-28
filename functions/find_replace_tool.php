<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Image Scrape</title>
</head>

<body>

<?php

if(empty($_POST['text'])) {

$_POST['text'] = '';

}


if(empty($_POST['find'])) {

$_POST['find'] = '';

}

if(empty($_POST['replace'])) {

$_POST['replace'] = '';

}

$_POST['text'] = stripslashes(str_replace($_POST['find'],$_POST['replace'],$_POST['text']));

?>

<form action="find_replace_tool.php" method="post">
<textarea rows="30" style="width:100%;" name="text"><?php echo $_POST['text']; ?></textarea><br /><br />
FIND: <textarea rows="5" style="width:100%;" name="find"></textarea><br />
REPLACE: <textarea rows="5" style="width:100%;" name="replace"></textarea><br /><br />
<input type="submit" name="submit" />
</form>

</body>
</html>

