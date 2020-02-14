<?php


class PSpellShell extends SpellChecker {
	
	function &checkWords($lang, $words) {
		$cmd = $this->_getCMD($lang);

		if ($fh = fopen($this->_tmpfile, "w")) {
			fwrite($fh, "!\n");

			foreach($words as $key => $value)
				fwrite($fh, "^" . $value . "\n");

			fclose($fh);
		} else
			$this->throwError("PSpell support was not found.");

		$data = shell_exec($cmd);
		@unlink($this->_tmpfile);

		$returnData = array();
		$dataArr = preg_split("/[\r\n]/", $data, -1, PREG_SPLIT_NO_EMPTY);

		foreach ($dataArr as $dstr) {
			$matches = array();

						if ($dstr[0] == "@")
				continue;

			preg_match("/(\&|#) ([^ ]+) .*/i", $dstr, $matches);

			if (!empty($matches[2]))
				$returnData[] = utf8_encode(trim($matches[2]));
		}

		return $returnData;
	}

	
	function &getSuggestions($lang, $word) {
		$cmd = $this->_getCMD($lang);

        if (function_exists("mb_convert_encoding"))
            $word = mb_convert_encoding($word, "ISO-8859-1", mb_detect_encoding($word, "UTF-8"));
        else
            $word = utf8_encode($word);

		if ($fh = fopen($this->_tmpfile, "w")) {
			fwrite($fh, "!\n");
			fwrite($fh, "^$word\n");
			fclose($fh);
		} else
			$this->throwError("Error opening tmp file.");

		$data = shell_exec($cmd);
		@unlink($this->_tmpfile);

		$returnData = array();
		$dataArr = preg_split("/\n/", $data, -1, PREG_SPLIT_NO_EMPTY);

		foreach($dataArr as $dstr) {
			$matches = array();

						if ($dstr[0] == "@")
				continue;

			preg_match("/\&[^:]+:(.*)/i", $dstr, $matches);

			if (!empty($matches[1])) {
				$words = array_slice(explode(',', $matches[1]), 0, 10);

				for ($i=0; $i<count($words); $i++)
					$words[$i] = trim($words[$i]);

				return $words;
			}
		}

		return array();
	}

	function _getCMD($lang) {
		$this->_tmpfile = tempnam($this->_config['PSpellShell.tmp'], "tinyspell");

		$file = $this->_tmpfile;
		$lang = preg_replace("/[^-_a-z]/", "", strtolower($lang));
		$bin  = $this->_config['PSpellShell.aspell'];

		if (preg_match("#win#i", php_uname()))
			return "$bin -a --lang=$lang --encoding=utf-8 -H < $file 2>&1";

		return "cat $file | $bin -a --lang=$lang --encoding=utf-8 -H";
	}
}

?>
