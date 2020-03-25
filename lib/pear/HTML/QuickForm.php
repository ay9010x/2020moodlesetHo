<?php


require_once('PEAR.php');
require_once('HTML/Common.php');

$GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'] =
        array(
            'group'         =>array('HTML/QuickForm/group.php','HTML_QuickForm_group'),
            'hidden'        =>array('HTML/QuickForm/hidden.php','HTML_QuickForm_hidden'),
            'reset'         =>array('HTML/QuickForm/reset.php','HTML_QuickForm_reset'),
            'checkbox'      =>array('HTML/QuickForm/checkbox.php','HTML_QuickForm_checkbox'),
            'file'          =>array('HTML/QuickForm/file.php','HTML_QuickForm_file'),
            'image'         =>array('HTML/QuickForm/image.php','HTML_QuickForm_image'),
            'password'      =>array('HTML/QuickForm/password.php','HTML_QuickForm_password'),
            'radio'         =>array('HTML/QuickForm/radio.php','HTML_QuickForm_radio'),
            'button'        =>array('HTML/QuickForm/button.php','HTML_QuickForm_button'),
            'submit'        =>array('HTML/QuickForm/submit.php','HTML_QuickForm_submit'),
            'select'        =>array('HTML/QuickForm/select.php','HTML_QuickForm_select'),
            'hiddenselect'  =>array('HTML/QuickForm/hiddenselect.php','HTML_QuickForm_hiddenselect'),
            'text'          =>array('HTML/QuickForm/text.php','HTML_QuickForm_text'),
            'textarea'      =>array('HTML/QuickForm/textarea.php','HTML_QuickForm_textarea'),
            'link'          =>array('HTML/QuickForm/link.php','HTML_QuickForm_link'),
            'advcheckbox'   =>array('HTML/QuickForm/advcheckbox.php','HTML_QuickForm_advcheckbox'),
            'date'          =>array('HTML/QuickForm/date.php','HTML_QuickForm_date'),
            'static'        =>array('HTML/QuickForm/static.php','HTML_QuickForm_static'),
            'header'        =>array('HTML/QuickForm/header.php', 'HTML_QuickForm_header'),
            'html'          =>array('HTML/QuickForm/html.php', 'HTML_QuickForm_html'),
            'hierselect'    =>array('HTML/QuickForm/hierselect.php', 'HTML_QuickForm_hierselect'),
            'autocomplete'  =>array('HTML/QuickForm/autocomplete.php', 'HTML_QuickForm_autocomplete'),
            'xbutton'       =>array('HTML/QuickForm/xbutton.php','HTML_QuickForm_xbutton')
        );

$GLOBALS['_HTML_QuickForm_registered_rules'] = array(
    'required'      => array('html_quickform_rule_required', 'HTML/QuickForm/Rule/Required.php'),
    'maxlength'     => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'minlength'     => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'rangelength'   => array('html_quickform_rule_range',    'HTML/QuickForm/Rule/Range.php'),
    'email'         => array('html_quickform_rule_email',    'HTML/QuickForm/Rule/Email.php'),
    'regex'         => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'lettersonly'   => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'alphanumeric'  => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'numeric'       => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'nopunctuation' => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'nonzero'       => array('html_quickform_rule_regex',    'HTML/QuickForm/Rule/Regex.php'),
    'callback'      => array('html_quickform_rule_callback', 'HTML/QuickForm/Rule/Callback.php'),
    'compare'       => array('html_quickform_rule_compare',  'HTML/QuickForm/Rule/Compare.php')
);




define('QUICKFORM_OK',                      1);
define('QUICKFORM_ERROR',                  -1);
define('QUICKFORM_INVALID_RULE',           -2);
define('QUICKFORM_NONEXIST_ELEMENT',       -3);
define('QUICKFORM_INVALID_FILTER',         -4);
define('QUICKFORM_UNREGISTERED_ELEMENT',   -5);
define('QUICKFORM_INVALID_ELEMENT_NAME',   -6);
define('QUICKFORM_INVALID_PROCESS',        -7);
define('QUICKFORM_DEPRECATED',             -8);
define('QUICKFORM_INVALID_DATASOURCE',     -9);



class HTML_QuickForm extends HTML_Common {
    
    
    var $_elements = array();

    
    var $_elementIndex = array();

    
    var $_duplicateIndex = array();

    
    var $_required = array();

    
    var $_jsPrefix = 'Invalid information entered.';

    
    var $_jsPostfix = 'Please correct these fields.';

    
    var $_datasource;

    
    var $_defaultValues = array();

    
    var $_constantValues = array();

    
    var $_submitValues = array();

    
    var $_submitFiles = array();

    
    var $_maxFileSize = 1048576; 
    
    var $_freezeAll = false;

    
    var $_rules = array();

    
    var $_formRules = array();

    
    var $_errors = array();

    
    var $_requiredNote = '<span style="font-size:80%; color:#ff0000;">*</span><span style="font-size:80%;"> denotes required field</span>';

    
    var $_flagSubmitted = false;

        
    
