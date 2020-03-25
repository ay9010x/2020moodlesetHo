<?php




require_once(__DIR__  . '/behat_form_field.php');


class behat_form_select extends behat_form_field {

    
    public function set_value($value) {

                $multiple = $this->field->hasAttribute('multiple');
        $singleselect = ($this->field->hasClass('singleselect') || $this->field->hasClass('urlselect'));

                if ($multiple) {
                        $options = preg_replace('/\\\,/', ',',  preg_split('/(?<!\\\),/', trim($value)));
                        $afterfirstoption = false;
            foreach ($options as $option) {
                $this->field->selectOption(trim($option), $afterfirstoption);
                $afterfirstoption = true;
            }
        } else {
                       $this->field->selectOption(trim($value));
       }

                        if ($this->running_javascript()) {
                                    if (!$singleselect) {
                $dialoguexpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' moodle-dialogue-focused ')]";
                if (!$node = $this->session->getDriver()->find($dialoguexpath)) {
                    $script = "Syn.trigger('change', {}, {{ELEMENT}})";
                    try {
                        $this->session->getDriver()->triggerSynScript($this->field->getXpath(), $script);
                        $this->session->getDriver()->click('//body//div[@class="skiplinks"]');
                    } catch (\Exception $e) {
                        return;
                    }
                } else {
                    try {
                        $this->session->getDriver()->click($dialoguexpath);
                    } catch (\Exception $e) {
                        return;
                    }
                }
            }
            $this->session->wait(behat_base::TIMEOUT * 1000, behat_base::PAGE_READY_JS);
        }
    }

    
    public function get_value() {
        return $this->get_selected_options();
    }

    
    public function matches($expectedvalue) {

        $multiple = $this->field->hasAttribute('multiple');

                if (!$multiple) {
            $cleanexpectedvalue = trim($expectedvalue);
            $selectedtext = trim($this->get_selected_options());
            $selectedvalue = trim($this->get_selected_options(false));
            if ($cleanexpectedvalue != $selectedvalue && $cleanexpectedvalue != $selectedtext) {
                return false;
            }
            return true;
        }

        
                $expectedoptions = $this->get_unescaped_options($expectedvalue);

                $texts = $this->get_selected_options(true);
        $selectedoptiontexts = $this->get_unescaped_options($texts);

                $values = $this->get_selected_options(false);
        $selectedoptionvalues = $this->get_unescaped_options($values);

                if (count($expectedoptions) !== count($selectedoptiontexts) ||
                count($expectedoptions) !== count($selectedoptionvalues)) {
            return false;
        }

                if ($expectedoptions != $selectedoptiontexts &&
                $expectedoptions != $selectedoptionvalues) {
            return false;
        }

        return true;
    }

    
    protected function get_unescaped_options($value) {

                $optionsarray = array_map(
            'trim',
            preg_replace('/\\\,/', ',',
                preg_split('/(?<!\\\),/', $value)
           )
        );

                core_collator::asort($optionsarray, SORT_STRING);

                return implode('|||', $optionsarray);
    }

    
    protected function get_selected_options($returntexts = true) {

        $method = 'getHtml';
        if ($returntexts === false) {
            $method = 'getValue';
        }

                $multiple = $this->field->hasAttribute('multiple');

        $selectedoptions = array(); 
                        $values = $this->field->getValue();
        if (!is_array($values)) {
            $values = array($values);
        }

                $alloptions = $this->field->findAll('xpath', '//option');
        foreach ($alloptions as $option) {
                        if (in_array($option->getValue(), $values)) {
                if ($multiple) {
                                        $selectedoptions[] = trim(str_replace(',', '\,', $option->{$method}()));
                } else {
                    $selectedoptions[] = trim($option->{$method}());
                }
            }
        }

        return implode(', ', $selectedoptions);
    }

    
    protected function get_option_xpath($option, $selectxpath) {
        $valueliteral = behat_context_helper::escape(trim($option));
        return $selectxpath . "/descendant::option[(./@value=$valueliteral or normalize-space(.)=$valueliteral)]";
    }
}
