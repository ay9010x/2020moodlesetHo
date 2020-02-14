<?php



class Google_Utils
{
  public static function urlSafeB64Encode($data)
  {
    $b64 = base64_encode($data);
    $b64 = str_replace(
        array('+', '/', '\r', '\n', '='),
        array('-', '_'),
        $b64
    );
    return $b64;
  }

  public static function urlSafeB64Decode($b64)
  {
    $b64 = str_replace(
        array('-', '_'),
        array('+', '/'),
        $b64
    );
    return base64_decode($b64);
  }

  
  public static function getStrLen($str)
  {
    $strlenVar = strlen($str);
    $d = $ret = 0;
    for ($count = 0; $count < $strlenVar; ++ $count) {
      $ordinalValue = ord($str{$ret});
      switch (true) {
        case (($ordinalValue >= 0x20) && ($ordinalValue <= 0x7F)):
                    $ret ++;
          break;
        case (($ordinalValue & 0xE0) == 0xC0):
                              $ret += 2;
          break;
        case (($ordinalValue & 0xF0) == 0xE0):
                              $ret += 3;
          break;
        case (($ordinalValue & 0xF8) == 0xF0):
                              $ret += 4;
          break;
        case (($ordinalValue & 0xFC) == 0xF8):
                              $ret += 5;
          break;
        case (($ordinalValue & 0xFE) == 0xFC):
                              $ret += 6;
          break;
        default:
          $ret ++;
      }
    }
    return $ret;
  }

  
  public static function normalize($arr)
  {
    if (!is_array($arr)) {
      return array();
    }

    $normalized = array();
    foreach ($arr as $key => $val) {
      $normalized[strtolower($key)] = $val;
    }
    return $normalized;
  }

  
  public static function camelCase($value)
  {
    $value = ucwords(str_replace(array('-', '_'), ' ', $value));
    $value = str_replace(' ', '', $value);
    $value[0] = strtolower($value[0]);
    return $value;
  }
}
