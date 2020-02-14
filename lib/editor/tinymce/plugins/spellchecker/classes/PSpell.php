<?php


class PSpell extends SpellChecker {
	
	function &checkWords($lang, $words) {
		$plink = $this->_getPLink($lang);

		$outWords = array();
		foreach ($words as $word) {
			if (!pspell_check($plink, trim($word)))
				$outWords[] = $word;
		}

		return $outWords;
	}

	
	function &getSuggestions($lang, $word) {
		$words = pspell_suggest($this->_getPLink($lang), $word);

		return $words;
	}

	
	function &_getPLink($lang) {
				if (!function_exists("pspell_new"))
			$this->throwError("PSpell support not found in PHP installation.");

				$plink = pspell_new(
			$lang,
			$this->_config['PSpell.spelling'],
			$this->_config['PSpell.jargon'],
			empty($this->_config['PSpell.encoding']) ? 'utf-8' : $this->_config['PSpell.encoding'],
			$this->_config['PSpell.mode']
		);

		

		if (!$plink)
			$this->throwError("No PSpell link found opened.");

		return $plink;
	}
}

?>
