<?php



require_once("HTML/QuickForm/text.php");



class HTML_QuickForm_autocomplete extends HTML_QuickForm_text
{
    
    
    var $_options = array();

    
    var $_js = '';

        
    
    public function __construct($elementName = null, $elementLabel = null, $options = null, $attributes = null) {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'autocomplete';
        if (isset($options)) {
            $this->setOptions($options);
        }
    } 
    
    public function HTML_QuickForm_autocomplete($elementName = null, $elementLabel = null, $options = null, $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

        
    
    function setOptions($options)
    {
        $this->_options = array_values($options);
    } 
        
    
    function toHtml()
    {
                $arrayName = str_replace(array('[', ']'), array('__', ''), $this->getName()) . '_values';

        $this->updateAttributes(array(
            'onkeypress' => 'return autocomplete(this, event, ' . $arrayName . ');'
        ));
        if ($this->_flagFrozen) {
            $js = '';
        } else {
            $js = "<script type=\"text/javascript\">\n//<![CDATA[\n";
            if (!defined('HTML_QUICKFORM_AUTOCOMPLETE_EXISTS')) {
                $this->_js .= <<<EOS

/* begin javascript for autocomplete */
function setSelectionRange(input, selectionStart, selectionEnd) {
    if (input.setSelectionRange) {
        input.setSelectionRange(selectionStart, selectionEnd);
    }
    else if (input.createTextRange) {
        var range = input.createTextRange();
        range.collapse(true);
        range.moveEnd("character", selectionEnd);
        range.moveStart("character", selectionStart);
        range.select();
    }
    input.focus();
}

function setCaretToPosition(input, position) {
    setSelectionRange(input, position, position);
}

function replaceSelection (input, replaceString) {
	var len = replaceString.length;
    if (input.setSelectionRange) {
        var selectionStart = input.selectionStart;
        var selectionEnd = input.selectionEnd;

        input.value = input.value.substring(0, selectionStart) + replaceString + input.value.substring(selectionEnd);
		input.selectionStart  = selectionStart + len;
		input.selectionEnd  = selectionStart + len;
    }
    else if (document.selection) {
        var range = document.selection.createRange();
		var saved_range = range.duplicate();

        if (range.parentElement() == input) {
            range.text = replaceString;
			range.moveEnd("character", saved_range.selectionStart + len);
			range.moveStart("character", saved_range.selectionStart + len);
			range.select();
        }
    }
    input.focus();
}


function autocompleteMatch (text, values) {
    for (var i = 0; i < values.length; i++) {
        if (values[i].toUpperCase().indexOf(text.toUpperCase()) == 0) {
            return values[i];
        }
    }

    return null;
}

function autocomplete(textbox, event, values) {
    if (textbox.setSelectionRange || textbox.createTextRange) {
        switch (event.keyCode) {
            case 38:    // up arrow
            case 40:    // down arrow
            case 37:    // left arrow
            case 39:    // right arrow
            case 33:    // page up
            case 34:    // page down
            case 36:    // home
            case 35:    // end
            case 13:    // enter
            case 9:     // tab
            case 27:    // esc
            case 16:    // shift
            case 17:    // ctrl
            case 18:    // alt
            case 20:    // caps lock
            case 8:     // backspace
            case 46:    // delete
                return true;
                break;

            default:
                var c = String.fromCharCode(
                    (event.charCode == undefined) ? event.keyCode : event.charCode
                );
                replaceSelection(textbox, c);
                sMatch = autocompleteMatch(textbox.value, values);
                var len = textbox.value.length;
				
                if (sMatch != null) {
                    textbox.value = sMatch;
                    setSelectionRange(textbox, len, textbox.value.length);
                }
                return false;
        }
    }
    else {
        return true;
    }
}
/* end javascript for autocomplete */

EOS;
                define('HTML_QUICKFORM_AUTOCOMPLETE_EXISTS', true);
            }
            $jsEscape = array(
                "\r"    => '\r',
                "\n"    => '\n',
                "\t"    => '\t',
                "'"     => "\\'",
                '"'     => '\"',
                '\\'    => '\\\\'
            );

            $js .= $this->_js;
            $js .= 'var ' . $arrayName . " = new Array();\n";
            for ($i = 0; $i < count($this->_options); $i++) {
                $js .= $arrayName . '[' . $i . "] = '" . strtr($this->_options[$i], $jsEscape) . "';\n";
            }
            $js .= "//]]>\n</script>";
        }
        return $js . parent::toHtml();
    }
    } ?>
