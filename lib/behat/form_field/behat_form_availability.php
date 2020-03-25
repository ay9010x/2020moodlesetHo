<?php




require_once(__DIR__  . '/behat_form_field.php');


class behat_form_availability extends behat_form_textarea {

    
    public function set_value($value) {
        global $DB;
        $driver = $this->session->getDriver();

                        $existing = $this->get_value();
        if ($existing && $existing !== '{"op":"&","c":[],"showc":[]}') {
            throw new Exception('Cannot automatically set availability when ' .
                    'there is existing setting - must clear manually');
        }

                $matches = array();
        if (!preg_match('~^\s*([^:]*):\s*(.*?)\s*$~', $value, $matches)) {
            throw new Exception('Value for availability field does not match correct ' .
                    'format. Example: "Grouping: G1"');
        }
        $type = $matches[1];
        $param = $matches[2];

        if ($this->running_javascript()) {
            switch (strtolower($type)) {
                case 'grouping' :
                                        $driver->click('//div[@class="availability-button"]/button');
                    $driver->click('//button[@id="availability_addrestriction_grouping"]');
                    $escparam = behat_context_helper::escape($param);
                    $nodes = $driver->find(
                            '//span[contains(concat(" " , @class, " "), " availability_grouping ")]//' .
                            'option[normalize-space(.) = ' . $escparam . ']');
                    if (count($nodes) != 1) {
                        throw new Exception('Cannot find grouping in dropdown' . count($nodes));
                    }
                    $node = reset($nodes);
                    $value = $node->getValue();
                    $driver->selectOption(
                            '//span[contains(concat(" " , @class, " "), " availability_grouping ")]//' .
                            'select', $value);
                    break;

                default:
                                                            throw new Exception('The availability type "' . $type .
                            '" is currently not supported - must set manually');
            }
        } else {
            $courseid = $driver->getValue('//input[@name="course"]');
            switch (strtolower($type)) {
                case 'grouping' :
                                        $groupingid = $DB->get_field('groupings', 'id',
                            array('courseid' => $courseid, 'name' => $param));
                    $json = \core_availability\tree::get_root_json(array(
                            \availability_grouping\condition::get_json($groupingid)));
                    break;

                default:
                                        throw new Exception('The availability type "' . $type .
                            '" is currently not supported - must set with JavaScript');
            }
            $driver->setValue('//textarea[@name="availabilityconditionsjson"]',
                    json_encode($json));
        }
    }
}
