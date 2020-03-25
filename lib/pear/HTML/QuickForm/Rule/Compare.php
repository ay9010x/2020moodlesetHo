<?php


require_once 'HTML/QuickForm/Rule.php';


class HTML_QuickForm_Rule_Compare extends HTML_QuickForm_Rule
{
   
    var $_operators = array(
        'eq'  => '==',
        'neq' => '!=',
        'gt'  => '>',
        'gte' => '>=',
        'lt'  => '<',
        'lte' => '<='
    );


   
    function _findOperator($name)
    {
        if (empty($name)) {
            return '==';
        } elseif (isset($this->_operators[$name])) {
            return $this->_operators[$name];
        } elseif (in_array($name, $this->_operators)) {
            return $name;
        } else {
            return '==';
        }
    }


    function validate($values, $operator = null)
    {
        $operator = $this->_findOperator($operator);
        if ('==' != $operator && '!=' != $operator) {
            $compareFn = create_function('$a, $b', 'return floatval($a) ' . $operator . ' floatval($b);');
        } else {
            $compareFn = create_function('$a, $b', 'return $a ' . $operator . ' $b;');
        }
        
        return $compareFn($values[0], $values[1]);
    }


    function getValidationScript($operator = null)
    {
        $operator = $this->_findOperator($operator);
        if ('==' != $operator && '!=' != $operator) {
            $check = "!(Number({jsVar}[0]) {$operator} Number({jsVar}[1]))";
        } else {
            $check = "!({jsVar}[0] {$operator} {jsVar}[1])";
        }
        return array('', "'' != {jsVar}[0] && {$check}");
    }
}
?>
