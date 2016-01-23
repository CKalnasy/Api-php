<?php
namespace private;

include_once dirname(__dir__) . '/Api.php';
use ckalnasy\Api;

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
use Zumba\Util\JsonSerializer;

/**
 * Wrapper for JSONSerializer.
 * Only difference is class names are converted to client-side names when serializing.
 * This class shouldn't be used outside of the Api library.
 */
class Serializer {
  private $jsonSerializer;
  private $classes;
  const CLASS_KEY = JSONSerializer::CLASS_IDENTIFIER_KEY;

  function __construct($classes = null) {
    $this->jsonSerializer = new JSONSerializer();
    if ($classes) {
      $this->classes = $classes;
    } else {
      $jsonFile = $_SERVER['DOCUMENT_ROOT'] . '/api.json';
      if (file_exists($jsonFile)) {
        $string = file_get_contents($jsonFile);
        $json = json_decode($string, true);
        $this->classes = $json['Classes'];
      }
    }
  }

  function serialize($obj) {
    $ret = json_decode($this->jsonSerializer->serialize($obj), true);
    $ret = $this->convertClassNames($ret);
    return json_encode($ret);
  }

  function unserialize($data) {
    return $this->jsonSerializer->unserialize($data);
  }

  private function convertClassNames($obj) {
    foreach ($obj as $key => $value) {
      if (is_array($value)) {
        $obj[$key] = $this->convertClassNames($obj[$key]);
      } else if ($key == self::CLASS_KEY) {
        $obj[$key] = $this->getClientClassName($value);
      }
    }
    return $obj;
  }

  private function getClientClassName($value) {
    foreach ($this->classes as $clientName => $classInfo) {
      if ($value == $classInfo['namespace']) {
        return $clientName;
      }
    }
    return $value;
  }
}

?>
