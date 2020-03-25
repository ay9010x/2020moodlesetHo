<?php



class t3lib_l10n_Locales implements t3lib_Singleton {

	
	protected $languages = array(
		'default' => 'English',
		'af' => 'Afrikaans',
		'ar' => 'Arabic',
		'bs' => 'Bosnian',
		'bg' => 'Bulgarian',
		'ca' => 'Catalan',
		'ch' => 'Chinese (Simpl.)',
		'cs' => 'Czech',
		'da' => 'Danish',
		'de' => 'German',
		'el' => 'Greek',
		'eo' => 'Esperanto',
		'es' => 'Spanish',
		'et' => 'Estonian',
		'eu' => 'Basque',
		'fa' => 'Persian',
		'fi' => 'Finnish',
		'fo' => 'Faroese',
		'fr' => 'French',
		'fr_CA' => 'French (Canada)',
		'gl' => 'Galician',
		'he' => 'Hebrew',
		'hi' => 'Hindi',
		'hr' => 'Croatian',
		'hu' => 'Hungarian',
		'is' => 'Icelandic',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'ka' => 'Georgian',
		'kl' => 'Greenlandic',
		'km' => 'Khmer',
		'ko' => 'Korean',
		'lt' => 'Lithuanian',
		'lv' => 'Latvian',
		'ms' => 'Malay',
		'nl' => 'Dutch',
		'no' => 'Norwegian',
		'pl' => 'Polish',
		'pt' => 'Portuguese',
		'pt_BR' => 'Brazilian Portuguese',
		'ro' => 'Romanian',
		'ru' => 'Russian',
		'sk' => 'Slovak',
		'sl' => 'Slovenian',
		'sq' => 'Albanian',
		'sr' => 'Serbian',
		'sv' => 'Swedish',
		'th' => 'Thai',
		'tr' => 'Turkish',
		'uk' => 'Ukrainian',
		'vi' => 'Vietnamese',
		'zh' => 'Chinese (Trad.)',
	);

	
	protected $locales = array();

	
	protected $isoReverseMapping = array(
		'bs' => 'ba',				'cs' => 'cz',				'da' => 'dk',				'el' => 'gr',				'fr_CA' => 'qc',			'gl' => 'ga',				'ja' => 'jp',				'ka' => 'ge',				'kl' => 'gl',				'ko' => 'kr',				'ms' => 'my',				'pt_BR' => 'br',			'sl' => 'si',				'sv' => 'se',				'uk' => 'ua',				'vi' => 'vn',				'zh' => 'hk',				'zh_CN' => 'ch',			'zh_HK' => 'hk',		);

	
	protected $isoMapping;

	
	protected $localeDependencies;

	
	public static function initialize() {
		
		$instance = t3lib_div::makeInstance('t3lib_l10n_Locales');
		$instance->isoMapping = array_flip($instance->isoReverseMapping);

					if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['user']) && is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['user'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['user'] as $locale => $name) {
				if (!isset($instance->languages[$locale])) {
					$instance->languages[$locale] = $name;
				}
			}
		}

					$instance->localeDependencies = array();
		foreach ($instance->languages as $locale => $name) {
			if (strlen($locale) == 5) {
				$instance->localeDependencies[$locale] = array(substr($locale, 0, 2));
			}
		}
					if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['dependencies']) && is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['dependencies'])) {
			$instance->localeDependencies = t3lib_div::array_merge_recursive_overrule($instance->localeDependencies, $GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['dependencies']);
		}

		
		$instance->locales = array_keys($instance->languages);

		
		define('TYPO3_languages', implode('|', $instance->getLocales()));
	}

	
	public function getLocales() {
		return array_keys($this->languages);
	}

	
	public function getLanguages() {
		return $this->languages;
	}

	
	public function getIsoMapping() {
		return $this->isoMapping;
	}

	
	public function getTerLocales() {
		return $this->convertToTerLocales(array_keys($this->languages));
	}

	
	public function getLocaleDependencies($locale) {
		$dependencies = array();
		if (isset($this->localeDependencies[$locale])) {
			$dependencies = $this->localeDependencies[$locale];

							$localeDependencies = $dependencies;
			foreach ($localeDependencies as $dependency) {
				if (isset($this->localeDependencies[$dependency])) {
					$dependencies = array_merge($dependencies, $this->getLocaleDependencies($dependency));
				}
			}
		}
		return $dependencies;
	}

	
	public function getTerLocaleDependencies($locale) {
		$terLocale = isset($this->isoMapping[$locale])
				? $this->isoMapping[$locale]
				: $locale;
		return $this->convertToTerLocales($this->getLocaleDependencies($terLocale));
	}

	
	protected function convertToTerLocales(array $locales) {
		$terLocales = array();
		foreach ($locales as $locale) {
			$terLocales[] = isset($this->isoReverseMapping[$locale]) ? $this->isoReverseMapping[$locale] : $locale;
		}
		return $terLocales;
	}

}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/l10n/class.t3lib_l10n_locales.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/l10n/class.t3lib_l10n_locales.php']);
}

?>