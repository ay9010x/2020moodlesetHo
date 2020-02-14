<?php





class phpunit_constraint_object_is_equal_with_exceptions extends PHPUnit_Framework_Constraint_IsEqual {

    
    protected $keys = array();

    
    public function add_exception($key, $comparator) {
        $this->keys[$key] = $comparator;
    }

    
    public function evaluate($other, $description = '', $shouldreturnesult = false) {
        foreach ($this->keys as $key => $comparison) {
            if (isset($other->$key) || isset($this->value->$key)) {
                                PHPUnit_Framework_Assert::$comparison($this->value->$key, $other->$key);

                                unset($other->$key);
                unset($this->value->$key);
            }
        }

                return parent::evaluate($other, $description, $shouldreturnesult);
    }

}
