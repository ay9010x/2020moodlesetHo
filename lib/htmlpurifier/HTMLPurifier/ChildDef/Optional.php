<?php


class HTMLPurifier_ChildDef_Optional extends HTMLPurifier_ChildDef_Required
{
    
    public $allow_empty = true;

    
    public $type = 'optional';

    
    public function validateChildren($children, $config, $context)
    {
        $result = parent::validateChildren($children, $config, $context);
                if ($result === false) {
            if (empty($children)) {
                return true;
            } elseif ($this->whitespace) {
                return $children;
            } else {
                return array();
            }
        }
        return $result;
    }
}

