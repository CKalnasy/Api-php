<?php
namespace ckalnasy\test;

class Location {
  public $latitude;
  public $longitude;

  function __construct($latitude, $longitude) {
    $this->latitude = $latitude;
    $this->longitude = $longitude;
  }
}
