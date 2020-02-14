<?php
namespace Michelf;



class Markdown implements MarkdownInterface {

	
	const  MARKDOWNLIB_VERSION  =  "1.6.0";

	
	public static function defaultTransform($text) {
								$parser_class = \get_called_class();

				static $parser_list;
		$parser =& $parser_list[$parser_class];

				if (!$parser)
			$parser = new $parser_class;

				return $parser->transform($text);
	}

	
		public $empty_element_suffix = " />";
	public $tab_width = 4;
	
		public $no_markup = false;
	public $no_entities = false;
	
		public $predef_urls = array();
	public $predef_titles = array();

		public $url_filter_func = null;

		public $header_id_func = null;
	
		public $code_block_content_func = null;

												public $enhanced_ordered_list = false;

	
			protected $nested_brackets_depth = 6;
	protected $nested_brackets_re;
	
	protected $nested_url_parenthesis_depth = 4;
	protected $nested_url_parenthesis_re;

		protected $escape_chars = '\`*_{}[]()>#+-.!';
	protected $escape_chars_re;


	public function __construct() {
					$this->_initDetab();
		$this->prepareItalicsAndBold();
	
		$this->nested_brackets_re = 
			str_repeat('(?>[^\[\]]+|\[', $this->nested_brackets_depth).
			str_repeat('\])*', $this->nested_brackets_depth);
	
		$this->nested_url_parenthesis_re = 
			str_repeat('(?>[^()\s]+|\(', $this->nested_url_parenthesis_depth).
			str_repeat('(?>\)))*', $this->nested_url_parenthesis_depth);
		
		$this->escape_chars_re = '['.preg_quote($this->escape_chars).']';
		
				asort($this->document_gamut);
		asort($this->block_gamut);
		asort($this->span_gamut);
	}


		protected $urls = array();
	protected $titles = array();
	protected $html_hashes = array();
	
		protected $in_anchor = false;
	
	
	protected function setup() {
								$this->urls = $this->predef_urls;
		$this->titles = $this->predef_titles;
		$this->html_hashes = array();
		
		$this->in_anchor = false;
	}
	
	protected function teardown() {
						$this->urls = array();
		$this->titles = array();
		$this->html_hashes = array();
	}


	public function transform($text) {
						$this->setup();
	
				$text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);

						$text = preg_replace('{\r\n?}', "\n", $text);

				$text .= "\n\n";

				$text = $this->detab($text);

				$text = $this->hashHTMLBlocks($text);

										$text = preg_replace('/^[ ]+$/m', '', $text);

				foreach ($this->document_gamut as $method => $priority) {
			$text = $this->$method($text);
		}
		
		$this->teardown();

