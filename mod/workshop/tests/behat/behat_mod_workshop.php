<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;


class behat_mod_workshop extends behat_base {
    
    public function i_change_phase_in_workshop_to($workshopname, $phase) {
        $workshopname = $this->escape($workshopname);
        $phaseliteral = behat_context_helper::escape($phase);
        $switchphase = behat_context_helper::escape(get_string('switchphase', 'workshop'));

        $xpath = "//*[@class='userplan']/descendant::div[./span[contains(.,$phaseliteral)]]/".
                "descendant-or-self::a[./img[@alt=$switchphase]]";
        $continue = $this->escape(get_string('continue'));

        $this->execute('behat_general::click_link', $workshopname);

        $this->execute("behat_general::i_click_on", array($xpath, "xpath_element"));

        $this->execute("behat_forms::press_button", $continue);
    }

    
    public function i_add_a_submission_in_workshop_as($workshopname, $table) {
        $workshopname = $this->escape($workshopname);
        $savechanges = $this->escape(get_string('savechanges'));
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' ownsubmission ')]/descendant::input[@type='submit']";

        $this->execute('behat_general::click_link', $workshopname);

        $this->execute("behat_general::i_click_on", array($xpath, "xpath_element"));

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", $savechanges);
    }

    
    public function i_edit_assessment_form_in_workshop_as($workshopname, $table) {
        $workshopname = $this->escape($workshopname);
        $editassessmentform = $this->escape(get_string('editassessmentform', 'workshop'));
        $saveandclose = $this->escape(get_string('saveandclose', 'workshop'));

        $this->execute('behat_general::click_link', $workshopname);

        $this->execute('behat_general::click_link', $editassessmentform);

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", $saveandclose);
    }

    
    public function i_assess_submission_in_workshop_as($submission, $workshopname, TableNode $table) {
        $workshopname = $this->escape($workshopname);
        $submissionliteral = behat_context_helper::escape($submission);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' assessment-summary ') ".
                "and contains(.,$submissionliteral)]";
        $assess = $this->escape(get_string('assess', 'workshop'));
        $saveandclose = $this->escape(get_string('saveandclose', 'workshop'));

        $this->execute('behat_general::click_link', $workshopname);

        $this->execute('behat_general::i_click_on_in_the',
            array($assess, "button", $xpath, "xpath_element")
        );

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $table);

        $this->execute("behat_forms::press_button", $saveandclose);
    }

    
    public function i_should_see_grade_for_workshop_participant_set_by_peer($grade, $participant, $reviewer) {
        $participantliteral = behat_context_helper::escape($participant);
        $reviewerliteral = behat_context_helper::escape($reviewer);
        $gradeliteral = behat_context_helper::escape($grade);
        $participantselector = "contains(concat(' ', normalize-space(@class), ' '), ' participant ') ".
                "and contains(.,$participantliteral)";
        $trxpath = "//table/tbody/tr[td[$participantselector]]";
        $tdparticipantxpath = "//table/tbody/tr/td[$participantselector]";
        $tdxpath = "/td[contains(concat(' ', normalize-space(@class), ' '), ' receivedgrade ') and contains(.,$reviewerliteral)]/".
                "descendant::span[contains(concat(' ', normalize-space(@class), ' '), ' grade ') and .=$gradeliteral]";

        $tr = $this->find('xpath', $trxpath);
        $rowspan = $this->find('xpath', $tdparticipantxpath)->getAttribute('rowspan');

        $xpath = $trxpath.$tdxpath;
        if (!empty($rowspan)) {
            for ($i = 1; $i < $rowspan; $i++) {
                $xpath .= ' | '.$trxpath."/following-sibling::tr[$i]".$tdxpath;
            }
        }
        $this->find('xpath', $xpath);
    }
}
