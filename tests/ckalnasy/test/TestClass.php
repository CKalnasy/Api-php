<?php
namespace ckalnasy\test;

require_once $_SERVER['DOCUMENT_ROOT'] . '/serializer/Serializer.php';
use serializer\Serializer;

class TestClass {
  public $string;
  public $number;
  public $location;
  public $array;
  public $map;
  public $set;

  function __construct($string, $number, $location, $array, $map, $set) {
    $this->string = $string;
    $this->number = $number;
    $this->location = $location;
    $this->array = $array;
    $this->map = $map;
    $this->set = $set;
  }

  public static function testFunctionParams($testClassObj) {
    $serializer = new Serializer();
    echo $serializer->serialize($testClassObj);
  }

  public static function testFunctionImage($image) {
    if ($image) {
      echo 1;
    } else {
      echo 0;
    }
  }
}
