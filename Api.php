<?php

/**
 *
 */
class Api {
  private static $classes;

  /**
   *
   */
  public static function staticInit() {
    $jsonFile = 'api.json';
    if (file_exists($jsonFile)) {
      $string = file_get_contents($jsonFile);
      $json = json_decode($string, true);
      self::$classes = $json['Classes'];
    }
  }

  /**
   *
   */
  public static function handleRequest() {
    self::callFunction(self::getParams());
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

  private static function callFunction($params) {
    self::includeClasses();
    $args = [];
    foreach ($params['args'] as $className => $json) {
      array_push($args, self::jsonToObject($className, $json));
    }

    call_user_func_array([$params['className'], $params['functionName']], $args);
  }

  private static function includeClasses() {
    foreach ($classes as $className => $classInfo) {
      include_once $classInfo['location'];
    }
  }

  private static function jsonToObject($className, $json) {
    $fullClassName = self::getFullClassNameFromPartialClassName($className);
    if (!self::hasClass($fullClassName)) {
      die('could not find class: ' . $fullClassName . '. If a namespace for class is used, set the class name (including the namespace) in the api.json file in the document root');
    }
    $class = new ReflectionClass($fullClassName);
    $constructor = $class->getConstructor();
    $paramsList = self::getParamsListFromParameters($constructor->getParameters());
    $values = self::sortParamValues($json, $paramsList);

    return self::initClass($fullClassName, $values);
  }

  private static function getFullClassNameFromPartialClassName($className) {
    return isset($classes[$className]) ? $json[$className]['namespace'] : $className;
  }

  private static function hasClass($className) {
    return in_array($className, get_declared_classes());
  }

  private static function getParamsListFromParameters($parameters) {
    $params = [];
    foreach ($parameters as $param) {
      array_push($params, $param->getName());
    }
    return $params;
  }

  private static function sortParamValues($params, $paramsList) {
    $ret = [];
    foreach ($paramsList as $param) {
      if (isset($params[$param])) {
        if (gettype($params[$param]) == 'array') {
          $className = key($params[$param]);
          array_push($ret, self::jsonToObject($className, $params[$param][$className]));
        } else {
          array_push($ret, $params[$param]);
        }
      } else {
        array_push(null);
      }
    }
    return $ret;
  }

  private static function initClass($class, $args) {
    $reflect  = new ReflectionClass($class);
    return $reflect->newInstanceArgs($args);
  }

  public static function serializeObject($obj) {
    $json = [];
    $reflectionClass = new ReflectionClass($obj);
    foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
      $name = $property->getName();
      $value = $property->getValue($obj);
      if (gettype($value) == 'object') {
        $json[$name] = self::serialize($value);
      } else {
        $json[$name] = $value;
      }
    }
    return [$reflectionClass->getName() => $json];
  }

  public static function serializeObjectArray($array) {
    $ret = [];
    foreach ($array as $obj) {
      array_push($ret, self::serializeObject($obj));
    }
    return $ret;
  }
}

Api::staticInit();
Api::handle_request();

?>
