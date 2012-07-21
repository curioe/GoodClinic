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
// 이미 저장된 데이터의 인덱스 시작열:
$i=27;

foreach ($arr as $v1) {
	echo "\n\n<h1> id: ";
	echo $i;
	echo "</h1>\n";
	
    foreach ($v1 as $key => $value) {	

	// 항생제 처방률
		if( $key == "항생제 처방률") {
			//echo "Key: $key; Value: $value<br/>\n";
			$query = "INSERT INTO form_response (incident_id, form_field_id, form_response)  VALUES ($i,1,'$value'); <br/>\n";
			echo $query;
	    	}

		// 주사제 처방률
		if( $key == "주사제 처방률") {
	    		//echo "Key: $key; Value: $value<br/>\n";
			$query = "INSERT INTO form_response (incident_id, form_field_id, form_response)  VALUES ($i,2,'$value'); <br/>\n";
			echo $query;
	    	}

		// 종합 결과
		if( $key == "종합결과") {
	    		//echo "Key: $key; Value: $value<br/>\n";
			$query = "INSERT INTO form_response (incident_id, form_field_id, form_response)  VALUES ($i,3,'$value'); <br/>\n";
			echo $query;
	    	}

		// PHONE
		if( $key == "PHONE") {
	    		//echo "Key: $key; Value: $value<br/>\n";
			$query = "INSERT INTO form_response (incident_id, form_field_id, form_response)  VALUES ($i,4,'$value'); <br/>\n";
			echo $query;
	    	}

		// NAME 확인용
		if( $key == "NAME") {
	    		//echo "Key: $key; Value: $value<br/>\n";
	    	}

		// HOMEPAGE
		if ($key == 'HOMEPAGE') {
			if($value) {
				$query = "INSERT INTO media (location_id, incident_id, media_type, media_link) 
			 	VALUES ((SELECT location_id FROM incident WHERE id=$i), $i,4,'$value'); <br/>\n";
				echo $query;
			}
		}
	
	} // foreach $v1

	$i++;

} // foreach $arr

?>
	</div>
  </body>
</html>
