<?php
$url = 'data.txt';
$myFile = file_get_contents($url);
$myData = utf8_encode($myFile);
$arr = json_decode($myData);

$i=27;
foreach ($arr as $v1) {

$footer="** 1등급에 가까울수록 항생제 처방을 적게하는 의료기관입니다.<br/>";
$str="";

	foreach ($v1 as $key => $value) {	
		
		if ($key == "항생제 처방률" || $key == "주사제 처방률" || $key == "종합결과" || $key == "PHONE") {

			if ($key == "PHONE") {
				$str = $str."<strong>전화번호</strong>: $value<br/>";
			} else {
				$str = $str."<strong>$key</strong>: $value<br/>";
			}
		}
	} // end-foreach
	//echo "$str <br/><br/>";
	echo "UPDATE incident SET incident_description = '$str $footer' WHERE id = $i;<br/>";
	
	$i++;

}

?>