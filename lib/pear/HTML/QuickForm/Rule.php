<?php


class HTML_QuickForm_Rule
{
   
    var $name;

   
    function validate($value, $options = null)
    {
        return true;
    }

   
    function setName($ruleName)
    {
        $this->name = $ruleName;
    }

    
    function getValidationScript($options = null)
    {
        return array('', '');
    }
}
?>