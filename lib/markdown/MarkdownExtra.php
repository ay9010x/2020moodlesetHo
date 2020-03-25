<?php
namespace Michelf;



class MarkdownExtra extends \Michelf\Markdown {

	
		public $fn_id_prefix = "";
	
		public $fn_link_title = "";
	public $fn_backlink_title = "";
	
		public $fn_link_class = "footnote-ref";
	public $fn_backlink_class = "footnote-backref";

				public $fn_backlink_html = '&#8617;&#xFE0E;';

				public $table_align_class_tmpl = '';

		public $code_class_prefix = "";
			public $code_attr_on_pre = false;

		public $predef_abbr = array();

	
	public function __construct() {
									$this->escape_chars .= ':|';
		
						$this->document_gamut += array(
			"doFencedCodeBlocks" => 5,
			"stripFootnotes"     => 15,
			"stripAbbreviations" => 25,
			"appendFootnotes"    => 50,
			);
		$this->block_gamut += array(
			"doFencedCodeBlocks" => 5,
			"doTables"           => 15,
			"doDefLists"         => 45,
			);
		$this->span_gamut += array(
			"doFootnotes"        => 5,
			"doAbbreviations"    => 70,
			);
		
		$this->enhanced_ordered_list = true;
		parent::__construct();
	}
	
	
		protected $footnotes = array();
	protected $footnotes_ordered = array();
	protected $footnotes_ref_count = array();
	protected $footnotes_numbers = array();
	protected $abbr_desciptions = array();
	protected $abbr_word_re = '';
	
		protected $footnote_counter = 1;
	
	
	protected function setup() {
					parent::setup();
		
		$this->footnotes = array();
		$this->footnotes_ordered = array();
		$this->footnotes_ref_count = array();
		$this->footnotes_numbers = array();
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
		$this->footnote_counter = 1;
		
		foreach ($this->predef_abbr as $abbr_word => $abbr_desc) {
			if ($this->abbr_word_re)
				$this->abbr_word_re .= '|';
			$this->abbr_word_re .= preg_quote($abbr_word);
			$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		}
	}
	
	protected function teardown() {
					$this->footnotes = array();
		$this->footnotes_ordered = array();
		$this->footnotes_ref_count = array();
		$this->footnotes_numbers = array();
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
		
		parent::teardown();
	}
	
	
	
		protected $id_class_attr_catch_re = '\{((?>[ ]*[#.a-z][-_:a-zA-Z0-9=]+){1,})[ ]*\}';
		protected $id_class_attr_nocatch_re = '\{(?>[ ]*[#.a-z][-_:a-zA-Z0-9=]+){1,}[ ]*\}';

