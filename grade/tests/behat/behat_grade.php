<?php




require_once(__DIR__ . '/../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

class behat_grade extends behat_base {

    
    public function i_give_the_grade($grade, $userfullname, $itemname) {
        $gradelabel = $userfullname . ' ' . $itemname;
        $fieldstr = get_string('useractivitygrade', 'gradereport_grader', $gradelabel);

        $this->execute('behat_forms::i_set_the_field_to', array($this->escape($fieldstr), $grade));
    }

    
    public function i_give_the_feedback($feedback, $userfullname, $itemname) {
        $gradelabel = $userfullname . ' ' . $itemname;
        $fieldstr = get_string('useractivityfeedback', 'gradereport_grader', $gradelabel);

        $this->execute('behat_forms::i_set_the_field_to', array($this->escape($fieldstr), $this->escape($feedback)));
    }

    
    public function i_set_the_following_settings_for_grade_item($gradeitem, TableNode $data) {

        $gradeitem = behat_context_helper::escape($gradeitem);

        if ($this->running_javascript()) {
            $xpath = "//tr[contains(.,$gradeitem)]//*[contains(@class,'moodle-actionmenu')]//a[contains(@class,'toggle-display')]";
            if ($this->getSession()->getPage()->findAll('xpath', $xpath)) {
                $this->execute("behat_general::i_click_on", array($this->escape($xpath), "xpath_element"));
            }
        }

        $savechanges = get_string('savechanges', 'grades');
        $edit = behat_context_helper::escape(get_string('edit') . '  ');
        $linkxpath = "//a[./img[starts-with(@title,$edit) and contains(@title,$gradeitem)]]";

        $this->execute("behat_general::i_click_on", array($this->escape($linkxpath), "xpath_element"));
        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $data);
        $this->execute('behat_forms::press_button', $this->escape($savechanges));
    }

    
    public function i_set_calculation_for_grade_item_with_idnumbers($calculation, $gradeitem, TableNode $data) {

        $gradeitem = behat_context_helper::escape($gradeitem);

        if ($this->running_javascript()) {
            $xpath = "//tr[contains(.,$gradeitem)]//*[contains(@class,'moodle-actionmenu')]//a[contains(@class,'toggle-display')]";
            if ($this->getSession()->getPage()->findAll('xpath', $xpath)) {
                $this->execute("behat_general::i_click_on", array($this->escape($xpath), "xpath_element"));
            }
        }

                $savechanges = get_string('savechanges', 'grades');
        $edit = behat_context_helper::escape(get_string('editcalculation', 'grades'));
        $linkxpath = "//a[./img[starts-with(@title,$edit) and contains(@title,$gradeitem)]]";
        $this->execute("behat_general::i_click_on", array($this->escape($linkxpath), "xpath_element"));

                $datahash = $data->getRowsHash();
        foreach ($datahash as $gradeitem => $idnumber) {
                                    $inputxpath ="//input[@class='idnumber'][" .
                "parent::li[@class='item'][text()='" . $gradeitem . "']" .
                " or " .
                "parent::li[@class='categoryitem' or @class='courseitem']/parent::ul/parent::li[starts-with(text(),'" . $gradeitem . "')]" .
            "]";
            $this->execute('behat_forms::i_set_the_field_with_xpath_to', array($inputxpath, $idnumber));
        }

        $this->execute('behat_forms::press_button', get_string('addidnumbers', 'grades'));
        $this->execute('behat_forms::i_set_the_field_to', array(get_string('calculation', 'grades'), $calculation));
        $this->execute('behat_forms::press_button', $savechanges);

    }

    
    public function i_set_calculation_for_grade_category_with_idnumbers($calculation, $gradeitem, TableNode $data) {

        $gradecategorytotal = behat_context_helper::escape($gradeitem . ' total');
        $gradeitem = behat_context_helper::escape($gradeitem);

        if ($this->running_javascript()) {
            $xpath = "//tr[contains(.,$gradecategorytotal)]//*[contains(@class,'moodle-actionmenu')]" .
                "//a[contains(@class,'toggle-display')]";
            if ($this->getSession()->getPage()->findAll('xpath', $xpath)) {
                $this->execute("behat_general::i_click_on", array($this->escape($xpath), "xpath_element"));
            }
        }

                $savechanges = get_string('savechanges', 'grades');
        $edit = behat_context_helper::escape(get_string('editcalculation', 'grades'));
        $linkxpath = "//a[./img[starts-with(@title,$edit) and contains(@title,$gradeitem)]]";
        $this->execute("behat_general::i_click_on", array($this->escape($linkxpath), "xpath_element"));

                $datahash = $data->getRowsHash();
        foreach ($datahash as $gradeitem => $idnumber) {
                                    $inputxpath = "//input[@class='idnumber'][" .
                "parent::li[@class='item'][text()='" . $gradeitem . "']" .
                " | " .
                "parent::li[@class='categoryitem' | @class='courseitem']" .
                "/parent::ul/parent::li[starts-with(text(),'" . $gradeitem . "')]" .
            "]";
            $this->execute('behat_forms::i_set_the_field_with_xpath_to', array($inputxpath, $idnumber));
        }

        $this->execute('behat_forms::press_button', get_string('addidnumbers', 'grades'));

        $this->execute('behat_forms::i_set_the_field_to', array(get_string('calculation', 'grades'), $calculation));
        $this->execute('behat_forms::press_button', $savechanges);
    }

    
    public function i_reset_weights_for_grade_category($gradeitem) {

        $steps = array();

        if ($this->running_javascript()) {
            $gradeitemliteral = behat_context_helper::escape($gradeitem);
            $xpath = "//tr[contains(.,$gradeitemliteral)]//*[contains(@class,'moodle-actionmenu')]//a[contains(@class,'toggle-display')]";
            if ($this->getSession()->getPage()->findAll('xpath', $xpath)) {
                $this->execute("behat_general::i_click_on", array($this->escape($xpath), "xpath_element"));
            }
        }

        $linktext = get_string('resetweights', 'grades', (object)array('itemname' => $gradeitem));
        $this->execute("behat_general::i_click_on", array($this->escape($linktext), "link"));
    }

    
    public function gradebook_calculations_for_the_course_are_frozen_at_version($coursename, $version) {
        global $DB;
        $courseid = $DB->get_field('course', 'id', array('shortname' => $coursename), MUST_EXIST);
        set_config('gradebook_calculations_freeze_' . $courseid, $version);
    }

    
    public function i_navigate_to_in_the_course_gradebook($gradepath) {
        $gradeadmin = get_string('gradeadministration', 'grades');

                $xpath = '//div[contains(@class,\'block_settings\')]//div[@id=\'settingsnav\']/ul/li[1]/p[1]/span[string(.)=' .
            behat_context_helper::escape($gradeadmin) . ']';
        if (!$this->getSession()->getPage()->findAll('xpath', $xpath)) {
            $this->execute("behat_general::i_click_on_in_the", array(get_string('grades'), 'link',
                get_string('pluginname', 'block_settings'), 'block'));
        }

        $parentnodes = preg_split('/\s*>\s*/', trim($gradepath));
        if ($parentnodes[0] === 'Letters' && count($parentnodes) > 1) {
                        if ($parentnodes[1] === 'Edit') {
                $this->execute("behat_navigation::i_navigate_to_node_in", [$parentnodes[0], $gradeadmin]);
                $this->execute("behat_general::click_link", "Edit grade letters");
                return;
            } else {
                array_pop($parentnodes);
            }
        }

        $lastitem = array_pop($parentnodes);
        if ($parentnodes && $parentnodes[0] === 'View') {
                                    array_shift($parentnodes);
        }
        array_unshift($parentnodes, $gradeadmin);
        $this->execute("behat_navigation::i_navigate_to_node_in", [$lastitem, join('>', $parentnodes)]);
    }
}
