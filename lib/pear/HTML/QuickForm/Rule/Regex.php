<?php


require_once('HTML/QuickForm/Rule.php');


class HTML_QuickForm_Rule_Regex extends HTML_QuickForm_Rule
{
    
    var $_data = array(
                    'lettersonly'   => '/^[a-zA-Z]+$/',
                    'alphanumeric'  => '/^[a-zA-Z0-9]+$/',
                    'numeric'       => '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/',
                    'nopunctuation' => '/^[^().\/\*\^\?#!@$%+=,\"\'><~\[\]{}]+$/',
                    'nonzero'       => '/^-?[1-9][0-9]*/'
                    );

    
    function validate($value, $regex = null)
    {
        if (isset($this->_data[$this->name])) {
            if (!preg_match($this->_data[$this->name], $value)) {
                return false;
            }
        } else {
            if (!preg_match($regex, $value)) {
                return false;
            }
        }
        return true;
    } 
    
    function addData($name, $pattern)
    {
        $this->_data[$name] = $pattern;
    } 

    function getValidationScript($options = null)
    {
        $regex = isset($this->_data[$this->name]) ? $this->_data[$this->name] : $options;

        return array("  var regex = " . $regex . ";\n", "{jsVar} != '' && !regex.test({jsVar})");
    } 
} ?>