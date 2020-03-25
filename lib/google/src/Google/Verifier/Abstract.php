<?php



abstract class Google_Verifier_Abstract
{
  
  abstract public function verify($data, $signature);
}
