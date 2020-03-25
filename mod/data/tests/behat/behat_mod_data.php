<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

class behat_mod_data extends behat_base {

    
    public function i_add_a_field_to_database_and_i_fill_the_form_with($fieldtype, $activityname, TableNode $fielddata) {

        $this->execute("behat_general::click_link", $this->escape($activityname));
        $this->execute("behat_general::click_link", get_string('fields', 'mod_data'));

        $this->execute('behat_forms::i_set_the_field_to', array('newtype', $this->escape($fieldtype)));

        if (!$this->running_javascript()) {
            $this->execute('behat_general::i_click_on_in_the',
                array(get_string('go'), "button", ".fieldadd", "css_element")
            );
        }

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $fielddata);
        $this->execute('behat_forms::press_button', get_string('add'));
    }

    
    public function i_add_an_entry_to_database_with($activityname, TableNode $entrydata) {

        $this->execute("behat_general::click_link", $this->escape($activityname));
        $this->execute("behat_general::click_link", get_string('add', 'mod_data'));

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $entrydata);
    }
}
