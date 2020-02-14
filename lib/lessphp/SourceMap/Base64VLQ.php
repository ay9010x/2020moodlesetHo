<?php


class Less_SourceMap_Base64VLQ {

	
	private $shift = 5;

	
	private $mask = 0x1F; 
	
	private $continuationBit = 0x20; 
	
	private $charToIntMap = array(
		'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6,
		'H' => 7,'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13,
		'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19, 'U' => 20,
		'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25, 'a' => 26, 'b' => 27,
		'c' => 28, 'd' => 29, 'e' => 30, 'f' => 31, 'g' => 32, 'h' => 33, 'i' => 34,
		'j' => 35, 'k' => 36, 'l' => 37, 'm' => 38, 'n' => 39, 'o' => 40, 'p' => 41,
		'q' => 42, 'r' => 43, 's' => 44, 't' => 45, 'u' => 46, 'v' => 47, 'w' => 48,
		'x' => 49, 'y' => 50, 'z' => 51, 0 => 52, 1 => 53, 2 => 54, 3 => 55, 4 => 56,
		5 => 57,	6 => 58, 7 => 59, 8 => 60, 9 => 61, '+' => 62, '/' => 63,
	);

	
	private $intToCharMap = array(
		0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G',
		7 => 'H', 8 => 'I', 9 => 'J', 10 => 'K', 11 => 'L', 12 => 'M', 13 => 'N',
		14 => 'O', 15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U',
		21 => 'V', 22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z', 26 => 'a', 27 => 'b',
		28 => 'c', 29 => 'd', 30 => 'e', 31 => 'f', 32 => 'g', 33 => 'h', 34 => 'i',
		35 => 'j', 36 => 'k', 37 => 'l', 38 => 'm', 39 => 'n', 40 => 'o', 41 => 'p',
		42 => 'q', 43 => 'r', 44 => 's', 45 => 't', 46 => 'u', 47 => 'v', 48 => 'w',
		49 => 'x', 50 => 'y', 51 => 'z', 52 => '0', 53 => '1', 54 => '2', 55 => '3',
		56 => '4', 57 => '5', 58 => '6', 59 => '7', 60 => '8', 61 => '9', 62 => '+',
		63 => '/',
	);

	
	public function __construct(){
													}

	
	public function toVLQSigned($aValue){
		return 0xffffffff & ($aValue < 0 ? ((-$aValue) << 1) + 1 : ($aValue << 1) + 0);
	}

	
	public function fromVLQSigned($aValue){
		return $aValue & 1 ? $this->zeroFill(~$aValue + 2, 1) | (-1 - 0x7fffffff) : $this->zeroFill($aValue, 1);
	}

	
	public function encode($aValue){
		$encoded = '';
		$vlq = $this->toVLQSigned($aValue);
		do
		{
			$digit = $vlq & $this->mask;
			$vlq = $this->zeroFill($vlq, $this->shift);
			if($vlq > 0){
				$digit |= $this->continuationBit;
			}
			$encoded .= $this->base64Encode($digit);
		} while($vlq > 0);

		return $encoded;
	}

	
	public function decode($encoded){
		$vlq = 0;
		$i = 0;
		do
		{
			$digit = $this->base64Decode($encoded[$i]);
			$vlq |= ($digit & $this->mask) << ($i * $this->shift);
			$i++;
		} while($digit & $this->continuationBit);

		return $this->fromVLQSigned($vlq);
	}

	
	public function zeroFill($a, $b){
		return ($a >= 0) ? ($a >> $b) : ($a >> $b) & (PHP_INT_MAX >> ($b - 1));
	}

	
	public function base64Encode($number){
		if($number < 0 || $number > 63){
			throw new Exception(sprintf('Invalid number "%s" given. Must be between 0 and 63.', $number));
		}
		return $this->intToCharMap[$number];
	}

	
	public function base64Decode($char){
		if(!array_key_exists($char, $this->charToIntMap)){
			throw new Exception(sprintf('Invalid base 64 digit "%s" given.', $char));
		}
		return $this->charToIntMap[$char];
	}

}
