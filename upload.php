<?php


$ct = file_get_contents('hospital.txt');

var_dump(json_decode(utf8_encode($ct)));

echo json_last_error();

?>