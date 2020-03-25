<?php




require_once(__DIR__  . '/behat_form_field.php');


class behat_form_checkbox extends behat_form_field {

    
    public function set_value($value) {

        if (!empty($value) && !$this->field->isChecked()) {

            if (!$this->running_javascript()) {
                $this->field->check();
                return;
            }

                        $this->field->click();

                        $this->trigger_on_change();

        } else if (empty($value) && $this->field->isChecked()) {

            if (!$this->running_javascript()) {
                $this->field->uncheck();
                return;
            }

                        $this->field->click();

                        $this->trigger_on_change();
        }
    }

    
    public function get_value() {
        return $this->field->isChecked();
    }

    
    public function matches($expectedvalue = false) {

        $ischecked = $this->field->isChecked();

                if (!empty($expectedvalue) && $ischecked) {
            return true;
        } else if (empty($expectedvalue) && !$ischecked) {
            return true;
        }

        return false;
    }

    
    protected function trigger_on_change() {
        $this->session->getDriver()->triggerSynScript(
            $this->field->getXPath(),
            "Syn.trigger('change', {}, {{ELEMENT}})"
        );
    }
}
