<?php


require_once('HTML/QuickForm/Rule.php');


class HTML_QuickForm_Rule_Email extends HTML_QuickForm_Rule
{
    var $regex = '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/';

    
    function validate($email, $checkDomain = false)
    {
        if (preg_match($this->regex, $email)) {
            if ($checkDomain && function_exists('checkdnsrr')) {
                $tokens = explode('@', $email);
                if (checkdnsrr($tokens[1], 'MX') || checkdnsrr($tokens[1], 'A')) {
                    return true;
                }
                return false;
            }
            return true;
        }
        return false;
    } 

    function getValidationScript($options = null)
    {
        return array("  var regex = " . $this->regex . ";\n", "{jsVar} != '' && !regex.test({jsVar})");
    } 
} ?>