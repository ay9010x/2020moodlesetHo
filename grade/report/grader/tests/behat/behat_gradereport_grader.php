<?php




require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;


class behat_gradereport_grader extends behat_base {
    
    public function i_click_on_student_and_grade_item($student, $itemname) {
        $xpath = $this->get_student_and_grade_cell_selector($student, $itemname);

        $this->execute("behat_general::i_click_on", array($this->escape($xpath), "xpath_element"));
    }

    
    public function i_click_away_from_student_and_grade_value($student, $itemname) {
        $xpath = $this->get_student_and_grade_value_selector($student, $itemname);

        $this->execute('behat_general::i_take_focus_off_field', array($this->escape($xpath), 'xpath_element'));
    }

    
    public function i_click_away_from_student_and_grade_feedback($student, $itemname) {
        $xpath = $this->get_student_and_grade_feedback_selector($student, $itemname);

        $this->execute('behat_general::i_take_focus_off_field', array($this->escape($xpath), 'xpath_element'));
    }

    
    public function the_grade_should_match($student, $itemname, $value) {
        $xpath = $this->get_student_and_grade_value_selector($student, $itemname);

        $gradefield = $this->getSession()->getPage()->find('xpath', $xpath);
        if (!empty($gradefield)) {
                        $fieldtype = behat_field_manager::guess_field_type($gradefield, $this->getSession());
            if (!$fieldtype) {
                throw new Exception('Could not get field type for grade field "' . $itemname . '"');
            }
            $field = behat_field_manager::get_field_instance($fieldtype, $gradefield, $this->getSession());
            if (!$field->matches($value)) {
                $fieldvalue = $field->get_value();
                throw new ExpectationException(
                    'The "' . $student . '" and "' . $itemname . '" grade is "' . $fieldvalue . '", "' . $value . '" expected' ,
                    $this->getSession()
                );
            }
        } else {
                        $valueliteral = behat_context_helper::escape($value);

            $xpath = $this->get_student_and_grade_cell_selector($student, $itemname);
            $xpath .= "[contains(normalize-space(.)," . $valueliteral . ")]";

            $node = $this->getSession()->getDriver()->find($xpath);
            if (empty($node)) {
                $locatorexceptionmsg = 'Cell for "' . $student . '" and "' . $itemname . '" with value "' . $value . '"';
                throw new ElementNotFoundException($this->getSession(), $locatorexceptionmsg, null, $xpath);
            }
        }
    }

    
    public function i_should_see_grade_field($student, $itemname) {
        $xpath = $this->get_student_and_grade_value_selector($student, $itemname);

        $this->execute('behat_general::should_be_visible', array($this->escape($xpath), 'xpath_element'));
    }

    
    public function i_should_see_feedback_field($student, $itemname) {
        $xpath = $this->get_student_and_grade_feedback_selector($student, $itemname);

        $this->execute('behat_general::should_be_visible', array($this->escape($xpath), 'xpath_element'));
    }

    
    public function i_should_not_see_grade_field($student, $itemname) {
        $xpath = $this->get_student_and_grade_value_selector($student, $itemname);

        $this->execute('behat_general::should_not_exist', array($this->escape($xpath), 'xpath_element'));
    }

    
    public function i_should_not_see_feedback_field($student, $itemname) {
        $xpath = $this->get_student_and_grade_feedback_selector($student, $itemname);

        $this->execute('behat_general::should_not_exist', array($this->escape($xpath), 'xpath_element'));
    }

    
    protected function get_user_id($name) {
        global $DB;
        $names = explode(' ', $name);

        if (!$id = $DB->get_field('user', 'id', array('firstname' => $names[0], 'lastname' => $names[1]))) {
            throw new Exception('The specified user with username "' . $name . '" does not exist');
        }
        return $id;
    }

    
    protected function get_grade_item_id($itemname) {
        global $DB;

        if ($id = $DB->get_field('grade_items', 'id', array('itemname' => $itemname))) {
            return $id;
        }

                if ($itemname === "Course total") {
            if (!$id = $DB->get_field('grade_items', 'id', array('itemtype' => 'course'))) {
                throw new Exception('The specified grade_item with name "' . $itemname . '" does not exist');
            }
            return $id;
        }

                if ($catid = $DB->get_field('grade_categories', 'id', array('fullname' => $itemname))) {
            if ($id = $DB->get_field('grade_items', 'id', array('iteminstance' => $catid))) {
                return $id;
            }
        }

        throw new Exception('The specified grade_item with name "' . $itemname . '" does not exist');
    }

    
    protected function get_student_and_grade_cell_selector($student, $itemname) {
        $itemid = 'u' . $this->get_user_id($student) . 'i' . $this->get_grade_item_id($itemname);
        return "//table[@id='user-grades']//td[@id='" . $itemid . "']";
    }

    
    protected function get_student_and_grade_value_selector($student, $itemname) {
        $cell = $this->get_student_and_grade_cell_selector($student, $itemname);
        return $cell . "//*[contains(@id, 'grade_') or @name='ajaxgrade']";
    }

    
    protected function get_student_and_grade_feedback_selector($student, $itemname) {
        $cell = $this->get_student_and_grade_cell_selector($student, $itemname);
        return $cell . "//input[contains(@id, 'feedback_') or @name='ajaxfeedback']";
    }

}
