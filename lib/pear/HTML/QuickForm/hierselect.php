<?php


require_once('HTML/QuickForm/group.php');
require_once('HTML/QuickForm/select.php');


class HTML_QuickForm_hierselect extends HTML_QuickForm_group
{
    
    
    var $_options = array();

    
    var $_nbElements = 0;

    
    var $_js = '';

        
    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $separator=null) {
                HTML_QuickForm_element::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        if (isset($separator)) {
            $this->_separator = $separator;
        }
        $this->_type = 'hierselect';
        $this->_appendName = true;
    } 
    
    public function HTML_QuickForm_hierselect($elementName=null, $elementLabel=null, $attributes=null, $separator=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $separator);
    }

        
    
    function setOptions($options)
    {
        $this->_options = $options;

        if (empty($this->_elements)) {
            $this->_nbElements = count($this->_options);
            $this->_createElements();
        } else {
                                    $totalNbElements = count($this->_options);
            for ($i = $this->_nbElements; $i < $totalNbElements; $i ++) {
                $this->_elements[] = new HTML_QuickForm_select($i, null, array(), $this->getAttributes());
                $this->_nbElements++;
            }
        }

        $this->_setOptions();
    } 
        
    
    function setMainOptions($array)
    {
        $this->_options[0] = $array;

        if (empty($this->_elements)) {
            $this->_nbElements = 2;
            $this->_createElements();
        }
    } 
        
    
    function setSecOptions($array)
    {
        $this->_options[1] = $array;

        if (empty($this->_elements)) {
            $this->_nbElements = 2;
            $this->_createElements();
        } else {
                                    $totalNbElements = 2;
            for ($i = $this->_nbElements; $i < $totalNbElements; $i ++) {
                $this->_elements[] = new HTML_QuickForm_select($i, null, array(), $this->getAttributes());
                $this->_nbElements++;
            }
        }

        $this->_setOptions();
    } 
        
    
    function _setOptions()
    {
        $toLoad = '';
        foreach (array_keys($this->_elements) AS $key) {
            $array = eval("return isset(\$this->_options[{$key}]{$toLoad})? \$this->_options[{$key}]{$toLoad}: null;");
            if (is_array($array)) {
                $select =& $this->_elements[$key];
                $select->_options = array();
                $select->loadArray($array);

                $value  = is_array($v = $select->getValue()) ? $v[0] : key($array);
                $toLoad .= '[\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $value) . '\']';
            }
        }
    } 
        
    
    function setValue($value)
    {
                                $this->_nbElements = max($this->_nbElements, count($value));
        parent::setValue($value);
        $this->_setOptions();
    } 
        
    
    function _createElements()
    {
        for ($i = 0; $i < $this->_nbElements; $i++) {
            $this->_elements[] = new HTML_QuickForm_select($i, null, array(), $this->getAttributes());
        }
    } 
        
    function toHtml()
    {
        $this->_js = '';
        if (!$this->_flagFrozen) {
                        $keys     = array_keys($this->_elements);
            $onChange = array();
            for ($i = 0; $i < count($keys) - 1; $i++) {
                $select =& $this->_elements[$keys[$i]];
                $onChange[$i] = $select->getAttribute('onchange');
                $select->updateAttributes(
                    array('onchange' => '_hs_swapOptions(this.form, \'' . $this->_escapeString($this->getName()) . '\', ' . $keys[$i] . ');' . $onChange[$i])
                );
            }

                        if (!defined('HTML_QUICKFORM_HIERSELECT_EXISTS')) {
                $this->_js .= <<<JAVASCRIPT
function _hs_findOptions(ary, keys)
{
    var key = keys.shift();
    if (!key in ary) {
        return {};
    } else if (0 == keys.length) {
        return ary[key];
    } else {
        return _hs_findOptions(ary[key], keys);
    }
}

function _hs_findSelect(form, groupName, selectIndex)
{
    if (groupName+'['+ selectIndex +']' in form) {
        return form[groupName+'['+ selectIndex +']'];
    } else {
        return form[groupName+'['+ selectIndex +'][]'];
    }
}

function _hs_unescapeEntities(str)
{
    var div = document.createElement('div');
    div.innerHTML = str;
    return div.childNodes[0] ? div.childNodes[0].nodeValue : '';
}

function _hs_replaceOptions(ctl, optionList)
{
    var j = 0;
    ctl.options.length = 0;
    for (i in optionList) {
        var optionText = (-1 == optionList[i].indexOf('&'))? optionList[i]: _hs_unescapeEntities(optionList[i]);
        ctl.options[j++] = new Option(optionText, i, false, false);
    }
}

function _hs_setValue(ctl, value)
{
    var testValue = {};
    if (value instanceof Array) {
        for (var i = 0; i < value.length; i++) {
            testValue[value[i]] = true;
        }
    } else {
        testValue[value] = true;
    }
    for (var i = 0; i < ctl.options.length; i++) {
        if (ctl.options[i].value in testValue) {
            ctl.options[i].selected = true;
        }
    }
}

function _hs_swapOptions(form, groupName, selectIndex)
{
    var hsValue = [];
    for (var i = 0; i <= selectIndex; i++) {
        hsValue[i] = _hs_findSelect(form, groupName, i).value;
    }

    _hs_replaceOptions(_hs_findSelect(form, groupName, selectIndex + 1),
                       _hs_findOptions(_hs_options[groupName][selectIndex], hsValue));
    if (selectIndex + 1 < _hs_options[groupName].length) {
        _hs_swapOptions(form, groupName, selectIndex + 1);
    }
}

function _hs_onReset(form, groupNames)
{
    for (var i = 0; i < groupNames.length; i++) {
        try {
            for (var j = 0; j <= _hs_options[groupNames[i]].length; j++) {
                _hs_setValue(_hs_findSelect(form, groupNames[i], j), _hs_defaults[groupNames[i]][j]);
                if (j < _hs_options[groupNames[i]].length) {
                    _hs_replaceOptions(_hs_findSelect(form, groupNames[i], j + 1),
                                       _hs_findOptions(_hs_options[groupNames[i]][j], _hs_defaults[groupNames[i]].slice(0, j + 1)));
                }
            }
        } catch (e) {
            if (!(e instanceof TypeError)) {
                throw e;
            }
        }
    }
}

function _hs_setupOnReset(form, groupNames)
{
    setTimeout(function() { _hs_onReset(form, groupNames); }, 25);
}

function _hs_onReload()
{
    var ctl;
    for (var i = 0; i < document.forms.length; i++) {
        for (var j in _hs_defaults) {
            if (ctl = _hs_findSelect(document.forms[i], j, 0)) {
                for (var k = 0; k < _hs_defaults[j].length; k++) {
                    _hs_setValue(_hs_findSelect(document.forms[i], j, k), _hs_defaults[j][k]);
                }
            }
        }
    }

    if (_hs_prevOnload) {
        _hs_prevOnload();
    }
}

var _hs_prevOnload = null;
if (window.onload) {
    _hs_prevOnload = window.onload;
}
window.onload = _hs_onReload;

var _hs_options = {};
var _hs_defaults = {};

JAVASCRIPT;
                define('HTML_QUICKFORM_HIERSELECT_EXISTS', true);
            }
                        $jsParts = array();
            for ($i = 1; $i < $this->_nbElements; $i++) {
                $jsParts[] = $this->_convertArrayToJavascript($this->_options[$i]);
            }
            $this->_js .= "\n_hs_options['" . $this->_escapeString($this->getName()) . "'] = [\n" .
                          implode(",\n", $jsParts) .
                          "\n];\n";
                                    $values = array();
            foreach (array_keys($this->_elements) as $key) {
                if (is_array($v = $this->_elements[$key]->getValue())) {
                    $values[] = count($v) > 1? $v: $v[0];
                } else {
                                        $values[] = $this->_elements[$key]->getMultiple() || empty($this->_elements[$key]->_options[0])?
                                array():
                                $this->_elements[$key]->_options[0]['attr']['value'];
                }
            }
            $this->_js .= "_hs_defaults['" . $this->_escapeString($this->getName()) . "'] = " .
                          $this->_convertArrayToJavascript($values, false) . ";\n";
        }
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);

        if (!empty($onChange)) {
            $keys     = array_keys($this->_elements);
            for ($i = 0; $i < count($keys) - 1; $i++) {
                $this->_elements[$keys[$i]]->updateAttributes(array('onchange' => $onChange[$i]));
            }
        }
        return (empty($this->_js)? '': "<script type=\"text/javascript\">\n//<![CDATA[\n" . $this->_js . "//]]>\n</script>") .
               $renderer->toHtml();
    } 
        
    function accept(&$renderer, $required = false, $error = null)
    {
        $renderer->renderElement($this, $required, $error);
    } 
        
    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' == $event) {
                                    return HTML_QuickForm_element::onQuickFormEvent($event, $arg, $caller);
        } else {
            $ret = parent::onQuickFormEvent($event, $arg, $caller);
                        if ('addElement' == $event) {
                $onReset = $caller->getAttribute('onreset');
                if (strlen($onReset)) {
                    if (strpos($onReset, '_hs_setupOnReset')) {
                        $caller->updateAttributes(array('onreset' => str_replace('_hs_setupOnReset(this, [', "_hs_setupOnReset(this, ['" . $this->_escapeString($this->getName()) . "', ", $onReset)));
                    } else {
                        $caller->updateAttributes(array('onreset' => "var temp = function() { {$onReset} } ; if (!temp()) { return false; } ; if (typeof _hs_setupOnReset != 'undefined') { return _hs_setupOnReset(this, ['" . $this->_escapeString($this->getName()) . "']); } "));
                    }
                } else {
                    $caller->updateAttributes(array('onreset' => "if (typeof _hs_setupOnReset != 'undefined') { return _hs_setupOnReset(this, ['" . $this->_escapeString($this->getName()) . "']); } "));
                }
            }
            return $ret;
        }
    } 
        
   
    function _convertArrayToJavascript($array, $assoc = true)
    {
        if (!is_array($array)) {
            return $this->_convertScalarToJavascript($array);
        } else {
            $items = array();
            foreach ($array as $key => $val) {
                $item = $assoc? "'" . $this->_escapeString($key) . "': ": '';
                if (is_array($val)) {
                    $item .= $this->_convertArrayToJavascript($val, $assoc);
                } else {
                    $item .= $this->_convertScalarToJavascript($val);
                }
                $items[] = $item;
            }
        }
        $js = implode(', ', $items);
        return $assoc? '{ ' . $js . ' }': '[' . $js . ']';
    }

        
   
    function _convertScalarToJavascript($val)
    {
        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        } elseif (is_int($val) || is_double($val)) {
            return $val;
        } elseif (is_string($val)) {
            return "'" . $this->_escapeString($val) . "'";
        } elseif (is_null($val)) {
            return 'null';
        } else {
                        return '{}';
        }
    }

        
   
    function _escapeString($str)
    {
        return strtr($str,array(
            "\r"    => '\r',
            "\n"    => '\n',
            "\t"    => '\t',
            "'"     => "\\'",
            '"'     => '\"',
            '\\'    => '\\\\'
        ));
    }

    } ?>