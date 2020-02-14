<?php


require_once("HTML/QuickForm/input.php");

if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerRule('uploadedfile', 'callback', '_ruleIsUploadedFile', 'HTML_QuickForm_file');
    HTML_QuickForm::registerRule('maxfilesize', 'callback', '_ruleCheckMaxFileSize', 'HTML_QuickForm_file');
    HTML_QuickForm::registerRule('mimetype', 'callback', '_ruleCheckMimeType', 'HTML_QuickForm_file');
    HTML_QuickForm::registerRule('filename', 'callback', '_ruleCheckFileName', 'HTML_QuickForm_file');
}


class HTML_QuickForm_file extends HTML_QuickForm_input
{
    
   
    var $_value = null;

        
    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->setType('file');
    } 
    
    public function HTML_QuickForm_file($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

        
    
    function setSize($size)
    {
        $this->updateAttributes(array('size' => $size));
    }     
        
    
    function getSize()
    {
        return $this->getAttribute('size');
    } 
        
    
    function freeze()
    {
        return false;
    } 
        
    
    function setValue($value)
    {
        return null;
    }     
        
    
    function getValue()
    {
        return $this->_value;
    } 
        
    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                if ($caller->getAttribute('method') == 'get') {
                    return self::raiseError('Cannot add a file upload field to a GET method form');
                }
                $this->_value = $this->_findValue();
                $caller->updateAttributes(array('enctype' => 'multipart/form-data'));
                $caller->setMaxFileSize();
                break;
            case 'addElement':
                $this->onQuickFormEvent('createElement', $arg, $caller);
                return $this->onQuickFormEvent('updateValue', null, $caller);
                break;
            case 'createElement':
                static::__construct($arg[0], $arg[1], $arg[2], $arg[3], $arg[4]);
                break;
        }
        return true;
    } 
        
    
    function moveUploadedFile($dest, $fileName = '')
    {
        if ($dest != ''  && substr($dest, -1) != '/') {
            $dest .= '/';
        }
        $fileName = ($fileName != '') ? $fileName : basename($this->_value['name']);
        if (move_uploaded_file($this->_value['tmp_name'], $dest . $fileName)) {
            return true;
        } else {
            return false;
        }
    }     
        
    
    function isUploadedFile()
    {
        return $this->_ruleIsUploadedFile($this->_value);
    } 
        
    
    function _ruleIsUploadedFile($elementValue)
    {
        if ((isset($elementValue['error']) && $elementValue['error'] == 0) ||
            (!empty($elementValue['tmp_name']) && $elementValue['tmp_name'] != 'none')) {
            return is_uploaded_file($elementValue['tmp_name']);
        } else {
            return false;
        }
    }     
        
    
    function _ruleCheckMaxFileSize($elementValue, $maxSize)
    {
        if (!empty($elementValue['error']) && 
            (UPLOAD_ERR_FORM_SIZE == $elementValue['error'] || UPLOAD_ERR_INI_SIZE == $elementValue['error'])) {
            return false;
        }
        if (!HTML_QuickForm_file::_ruleIsUploadedFile($elementValue)) {
            return true;
        }
        return ($maxSize >= @filesize($elementValue['tmp_name']));
    } 
        
    
    function _ruleCheckMimeType($elementValue, $mimeType)
    {
        if (!HTML_QuickForm_file::_ruleIsUploadedFile($elementValue)) {
            return true;
        }
        if (is_array($mimeType)) {
            return in_array($elementValue['type'], $mimeType);
        }
        return $elementValue['type'] == $mimeType;
    } 
        
    
    function _ruleCheckFileName($elementValue, $regex)
    {
        if (!HTML_QuickForm_file::_ruleIsUploadedFile($elementValue)) {
            return true;
        }
        return preg_match($regex, $elementValue['name']);
    }     
        
   
    function _findValue()
    {
        if (empty($_FILES)) {
            return null;
        }
        $elementName = $this->getName();
        if (isset($_FILES[$elementName])) {
            return $_FILES[$elementName];
        } elseif (false !== ($pos = strpos($elementName, '['))) {
            $base  = substr($elementName, 0, $pos);
            $idx   = "['" . str_replace(array(']', '['), array('', "']['"), substr($elementName, $pos + 1, -1)) . "']";
            $props = array('name', 'type', 'size', 'tmp_name', 'error');
            $code  = "if (!isset(\$_FILES['{$base}']['name']{$idx})) {\n" .
                     "    return null;\n" .
                     "} else {\n" .
                     "    \$value = array();\n";
            foreach ($props as $prop) {
                $code .= "    \$value['{$prop}'] = \$_FILES['{$base}']['{$prop}']{$idx};\n";
            }
            return eval($code . "    return \$value;\n}\n");
        } else {
            return null;
        }
    }

    } ?>
