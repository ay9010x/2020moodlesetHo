<?php




require_once(__DIR__ . '/../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Gherkin\Node\TableNode as TableNode;


class behat_deprecated extends behat_base {

    
    public function i_click_on_in_the_table_row($element, $selectortype, $tablerowtext) {
        $alternative = 'I click on "' . $this->escape($element) . '" "' . $this->escape($selectortype) .
            '" in the "' . $this->escape($tablerowtext) . '" "table_row"';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_go_to_notifications_page() {
        $alternative = array(
            'I expand "' . get_string('administrationsite') . '" node',
            'I click on "' . get_string('notifications') . '" "link" in the "'.get_string('administration').'" "block"'
        );
        $this->deprecated_message($alternative, true);
    }

    
    public function i_add_file_from_recent_files_to_filepicker($filename, $filepickerelement) {
        $reponame = get_string('pluginname', 'repository_recent');
        $alternative = 'I add "' . $this->escape($filename) . '" file from "' .
                $reponame . '" to "' . $this->escape($filepickerelement) . '" filemanager';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_upload_file_to_filepicker($filepath, $filepickerelement) {
        $alternative = 'I upload "' . $this->escape($filepath) . '" file to "' .
                $this->escape($filepickerelement) . '" filemanager';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_create_folder_in_filepicker($foldername, $filepickerelement) {
        $alternative = 'I create "' . $this->escape($foldername) .
                '" folder in "' . $this->escape($filepickerelement) . '" filemanager';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_open_folder_from_filepicker($foldername, $filepickerelement) {
        $alternative = 'I open "' . $this->escape($foldername) . '" folder from "' .
                $this->escape($filepickerelement) . '" filemanager';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_unzip_file_from_filepicker($filename, $filepickerelement) {
        $alternative = 'I unzip "' . $this->escape($filename) . '" file from "' .
                $this->escape($filepickerelement) . '" filemanager';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_zip_folder_from_filepicker($foldername, $filepickerelement) {
        $alternative = 'I zip "' . $this->escape($foldername) . '" folder from "' .
                $this->escape($filepickerelement) . '" filemanager';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_delete_file_from_filepicker($name, $filepickerelement) {
        $alternative = 'I delete "' . $this->escape($name) . '" from "' .
                $this->escape($filepickerelement) . '" filemanager';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_send_message_to_user($messagecontent, $tousername) {
        $alternative = 'I send "' . $this->escape($messagecontent) . '" message to "USER_FULL_NAME" user';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_add_user_to_cohort($username, $cohortidnumber) {
        $alternative = 'I add "USER_FIRST_NAME USER_LAST_NAME (USER_EMAIL)" user to "'
                . $this->escape($cohortidnumber) . '" cohort members';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_add_user_to_group($username, $groupname) {
        $alternative = 'I add "USER_FULL_NAME" user to "' . $this->escape($groupname) . '" group members';
        $this->deprecated_message($alternative, true);
    }

    
    public function fill_field($field, $value) {
        $alternative = 'I set the field "' . $this->escape($field) . '" to "' . $this->escape($value) . '"';
        $this->deprecated_message($alternative, true);
    }

    
    public function select_option($option, $select) {
        $alternative = 'I set the field "' . $this->escape($select) . '" to "' . $this->escape($option) . '"';
        $this->deprecated_message($alternative, true);
    }

    
    public function select_radio($radio) {
        $alternative = 'I set the field "' . $this->escape($radio) . '" to "1"';
        $this->deprecated_message($alternative, true);
    }

    
    public function check_option($option) {
        $alternative = 'I set the field "' . $this->escape($option) . '" to "1"';
        $this->deprecated_message($alternative, true);
    }

    
    public function uncheck_option($option) {
        $alternative = 'I set the field "' . $this->escape($option) . '" to ""';
        $this->deprecated_message($alternative, true);
    }

    
    public function the_field_should_match_value($locator, $value) {
        $alternative = 'the field "' . $this->escape($locator) . '" matches value "' . $this->escape($value) . '"';
        $this->deprecated_message($alternative, true);
    }

    
    public function assert_checkbox_checked($checkbox) {
        $alternative = 'the field "' . $this->escape($checkbox) . '" matches value "1"';
        $this->deprecated_message($alternative, true);
    }

    
    public function assert_checkbox_not_checked($checkbox) {
        $alternative = 'the field "' . $this->escape($checkbox) . '" matches value ""';
        $this->deprecated_message($alternative, true);
    }

    
    public function i_fill_the_moodle_form_with(TableNode $data) {
        $alternative = 'I set the following fields to these values:';
        $this->deprecated_message($alternative, true);
    }

    
    public function should_exists($element, $selectortype) {
        $alternative = '"' . $this->escape($element) . '" "' . $this->escape($selectortype) . '" should exist';
        $this->deprecated_message($alternative, true);
    }

    
    public function should_not_exists($element, $selectortype) {
        $alternative = '"' . $this->escape($element) . '" "' . $this->escape($selectortype) . '" should not exist';
        $this->deprecated_message($alternative, true);
    }

    
    public function the_following_exists($elementname, TableNode $data) {
        $alternative = 'the following "' . $this->escape($elementname) . '" exist:';
        $this->deprecated_message($alternative, true);
    }


    
    protected function deprecated_message($alternatives, $throwexception = false) {
        global $CFG;

                if (!empty($CFG->behat_usedeprecated) && !$throwexception) {
            return;
        }

        if (is_scalar($alternatives)) {
            $alternatives = array($alternatives);
        }

                if ($throwexception) {
            $message = 'This step has been removed. Rather than using this step you can:';
        } else {
            $message = 'Deprecated step, rather than using this step you can:';
        }

                foreach ($alternatives as $alternative) {
            $message .= PHP_EOL . '- ' . $alternative;
        }

        if (!$throwexception) {
            $message .= PHP_EOL . '- Set $CFG->behat_usedeprecated in config.php to allow the use of deprecated steps
                    if you don\'t have any other option';
        }

        throw new Exception($message);
    }

}
