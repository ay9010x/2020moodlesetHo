<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;


class behat_grading extends behat_base {

    
    public function i_go_to_advanced_grading_page($activityname) {

        $this->execute('behat_general::click_link', $this->escape($activityname));

        $this->execute('behat_general::click_link', get_string('gradingmanagement', 'grading'));
    }

    
    public function i_go_to_advanced_grading_definition_page($activityname) {

                $newactionliteral = behat_context_helper::escape(get_string("manageactionnew", "grading"));
        $editactionliteral = behat_context_helper::escape(get_string("manageactionedit", "grading"));

                $definitionxpath = "//a[@class='action']" .
            "[./descendant::*[contains(., $newactionliteral) or contains(., $editactionliteral)]]";

        $this->execute('behat_grading::i_go_to_advanced_grading_page', $this->escape($activityname));

        $this->execute("behat_general::i_click_on", array($this->escape($definitionxpath), "xpath_element"));
    }
    
    public function i_go_to_activity_advanced_grading_page($userfullname, $activityname) {

                $gradetext = get_string('grade');

        $this->execute('behat_general::click_link', $this->escape($activityname));

        $this->execute('behat_general::click_link', $this->escape(get_string('viewgrading', 'assign')));

        $this->execute('behat_general::i_click_on_in_the',
                       array(
                           $this->escape($gradetext),
                           'link',
                           $this->escape($userfullname),
                           'table_row'
                       ));
    }

    
    public function i_publish_grading_form_definition_as_a_public_template($activityname) {

        $this->execute('behat_grading::i_go_to_advanced_grading_page', $this->escape($activityname));

        $this->execute("behat_general::i_click_on", array($this->escape(get_string("manageactionshare", "grading")), "link"));

        $this->execute('behat_forms::press_button', get_string('continue'));
    }

    
    public function i_set_activity_to_use_grading_form($activityname, $templatename) {

        $templateliteral = behat_context_helper::escape($templatename);

        $templatexpath = "//h2[@class='template-name'][contains(., $templateliteral)]/" .
            "following-sibling::div[contains(concat(' ', normalize-space(@class), ' '), ' template-actions ')]";

                $literaltemplate = behat_context_helper::escape(get_string('templatepick', 'grading'));
        $literalownform = behat_context_helper::escape(get_string('templatepickownform', 'grading'));
        $usetemplatexpath = "/a[./descendant::div[text()=$literaltemplate]]|" .
            "/a[./descendant::div[text()=$literalownform]]";

        $this->execute('behat_grading::i_go_to_advanced_grading_page', $this->escape($activityname));

        $this->execute('behat_general::click_link', $this->escape(get_string('manageactionclone', 'grading')));
        $this->execute('behat_forms::i_set_the_field_to', array(get_string('searchownforms', 'grading'), 1));
        $this->execute('behat_general::i_click_on_in_the',
            array(get_string('search'), "button", "region-main", "region")
        );
        $this->execute('behat_general::i_click_on_in_the',
            array($this->escape($usetemplatexpath), "xpath_element", $this->escape($templatexpath), "xpath_element")
        );
        $this->execute('behat_forms::press_button', get_string('continue'));

    }

    
    public function i_save_the_advanced_grading_form() {

        $this->execute('behat_forms::press_button', get_string('savechanges'));
        $this->execute('behat_forms::press_button', 'Ok');
        $this->execute('behat_general::i_click_on', array($this->escape(get_string('editsettings')), 'link'));
        $this->execute('behat_forms::press_button', get_string('cancel'));
        $this->execute('behat_general::i_click_on', array($this->escape(get_string('viewgrading', 'mod_assign')), 'link'));
    }

    
    public function i_complete_the_advanced_grading_form_with_these_values(TableNode $data) {
        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $data);
        $this->execute('behat_grading::i_save_the_advanced_grading_form');
    }
}
