<?php




require_once(__DIR__  . '/behat_form_text.php');


class behat_form_autocomplete extends behat_form_text {

    
    public function set_value($value) {
        if (!$this->running_javascript()) {
            throw new coding_exception('Setting the valid of an autocomplete field requires javascript.');
        }
        $this->field->setValue($value);
                        sleep(2);
        $id = $this->field->getAttribute('id');
        $js = ' require(["jquery"], function($) { $(document.getElementById("'.$id.'")).trigger("behat:set-value"); }); ';
        $this->session->executeScript($js);
    }
}