    public function __construct($formName='', $method='post', $action='', $target='', $attributes=null, $trackSubmit = false)
    {
        parent::__construct($attributes);
        $method = (strtoupper($method) == 'GET') ? 'get' : 'post';
        $action = ($action == '') ? $_SERVER['PHP_SELF'] : $action;
        $target = empty($target) ? array() : array('target' => $target);
        $attributes = array('action'=>$action, 'method'=>$method, 'name'=>$formName, 'id'=>$formName) + $target;
        $this->updateAttributes($attributes);
        if (!$trackSubmit || isset($_REQUEST['_qf__' . $formName])) {
            if (1 == get_magic_quotes_gpc()) {
                $this->_submitValues = ('get' == $method? $_GET: $_POST);                 foreach ($_FILES as $keyFirst => $valFirst) {
                    foreach ($valFirst as $keySecond => $valSecond) {
                        if ('name' == $keySecond) {
                            $this->_submitFiles[$keyFirst][$keySecond] = $valSecond;                         } else {
                            $this->_submitFiles[$keyFirst][$keySecond] = $valSecond;
                        }
                    }
                }
            } else {
                $this->_submitValues = 'get' == $method? $_GET: $_POST;
                $this->_submitFiles  = $_FILES;
            }
            $this->_flagSubmitted = count($this->_submitValues) > 0 || count($this->_submitFiles) > 0;
        }
        if ($trackSubmit) {
            unset($this->_submitValues['_qf__' . $formName]);
            $this->addElement('hidden', '_qf__' . $formName, null);
        }
        if (preg_match('/^([0-9]+)([a-zA-Z]*)$/', ini_get('upload_max_filesize'), $matches)) {
                        switch (strtoupper($matches['2'])) {
                case 'G':
                    $this->_maxFileSize = $matches['1'] * 1073741824;
                    break;
                case 'M':
                    $this->_maxFileSize = $matches['1'] * 1048576;
                    break;
                case 'K':
                    $this->_maxFileSize = $matches['1'] * 1024;
                    break;
                default:
                    $this->_maxFileSize = $matches['1'];
            }
        }
    } 
    
