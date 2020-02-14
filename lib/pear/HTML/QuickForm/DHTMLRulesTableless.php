<?php


require_once 'HTML/QuickForm.php';


class HTML_QuickForm_DHTMLRulesTableless extends HTML_QuickForm {
    
    
    function getValidationScript()
    {
        if (empty($this->_rules) || empty($this->_attributes['onsubmit'])) {
            return '';
        }

        include_once('HTML/QuickForm/RuleRegistry.php');
        $registry =& HTML_QuickForm_RuleRegistry::singleton();
        $test = array();
        $js_escape = array(
            "\r"    => '\r',
            "\n"    => '\n',
            "\t"    => '\t',
            "'"     => "\\'",
            '"'     => '\"',
            '\\'    => '\\\\'
        );

        foreach ($this->_rules as $elementName => $rules) {
            foreach ($rules as $rule) {
                if ('client' == $rule['validation']) {
                    unset($element);

                    $dependent  = isset($rule['dependent']) && is_array($rule['dependent']);
                    $rule['message'] = strtr($rule['message'], $js_escape);

                    if (isset($rule['group'])) {
                        $group    =& $this->getElement($rule['group']);
                                                if ($group->isFrozen()) {
                            continue 2;
                        }
                        $elements =& $group->getElements();
                        foreach (array_keys($elements) as $key) {
                            if ($elementName == $group->getElementName($key)) {
                                $element =& $elements[$key];
                                break;
                            }
                        }
                    } elseif ($dependent) {
                        $element   =  array();
                        $element[] =& $this->getElement($elementName);
                        foreach ($rule['dependent'] as $idx => $elName) {
                            $element[] =& $this->getElement($elName);
                        }
                    } else {
                        $element =& $this->getElement($elementName);
                    }
                                        if (is_object($element) && $element->isFrozen()) {
                        continue 2;
                    } elseif (is_array($element)) {
                        foreach (array_keys($element) as $key) {
                            if ($element[$key]->isFrozen()) {
                                continue 3;
                            }
                        }
                    }

                    $test[$elementName][] = $registry->getValidationScript($element, $elementName, $rule);
                }
            }
        }
        $js = '
<script type="text/javascript">
//<![CDATA[
function qf_errorHandler(element, _qfMsg) {
  div = element.parentNode;
  if (_qfMsg != \'\') {
    span = document.createElement("span");
    span.className = "error";
    span.appendChild(document.createTextNode(_qfMsg.substring(3)));
    br = document.createElement("br");

    var errorDiv = document.getElementById(element.name + \'_errorDiv\');
    if (!errorDiv) {
      errorDiv = document.createElement("div");
      errorDiv.id = element.name + \'_errorDiv\';
    }
    while (errorDiv.firstChild) {
      errorDiv.removeChild(errorDiv.firstChild);
    }
    
    errorDiv.insertBefore(br, errorDiv.firstChild);
    errorDiv.insertBefore(span, errorDiv.firstChild);
    element.parentNode.insertBefore(errorDiv, element.parentNode.firstChild);

    if (div.className.substr(div.className.length - 6, 6) != " error"
        && div.className != "error") {
      div.className += " error";
    }

    return false;
  } else {
    var errorDiv = document.getElementById(element.name + \'_errorDiv\');
    if (errorDiv) {
      errorDiv.parentNode.removeChild(errorDiv);
    }

    if (div.className.substr(div.className.length - 6, 6) == " error") {
      div.className = div.className.substr(0, div.className.length - 6);
    } else if (div.className == "error") {
      div.className = "";
    }

    return true;
  }
}';
        $validateJS = '';
        foreach ($test as $elementName => $jsArr) {
            $js .= '
function validate_' . $this->_attributes['id'] . '_' . $elementName . '(element) {
  var value = \'\';
  var errFlag = new Array();
  var _qfGroups = {};
  var _qfMsg = \'\';
  var frm = element.parentNode;
  while (frm && frm.nodeName != "FORM") {
    frm = frm.parentNode;
  }
' . join("\n", $jsArr) . '
  return qf_errorHandler(element, _qfMsg);
}
';
            $validateJS .= '
  ret = validate_' . $this->_attributes['id'] . '_' . $elementName.'(frm.elements[\''.$elementName.'\']) && ret;';
            unset($element);
            $element =& $this->getElement($elementName);
            $valFunc = 'validate_' . $this->_attributes['id'] . '_' . $elementName . '(this)';
            $onBlur = $element->getAttribute('onBlur');
            $onChange = $element->getAttribute('onChange');
            $element->updateAttributes(array('onBlur' => $onBlur . $valFunc,
                                             'onChange' => $onChange . $valFunc));
        }
        $js .= '
function validate_' . $this->_attributes['id'] . '(frm) {
  var ret = true;
' . $validateJS . ';
  return ret;
}
//]]>
</script>';
        return $js;
    } 
    }

?>
