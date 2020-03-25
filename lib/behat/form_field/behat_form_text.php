<?php




require_once(__DIR__  . '/behat_form_field.php');


class behat_form_text extends behat_form_field {

    
    public function set_value($value) {
        $this->field->setValue($value);
    }

    
    public function get_value() {
        return $this->field->getValue();
    }

    
    public function matches($expectedvalue) {
        return $this->text_matches($expectedvalue);
    }

}