    public function HTML_QuickForm($formName='', $method='post', $action='', $target='', $attributes=null, $trackSubmit = false) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($formName, $method, $action, $target, $attributes, $trackSubmit);
    }

        
    
    function apiVersion()
    {
        return 3.2;
    } 
        
    
    static function registerElementType($typeName, $include, $className)
    {
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][strtolower($typeName)] = array($include, $className);
    } 
        
    
    static function registerRule($ruleName, $type, $data1, $data2 = null)
    {
        include_once('HTML/QuickForm/RuleRegistry.php');
        $registry =& HTML_QuickForm_RuleRegistry::singleton();
        $registry->registerRule($ruleName, $type, $data1, $data2);
    } 
        
    
    function elementExists($element=null)
    {
        return isset($this->_elementIndex[$element]);
    } 
        
    
    function setDatasource(&$datasource, $defaultsFilter = null, $constantsFilter = null)
    {
        if (is_object($datasource)) {
            $this->_datasource =& $datasource;
            if (is_callable(array($datasource, 'defaultValues'))) {
                $this->setDefaults($datasource->defaultValues($this), $defaultsFilter);
            }
            if (is_callable(array($datasource, 'constantValues'))) {
                $this->setConstants($datasource->constantValues($this), $constantsFilter);
            }
        } else {
            return self::raiseError(null, QUICKFORM_INVALID_DATASOURCE, null, E_USER_WARNING, "Datasource is not an object in QuickForm::setDatasource()", 'HTML_QuickForm_Error', true);
        }
    } 
        
    
    function setDefaults($defaultValues = null, $filter = null)
    {
        if (is_array($defaultValues)) {
            if (isset($filter)) {
                if (is_array($filter) && (2 != count($filter) || !is_callable($filter))) {
                    foreach ($filter as $val) {
                        if (!is_callable($val)) {
                            return self::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setDefaults()", 'HTML_QuickForm_Error', true);
                        } else {
                            $defaultValues = $this->_recursiveFilter($val, $defaultValues);
                        }
                    }
                } elseif (!is_callable($filter)) {
                    return self::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setDefaults()", 'HTML_QuickForm_Error', true);
                } else {
                    $defaultValues = $this->_recursiveFilter($filter, $defaultValues);
                }
            }
            $this->_defaultValues = HTML_QuickForm::arrayMerge($this->_defaultValues, $defaultValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    } 
        
    
    function setConstants($constantValues = null, $filter = null)
    {
        if (is_array($constantValues)) {
            if (isset($filter)) {
                if (is_array($filter) && (2 != count($filter) || !is_callable($filter))) {
                    foreach ($filter as $val) {
                        if (!is_callable($val)) {
                            return self::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setConstants()", 'HTML_QuickForm_Error', true);
                        } else {
                            $constantValues = $this->_recursiveFilter($val, $constantValues);
                        }
                    }
                } elseif (!is_callable($filter)) {
                    return self::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::setConstants()", 'HTML_QuickForm_Error', true);
                } else {
                    $constantValues = $this->_recursiveFilter($filter, $constantValues);
                }
            }
            $this->_constantValues = HTML_QuickForm::arrayMerge($this->_constantValues, $constantValues);
            foreach (array_keys($this->_elements) as $key) {
                $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
            }
        }
    } 
        
    
    function setMaxFileSize($bytes = 0)
    {
        if ($bytes > 0) {
            $this->_maxFileSize = $bytes;
        }
        if (!$this->elementExists('MAX_FILE_SIZE')) {
            $this->addElement('hidden', 'MAX_FILE_SIZE', $this->_maxFileSize);
        } else {
            $el =& $this->getElement('MAX_FILE_SIZE');
            $el->updateAttributes(array('value' => $this->_maxFileSize));
        }
    } 
        
    
    function getMaxFileSize()
    {
        return $this->_maxFileSize;
    } 
        
    
    function &createElement($elementType)
    {
        $args    =  func_get_args();
        $element =& HTML_QuickForm::_loadElement('createElement', $elementType, array_slice($args, 1));
        return $element;
    } 
        
    
    function &_loadElement($event, $type, $args)
    {
        $type = strtolower($type);
        if (!HTML_QuickForm::isTypeRegistered($type)) {
            $error = self::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, "Element '$type' does not exist in HTML_QuickForm::_loadElement()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $className = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][1];
        $includeFile = $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][$type][0];
        include_once($includeFile);
        $elementObject = new $className();         for ($i = 0; $i < 5; $i++) {
            if (!isset($args[$i])) {
                $args[$i] = null;
            }
        }
        $err = $elementObject->onQuickFormEvent($event, $args, $this);
        if ($err !== true) {
            return $err;
        }
        return $elementObject;
    } 
        
    
    function &addElement($element)
    {
        if (is_object($element) && is_subclass_of($element, 'html_quickform_element')) {
           $elementObject = &$element;
           $elementObject->onQuickFormEvent('updateValue', null, $this);
        } else {
            $args = func_get_args();
            $elementObject =& $this->_loadElement('addElement', $element, array_slice($args, 1));
            $pear = new PEAR();
            if ($pear->isError($elementObject)) {
                return $elementObject;
            }
        }
        $elementName = $elementObject->getName();

                if (!empty($elementName) && isset($this->_elementIndex[$elementName])) {
            if ($this->_elements[$this->_elementIndex[$elementName]]->getType() ==
                $elementObject->getType()) {
                $this->_elements[] =& $elementObject;
                $elKeys = array_keys($this->_elements);
                $this->_duplicateIndex[$elementName][] = end($elKeys);
            } else {
                $error = self::raiseError(null, QUICKFORM_INVALID_ELEMENT_NAME, null, E_USER_WARNING, "Element '$elementName' already exists in HTML_QuickForm::addElement()", 'HTML_QuickForm_Error', true);
                return $error;
            }
        } else {
            $this->_elements[] =& $elementObject;
            $elKeys = array_keys($this->_elements);
            $this->_elementIndex[$elementName] = end($elKeys);
        }
        if ($this->_freezeAll) {
            $elementObject->freeze();
        }

        return $elementObject;
    } 
        
   
    function &insertElementBefore(&$element, $nameAfter)
    {
        if (!empty($this->_duplicateIndex[$nameAfter])) {
            $error = self::raiseError(null, QUICKFORM_INVALID_ELEMENT_NAME, null, E_USER_WARNING, 'Several elements named "' . $nameAfter . '" exist in HTML_QuickForm::insertElementBefore().', 'HTML_QuickForm_Error', true);
            return $error;
        } elseif (!$this->elementExists($nameAfter)) {
            $error = self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$nameAfter' does not exist in HTML_QuickForm::insertElementBefore()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $elementName = $element->getName();
        $targetIdx   = $this->_elementIndex[$nameAfter];
        $duplicate   = false;
                if (!empty($elementName) && isset($this->_elementIndex[$elementName])) {
            if ($this->_elements[$this->_elementIndex[$elementName]]->getType() != $element->getType()) {
                $error = self::raiseError(null, QUICKFORM_INVALID_ELEMENT_NAME, null, E_USER_WARNING, "Element '$elementName' already exists in HTML_QuickForm::insertElementBefore()", 'HTML_QuickForm_Error', true);
                return $error;
            }
            $duplicate = true;
        }
                $elKeys = array_keys($this->_elements);
        for ($i = end($elKeys); $i >= $targetIdx; $i--) {
            if (isset($this->_elements[$i])) {
                $currentName = $this->_elements[$i]->getName();
                $this->_elements[$i + 1] =& $this->_elements[$i];
                if ($this->_elementIndex[$currentName] == $i) {
                    $this->_elementIndex[$currentName] = $i + 1;
                } else {
                    if (!empty($currentName)) {
                        $dupIdx = array_search($i, $this->_duplicateIndex[$currentName]);
                        $this->_duplicateIndex[$currentName][$dupIdx] = $i + 1;
                    }
                }
                unset($this->_elements[$i]);
            }
        }
                $this->_elements[$targetIdx] =& $element;
        if (!$duplicate) {
            $this->_elementIndex[$elementName] = $targetIdx;
        } else {
            $this->_duplicateIndex[$elementName][] = $targetIdx;
        }
        $element->onQuickFormEvent('updateValue', null, $this);
        if ($this->_freezeAll) {
            $element->freeze();
        }
                ksort($this->_elements);
        return $element;
    }

        
    
    function &addGroup($elements, $name=null, $groupLabel='', $separator=null, $appendName = true)
    {
        static $anonGroups = 1;

        if (0 == strlen($name)) {
            $name       = 'qf_group_' . $anonGroups++;
            $appendName = false;
        }
        $group =& $this->addElement('group', $name, $groupLabel, $elements, $separator, $appendName);
        return $group;
    } 
        
    
    function &getElement($element)
    {
        if (isset($this->_elementIndex[$element])) {
            return $this->_elements[$this->_elementIndex[$element]];
        } else {
            $error = self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElement()", 'HTML_QuickForm_Error', true);
            return $error;
        }
    } 
        
    
    function &getElementValue($element)
    {
        if (!isset($this->_elementIndex[$element])) {
            $error = self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElementValue()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $value = $this->_elements[$this->_elementIndex[$element]]->getValue();
        if (isset($this->_duplicateIndex[$element])) {
            foreach ($this->_duplicateIndex[$element] as $index) {
                if (null !== ($v = $this->_elements[$index]->getValue())) {
                    if (is_array($value)) {
                        $value[] = $v;
                    } else {
                        $value = (null === $value)? $v: array($value, $v);
                    }
                }
            }
        }
        return $value;
    } 
        
    
    function getSubmitValue($elementName)
    {
        $value = null;
        if (isset($this->_submitValues[$elementName]) || isset($this->_submitFiles[$elementName])) {
            $value = isset($this->_submitValues[$elementName])? $this->_submitValues[$elementName]: array();
            if (is_array($value) && isset($this->_submitFiles[$elementName])) {
                foreach ($this->_submitFiles[$elementName] as $k => $v) {
                    $value = HTML_QuickForm::arrayMerge($value, $this->_reindexFiles($this->_submitFiles[$elementName][$k], $k));
                }
            }

        } elseif ('file' == $this->getElementType($elementName)) {
            return $this->getElementValue($elementName);

        } elseif (false !== ($pos = strpos($elementName, '['))) {
            $base = substr($elementName, 0, $pos);
            $idx  = "['" . str_replace(array(']', '['), array('', "']['"), substr($elementName, $pos + 1, -1)) . "']";
            if (isset($this->_submitValues[$base])) {
                $value = eval("return (isset(\$this->_submitValues['{$base}']{$idx})) ? \$this->_submitValues['{$base}']{$idx} : null;");
            }

            if ((is_array($value) || null === $value) && isset($this->_submitFiles[$base])) {
                $props = array('name', 'type', 'size', 'tmp_name', 'error');
                $code  = "if (!isset(\$this->_submitFiles['{$base}']['name']{$idx})) {\n" .
                         "    return null;\n" .
                         "} else {\n" .
                         "    \$v = array();\n";
                foreach ($props as $prop) {
                    $code .= "    \$v = HTML_QuickForm::arrayMerge(\$v, \$this->_reindexFiles(\$this->_submitFiles['{$base}']['{$prop}']{$idx}, '{$prop}'));\n";
                }
                $fileValue = eval($code . "    return \$v;\n}\n");
                if (null !== $fileValue) {
                    $value = null === $value? $fileValue: HTML_QuickForm::arrayMerge($value, $fileValue);
                }
            }
        }

                if (null === $value && 'group' == $this->getElementType($elementName)) {
            $group    =& $this->getElement($elementName);
            $elements =& $group->getElements();
            foreach (array_keys($elements) as $key) {
                $name = $group->getElementName($key);
                                if ($name != $elementName) {
                    if (null !== ($v = $this->getSubmitValue($name))) {
                        $value[$name] = $v;
                    }
                }
            }
        }
        return $value;
    } 
        
   
    function _reindexFiles($value, $key)
    {
        if (!is_array($value)) {
            return array($key => $value);
        } else {
            $ret = array();
            foreach ($value as $k => $v) {
                $ret[$k] = $this->_reindexFiles($v, $key);
            }
            return $ret;
        }
    }

        
    
    function getElementError($element)
    {
        if (isset($this->_errors[$element])) {
            return $this->_errors[$element];
        }
    } 
        
    
    function setElementError($element, $message = null)
    {
        if (!empty($message)) {
            $this->_errors[$element] = $message;
        } else {
            unset($this->_errors[$element]);
        }
    } 
          
     
     function getElementType($element)
     {
         if (isset($this->_elementIndex[$element])) {
             return $this->_elements[$this->_elementIndex[$element]]->getType();
         }
         return false;
     } 
          
    
    function updateElementAttr($elements, $attrs)
    {
        if (is_string($elements)) {
            $elements = preg_split('/[ ]?,[ ]?/', $elements);
        }
        foreach (array_keys($elements) as $key) {
            if (is_object($elements[$key]) && is_a($elements[$key], 'HTML_QuickForm_element')) {
                $elements[$key]->updateAttributes($attrs);
            } elseif (isset($this->_elementIndex[$elements[$key]])) {
                $this->_elements[$this->_elementIndex[$elements[$key]]]->updateAttributes($attrs);
                if (isset($this->_duplicateIndex[$elements[$key]])) {
                    foreach ($this->_duplicateIndex[$elements[$key]] as $index) {
                        $this->_elements[$index]->updateAttributes($attrs);
                    }
                }
            }
        }
    } 
        
    
    function &removeElement($elementName, $removeRules = true)
    {
        if (!isset($this->_elementIndex[$elementName])) {
            $error = self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$elementName' does not exist in HTML_QuickForm::removeElement()", 'HTML_QuickForm_Error', true);
            return $error;
        }
        $el =& $this->_elements[$this->_elementIndex[$elementName]];
        unset($this->_elements[$this->_elementIndex[$elementName]]);
        if (empty($this->_duplicateIndex[$elementName])) {
            unset($this->_elementIndex[$elementName]);
        } else {
            $this->_elementIndex[$elementName] = array_shift($this->_duplicateIndex[$elementName]);
        }
        if ($removeRules) {
            unset($this->_rules[$elementName], $this->_errors[$elementName]);
        }
        return $el;
    } 
        
    
    function addRule($element, $message, $type, $format=null, $validation='server', $reset = false, $force = false)
    {
        if (!$force) {
            if (!is_array($element) && !$this->elementExists($element)) {
                return self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
            } elseif (is_array($element)) {
                foreach ($element as $el) {
                    if (!$this->elementExists($el)) {
                        return self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$el' does not exist in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
                    }
                }
            }
        }
        if (false === ($newName = $this->isRuleRegistered($type, true))) {
            return self::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addRule()", 'HTML_QuickForm_Error', true);
        } elseif (is_string($newName)) {
            $type = $newName;
        }
        if (is_array($element)) {
            $dependent = $element;
            $element   = array_shift($dependent);
        } else {
            $dependent = null;
        }
        if ($type == 'required' || $type == 'uploadedfile') {
            $this->_required[] = $element;
        }
        if (!isset($this->_rules[$element])) {
            $this->_rules[$element] = array();
        }
        $this->_rules[$element][] = array(
            'type'        => $type,
            'format'      => $format,
            'message'     => $message,
            'validation'  => $validation,
            'reset'       => $reset,
            'dependent'   => $dependent
        );
    } 
        
    
    function addGroupRule($group, $arg1, $type='', $format=null, $howmany=0, $validation = 'server', $reset = false)
    {
        if (!$this->elementExists($group)) {
            return self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Group '$group' does not exist in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
        }

        $groupObj =& $this->getElement($group);
        if (is_array($arg1)) {
            $required = 0;
            foreach ($arg1 as $elementIndex => $rules) {
                $elementName = $groupObj->getElementName($elementIndex);
                foreach ($rules as $rule) {
                    $format = (isset($rule[2])) ? $rule[2] : null;
                    $validation = (isset($rule[3]) && 'client' == $rule[3])? 'client': 'server';
                    $reset = isset($rule[4]) && $rule[4];
                    $type = $rule[1];
                    if (false === ($newName = $this->isRuleRegistered($type, true))) {
                        return self::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
                    } elseif (is_string($newName)) {
                        $type = $newName;
                    }

                    $this->_rules[$elementName][] = array(
                                                        'type'        => $type,
                                                        'format'      => $format,
                                                        'message'     => $rule[0],
                                                        'validation'  => $validation,
                                                        'reset'       => $reset,
                                                        'group'       => $group);

                    if ('required' == $type || 'uploadedfile' == $type) {
                        $groupObj->_required[] = $elementName;
                        $this->_required[] = $elementName;
                        $required++;
                    }
                }
            }
            if ($required > 0 && count($groupObj->getElements()) == $required) {
                $this->_required[] = $group;
            }
        } elseif (is_string($arg1)) {
            if (false === ($newName = $this->isRuleRegistered($type, true))) {
                return self::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, "Rule '$type' is not registered in HTML_QuickForm::addGroupRule()", 'HTML_QuickForm_Error', true);
            } elseif (is_string($newName)) {
                $type = $newName;
            }

                        if (is_a($groupObj, 'html_quickform_group')) {
                                if ($type == 'required' && $groupObj->getGroupType() == 'radio') {
                    $howmany = ($howmany == 0) ? 1 : $howmany;
                } else {
                    $howmany = ($howmany == 0) ? count($groupObj->getElements()) : $howmany;
                }
            }

            $this->_rules[$group][] = array('type'       => $type,
                                            'format'     => $format,
                                            'message'    => $arg1,
                                            'validation' => $validation,
                                            'howmany'    => $howmany,
                                            'reset'      => $reset);
            if ($type == 'required') {
                $this->_required[] = $group;
            }
        }
    } 
        
   
    function addFormRule($rule)
    {
        if (!is_callable($rule)) {
            return self::raiseError(null, QUICKFORM_INVALID_RULE, null, E_USER_WARNING, 'Callback function does not exist in HTML_QuickForm::addFormRule()', 'HTML_QuickForm_Error', true);
        }
        $this->_formRules[] = $rule;
    }

        
    
    function applyFilter($element, $filter)
    {
        if (!is_callable($filter)) {
            return self::raiseError(null, QUICKFORM_INVALID_FILTER, null, E_USER_WARNING, "Callback function does not exist in QuickForm::applyFilter()", 'HTML_QuickForm_Error', true);
        }
        if ($element == '__ALL__') {
            $this->_submitValues = $this->_recursiveFilter($filter, $this->_submitValues);
        } else {
            if (!is_array($element)) {
                $element = array($element);
            }
            foreach ($element as $elName) {
                $value = $this->getSubmitValue($elName);
                if (null !== $value) {
                    if (false === strpos($elName, '[')) {
                        $this->_submitValues[$elName] = $this->_recursiveFilter($filter, $value);
                    } else {
                        $idx  = "['" . str_replace(array(']', '['), array('', "']['"), $elName) . "']";
                        eval("\$this->_submitValues{$idx} = \$this->_recursiveFilter(\$filter, \$value);");
                    }
                }
            }
        }
    } 
        
    
    function _recursiveFilter($filter, $value)
    {
        if (is_array($value)) {
            $cleanValues = array();
            foreach ($value as $k => $v) {
                $cleanValues[$k] = $this->_recursiveFilter($filter, $v);
            }
            return $cleanValues;
        } else {
            return call_user_func($filter, $value);
        }
    } 
        
   
    static function arrayMerge($a, $b)
    {
        if (is_null($a)) {$a = array();}
        if (is_null($b)) {$b = array();}
        foreach ($b as $k => $v) {
            if (is_array($v)) {
                if (isset($a[$k]) && !is_array($a[$k])) {
                    $a[$k] = $v;
                } else {
                    if (!isset($a[$k])) {
                        $a[$k] = array();
                    }
                    $a[$k] = HTML_QuickForm::arrayMerge($a[$k], $v);
                }
            } else {
                $a[$k] = $v;
            }
        }
        return $a;
    } 
        
    
    function isTypeRegistered($type)
    {
        return isset($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES'][strtolower($type)]);
    } 
        
    
    function getRegisteredTypes()
    {
        return array_keys($GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']);
    } 
        
    
    function isRuleRegistered($name, $autoRegister = false)
    {
        if (is_scalar($name) && isset($GLOBALS['_HTML_QuickForm_registered_rules'][$name])) {
            return true;
        } elseif (!$autoRegister) {
            return false;
        }
                include_once 'HTML/QuickForm/RuleRegistry.php';
        $ruleName = false;
        if (is_object($name) && is_a($name, 'html_quickform_rule')) {
            $ruleName = !empty($name->name)? $name->name: strtolower(get_class($name));
        } elseif (is_string($name) && class_exists($name)) {
            $parent = strtolower($name);
            do {
                if ('html_quickform_rule' == strtolower($parent)) {
                    $ruleName = strtolower($name);
                    break;
                }
            } while ($parent = get_parent_class($parent));
        }
        if ($ruleName) {
            $registry =& HTML_QuickForm_RuleRegistry::singleton();
            $registry->registerRule($ruleName, null, $name);
        }
        return $ruleName;
    } 
        
    
    function getRegisteredRules()
    {
        return array_keys($GLOBALS['_HTML_QuickForm_registered_rules']);
    } 
        
    
    function isElementRequired($element)
    {
        return in_array($element, $this->_required, true);
    } 
        
    
    function isElementFrozen($element)
    {
         if (isset($this->_elementIndex[$element])) {
             return $this->_elements[$this->_elementIndex[$element]]->isFrozen();
         }
         return false;
    } 
        
    
    function setJsWarnings($pref, $post)
    {
        $this->_jsPrefix = $pref;
        $this->_jsPostfix = $post;
    } 
        
    
    function setRequiredNote($note)
    {
        $this->_requiredNote = $note;
    } 
        
    
    function getRequiredNote()
    {
        return $this->_requiredNote;
    } 
        
    
    function validate()
    {
        if (count($this->_rules) == 0 && count($this->_formRules) == 0 &&
            $this->isSubmitted()) {
            return (0 == count($this->_errors));
        } elseif (!$this->isSubmitted()) {
            return false;
        }

        include_once('HTML/QuickForm/RuleRegistry.php');
        $registry =& HTML_QuickForm_RuleRegistry::singleton();

        foreach ($this->_rules as $target => $rules) {
            $submitValue = $this->getSubmitValue($target);

            foreach ($rules as $rule) {
                if ((isset($rule['group']) && isset($this->_errors[$rule['group']])) ||
                     isset($this->_errors[$target])) {
                    continue 2;
                }
                                if (!$this->isElementRequired($target)) {
                    if (!isset($submitValue) || '' == $submitValue) {
                        continue 2;
                                                                                } elseif (is_array($submitValue)) {
                        if (false === ($pos = strpos($target, '['))) {
                            $isUpload = !empty($this->_submitFiles[$target]);
                        } else {
                            $base = substr($target, 0, $pos);
                            $idx  = "['" . str_replace(array(']', '['), array('', "']['"), substr($target, $pos + 1, -1)) . "']";
                            eval("\$isUpload = isset(\$this->_submitFiles['{$base}']['name']{$idx});");
                        }
                        if ($isUpload && (!isset($submitValue['error']) || 0 != $submitValue['error'])) {
                            continue 2;
                        }
                    }
                }
                if (isset($rule['dependent']) && is_array($rule['dependent'])) {
                    $values = array($submitValue);
                    foreach ($rule['dependent'] as $elName) {
                        $values[] = $this->getSubmitValue($elName);
                    }
                    $result = $registry->validate($rule['type'], $values, $rule['format'], true);
                } elseif (is_array($submitValue) && !isset($rule['howmany'])) {
                    $result = $registry->validate($rule['type'], $submitValue, $rule['format'], true);
                } else {
                    $result = $registry->validate($rule['type'], $submitValue, $rule['format'], false);
                }

                if (!$result || (!empty($rule['howmany']) && $rule['howmany'] > (int)$result)) {
                    if (isset($rule['group'])) {
                        $this->_errors[$rule['group']] = $rule['message'];
                    } else {
                        $this->_errors[$target] = $rule['message'];
                    }
                }
            }
        }

                foreach ($this->_formRules as $rule) {
            if (true !== ($res = call_user_func($rule, $this->_submitValues, $this->_submitFiles))) {
                if (is_array($res)) {
                    $this->_errors += $res;
                } else {
                    return self::raiseError(null, QUICKFORM_ERROR, null, E_USER_WARNING, 'Form rule callback returned invalid value in HTML_QuickForm::validate()', 'HTML_QuickForm_Error', true);
                }
            }
        }

        return (0 == count($this->_errors));
    } 
        
    
    function freeze($elementList=null)
    {
        if (!isset($elementList)) {
            $this->_freezeAll = true;
            $elementList = array();
        } else {
            if (!is_array($elementList)) {
                $elementList = preg_split('/[ ]*,[ ]*/', $elementList);
            }
            $elementList = array_flip($elementList);
        }

        foreach (array_keys($this->_elements) as $key) {
            $name = $this->_elements[$key]->getName();
            if ($this->_freezeAll || isset($elementList[$name])) {
                $this->_elements[$key]->freeze();
                unset($elementList[$name]);
            }
        }

        if (!empty($elementList)) {
            return self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Nonexistant element(s): '" . implode("', '", array_keys($elementList)) . "' in HTML_QuickForm::freeze()", 'HTML_QuickForm_Error', true);
        }
        return true;
    } 
        
    
    function isFrozen()
    {
         return $this->_freezeAll;
    } 
        
    
    function process($callback, $mergeFiles = true)
    {
        if (!is_callable($callback)) {
            return self::raiseError(null, QUICKFORM_INVALID_PROCESS, null, E_USER_WARNING, "Callback function does not exist in QuickForm::process()", 'HTML_QuickForm_Error', true);
        }
        $values = ($mergeFiles === true) ? HTML_QuickForm::arrayMerge($this->_submitValues, $this->_submitFiles) : $this->_submitValues;
        return call_user_func($callback, $values);
    } 
        
   
    function accept(&$renderer)
    {
        $renderer->startForm($this);
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            $elementName = $element->getName();
            $required    = ($this->isElementRequired($elementName) && !$element->isFrozen());
            $error       = $this->getElementError($elementName);
            $element->accept($renderer, $required, $error);
        }
        $renderer->finishForm($this);
    } 
        
   
    function &defaultRenderer()
    {
        if (!isset($GLOBALS['_HTML_QuickForm_default_renderer'])) {
            include_once('HTML/QuickForm/Renderer/Default.php');
            $GLOBALS['_HTML_QuickForm_default_renderer'] = new HTML_QuickForm_Renderer_Default();         }
        return $GLOBALS['_HTML_QuickForm_default_renderer'];
    } 
        
    
    function toHtml ($in_data = null)
    {
        if (!is_null($in_data)) {
            $this->addElement('html', $in_data);
        }
        $renderer =& $this->defaultRenderer();
        $this->accept($renderer);
        return $renderer->toHtml();
    } 
        
    
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
                        foreach ($rule['dependent'] as $elName) {
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

                    $test[] = $registry->getValidationScript($element, $elementName, $rule);
                }
            }
        }
        if (count($test) > 0) {
            return
                "\n<script type=\"text/javascript\">\n" .
                "//<![CDATA[\n" .
                "function validate_" . $this->_attributes['id'] . "(frm) {\n" .
                "  var value = '';\n" .
                "  var errFlag = new Array();\n" .
                "  var _qfGroups = {};\n" .
                "  _qfMsg = '';\n\n" .
                join("\n", $test) .
                "\n  if (_qfMsg != '') {\n" .
                "    _qfMsg = '" . strtr($this->_jsPrefix, $js_escape) . "' + _qfMsg;\n" .
                "    _qfMsg = _qfMsg + '\\n" . strtr($this->_jsPostfix, $js_escape) . "';\n" .
                "    alert(_qfMsg);\n" .
                "    return false;\n" .
                "  }\n" .
                "  return true;\n" .
                "}\n" .
                "//]]>\n" .
                "</script>";
        }
        return '';
    } 
        
    
    function getSubmitValues($mergeFiles = false)
    {
        return $mergeFiles? HTML_QuickForm::arrayMerge($this->_submitValues, $this->_submitFiles): $this->_submitValues;
    } 
        
    
    function toArray($collectHidden = false)
    {
        include_once 'HTML/QuickForm/Renderer/Array.php';
        $renderer = new HTML_QuickForm_Renderer_Array($collectHidden);         $this->accept($renderer);
        return $renderer->toArray();
     } 
        
    
    function exportValue($element)
    {
        if (!isset($this->_elementIndex[$element])) {
            return self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Element '$element' does not exist in HTML_QuickForm::getElementValue()", 'HTML_QuickForm_Error', true);
        }
        $value = $this->_elements[$this->_elementIndex[$element]]->exportValue($this->_submitValues, false);
        if (isset($this->_duplicateIndex[$element])) {
            foreach ($this->_duplicateIndex[$element] as $index) {
                if (null !== ($v = $this->_elements[$index]->exportValue($this->_submitValues, false))) {
                    if (is_array($value)) {
                        $value[] = $v;
                    } else {
                        $value = (null === $value)? $v: array($value, $v);
                    }
                }
            }
        }
        return $value;
    }

        
    
    function exportValues($elementList = null)
    {
        $values = array();
        if (null === $elementList) {
                        foreach (array_keys($this->_elements) as $key) {
                $value = $this->_elements[$key]->exportValue($this->_submitValues, true);
                if (is_array($value)) {
                                        $values = HTML_QuickForm::arrayMerge($values, $value);
                }
            }
        } else {
            if (!is_array($elementList)) {
                $elementList = array_map('trim', explode(',', $elementList));
            }
            foreach ($elementList as $elementName) {
                $value = $this->exportValue($elementName);
                $pear = new PEAR();
                if ($pear->isError($value)) {
                    return $value;
                }
                $values[$elementName] = $value;
            }
        }
        return $values;
    }

        
   
    function isSubmitted()
    {
        return $this->_flagSubmitted;
    }


        
    
    static function isError($value)
    {
        return (is_object($value) && is_a($value, 'html_quickform_error'));
    } 
        
    
    static function errorMessage($value)
    {
                static $errorMessages;

                if (!isset($errorMessages)) {
            $errorMessages = array(
                QUICKFORM_OK                    => 'no error',
                QUICKFORM_ERROR                 => 'unknown error',
                QUICKFORM_INVALID_RULE          => 'the rule does not exist as a registered rule',
                QUICKFORM_NONEXIST_ELEMENT      => 'nonexistent html element',
                QUICKFORM_INVALID_FILTER        => 'invalid filter',
                QUICKFORM_UNREGISTERED_ELEMENT  => 'unregistered element',
                QUICKFORM_INVALID_ELEMENT_NAME  => 'element already exists',
                QUICKFORM_INVALID_PROCESS       => 'process callback does not exist',
                QUICKFORM_DEPRECATED            => 'method is deprecated',
                QUICKFORM_INVALID_DATASOURCE    => 'datasource is not an object'
            );
        }

                if (HTML_QuickForm::isError($value)) {
            $value = $value->getCode();
        }

                return isset($errorMessages[$value]) ? $errorMessages[$value] : $errorMessages[QUICKFORM_ERROR];
    } 
    } 
class HTML_QuickForm_Error extends PEAR_Error {

    
    
    var $error_message_prefix = 'QuickForm Error: ';

        
    
    public function __construct($code = QUICKFORM_ERROR, $mode = PEAR_ERROR_RETURN,
                         $level = E_USER_NOTICE, $debuginfo = null)
    {
        if (is_int($code)) {
            parent::__construct(HTML_QuickForm::errorMessage($code), $code, $mode, $level, $debuginfo);
        } else {
            parent::__construct("Invalid error code: $code", QUICKFORM_ERROR, $mode, $level, $debuginfo);
        }
    }

    
    public function HTML_QuickForm_Error($code = QUICKFORM_ERROR, $mode = PEAR_ERROR_RETURN,
                         $level = E_USER_NOTICE, $debuginfo = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($code, $mode, $level, $debuginfo);
    }

    } ?>
