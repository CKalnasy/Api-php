<?php

$json = @file_get_contents('php://input');
echo $json;

echo json_encode($_FILES);


?>
