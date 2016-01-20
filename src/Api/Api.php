<?php

require_once 'vendor/autoload.php';
use Zumba\Util\JsonSerializer;

namespace ckalnasy;

/**
 * This class handles http requests and deserializes the parameters for functions it should call
 */
class Api {
  private $classes;
  private $serializer;

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
   * Handle the request we've just been given. Deserialize the parameters and pass them as parameters to the static class function
   */
  public function handleRequest() {
    $this->callFunction(self::getParams());
  }

  private static function getParams() {
    $ret = [];
    $json = json_decode(@file_get_contents('php://input'), true);
    echo json_encode($json);
    foreach ($json['params'] as $paramName => $paramValue) {
      $_POST[$paramName] = $paramValue;
    }
    foreach ($json['args'] as $paramName => $paramValue) {
      $ret['args'][$paramName] = $paramValue;
    }

    $ret['className'] = $json['method']['className'];
    $ret['functionName'] = $json['method']['functionName'];
    return $ret;
  }

  private function callFunction($params) {
    $this->includeClasses();
    $args = [];
    foreach ($params['args'] as $className => $json) {
      array_push($args, $this->serializer->unserialize($json));
    }

    call_user_func_array([$params['className'], $params['functionName']], $args);
  }

  private function includeClasses() {
    foreach ($this->classes as $className => $classInfo) {
      include_once $classInfo['location'];
    }
  }
}

?>
