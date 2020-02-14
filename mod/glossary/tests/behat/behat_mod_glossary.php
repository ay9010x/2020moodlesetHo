<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;


class behat_mod_glossary extends behat_base {

    
    public function i_add_a_glossary_entry_with_the_following_data(TableNode $data) {
        $this->execute("behat_forms::press_button", get_string('addentry', 'mod_glossary'));

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $data);

        $this->execute("behat_forms::press_button", get_string('savechanges'));
    }

    
    public function i_add_a_glossary_entries_category_named($categoryname) {

        $this->execute("behat_general::click_link", get_string('categoryview', 'mod_glossary'));

        $this->execute("behat_forms::press_button", get_string('editcategories', 'mod_glossary'));

        $this->execute("behat_forms::press_button", get_string('add').' '.get_string('category', 'glossary'));

        $this->execute('behat_forms::i_set_the_field_to', array('name', $this->escape($categoryname)));

        $this->execute("behat_forms::press_button", get_string('savechanges'));
        $this->execute("behat_forms::press_button", get_string('back', 'mod_glossary'));
    }
}
