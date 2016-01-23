<?php
namespace ckalnasy;

include_once dirname(dirname(__dir__)) . '/serializer/Serializer.php';
use serializer\Serializer;

/**
 * This class handles http requests and deserializes the parameters for functions it should call.
 */
class Api {
  private $classes;
  private $serializer;

  public function __construct() {
    $jsonFile = $_SERVER['DOCUMENT_ROOT'] . '/api.json';
    if (file_exists($jsonFile)) {
      $string = file_get_contents($jsonFile);
      $json = json_decode($string, true);
      $this->classes = $json['Classes'];
    }
    $this->serializer = new Serializer($this->classes);
  }

  /**
   * Handle the request we've just been given. Deserialize the parameters and pass them as parameters to the static class function
   */
  public function handleRequest() {
    $this->callFunction($this->getParams());
  }

  /**
   * Return an object to the client. Pass an array for multiple objects
   */
  public function returnObject($obj) {
    echo $serializer->serialize($obj);
  }

  private function getParams() {
    if (isset($_FILES['file'])) {
      $ret = [];
      $ret['args'] = [$_FILES['file']];
      $ret['className'] = $this->getPhpClassName(filter_input(INPUT_POST, 'className'));
      $ret['functionName'] = filter_input(INPUT_POST, 'functionName');
      return $ret;
    }

    $ret = [];
    $json = json_decode(@file_get_contents('php://input'), true);
    if (isset($json['params'])) {
      foreach ($json['params'] as $paramName => $paramValue) {
        $_POST[$paramName] = $paramValue;
      }
    }
    if (isset($json['args'])) {
      foreach ($json['args'] as $paramName => $paramValue) {
        $ret['args'][$paramName] = $paramValue;
      }
    }

    $ret['className'] = $this->getPhpClassName($json['method']['className']);
    $ret['functionName'] = $json['method']['functionName'];
    return $ret;
  }

  private function callFunction($params) {
    $this->includeClasses();
    $params = $this->convertParamClassNames($params);
    $args = [];
    foreach ($params['args'] as $propertyName => $json) {
      array_push($args, $this->serializer->unserialize(json_encode($json)));
    }

    call_user_func_array([$params['className'], $params['functionName']], $args);
  }

  private function includeClasses() {
    foreach ($this->classes as $className => $classInfo) {
      include_once $classInfo['location'];
    }
  }

  private function convertParamClassNames($params) {
    foreach ($params as $key => $value) {
      if (is_array($value)) {
        $params[$key] = $this->convertParamClassNames($params[$key]);
      } else if ($key == Serializer::CLASS_KEY && is_string($value)) {
        $params[$key] = $this->getPhpClassName($value);
      }
    }
    return $params;
  }

  private function getPhpClassName($value) {
    if (array_key_exists($value, $this->classes)) {
      if (isset($this->classes[$value]['namespace'])) {
        return $this->classes[$value]['namespace'];
      }
    }
    return $value;
  }
}
