<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/ckalnasy/Api.php';
use ckalnasy\Api;

$api = new Api();
$api->handleRequest();
