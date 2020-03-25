<?php


class EnchantSpell extends SpellChecker {
	
	function &checkWords($lang, $words) {
		$r = enchant_broker_init();
		
		if (enchant_broker_dict_exists($r,$lang)) {
			$d = enchant_broker_request_dict($r, $lang);
			
			$returnData = array();
			foreach($words as $key => $value) {
				$correct = enchant_dict_check($d, $value);
				if(!$correct) {
					$returnData[] = trim($value);
				}
			}
	
			return $returnData;
			enchant_broker_free_dict($d);
		} else {

		}
		enchant_broker_free($r);
	}

	
	function &getSuggestions($lang, $word) {
		$r = enchant_broker_init();

		if (enchant_broker_dict_exists($r,$lang)) {
			$d = enchant_broker_request_dict($r, $lang);
			$suggs = enchant_dict_suggest($d, $word);

						if (!is_array($suggs))
				$suggs = array();

			enchant_broker_free_dict($d);
		} else {
			$suggs = array();
		}

		enchant_broker_free($r);

		return $suggs;
	}
}

?>