	protected function doExtraAttributes($tag_name, $attr, $defaultIdValue = null, $classes = array()) {
											if (empty($attr) && !$defaultIdValue && empty($classes)) return "";
		
				preg_match_all('/[#.a-z][-_:a-zA-Z0-9=]+/', $attr, $matches);
		$elements = $matches[0];

				$attributes = array();
		$id = false;
		foreach ($elements as $element) {
			if ($element{0} == '.') {
				$classes[] = substr($element, 1);
			} else if ($element{0} == '#') {
				if ($id === false) $id = substr($element, 1);
			} else if (strpos($element, '=') > 0) {
				$parts = explode('=', $element, 2);
				$attributes[] = $parts[0] . '="' . $parts[1] . '"';
			}
		}

		if (!$id) $id = $defaultIdValue;

				$attr_str = "";
		if (!empty($id)) {
			$attr_str .= ' id="'.$this->encodeAttribute($id) .'"';
		}
		if (!empty($classes)) {
			$attr_str .= ' class="'. implode(" ", $classes) . '"';
		}
		if (!$this->no_markup && !empty($attributes)) {
			$attr_str .= ' '.implode(" ", $attributes);
		}
		return $attr_str;
	}


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
					(?:[ ]* '.$this->id_class_attr_catch_re.' )?  # $5 = extra id & class attr
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
		$this->ref_attr[$link_id] = $this->doExtraAttributes("", $dummy =& $matches[5]);
		return ''; 	}


		
		protected $block_tags_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend|article|section|nav|aside|hgroup|header|footer|figcaption|figure';
						   
		protected $context_block_tags_re = 'script|noscript|style|ins|del|iframe|object|source|track|param|math|svg|canvas|audio|video';
	
		protected $contain_span_tags_re = 'p|h[1-6]|li|dd|dt|td|th|legend|address';
	
			protected $clean_tags_re = 'script|style|math|svg';
	
		protected $auto_close_tags_re = 'hr|img|param|source|track';
	

	protected function hashHTMLBlocks($text) {
																	if ($this->no_markup)  return $text;

								list($text, ) = $this->_hashHTMLBlocks_inMarkdown($text);
		
		return $text;
	}
	protected function _hashHTMLBlocks_inMarkdown($text, $indent = 0,
										$enclosing_tag_re = '', $span = false)
	{
																											if ($text === '') return array('', '');

				$newline_before_re = '/(?:^\n?|\n\n)*$/';
		$newline_after_re = 
			'{
				^						# Start of text following the tag.
				(?>[ ]*<!--.*?-->)?		# Optional comment.
				[ ]*\n					# Must be followed by newline.
			}xs';
		
				$block_tag_re =
			'{
				(					# $2: Capture whole tag.
					</?					# Any opening or closing tag.
						(?>				# Tag name.
							'.$this->block_tags_re.'			|
							'.$this->context_block_tags_re.'	|
							'.$this->clean_tags_re.'        	|
							(?!\s)'.$enclosing_tag_re.'
						)
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	# Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				# Anything but quotes and `>`.
							)*?
						)?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				'. ( !$span ? ' # If not in span.
				|
					# Indented code block
					(?: ^[ ]*\n | ^ | \n[ ]*\n )
					[ ]{'.($indent+4).'}[^\n]* \n
					(?>
						(?: [ ]{'.($indent+4).'}[^\n]* | [ ]* ) \n
					)*
				|
					# Fenced code block marker
					(?<= ^ | \n )
					[ ]{0,'.($indent+3).'}(?:~{3,}|`{3,})
					[ ]*
					(?: \.?[-_:a-zA-Z0-9]+ )? # standalone class name
					[ ]*
					(?: '.$this->id_class_attr_nocatch_re.' )? # extra attributes
					[ ]*
					(?= \n )
				' : '' ). ' # End (if not is span).
				|
					# Code span marker
					# Note, this regex needs to go after backtick fenced
					# code blocks but it should also be kept outside of the
					# "if not in span" condition adding backticks to the parser
					`+
				)
			}xs';

		
		$depth = 0;				$parsed = "";	
										do {
																					$parts = preg_split($block_tag_re, $text, 2, 
								PREG_SPLIT_DELIM_CAPTURE);
			
									if ($span) {
				$void = $this->hashPart("", ':');
				$newline = "$void\n";
				$parts[0] = $void . str_replace("\n", $newline, $parts[0]) . $void;
			}
			
			$parsed .= $parts[0]; 			
						if (count($parts) < 3) {
				$text = "";
				break;
			}
			
			$tag  = $parts[1]; 			$text = $parts[2]; 			$tag_re = preg_quote($tag); 			
																		if (preg_match('{^\n?([ ]{0,'.($indent+3).'})(~{3,}|`{3,})[ ]*(?:\.?[-_:a-zA-Z0-9]+)?[ ]*(?:'.$this->id_class_attr_nocatch_re.')?[ ]*\n?$}', $tag, $capture)) {
								$fence_indent = strlen($capture[1]); 				$fence_re = $capture[2]; 				if (preg_match('{^(?>.*\n)*?[ ]{'.($fence_indent).'}'.$fence_re.'[ ]*(?:\n|$)}', $text,
					$matches)) 
				{
										$parsed .= $tag . $matches[0];
					$text = substr($text, strlen($matches[0]));
				}
				else {
										$parsed .= $tag;
				}
			}
												else if ($tag{0} == "\n" || $tag{0} == " ") {
												$parsed .= $tag;
			}
															else if ($tag{0} == "`") {
								$tag_re = preg_quote($tag);
				if (preg_match('{^(?>.+?|\n(?!\n))*?(?<!`)'.$tag_re.'(?!`)}',
					$text, $matches))
				{
										$parsed .= $tag . $matches[0];
					$text = substr($text, strlen($matches[0]));
				}
				else {
										$parsed .= $tag;
				}
			}
																		else if (preg_match('{^<(?:'.$this->block_tags_re.')\b}', $tag) ||
				(	preg_match('{^<(?:'.$this->context_block_tags_re.')\b}', $tag) &&
					preg_match($newline_before_re, $parsed) &&
					preg_match($newline_after_re, $text)	)
				)
			{
								list($block_text, $text) = 
					$this->_hashHTMLBlocks_inHTML($tag . $text, "hashBlock", true);
				
								$parsed .= "\n\n$block_text\n\n";
			}
															else if (preg_match('{^<(?:'.$this->clean_tags_re.')\b}', $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
												list($block_text, $text) = 
					$this->_hashHTMLBlocks_inHTML($tag . $text, "hashClean", false);
				
				$parsed .= $block_text;
			}
												else if ($enclosing_tag_re !== '' &&
								preg_match('{^</?(?:'.$enclosing_tag_re.')\b}', $tag))
			{
																if ($tag{1} == '/')						$depth--;
				else if ($tag{strlen($tag)-2} != '/')	$depth++;

				if ($depth < 0) {
																									$text = $tag . $text;
					break;
				}
				
				$parsed .= $tag;
			}
			else {
				$parsed .= $tag;
			}
		} while ($depth >= 0);
		
		return array($parsed, $text);
	}
	protected function _hashHTMLBlocks_inHTML($text, $hash_method, $md_attr) {
												if ($text === '') return array('', '');
		
				$markdown_attr_re = '
			{
				\s*			# Eat whitespace before the `markdown` attribute
				markdown
				\s*=\s*
				(?>
					(["\'])		# $1: quote delimiter		
					(.*?)		# $2: attribute value
					\1			# matching delimiter	
				|
					([^\s>]*)	# $3: unquoted attribute value
				)
				()				# $4: make $3 always defined (avoid warnings)
			}xs';
		
				$tag_re = '{
				(					# $2: Capture whole tag.
					</?					# Any opening or closing tag.
						[\w:$]+			# Tag name.
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	# Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				# Anything but quotes and `>`.
							)*?
						)?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs';
		
		$original_text = $text;				
		$depth		= 0;			$block_text	= "";			$parsed		= "";	
										if (preg_match('/^<([\w:$]*)\b/', $text, $matches))
			$base_tag_name_re = $matches[1];

								do {
																					$parts = preg_split($tag_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			if (count($parts) < 3) {
																												return array($original_text{0}, substr($original_text, 1));
			}
			
			$block_text .= $parts[0]; 			$tag         = $parts[1]; 			$text        = $parts[2]; 			
															if (preg_match('{^</?(?:'.$this->auto_close_tags_re.')\b}', $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
								$block_text .= $tag;
			}
			else {
																				if (preg_match('{^</?'.$base_tag_name_re.'\b}', $tag)) {
					if ($tag{1} == '/')						$depth--;
					else if ($tag{strlen($tag)-2} != '/')	$depth++;
				}
				
																if ($md_attr && 
					preg_match($markdown_attr_re, $tag, $attr_m) &&
					preg_match('/^1|block|span$/', $attr_m[2] . $attr_m[3]))
				{
										$tag = preg_replace($markdown_attr_re, '', $tag);
					
										$this->mode = $attr_m[2] . $attr_m[3];
					$span_mode = $this->mode == 'span' || $this->mode != 'block' &&
						preg_match('{^<(?:'.$this->contain_span_tags_re.')\b}', $tag);
					
										if (preg_match('/(?:^|\n)( *?)(?! ).*?$/', $block_text, $matches)) {
						$strlen = $this->utf8_strlen;
						$indent = $strlen($matches[1], 'UTF-8');
					} else {
						$indent = 0;
					}
					
										$block_text .= $tag;
					$parsed .= $this->$hash_method($block_text);
					
															preg_match('/^<([\w:$]*)\b/', $tag, $matches);
					$tag_name_re = $matches[1];
					
										list ($block_text, $text)
						= $this->_hashHTMLBlocks_inMarkdown($text, $indent, 
							$tag_name_re, $span_mode);
					
										if ($indent > 0) {
						$block_text = preg_replace("/^[ ]{1,$indent}/m", "", 
													$block_text);
					}
					
										if (!$span_mode)	$parsed .= "\n\n$block_text\n\n";
					else				$parsed .= "$block_text";
					
										$block_text = "";
				}
				else $block_text .= $tag;
			}
			
		} while ($depth > 0);
		
								$parsed .= $this->$hash_method($block_text);
		
		return array($parsed, $text);
	}


	protected function hashClean($text) {
							return $this->hashPart($text, 'C');
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
			  (?:[ ]? '.$this->id_class_attr_catch_re.' )?	 # $8 = id/class attributes
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
			if (isset($this->ref_attr[$link_id]))
				$result .= $this->ref_attr[$link_id];
		
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
		$attr  = $this->doExtraAttributes("a", $dummy =& $matches[8]);

						$unhashed = $this->unhash($url);
		if ($unhashed != $url)
			$url = preg_replace('/^<(.*)>$/', '\1', $unhashed);

		$url = $this->encodeURLAttribute($url);

		$result = "<a href=\"$url\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\"";
		}
		$result .= $attr;
		
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
			  (?:[ ]? '.$this->id_class_attr_catch_re.' )?	 # $8 = id/class attributes
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
			if (isset($this->ref_attr[$link_id]))
				$result .= $this->ref_attr[$link_id];
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
		$attr  = $this->doExtraAttributes("img", $dummy =& $matches[8]);

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeURLAttribute($url);
		$result = "<img src=\"$url\" alt=\"$alt_text\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\""; 		}
		$result .= $attr;
		$result .= $this->empty_element_suffix;

		return $this->hashPart($result);
	}


	protected function doHeaders($text) {
																			$text = preg_replace_callback(
			'{
				(^.+?)								# $1: Header text
				(?:[ ]+ '.$this->id_class_attr_catch_re.' )?	 # $3 = id/class attributes
				[ ]*\n(=+|-+)[ ]*\n+				# $3: Header footer
			}mx',
			array($this, '_doHeaders_callback_setext'), $text);

																$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				(?:[ ]+ '.$this->id_class_attr_catch_re.' )?	 # $3 = id/class attributes
				[ ]*
				\n+
			}xm',
			array($this, '_doHeaders_callback_atx'), $text);

		return $text;
	}
	protected function _doHeaders_callback_setext($matches) {
		if ($matches[3] == '-' && preg_match('{^- }', $matches[1]))
			return $matches[0];

		$level = $matches[3]{0} == '=' ? 1 : 2;

		$defaultId = is_callable($this->header_id_func) ? call_user_func($this->header_id_func, $matches[1]) : null;

		$attr  = $this->doExtraAttributes("h$level", $dummy =& $matches[2], $defaultId);
		$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
	protected function _doHeaders_callback_atx($matches) {
		$level = strlen($matches[1]);

		$defaultId = is_callable($this->header_id_func) ? call_user_func($this->header_id_func, $matches[2]) : null;
		$attr  = $this->doExtraAttributes("h$level", $dummy =& $matches[3], $defaultId);
		$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}


	protected function doTables($text) {
					$less_than_tab = $this->tab_width - 1;
																		$text = preg_replace_callback('
			{
				^							# Start of a line
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				[|]							# Optional leading pipe (present)
				(.+) \n						# $1: Header row (at least one pipe)
				
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				[|] ([ ]*[-:]+[-| :]*) \n	# $2: Header underline
				
				(							# $3: Cells
					(?>
						[ ]*				# Allowed whitespace.
						[|] .* \n			# Row content.
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm',
			array($this, '_doTable_leadingPipe_callback'), $text);
		
																		$text = preg_replace_callback('
			{
				^							# Start of a line
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				(\S.*[|].*) \n				# $1: Header row (at least one pipe)
				
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				([-:]+[ ]*[|][-| :]*) \n	# $2: Header underline
				
				(							# $3: Cells
					(?>
						.* [|] .* \n		# Row content
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm',
			array($this, '_DoTable_callback'), $text);

		return $text;
	}
	protected function _doTable_leadingPipe_callback($matches) {
		$head		= $matches[1];
		$underline	= $matches[2];
		$content	= $matches[3];
		
				$content	= preg_replace('/^ *[|]/m', '', $content);
		
		return $this->_doTable_callback(array($matches[0], $head, $underline, $content));
	}
	protected function _doTable_makeAlignAttr($alignname)
	{
		if (empty($this->table_align_class_tmpl))
			return " align=\"$alignname\"";

		$classname = str_replace('%%', $alignname, $this->table_align_class_tmpl);
		return " class=\"$classname\"";
	}
	protected function _doTable_callback($matches) {
		$head		= $matches[1];
		$underline	= $matches[2];
		$content	= $matches[3];

				$head		= preg_replace('/[|] *$/m', '', $head);
		$underline	= preg_replace('/[|] *$/m', '', $underline);
		$content	= preg_replace('/[|] *$/m', '', $content);
		
				$separators	= preg_split('/ *[|] */', $underline);
		foreach ($separators as $n => $s) {
			if (preg_match('/^ *-+: *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('right');
			else if (preg_match('/^ *:-+: *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('center');
			else if (preg_match('/^ *:-+ *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('left');
			else
				$attr[$n] = '';
		}
		
						$head		= $this->parseSpan($head);
		$headers	= preg_split('/ *[|] */', $head);
		$col_count	= count($headers);
		$attr       = array_pad($attr, $col_count, '');
		
				$text = "<table>\n";
		$text .= "<thead>\n";
		$text .= "<tr>\n";
		foreach ($headers as $n => $header)
			$text .= "  <th$attr[$n]>".$this->runSpanGamut(trim($header))."</th>\n";
		$text .= "</tr>\n";
		$text .= "</thead>\n";
		
				$rows = explode("\n", trim($content, "\n"));
		
		$text .= "<tbody>\n";
		foreach ($rows as $row) {
									$row = $this->parseSpan($row);
			
						$row_cells = preg_split('/ *[|] */', $row, $col_count);
			$row_cells = array_pad($row_cells, $col_count, '');
			
			$text .= "<tr>\n";
			foreach ($row_cells as $n => $cell)
				$text .= "  <td$attr[$n]>".$this->runSpanGamut(trim($cell))."</td>\n";
			$text .= "</tr>\n";
		}
		$text .= "</tbody>\n";
		$text .= "</table>";
		
		return $this->hashBlock($text) . "\n";
	}

	
	protected function doDefLists($text) {
					$less_than_tab = $this->tab_width - 1;

				$whole_list_re = '(?>
			(								# $1 = whole list
			  (								# $2
				[ ]{0,'.$less_than_tab.'}
				((?>.*\S.*\n)+)				# $3 = defined term
				\n?
				[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
			  )
			  (?s:.+?)
			  (								# $4
				  \z
				|
				  \n{2,}
				  (?=\S)
				  (?!						# Negative lookahead for another term
					[ ]{0,'.$less_than_tab.'}
					(?: \S.*\n )+?			# defined term
					\n?
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
				  (?!						# Negative lookahead for another definition
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
			  )
			)
		)'; 
		$text = preg_replace_callback('{
				(?>\A\n?|(?<=\n\n))
				'.$whole_list_re.'
			}mx',
			array($this, '_doDefLists_callback'), $text);

		return $text;
	}
	protected function _doDefLists_callback($matches) {
				$list = $matches[1];
		
						$result = trim($this->processDefListItems($list));
		$result = "<dl>\n" . $result . "\n</dl>";
		return $this->hashBlock($result) . "\n\n";
	}


	protected function processDefListItems($list_str) {
						$less_than_tab = $this->tab_width - 1;
		
				$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

				$list_str = preg_replace_callback('{
			(?>\A\n?|\n\n+)					# leading line
			(								# definition terms = $1
				[ ]{0,'.$less_than_tab.'}	# leading whitespace
				(?!\:[ ]|[ ])				# negative lookahead for a definition
											#   mark (colon) or more whitespace.
				(?> \S.* \n)+?				# actual term (not whitespace).	
			)			
			(?=\n?[ ]{0,3}:[ ])				# lookahead for following line feed 
											#   with a definition mark.
			}xm',
			array($this, '_processDefListItems_callback_dt'), $list_str);

				$list_str = preg_replace_callback('{
			\n(\n+)?						# leading line = $1
			(								# marker space = $2
				[ ]{0,'.$less_than_tab.'}	# whitespace before colon
				\:[ ]+						# definition mark (colon)
			)
			((?s:.+?))						# definition text = $3
			(?= \n+ 						# stop at next definition mark,
				(?:							# next term or end of text
					[ ]{0,'.$less_than_tab.'} \:[ ]	|
					<dt> | \z
				)						
			)					
			}xm',
			array($this, '_processDefListItems_callback_dd'), $list_str);

		return $list_str;
	}
	protected function _processDefListItems_callback_dt($matches) {
		$terms = explode("\n", trim($matches[1]));
		$text = '';
		foreach ($terms as $term) {
			$term = $this->runSpanGamut(trim($term));
			$text .= "\n<dt>" . $term . "</dt>";
		}
		return $text . "\n";
	}
	protected function _processDefListItems_callback_dd($matches) {
		$leading_line	= $matches[1];
		$marker_space	= $matches[2];
		$def			= $matches[3];

		if ($leading_line || preg_match('/\n{2,}/', $def)) {
						$def = str_repeat(' ', strlen($marker_space)) . $def;
			$def = $this->runBlockGamut($this->outdent($def . "\n\n"));
			$def = "\n". $def ."\n";
		}
		else {
			$def = rtrim($def);
			$def = $this->runSpanGamut($this->outdent($def));
		}

		return "\n<dd>" . $def . "</dd>\n";
	}


	protected function doFencedCodeBlocks($text) {
									$less_than_tab = $this->tab_width;
		
		$text = preg_replace_callback('{
				(?:\n|\A)
				# 1: Opening marker
				(
					(?:~{3,}|`{3,}) # 3 or more tildes/backticks.
				)
				[ ]*
				(?:
					\.?([-_:a-zA-Z0-9]+) # 2: standalone class name
				)?
				[ ]*
				(?:
					'.$this->id_class_attr_catch_re.' # 3: Extra attributes
				)?
				[ ]* \n # Whitespace and newline following marker.
				
				# 4: Content
				(
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)
				
				# Closing marker.
				\1 [ ]* (?= \n )
			}xm',
			array($this, '_doFencedCodeBlocks_callback'), $text);

		return $text;
	}
	protected function _doFencedCodeBlocks_callback($matches) {
		$classname =& $matches[2];
		$attrs     =& $matches[3];
		$codeblock = $matches[4];

		if ($this->code_block_content_func) {
			$codeblock = call_user_func($this->code_block_content_func, $codeblock, $classname);
		} else {
			$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		}

		$codeblock = preg_replace_callback('/^\n+/',
			array($this, '_doFencedCodeBlocks_newlines'), $codeblock);

		$classes = array();
		if ($classname != "") {
			if ($classname{0} == '.')
				$classname = substr($classname, 1);
			$classes[] = $this->code_class_prefix.$classname;
		}
		$attr_str = $this->doExtraAttributes($this->code_attr_on_pre ? "pre" : "code", $attrs, null, $classes);
		$pre_attr_str  = $this->code_attr_on_pre ? $attr_str : '';
		$code_attr_str = $this->code_attr_on_pre ? '' : $attr_str;
		$codeblock  = "<pre$pre_attr_str><code$code_attr_str>$codeblock</code></pre>";
		
		return "\n\n".$this->hashBlock($codeblock)."\n\n";
	}
	protected function _doFencedCodeBlocks_newlines($matches) {
		return str_repeat("<br$this->empty_element_suffix", 
			strlen($matches[0]));
	}


					protected $em_relist = array(
		''  => '(?:(?<!\*)\*(?!\*)|(?<![a-zA-Z0-9_])_(?!_))(?![\.,:;]?\s)',
		'*' => '(?<![\s*])\*(?!\*)',
		'_' => '(?<![\s_])_(?![a-zA-Z0-9_])',
		);
	protected $strong_relist = array(
		''   => '(?:(?<!\*)\*\*(?!\*)|(?<![a-zA-Z0-9_])__(?!_))(?![\.,:;]?\s)',
		'**' => '(?<![\s*])\*\*(?!\*)',
		'__' => '(?<![\s_])__(?![a-zA-Z0-9_])',
		);
	protected $em_strong_relist = array(
		''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<![a-zA-Z0-9_])___(?!_))(?![\.,:;]?\s)',
		'***' => '(?<![\s*])\*\*\*(?!\*)',
		'___' => '(?<![\s_])___(?![a-zA-Z0-9_])',
		);


	protected function formParagraphs($text) {
								$text = preg_replace('/\A\n+|\n+\z/', '', $text);
		
		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

								foreach ($grafs as $key => $value) {
			$value = trim($this->runSpanGamut($value));
			
									$is_p = !preg_match('/^B\x1A[0-9]+B|^C\x1A[0-9]+C$/', $value);
			
			if ($is_p) {
				$value = "<p>$value</p>";
			}
			$grafs[$key] = $value;
		}
		
				$text = implode("\n\n", $grafs);
		
				$text = $this->unhash($text);
		
		return $text;
	}
	
	
		
	protected function stripFootnotes($text) {
						$less_than_tab = $this->tab_width - 1;

				$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\[\^(.+?)\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					# maybe *one* newline
			(						# text = $2 (no blank lines allowed)
				(?:					
					.+				# actual text
				|
					\n				# newlines but 
					(?!\[.+?\][ ]?:\s)# negative lookahead for footnote or link definition marker.
					(?!\n+[ ]{0,3}\S)# ensure line is not blank and followed 
									# by non-indented content
				)*
			)		
			}xm',
			array($this, '_stripFootnotes_callback'),
			$text);
		return $text;
	}
	protected function _stripFootnotes_callback($matches) {
		$note_id = $this->fn_id_prefix . $matches[1];
		$this->footnotes[$note_id] = $this->outdent($matches[2]);
		return ''; 	}


	protected function doFootnotes($text) {
						if (!$this->in_anchor) {
			$text = preg_replace('{\[\^(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
		}
		return $text;
	}

	
	protected function appendFootnotes($text) {
					$text = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', 
			array($this, '_appendFootnotes_callback'), $text);
	
		if (!empty($this->footnotes_ordered)) {
			$text .= "\n\n";
			$text .= "<div class=\"footnotes\">\n";
			$text .= "<hr". $this->empty_element_suffix ."\n";
			$text .= "<ol>\n\n";

			$attr = "";
			if ($this->fn_backlink_class != "") {
				$class = $this->fn_backlink_class;
				$class = $this->encodeAttribute($class);
				$attr .= " class=\"$class\"";
			}
			if ($this->fn_backlink_title != "") {
				$title = $this->fn_backlink_title;
				$title = $this->encodeAttribute($title);
				$attr .= " title=\"$title\"";
			}
			$backlink_text = $this->fn_backlink_html;
			$num = 0;
			
			while (!empty($this->footnotes_ordered)) {
				$footnote = reset($this->footnotes_ordered);
				$note_id = key($this->footnotes_ordered);
				unset($this->footnotes_ordered[$note_id]);
				$ref_count = $this->footnotes_ref_count[$note_id];
				unset($this->footnotes_ref_count[$note_id]);
				unset($this->footnotes[$note_id]);
				
				$footnote .= "\n"; 				$footnote = $this->runBlockGamut("$footnote\n");				
				$footnote = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}', 
					array($this, '_appendFootnotes_callback'), $footnote);
				
				$attr = str_replace("%%", ++$num, $attr);
				$note_id = $this->encodeAttribute($note_id);

								$backlink = "<a href=\"#fnref:$note_id\"$attr>$backlink_text</a>";
				for ($ref_num = 2; $ref_num <= $ref_count; ++$ref_num) {
					$backlink .= " <a href=\"#fnref$ref_num:$note_id\"$attr>$backlink_text</a>";
				}
								if (preg_match('{</p>$}', $footnote)) {
					$footnote = substr($footnote, 0, -4) . "&#160;$backlink</p>";
				} else {
					$footnote .= "\n\n<p>$backlink</p>";
				}
				
				$text .= "<li id=\"fn:$note_id\">\n";
				$text .= $footnote . "\n";
				$text .= "</li>\n\n";
			}
			
			$text .= "</ol>\n";
			$text .= "</div>";
		}
		return $text;
	}
	protected function _appendFootnotes_callback($matches) {
		$node_id = $this->fn_id_prefix . $matches[1];
		
						if (isset($this->footnotes[$node_id])) {
			$num =& $this->footnotes_numbers[$node_id];
			if (!isset($num)) {
												$this->footnotes_ordered[$node_id] = $this->footnotes[$node_id];
				$this->footnotes_ref_count[$node_id] = 1;
				$num = $this->footnote_counter++;
				$ref_count_mark = '';
			} else {
				$ref_count_mark = $this->footnotes_ref_count[$node_id] += 1;
			}

			$attr = "";
			if ($this->fn_link_class != "") {
				$class = $this->fn_link_class;
				$class = $this->encodeAttribute($class);
				$attr .= " class=\"$class\"";
			}
			if ($this->fn_link_title != "") {
				$title = $this->fn_link_title;
				$title = $this->encodeAttribute($title);
				$attr .= " title=\"$title\"";
			}
			
			$attr = str_replace("%%", $num, $attr);
			$node_id = $this->encodeAttribute($node_id);
			
			return
				"<sup id=\"fnref$ref_count_mark:$node_id\">".
				"<a href=\"#fn:$node_id\"$attr>$num</a>".
				"</sup>";
		}
		
		return "[^".$matches[1]."]";
	}
		
	
		
	protected function stripAbbreviations($text) {
					$less_than_tab = $this->tab_width - 1;

				$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\*\[(.+?)\][ ]?:	# abbr_id = $1
			(.*)					# text = $2 (no blank lines allowed)	
			}xm',
			array($this, '_stripAbbreviations_callback'),
			$text);
		return $text;
	}
	protected function _stripAbbreviations_callback($matches) {
		$abbr_word = $matches[1];
		$abbr_desc = $matches[2];
		if ($this->abbr_word_re)
			$this->abbr_word_re .= '|';
		$this->abbr_word_re .= preg_quote($abbr_word);
		$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		return ''; 	}
	
	
	protected function doAbbreviations($text) {
					if ($this->abbr_word_re) {
									$text = preg_replace_callback('{'.
				'(?<![\w\x1A])'.
				'(?:'.$this->abbr_word_re.')'.
				'(?![\w\x1A])'.
				'}', 
				array($this, '_doAbbreviations_callback'), $text);
		}
		return $text;
	}
	protected function _doAbbreviations_callback($matches) {
		$abbr = $matches[0];
		if (isset($this->abbr_desciptions[$abbr])) {
			$desc = $this->abbr_desciptions[$abbr];
			if (empty($desc)) {
				return $this->hashPart("<abbr>$abbr</abbr>");
			} else {
				$desc = $this->encodeAttribute($desc);
				return $this->hashPart("<abbr title=\"$desc\">$abbr</abbr>");
			}
		} else {
			return $matches[0];
		}
	}
}
