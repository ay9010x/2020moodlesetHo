<?php


require_once('HTML/QuickForm/Rule.php');


class HTML_QuickForm_Rule_Callback extends HTML_QuickForm_Rule
{
    
    var $_data = array();

   
    var $_BCMode = array();

    
    function validate($value, $options = null)
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name];
            if (isset($callback[1])) {
                return call_user_func(array($callback[1], $callback[0]), $value, $options);
            } elseif ($this->_BCMode[$this->name]) {
                return $callback[0]('', $value, $options);
            } else {
                return $callback[0]($value, $options);
            }
        } elseif (is_callable($options)) {
            return call_user_func($options, $value);
        } else {
            return true;
        }
    } 
    
    function addData($name, $callback, $class = null, $BCMode = false)
    {
        if (!empty($class)) {
            $this->_data[$name] = array($callback, $class);
        } else {
            $this->_data[$name] = array($callback);
        }
        $this->_BCMode[$name] = $BCMode;
    } 

    function getValidationScript($options = null)
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name][0];
            $params   = ($this->_BCMode[$this->name]? "'', {jsVar}": '{jsVar}') .
                        (isset($options)? ", '{$options}'": '');
        } else {
            $callback = is_array($options)? $options[1]: $options;
            $params   = '{jsVar}';
        }
        return array('', "{jsVar} != '' && !{$callback}({$params})");
    } 
} ?>