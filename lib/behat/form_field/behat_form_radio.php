<?php




require_once(__DIR__  . '/behat_form_checkbox.php');


class behat_form_radio extends behat_form_checkbox {

    
    public function get_value() {
        return $this->field->isSelected();
    }

    
    public function set_value($value) {

        if ($this->running_javascript()) {
                        $this->field->click();

                        if (!empty($value) && !$this->field->isSelected()) {
                $this->trigger_on_change();
            }
        } else {
                        $this->field->setValue($this->field->getAttribute('value'));
        }
    }

}
