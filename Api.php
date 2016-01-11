<?php

require_once 'vendor/autoload.php';
use Zumba\Util\JsonSerializer;

/**
 *
 */
class Api {
  private $classes;
  private $serializer;

  /**
   *
   */
  public function __construct() {
    $jsonFile = 'api.json';
    if (file_exists($jsonFile)) {
      $string = file_get_contents($jsonFile);
      $json = json_decode($string, true);
      $this->classes = $json['Classes'];
    }
    $this->serializer = new JsonSerializer();
  }

  /**
   *
   */
  public function handleRequest() {
    $this->callFunction(self::getParams());
  }

  private static function getParams() {
    $ret = [];
    $json = json_decode(@file_get_contents('php://input'), true);
    foreach ($json['params'] as $paramName => $paramValue) {
      $_POST[$paramName] = $paramValue;
    }
    foreach ($json['args'] as $paramName => $paramValue) {
      $ret['args'][$paramName] = $paramValue;
    }

    $ret['className'] = $json['className'];
    $ret['functionName'] = $json['functionName'];
    return $ret;
  }

  private function callFunction($params) {
    self::includeClasses();
    $args = [];
    foreach ($params['args'] as $className => $json) {
      array_push($args, $this->serializer->serialize($json));
    }
    call_user_func_array([$params['className'], $params['functionName']], $args);
  }

  private static function includeClasses() {
    foreach ($classes as $className => $classInfo) {
      include_once $classInfo['location'];
    }
  }
}

?>
