<?php


require_once('HTML/QuickForm/Rule.php');


class HTML_QuickForm_Rule_Range extends HTML_QuickForm_Rule
{
    
    function validate($value, $options = null)
    {
        $length = core_text::strlen($value);
        switch ($this->name) {
            case 'minlength': return ($length >= $options);
            case 'maxlength': return ($length <= $options);
            default:          return ($length >= $options[0] && $length <= $options[1]);
        }
    } 

    function getValidationScript($options = null)
    {
        switch ($this->name) {
            case 'minlength':
                $test = '{jsVar}.length < '.$options;
                break;
            case 'maxlength':
                $test = '{jsVar}.length > '.$options;
                break;
            default:
                $test = '({jsVar}.length < '.$options[0].' || {jsVar}.length > '.$options[1].')';
        }
        return array('', "{jsVar} != '' && {$test}");
    } 
} ?>
