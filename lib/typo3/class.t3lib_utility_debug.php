<?php



final class t3lib_utility_Debug {

	
	const DEBUG_TABLE_TEMPLATE = '
	<table class="typo3-debug" border="0" cellpadding="0" cellspacing="0" bgcolor="white" style="border:0px; margin-top:3px; margin-bottom:3px;">
		<tr>
			<td style="background-color:#bbbbbb; font-family: verdana,arial; font-weight: bold; font-size: 10px;">%s</td>
		</tr>
		<tr>
			<td>
			%s
			</td>
		</tr>
	</table>
	';


	public static function debug($var = '', $header = '', $group = 'Debug') {
					if (ob_get_level() == 0) {
			ob_start();
		}

		$debug = self::convertVariableToString($var);
		if ($header) {
			$debug = sprintf(self::DEBUG_TABLE_TEMPLATE, htmlspecialchars((string) $header), $debug);
		}

		if (TYPO3_MODE === 'BE') {
			$debugString = self::prepareVariableForJavascript($debug, is_object($var));
			$group = htmlspecialchars($group);

			if ($header !== '') {
				$tabHeader = htmlspecialchars($header);
			} else {
				$tabHeader = 'Debug';
			}

			$script = '
				(function debug() {
					var debugMessage = "' . $debugString . '";
					var header = "' . $tabHeader . '";
					var group = "' . $group . '";

					if (typeof Ext !== "object" && (top && typeof top.Ext !== "object")) {
						document.write(debugMessage);
						return;
					}

					if (top && typeof Ext !== "object") {
						Ext = top.Ext;
					}

					Ext.onReady(function() {
						var TYPO3ViewportInstance = null;

						if (top && top.TYPO3 && typeof top.TYPO3.Backend === "object") {
							TYPO3ViewportInstance = top.TYPO3.Backend;
						} else if (typeof TYPO3 === "object" && typeof TYPO3.Backend === "object") {
							TYPO3ViewportInstance = TYPO3.Backend;
						}

						if (TYPO3ViewportInstance !== null) {
							TYPO3ViewportInstance.DebugConsole.addTab(debugMessage, header, group);
						} else {
							document.write(debugMessage);
						}
					});
				})();
			';
			echo t3lib_div::wrapJS($script);
		} else {
			echo $debug;
		}
	}

	
	public static function prepareVariableForJavascript($string, $asObject) {
		if ($asObject) {
			$string = str_replace(array(
				'"', '/', '<', "\n", "\r"
			), array(
				'\"', '\/', '\<', '<br />', ''
			), $string);
		} else {
			$string = str_replace(array(
				'"', '/', '<', "\n", "\r"
		  ), array(
				'\"', '\/', '\<', '', ''
			), $string);
		}

		return $string;
	}

	
	public static function convertVariableToString($variable) {
		$string = '';
		if (is_array($variable)) {
			$string = self::viewArray($variable);
		} elseif (is_object($variable)) {
			$string = '<strong>|Object:<pre>';
			$string .= print_r($variable, TRUE);
			$string .= '</pre>|</strong>';
		} elseif ((string) $variable !== '') {
			$string = '<strong>|' . htmlspecialchars((string) $variable) . '|</strong>';
		} else {
			$string = '<strong>| debug |</strong>';
		}

		return $string;
	}

	
	public static function debugInPopUpWindow($debugVariable, $header = 'Debug', $group = 'Debug') {
		$debugString = self::prepareVariableForJavascript(
			self::convertVariableToString($debugVariable),
			is_object($debugVariable)
		);

		$script = '
			(function debug() {
				var debugMessage = "' . $debugString . '",
					header = "' . htmlspecialchars($header) . '",
					group = "' . htmlspecialchars($group) . '",

					browserWindow = function(debug, header, group) {
						var newWindow = window.open("", "TYPO3DebugWindow_" + group,
							"width=600,height=400,menubar=0,toolbar=1,status=0,scrollbars=1,resizable=1"
						);
						if (newWindow.document.body.innerHTML) {
							newWindow.document.body.innerHTML = newWindow.document.body.innerHTML +
								"<hr />" + debugMessage;
						} else {
							newWindow.document.writeln(
								"<html><head><title>Debug: " + header + "(" + group + ")</title></head>"
								+ "<body onload=\"self.focus()\">"
								+ debugMessage
								+ "</body></html>"
							);
						}
					}

				if (!top.Ext) {
					browserWindow(debugMessage, header, group);
				} else {
					top.Ext.onReady(function() {
						if (top && top.TYPO3 && top.TYPO3.Backend) {
							top.TYPO3.Backend.DebugConsole.openBrowserWindow(header, debugMessage, group);
						} else {
							browserWindow(debugMessage, header, group);
						}
					});
				}
			})();
		';
		echo t3lib_div::wrapJS($script);
	}

	
	public static function debugTrail() {
		$trail = debug_backtrace();
		$trail = array_reverse($trail);
		array_pop($trail);

		$path = array();
		foreach ($trail as $dat) {
			$pathFragment = $dat['class'] . $dat['type'] . $dat['function'];
							if (in_array($dat['function'], array('require', 'include', 'require_once', 'include_once'))) {
				$pathFragment .= '(' . substr($dat['args'][0], strlen(PATH_site)) . '),' . substr($dat['file'], strlen(PATH_site));
			}
			$path[] = $pathFragment . '#' . $dat['line'];
		}

		return implode(' // ', $path);
	}

	
	public static function debugRows($rows, $header = '', $returnHTML = FALSE) {
		if (is_array($rows)) {
			$firstEl = reset($rows);
			if (is_array($firstEl)) {
				$headerColumns = array_keys($firstEl);
				$tRows = array();

									$tRows[] = '<tr><td colspan="' . count($headerColumns) .
						   '" style="background-color:#bbbbbb; font-family: verdana,arial; font-weight: bold; font-size: 10px;"><strong>' .
						   htmlspecialchars($header) . '</strong></td></tr>';
				$tCells = array();
				foreach ($headerColumns as $key) {
					$tCells[] = '
							<td><font face="Verdana,Arial" size="1"><strong>' . htmlspecialchars($key) . '</strong></font></td>';
				}
				$tRows[] = '
						<tr>' . implode('', $tCells) . '
						</tr>';

									foreach ($rows as $singleRow) {
					$tCells = array();
					foreach ($headerColumns as $key) {
						$tCells[] = '
							<td><font face="Verdana,Arial" size="1">' .
									(is_array($singleRow[$key]) ? self::debugRows($singleRow[$key], '', TRUE) : htmlspecialchars($singleRow[$key])) .
									'</font></td>';
					}
					$tRows[] = '
						<tr>' . implode('', $tCells) . '
						</tr>';
				}

				$table = '
					<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">' . implode('', $tRows) . '
					</table>';
				if ($returnHTML) {
					return $table;
				}
				else
				{
					echo $table;
				}
			} else
			{
				debug('Empty array of rows', $header);
			}
		} else {
			debug('No array of rows', $header);
		}
	}

	
	public static function ordinalValue($string, $characters = 100) {
		if (strlen($string) < $characters) {
			$characters = strlen($string);
		}
		for ($i = 0; $i < $characters; $i++) {
			$valuestring .= ' ' . ord(substr($string, $i, 1));
		}
		return trim($valuestring);
	}

	
	public static function viewArray($array_in) {
		if (is_array($array_in)) {
			$result = '
			<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">';
			if (count($array_in) == 0) {
				$result .= '<tr><td><font face="Verdana,Arial" size="1"><strong>EMPTY!</strong></font></td></tr>';
			} else {
				foreach ($array_in as $key => $val) {
					$result .= '<tr>
						<td valign="top"><font face="Verdana,Arial" size="1">' . htmlspecialchars((string) $key) . '</font></td>
						<td>';
					if (is_array($val)) {
						$result .= self::viewArray($val);
					} elseif (is_object($val)) {
						$string = '';
						if (method_exists($val, '__toString')) {
							$string .= get_class($val) . ': ' . (string) $val;
						} else {
							$string .= print_r($val, TRUE);
						}
						$result .= '<font face="Verdana,Arial" size="1" color="red">' .
								   nl2br(htmlspecialchars($string)) .
								   '<br /></font>';
					} else {
						if (gettype($val) == 'object') {
							$string = 'Unknown object';
						} else {
							$string = (string) $val;
						}
						$result .= '<font face="Verdana,Arial" size="1" color="red">' .
								   nl2br(htmlspecialchars($string)) .
								   '<br /></font>';
					}
					$result .= '</td>
					</tr>';
				}
			}
			$result .= '</table>';
		} else {
			$result = '<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">
				<tr>
					<td><font face="Verdana,Arial" size="1" color="red">' .
					  nl2br(htmlspecialchars((string) $array_in)) .
					  '<br /></font></td>
				</tr>
			</table>'; 		}
		return $result;
	}

	
	public static function printArray($array_in) {
		echo self::viewArray($array_in);
	}
}

?>