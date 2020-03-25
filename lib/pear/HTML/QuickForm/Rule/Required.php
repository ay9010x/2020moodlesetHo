<?php


require_once('HTML/QuickForm/Rule.php');


class HTML_QuickForm_Rule_Required extends HTML_QuickForm_Rule
{
    
    function validate($value, $options = null)
    {
        if ((string)$value == '') {
            return false;
        }
        return true;
    } 

    function getValidationScript($options = null)
    {
        return array('', "{jsVar} == ''");
    } 
} ?>
