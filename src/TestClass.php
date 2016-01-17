<?php

require_once 'vendor/autoload.php';
use Zumba\Util\JsonSerializer;

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

  public static function testFunction($testClassObj) {
    $serializer = new JsonSerializer();
    echo $serializer->serialize($testClassObj);
  }
}

?>
