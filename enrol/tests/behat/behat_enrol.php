<?php




require_once(__DIR__ . '/../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;


class behat_enrol extends behat_base {

    
    public function i_add_enrolment_method_with($enrolmethod, TableNode $table) {
                $parentnodes = get_string('courseadministration') . ' > ' . get_string('users', 'admin');
        $this->execute("behat_navigation::i_navigate_to_node_in",
            array(get_string('type_enrol_plural', 'plugin'), $parentnodes)
        );

                $this->execute('behat_forms::i_select_from_the_singleselect',
            array($this->escape($enrolmethod), get_string('addinstance', 'enrol'))
        );

                $this->execute('behat_general::i_wait_to_be_redirected');

                $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

                $this->execute("behat_forms::press_button", get_string('addinstance', 'enrol'));

    }

    
    public function i_enrol_user_as($userfullname, $rolename) {

                $parentnodes = get_string('courseadministration') . ' > ' . get_string('users', 'admin');
        $this->execute("behat_navigation::i_navigate_to_node_in",
            array(get_string('enrolledusers', 'enrol'), $parentnodes)
        );

        $this->execute("behat_forms::press_button", get_string('enrolusers', 'enrol'));

        if ($this->running_javascript()) {
            $this->execute('behat_forms::i_set_the_field_to', array(get_string('assignroles', 'role'), $rolename));

                        $userliteral = behat_context_helper::escape($userfullname);
            $userrowxpath = "//div[contains(concat(' ',normalize-space(@class),' '),' user ')][contains(., $userliteral)]";

            $this->execute('behat_general::i_click_on_in_the',
                array(get_string('enrol', 'enrol'), "button", $userrowxpath, "xpath_element")
            );
            $this->execute("behat_forms::press_button", get_string('finishenrollingusers', 'enrol'));

        } else {
            $this->execute('behat_forms::i_set_the_field_to', array(get_string('assignrole', 'role'), $rolename));
            $this->execute('behat_forms::i_set_the_field_to', array("addselect", $userfullname));
            $this->execute("behat_forms::press_button", "add");
        }
    }

}
