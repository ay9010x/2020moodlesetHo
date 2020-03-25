<?php




require_once(__DIR__ . '/../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../lib/behat/behat_field_manager.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Gherkin\Node\PyStringNode as PyStringNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;


class behat_forms extends behat_base {

    
    public function press_button($button) {

                $buttonnode = $this->find_button($button);
                if ($this->running_javascript()) {
            $buttonnode->focus();
        }
        $buttonnode->press();
    }

    
    public function press_button_and_switch_to_main_window($button) {
                $buttonnode = $this->find_button($button);
        $buttonnode->press();

                $this->getSession()->switchToWindow(behat_general::MAIN_WINDOW_NAME);
    }

    
    public function i_set_the_following_fields_to_these_values(TableNode $data) {

                $this->expand_all_fields();

        $datahash = $data->getRowsHash();

                foreach ($datahash as $locator => $value) {
            $this->set_field_value($locator, $value);
        }
    }

    
    public function i_expand_all_fieldsets() {
        $this->expand_all_fields();
    }

    
    protected function expand_all_fields() {
                if (!$this->running_javascript()) {
            return;
        }

                        try {

                        $xpath = "//div[@class='collapsible-actions']" .
                "/descendant::a[contains(concat(' ', @class, ' '), ' collapseexpand ')]" .
                "[not(contains(concat(' ', @class, ' '), ' collapse-all '))]";
            $collapseexpandlink = $this->find('xpath', $xpath, false, false, self::REDUCED_TIMEOUT);
            $collapseexpandlink->click();

        } catch (ElementNotFoundException $e) {
                                }

                try {

                        $showmorexpath = "//a[normalize-space(.)='" . get_string('showmore', 'form') . "']" .
                "[contains(concat(' ', normalize-space(@class), ' '), ' moreless-toggler')]";

                        if (!$showmores = $this->getSession()->getPage()->findAll('xpath', $showmorexpath)) {
                return;
            }

                                                            $iterations = count($showmores);
            for ($i = 0; $i < $iterations; $i++) {
                $showmores[0]->click();
            }

        } catch (ElementNotFoundException $e) {
                    }

    }

    
    public function i_set_the_field_to_local_url($field, $path) {
        global $CFG;
        $this->set_field_value($field, $CFG->wwwroot . $path);
    }

    
    public function i_set_the_field_to($field, $value) {
        $this->set_field_value($field, $value);
    }

    
    public function i_press_key_in_the_field($key, $field) {
        if (!$this->running_javascript()) {
            throw new DriverException('Key press step is not available with Javascript disabled');
        }
        $fld = behat_field_manager::get_form_field_from_label($field, $this);
        $modifier = null;
        $char = $key;
        if (preg_match('/-/', $key)) {
            list($modifier, $char) = preg_split('/-/', $key, 2);
        }
        if (is_numeric($char)) {
            $char = (int)$char;
        }
        $fld->key_press($char, $modifier);
    }

    
    public function i_set_the_field_to_multiline($field, PyStringNode $value) {
        $this->set_field_value($field, (string)$value);
    }

    
    public function i_set_the_field_with_xpath_to($fieldxpath, $value) {
        $fieldNode = $this->find('xpath', $fieldxpath);
        $field = behat_field_manager::get_form_field($fieldNode, $this->getSession());
        $field->set_value($value);
    }

    
    public function the_field_matches_value($field, $value) {

                $formfield = behat_field_manager::get_form_field_from_label($field, $this);

                if (!$formfield->matches($value)) {
            $fieldvalue = $formfield->get_value();
            throw new ExpectationException(
                'The \'' . $field . '\' value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                $this->getSession()
            );
        }
    }

    
    public function the_field_does_not_match_value($field, $value) {

                $formfield = behat_field_manager::get_form_field_from_label($field, $this);

                if ($formfield->matches($value)) {
            throw new ExpectationException(
                'The \'' . $field . '\' value matches \'' . $value . '\' and it should not match it' ,
                $this->getSession()
            );
        }
    }

    
    public function the_field_with_xpath_matches_value($fieldxpath, $value) {

                $fieldnode = $this->find('xpath', $fieldxpath);
        $formfield = behat_field_manager::get_form_field($fieldnode, $this->getSession());

                if (!$formfield->matches($value)) {
            $fieldvalue = $formfield->get_value();
            throw new ExpectationException(
                'The \'' . $fieldxpath . '\' value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                $this->getSession()
            );
        }
    }

    
    public function the_field_with_xpath_does_not_match_value($fieldxpath, $value) {

                $fieldnode = $this->find('xpath', $fieldxpath);
        $formfield = behat_field_manager::get_form_field($fieldnode, $this->getSession());

                if ($formfield->matches($value)) {
            throw new ExpectationException(
                'The \'' . $fieldxpath . '\' value matches \'' . $value . '\' and it should not match it' ,
                $this->getSession()
            );
        }
    }

    
    public function the_following_fields_match_these_values(TableNode $data) {

                $this->expand_all_fields();

        $datahash = $data->getRowsHash();

                foreach ($datahash as $locator => $value) {
            $this->the_field_matches_value($locator, $value);
        }
    }

    
    public function the_following_fields_do_not_match_these_values(TableNode $data) {

                $this->expand_all_fields();

        $datahash = $data->getRowsHash();

                foreach ($datahash as $locator => $value) {
            $this->the_field_does_not_match_value($locator, $value);
        }
    }

    
    public function the_select_box_should_contain($select, $option) {

        $selectnode = $this->find_field($select);
        $multiple = $selectnode->hasAttribute('multiple');
        $optionsarr = array(); 
        if ($multiple) {
                        foreach (preg_replace('/\\\,/', ',',  preg_split('/(?<!\\\),/', $option)) as $opt) {
                $optionsarr[] = trim($opt);
            }
        } else {
                        $optionsarr[] = trim($option);
        }

                $options = $selectnode->findAll('xpath', '//option');
        $values = array();
        foreach ($options as $opt) {
            $values[trim($opt->getValue())] = trim($opt->getText());
        }

        foreach ($optionsarr as $opt) {
                        if (!in_array($opt, $values) && !array_key_exists($opt, $values)) {
                throw new ExpectationException(
                    'The select box "' . $select . '" does not contain the option "' . $opt . '"',
                    $this->getSession()
                );
            }
        }
    }

    
    public function the_select_box_should_not_contain($select, $option) {

        $selectnode = $this->find_field($select);
        $multiple = $selectnode->hasAttribute('multiple');
        $optionsarr = array(); 
        if ($multiple) {
                        foreach (preg_replace('/\\\,/', ',',  preg_split('/(?<!\\\),/', $option)) as $opt) {
                $optionsarr[] = trim($opt);
            }
        } else {
                        $optionsarr[] = trim($option);
        }

                $options = $selectnode->findAll('xpath', '//option');
        $values = array();
        foreach ($options as $opt) {
            $values[trim($opt->getValue())] = trim($opt->getText());
        }

        foreach ($optionsarr as $opt) {
                        if (in_array($opt, $values) || array_key_exists($opt, $values)) {
                throw new ExpectationException(
                    'The select box "' . $select . '" contains the option "' . $opt . '"',
                    $this->getSession()
                );
            }
        }
    }

    
    protected function set_field_value($fieldlocator, $value) {

                        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);
        $field->set_value($value);
    }

    
    public function i_select_from_the_singleselect($option, $singleselect) {

        $this->execute('behat_forms::i_set_the_field_to', array($this->escape($singleselect), $this->escape($option)));

        if (!$this->running_javascript()) {
                        $containerxpath = "//div[" .
                "(contains(concat(' ', normalize-space(@class), ' '), ' singleselect ') " .
                    "or contains(concat(' ', normalize-space(@class), ' '), ' urlselect ')".
                ") and (
                .//label[contains(normalize-space(string(.)), '" . $singleselect . "')] " .
                    "or .//select[(./@name='" . $singleselect . "' or ./@id='". $singleselect . "')]" .
                ")]";

            $this->execute('behat_general::i_click_on_in_the',
                array(get_string('go'), "button", $containerxpath, "xpath_element")
            );
        }
    }

}