		return $text . "\n";
	}
	
	protected $document_gamut = array(
				"stripLinkDefinitions" => 20,
		
		"runBasicBlockGamut"   => 30,
		);


	protected function stripLinkDefinitions($text) {
						$less_than_tab = $this->tab_width - 1;

				$text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>			# url = $2
							|
							  (\S+?)			# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)			# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	# title is optional
							(?:\n+|\Z)
			}xm',
			array($this, '_stripLinkDefinitions_callback'),
			$text);
		return $text;
	}
	protected function _stripLinkDefinitions_callback($matches) {
		$link_id = strtolower($matches[1]);
		$url = $matches[2] == '' ? $matches[3] : $matches[2];
		$this->urls[$link_id] = $url;
		$this->titles[$link_id] =& $matches[4];
		return ''; 	}


	protected function hashHTMLBlocks($text) {
		if ($this->no_markup)  return $text;

		$less_than_tab = $this->tab_width - 1;

																												$block_tags_a_re = 'ins|del';
		$block_tags_b_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|'.
						   'script|noscript|style|form|fieldset|iframe|math|svg|'.
						   'article|section|nav|aside|hgroup|header|footer|'.
						   'figure';

				$nested_tags_level = 4;
		$attr = '
			(?>				# optional tag attributes
			  \s			# starts with whitespace
			  (?>
				[^>"/]+		# text outside quotes
			  |
				/+(?!>)		# slash not followed by ">"
			  |
				"[^"]*"		# text inside double quotes (tolerate ">")
			  |
				\'[^\']*\'	# text inside single quotes (tolerate ">")
			  )*
			)?	
			';
		$content =
			str_repeat('
				(?>
				  [^<]+			# content without tag
				|
				  <\2			# nested opening tag
					'.$attr.'	# attributes
					(?>
					  />
					|
					  >', $nested_tags_level).						  '.*?'.								str_repeat('
					  </\2\s*>	# closing nested tag
					)
				  |				
					<(?!/\2\s*>	# other tags with a different name
				  )
				)*',
				$nested_tags_level);
		$content2 = str_replace('\2', '\3', $content);

																								$text = preg_replace_callback('{(?>
			(?>
				(?<=\n)			# Starting on its own line
				|				# or
				\A\n?			# the at beginning of the doc
			)
			(						# save in $1

			  # Match from `\n<tag>` to `</tag>\n`, handling nested tags 
			  # in between.
					
						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_b_re.')# start tag = $2
						'.$attr.'>			# attributes followed by > and \n
						'.$content.'		# content, support nesting
						</\2>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document

			| # Special version for tags of group a.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_a_re.')# start tag = $3
						'.$attr.'>[ ]*\n	# attributes followed by >
						'.$content2.'		# content, support nesting
						</\3>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document
					
			| # Special case just for <hr />. It was easier to make a special 
			  # case than to make the other regex more complicated.
			
						[ ]{0,'.$less_than_tab.'}
						<(hr)				# start tag = $2
						'.$attr.'			# attributes
						/?>					# the matching end tag
						[ ]*
						(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # Special case for standalone HTML comments:
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<!-- .*? -->
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
			
			| # PHP and ASP-style processor instructions (<? and <%)
			
					[ ]{0,'.$less_than_tab.'}
					(?s:
						<([?%])			# $2
						.*?
						\2>
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document
					
			)
			)}Sxmi',
			array($this, '_hashHTMLBlocks_callback'),
			$text);

		return $text;
	}
	protected function _hashHTMLBlocks_callback($matches) {
		$text = $matches[1];
		$key  = $this->hashBlock($text);
		return "\n\n$key\n\n";
	}
	
	
	protected function hashPart($text, $boundary = 'X') {
																$text = $this->unhash($text);
		
				static $i = 0;
		$key = "$boundary\x1A" . ++$i . $boundary;
		$this->html_hashes[$key] = $text;
		return $key; 	}


	protected function hashBlock($text) {
					return $this->hashPart($text, 'B');
	}


	protected $block_gamut = array(
						"doHeaders"         => 10,
		"doHorizontalRules" => 20,
		
		"doLists"           => 40,
		"doCodeBlocks"      => 50,
		"doBlockQuotes"     => 60,
		);

	protected function runBlockGamut($text) {
															$text = $this->hashHTMLBlocks($text);
		
		return $this->runBasicBlockGamut($text);
	}
	
	protected function runBasicBlockGamut($text) {
							foreach ($this->block_gamut as $method => $priority) {
			$text = $this->$method($text);
		}
		
				$text = $this->formParagraphs($text);

		return $text;
	}
	
	
	protected function doHorizontalRules($text) {
				return preg_replace(
			'{
				^[ ]{0,3}	# Leading space
				([-*_])		# $1: First marker
				(?>			# Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			# Marker character
				){2,}		# Group repeated at least twice
				[ ]*		# Tailing spaces
				$			# End of line.
			}mx',
			"\n".$this->hashBlock("<hr$this->empty_element_suffix")."\n", 
			$text);
	}


	protected $span_gamut = array(
										"parseSpan"           => -30,

						"doImages"            =>  10,
		"doAnchors"           =>  20,
		
								"doAutoLinks"         =>  30,
		"encodeAmpsAndAngles" =>  40,

		"doItalicsAndBold"    =>  50,
		"doHardBreaks"        =>  60,
		);

	protected function runSpanGamut($text) {
					foreach ($this->span_gamut as $method => $priority) {
			$text = $this->$method($text);
		}

		return $text;
	}
	
	
	protected function doHardBreaks($text) {
				return preg_replace_callback('/ {2,}\n/', 
			array($this, '_doHardBreaks_callback'), $text);
	}
	protected function _doHardBreaks_callback($matches) {
		return $this->hashPart("<br$this->empty_element_suffix\n");
	}


	protected function doAnchors($text) {
					if ($this->in_anchor) return $text;
		$this->in_anchor = true;
		
								$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs',
			array($this, '_doAnchors_reference_callback'), $text);

								$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]
			  \(			# literal paren
				[ \n]*
				(?:
					<(.+?)>	# href = $3
				|
					('.$this->nested_url_parenthesis_re.')	# href = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ \n]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			)
			}xs',
			array($this, '_doAnchors_inline_callback'), $text);

												$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs',
			array($this, '_doAnchors_reference_callback'), $text);

		$this->in_anchor = false;
		return $text;
	}
	protected function _doAnchors_reference_callback($matches) {
		$whole_match =  $matches[1];
		$link_text   =  $matches[2];
		$link_id     =& $matches[3];

		if ($link_id == "") {
						$link_id = $link_text;
		}
		
				$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

		if (isset($this->urls[$link_id])) {
			$url = $this->urls[$link_id];
			$url = $this->encodeURLAttribute($url);
			
			$result = "<a href=\"$url\"";
			if ( isset( $this->titles[$link_id] ) ) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .=  " title=\"$title\"";
			}
		
			$link_text = $this->runSpanGamut($link_text);
			$result .= ">$link_text</a>";
			$result = $this->hashPart($result);
		}
		else {
			$result = $whole_match;
		}
		return $result;
	}
	protected function _doAnchors_inline_callback($matches) {
		$whole_match	=  $matches[1];
		$link_text		=  $this->runSpanGamut($matches[2]);
		$url			=  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

						$unhashed = $this->unhash($url);
		if ($unhashed != $url)
			$url = preg_replace('/^<(.*)>$/', '\1', $unhashed);

		$url = $this->encodeURLAttribute($url);

		$result = "<a href=\"$url\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\"";
		}
		
		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}


	protected function doImages($text) {
											$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs', 
			array($this, '_doImages_reference_callback'), $text);

										$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ \n]*
				)?			# title is optional
			  \)
			)
			}xs',
			array($this, '_doImages_inline_callback'), $text);

		return $text;
	}
	protected function _doImages_reference_callback($matches) {
		$whole_match = $matches[1];
		$alt_text    = $matches[2];
		$link_id     = strtolower($matches[3]);

		if ($link_id == "") {
			$link_id = strtolower($alt_text); 		}

		$alt_text = $this->encodeAttribute($alt_text);
		if (isset($this->urls[$link_id])) {
			$url = $this->encodeURLAttribute($this->urls[$link_id]);
			$result = "<img src=\"$url\" alt=\"$alt_text\"";
			if (isset($this->titles[$link_id])) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .=  " title=\"$title\"";
			}
			$result .= $this->empty_element_suffix;
			$result = $this->hashPart($result);
		}
		else {
						$result = $whole_match;
		}

		return $result;
	}
	protected function _doImages_inline_callback($matches) {
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeURLAttribute($url);
		$result = "<img src=\"$url\" alt=\"$alt_text\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\""; 		}
		$result .= $this->empty_element_suffix;

		return $this->hashPart($result);
	}


	protected function doHeaders($text) {
																$text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx',
			array($this, '_doHeaders_callback_setext'), $text);

																$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				\n+
			}xm',
			array($this, '_doHeaders_callback_atx'), $text);

		return $text;
	}

	protected function _doHeaders_callback_setext($matches) {
				if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1]))
			return $matches[0];
		
		$level = $matches[2]{0} == '=' ? 1 : 2;

				$idAtt = $this->_generateIdFromHeaderValue($matches[1]);

		$block = "<h$level$idAtt>".$this->runSpanGamut($matches[1])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
	protected function _doHeaders_callback_atx($matches) {

				$idAtt = $this->_generateIdFromHeaderValue($matches[2]);

		$level = strlen($matches[1]);
		$block = "<h$level$idAtt>".$this->runSpanGamut($matches[2])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	protected function _generateIdFromHeaderValue($headerValue) {

												if (!is_callable($this->header_id_func)) {
			return "";
		}
		$idValue = call_user_func($this->header_id_func, $headerValue);
		if (!$idValue) return "";

		return ' id="' . $this->encodeAttribute($idValue) . '"';

	}

	protected function doLists($text) {
					$less_than_tab = $this->tab_width - 1;

				$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[\.]';

		$markers_relist = array(
			$marker_ul_re => $marker_ol_re,
			$marker_ol_re => $marker_ul_re,
			);

		foreach ($markers_relist as $marker_re => $other_marker_re) {
						$whole_list_re = '
				(								# $1 = whole list
				  (								# $2
					([ ]{0,'.$less_than_tab.'})	# $3 = number of spaces
					('.$marker_re.')			# $4 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (								# $5
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!						# Negative lookahead for another list item marker
						[ ]*
						'.$marker_re.'[ ]+
					  )
					|
					  (?=						# Lookahead for another kind of list
					    \n
						\3						# Must have the same indentation
						'.$other_marker_re.'[ ]+
					  )
				  )
				)
			'; 			
								
			if ($this->list_level) {
				$text = preg_replace_callback('{
						^
						'.$whole_list_re.'
					}mx',
					array($this, '_doLists_callback'), $text);
			}
			else {
				$text = preg_replace_callback('{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						'.$whole_list_re.'
					}mx',
					array($this, '_doLists_callback'), $text);
			}
		}

		return $text;
	}
	protected function _doLists_callback($matches) {
				$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[\.]';
		$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";
		$marker_ol_start_re = '[0-9]+';

		$list = $matches[1];
		$list_type = preg_match("/$marker_ul_re/", $matches[4]) ? "ul" : "ol";

		$marker_any_re = ( $list_type == "ul" ? $marker_ul_re : $marker_ol_re );

		$list .= "\n";
		$result = $this->processListItems($list, $marker_any_re);

		$ol_start = 1;
		if ($this->enhanced_ordered_list) {
						if ($list_type == 'ol') {
				$ol_start_array = array();
				$ol_start_check = preg_match("/$marker_ol_start_re/", $matches[4], $ol_start_array);
				if ($ol_start_check){
					$ol_start = $ol_start_array[0];
				}
			}
		}

		if ($ol_start > 1 && $list_type == 'ol'){
			$result = $this->hashBlock("<$list_type start=\"$ol_start\">\n" . $result . "</$list_type>");
		} else {
			$result = $this->hashBlock("<$list_type>\n" . $result . "</$list_type>");
		}
		return "\n". $result ."\n\n";
	}

	protected $list_level = 0;

	protected function processListItems($list_str, $marker_any_re) {
																																														
		$this->list_level++;

				$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		$list_str = preg_replace_callback('{
			(\n)?							# leading line = $1
			(^[ ]*)							# leading whitespace = $2
			('.$marker_any_re.'				# list marker and space = $3
				(?:[ ]+|(?=\n))	# space only required if item is not empty
			)
			((?s:.*?))						# list item text   = $4
			(?:(\n+(?=\n))|\n)				# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
			}xm',
			array($this, '_processListItems_callback'), $list_str);

		$this->list_level--;
		return $list_str;
	}
	protected function _processListItems_callback($matches) {
		$item = $matches[4];
		$leading_line =& $matches[1];
		$leading_space =& $matches[2];
		$marker_space = $matches[3];
		$tailing_blank_line =& $matches[5];

		if ($leading_line || $tailing_blank_line || 
			preg_match('/\n{2,}/', $item))
		{
						$item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
			$item = $this->runBlockGamut($this->outdent($item)."\n");
		}
		else {
						$item = $this->doLists($this->outdent($item));
			$item = preg_replace('/\n+$/', '', $item);
			$item = $this->runSpanGamut($item);
		}

		return "<li>" . $item . "</li>\n";
	}


	protected function doCodeBlocks($text) {
					$text = preg_replace_callback('{
				(?:\n\n|\A\n?)
				(	            # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.$this->tab_width.'}  # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.$this->tab_width.'}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm',
			array($this, '_doCodeBlocks_callback'), $text);

		return $text;
	}
	protected function _doCodeBlocks_callback($matches) {
		$codeblock = $matches[1];

		$codeblock = $this->outdent($codeblock);
		if ($this->code_block_content_func) {
			$codeblock = call_user_func($this->code_block_content_func, $codeblock, "");
		} else {
			$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		}

				$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

		$codeblock = "<pre><code>$codeblock\n</code></pre>";
		return "\n\n".$this->hashBlock($codeblock)."\n\n";
	}


	protected function makeCodeSpan($code) {
					$code = htmlspecialchars(trim($code), ENT_NOQUOTES);
		return $this->hashPart("<code>$code</code>");
	}


	protected $em_relist = array(
		''  => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?![\.,:;]?\s)',
		'*' => '(?<![\s*])\*(?!\*)',
		'_' => '(?<![\s_])_(?!_)',
		);
	protected $strong_relist = array(
		''   => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?![\.,:;]?\s)',
		'**' => '(?<![\s*])\*\*(?!\*)',
		'__' => '(?<![\s_])__(?!_)',
		);
	protected $em_strong_relist = array(
		''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?![\.,:;]?\s)',
		'***' => '(?<![\s*])\*\*\*(?!\*)',
		'___' => '(?<![\s_])___(?!_)',
		);
	protected $em_strong_prepared_relist;
	
	protected function prepareItalicsAndBold() {
						foreach ($this->em_relist as $em => $em_re) {
			foreach ($this->strong_relist as $strong => $strong_re) {
								$token_relist = array();
				if (isset($this->em_strong_relist["$em$strong"])) {
					$token_relist[] = $this->em_strong_relist["$em$strong"];
				}
				$token_relist[] = $em_re;
				$token_relist[] = $strong_re;
				
								$token_re = '{('. implode('|', $token_relist) .')}';
				$this->em_strong_prepared_relist["$em$strong"] = $token_re;
			}
		}
	}
	
	protected function doItalicsAndBold($text) {
		$token_stack = array('');
		$text_stack = array('');
		$em = '';
		$strong = '';
		$tree_char_em = false;
		
		while (1) {
															$token_re = $this->em_strong_prepared_relist["$em$strong"];
			
															$parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			$text_stack[0] .= $parts[0];
			$token =& $parts[1];
			$text =& $parts[2];
			
			if (empty($token)) {
												while ($token_stack[0]) {
					$text_stack[1] .= array_shift($token_stack);
					$text_stack[0] .= array_shift($text_stack);
				}
				break;
			}
			
			$token_len = strlen($token);
			if ($tree_char_em) {
								if ($token_len == 3) {
										array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runSpanGamut($span);
					$span = "<strong><em>$span</em></strong>";
					$text_stack[0] .= $this->hashPart($span);
					$em = '';
					$strong = '';
				} else {
															$token_stack[0] = str_repeat($token{0}, 3-$token_len);
					$tag = $token_len == 2 ? "strong" : "em";
					$span = $text_stack[0];
					$span = $this->runSpanGamut($span);
					$span = "<$tag>$span</$tag>";
					$text_stack[0] = $this->hashPart($span);
					$$tag = ''; 				}
				$tree_char_em = false;
			} else if ($token_len == 3) {
				if ($em) {
															for ($i = 0; $i < 2; ++$i) {
						$shifted_token = array_shift($token_stack);
						$tag = strlen($shifted_token) == 2 ? "strong" : "em";
						$span = array_shift($text_stack);
						$span = $this->runSpanGamut($span);
						$span = "<$tag>$span</$tag>";
						$text_stack[0] .= $this->hashPart($span);
						$$tag = ''; 					}
				} else {
															$em = $token{0};
					$strong = "$em$em";
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$tree_char_em = true;
				}
			} else if ($token_len == 2) {
				if ($strong) {
										if (strlen($token_stack[0]) == 1) {
						$text_stack[1] .= array_shift($token_stack);
						$text_stack[0] .= array_shift($text_stack);
					}
										array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runSpanGamut($span);
					$span = "<strong>$span</strong>";
					$text_stack[0] .= $this->hashPart($span);
					$strong = '';
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$strong = $token;
				}
			} else {
								if ($em) {
					if (strlen($token_stack[0]) == 1) {
												array_shift($token_stack);
						$span = array_shift($text_stack);
						$span = $this->runSpanGamut($span);
						$span = "<em>$span</em>";
						$text_stack[0] .= $this->hashPart($span);
						$em = '';
					} else {
						$text_stack[0] .= $token;
					}
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$em = $token;
				}
			}
		}
		return $text_stack[0];
	}


	protected function doBlockQuotes($text) {
		$text = preg_replace_callback('/
			  (								# Wrap whole match in $1
				(?>
				  ^[ ]*>[ ]?			# ">" at the start of a line
					.+\n					# rest of the first line
				  (.+\n)*					# subsequent consecutive lines
				  \n*						# blanks
				)+
			  )
			/xm',
			array($this, '_doBlockQuotes_callback'), $text);

		return $text;
	}
	protected function _doBlockQuotes_callback($matches) {
		$bq = $matches[1];
				$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		$bq = $this->runBlockGamut($bq);		
		$bq = preg_replace('/^/m', "  ", $bq);
						$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', 
			array($this, '_doBlockQuotes_callback2'), $bq);

		return "\n". $this->hashBlock("<blockquote>\n$bq\n</blockquote>")."\n\n";
	}
	protected function _doBlockQuotes_callback2($matches) {
		$pre = $matches[1];
		$pre = preg_replace('/^  /m', '', $pre);
		return $pre;
	}


	protected function formParagraphs($text) {
								$text = preg_replace('/\A\n+|\n+\z/', '', $text);

		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

								foreach ($grafs as $key => $value) {
			if (!preg_match('/^B\x1A[0-9]+B$/', $value)) {
								$value = $this->runSpanGamut($value);
				$value = preg_replace('/^([ ]*)/', "<p>", $value);
				$value .= "</p>";
				$grafs[$key] = $this->unhash($value);
			}
			else {
												$graf = $value;
				$block = $this->html_hashes[$graf];
				$graf = $block;
				$grafs[$key] = $graf;
			}
		}

		return implode("\n\n", $grafs);
	}


	protected function encodeAttribute($text) {
						$text = $this->encodeAmpsAndAngles($text);
		$text = str_replace('"', '&quot;', $text);
		return $text;
	}


	protected function encodeURLAttribute($url, &$text = null) {
								if ($this->url_filter_func)
			$url = call_user_func($this->url_filter_func, $url);

		if (preg_match('{^mailto:}i', $url))
			$url = $this->encodeEntityObfuscatedAttribute($url, $text, 7);
		else if (preg_match('{^tel:}i', $url))
		{
			$url = $this->encodeAttribute($url);
			$text = substr($url, 4);
		}
		else
		{
			$url = $this->encodeAttribute($url);
			$text = $url;
		}

		return $url;
	}
	
	
	protected function encodeAmpsAndAngles($text) {
							if ($this->no_entities) {
			$text = str_replace('&', '&amp;', $text);
		} else {
									$text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', 
								'&amp;', $text);
		}
				$text = str_replace('<', '&lt;', $text);

		return $text;
	}


	protected function doAutoLinks($text) {
		$text = preg_replace_callback('{<((https?|ftp|dict|tel):[^\'">\s]+)>}i',
			array($this, '_doAutoLinks_url_callback'), $text);

				$text = preg_replace_callback('{
			<
			(?:mailto:)?
			(
				(?:
					[-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
				|
					".*?"
				)
				\@
				(?:
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				|
					\[[\d.a-fA-F:]+\]	# IPv4 & IPv6
				)
			)
			>
			}xi',
			array($this, '_doAutoLinks_email_callback'), $text);

		return $text;
	}
	protected function _doAutoLinks_url_callback($matches) {
		$url = $this->encodeURLAttribute($matches[1], $text);
		$link = "<a href=\"$url\">$text</a>";
		return $this->hashPart($link);
	}
	protected function _doAutoLinks_email_callback($matches) {
		$addr = $matches[1];
		$url = $this->encodeURLAttribute("mailto:$addr", $text);
		$link = "<a href=\"$url\">$text</a>";
		return $this->hashPart($link);
	}


	protected function encodeEntityObfuscatedAttribute($text, &$tail = null, $head_length = 0) {
																				if ($text == "") return $tail = "";

		$chars = preg_split('/(?<!^)(?!$)/', $text);
		$seed = (int)abs(crc32($text) / strlen($text)); 
		foreach ($chars as $key => $char) {
			$ord = ord($char);
						if ($ord < 128) {
				$r = ($seed * (1 + $key)) % 100; 																if ($r > 90 && strpos('@"&>', $char) === false) ;
				else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';';
				else              $chars[$key] = '&#'.$ord.';';
			}
		}

		$text = implode('', $chars);
		$tail = $head_length ? implode('', array_slice($chars, $head_length)) : $text;

		return $text;
	}


	protected function parseSpan($str) {
						$output = '';
		
		$span_re = '{
				(
					\\\\'.$this->escape_chars_re.'
				|
					(?<![`\\\\])
					`+						# code span marker
			'.( $this->no_markup ? '' : '
				|
					<!--    .*?     -->		# comment
				|
					<\?.*?\?> | <%.*?%>		# processing instruction
				|
					<[!$]?[-a-zA-Z0-9:_]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
				|
					<[-a-zA-Z0-9:_]+\s*/> # xml-style empty tag
				|
					</[-a-zA-Z0-9:_]+\s*> # closing tag
			').'
				)
				}xs';

		while (1) {
																		$parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);
			
						if ($parts[0] != "") {
				$output .= $parts[0];
			}
			
						if (isset($parts[1])) {
				$output .= $this->handleSpanToken($parts[1], $parts[2]);
				$str = $parts[2];
			}
			else {
				break;
			}
		}
		
		return $output;
	}
	
	
	protected function handleSpanToken($token, &$str) {
						switch ($token{0}) {
			case "\\":
				return $this->hashPart("&#". ord($token{1}). ";");
			case "`":
								if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm', 
					$str, $matches))
				{
					$str = $matches[2];
					$codespan = $this->makeCodeSpan($matches[1]);
					return $this->hashPart($codespan);
				}
				return $token; 			default:
				return $this->hashPart($token);
		}
	}


	protected function outdent($text) {
					return preg_replace('/^(\t|[ ]{1,'.$this->tab_width.'})/m', '', $text);
	}


			protected $utf8_strlen = 'mb_strlen';
	
	protected function detab($text) {
											
		$text = preg_replace_callback('/^.*\t.*$/m',
			array($this, '_detab_callback'), $text);

		return $text;
	}
	protected function _detab_callback($matches) {
		$line = $matches[0];
		$strlen = $this->utf8_strlen; 		
				$blocks = explode("\t", $line);
				$line = $blocks[0];
		unset($blocks[0]); 		foreach ($blocks as $block) {
						$amount = $this->tab_width - 
				$strlen($line, 'UTF-8') % $this->tab_width;
			$line .= str_repeat(" ", $amount) . $block;
		}
		return $line;
	}
	protected function _initDetab() {
								if (function_exists($this->utf8_strlen)) return;
		$this->utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/", 
			$text, $m);');
	}


	protected function unhash($text) {
					return preg_replace_callback('/(.)\x1A[0-9]+\1/', 
			array($this, '_unhash_callback'), $text);
	}
	protected function _unhash_callback($matches) {
		return $this->html_hashes[$matches[0]];
	}

}
