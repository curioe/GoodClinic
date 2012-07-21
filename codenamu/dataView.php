<?php
$url = 'data.txt';
$myFile = file_get_contents($url);
$myData = utf8_encode($myFile);
$arr = json_decode($myData);
?>

<!DOCTYPE html>
<html dir="ltr" lang="ko-KR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, width=device-width" />

    <title>Codename-team#1 DataView</title>
  </head>
  <body>
	<div id="content">

<?php
$i=0;
foreach ($arr as $v1) {
	echo "\n<h1> id: ";
	echo $i++;
	echo "</h1>\n";

	foreach ($v1 as $key => $value) {	
		echo "Key: $key;  Value: $value<br/>\n";
	}

}

?>
	</div>
  </body>
</html>