<?php




use Behat\Mink\Session as Session,
    Behat\Mink\Element\NodeElement as NodeElement;


class behat_form_field {

    
    protected $session;

    
    protected $field;

    
    protected $fieldlocator = false;


    
    public function __construct(Session $session, NodeElement $fieldnode) {
        $this->session = $session;
        $this->field = $fieldnode;
    }

    
    public function set_value($value) {
                                $instance = $this->guess_type();
        return $instance->set_value($value);
    }

    
    public function get_value() {
                                $instance = $this->guess_type();
        return $instance->get_value();
    }

    
    public function key_press($char, $modifier = null) {
                                $instance = $this->guess_type();
        $instance->field->keyDown($char, $modifier);
        try {
            $instance->field->keyPress($char, $modifier);
            $instance->field->keyUp($char, $modifier);
        } catch (WebDriver\Exception $e) {
                                            }
    }

    
    public function matches($expectedvalue) {
                                $instance = $this->guess_type();
        return $instance->matches($expectedvalue);
    }

    
    private function guess_type() {
        global $CFG;

                if (!$type = behat_field_manager::guess_field_type($this->field, $this->session)) {
            $type = 'text';
        }

        $classname = 'behat_form_' . $type;
        $classpath = $CFG->dirroot . '/lib/behat/form_field/' . $classname . '.php';
        require_once($classpath);
        return new $classname($this->session, $this->field);
    }

    
    protected function running_javascript() {
        return get_class($this->session->getDriver()) !== 'Behat\Mink\Driver\GoutteDriver';
    }

    
    protected function get_internal_field_id() {

        if (!$this->running_javascript()) {
            throw new coding_exception('You can only get an internal ID using the selenium driver.');
        }

        return $this->session->getDriver()->getWebDriverSession()->element('xpath', $this->field->getXPath())->getID();
    }

    
    protected function text_matches($expectedvalue) {
        if (trim($expectedvalue) != trim($this->get_value())) {
            return false;
        }
        return true;
    }

    
    protected function get_field_locator($locatortype = false) {

        if (!empty($this->fieldlocator)) {
            return $this->fieldlocator;
        }

        $fieldid = $this->field->getAttribute('id');

                if ($locatortype == 'label' || $locatortype == false) {

            $labelnode = $this->session->getPage()->find('xpath', '//label[@for="' . $fieldid . '"]');

                        if (!$labelnode && $locatortype == 'label') {
                throw new coding_exception('Field with "' . $fieldid . '" id does not have a label.');
            }

            $this->fieldlocator = $labelnode->getText();
        }

                        if (($locatortype == 'name' || $locatortype == false) &&
                empty($this->fieldlocator)) {

            $name = $this->field->getAttribute('name');

                        if (!$name && $locatortype == 'name') {
                throw new coding_exception('Field with "' . $fieldid . '" id does not have a name attribute.');
            }

            $this->fieldlocator = $name;
        }

                if (empty($this->fieldlocator)) {
            $this->fieldlocator = $fieldid;
        }

        return $this->fieldlocator;
    }
}
