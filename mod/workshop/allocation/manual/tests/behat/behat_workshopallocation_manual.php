<?php




require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../../../lib/behat/behat_field_manager.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementTextException as ElementTextException;


class behat_workshopallocation_manual extends behat_base {
    
    public function i_add_a_reviewer_for_workshop_participant($reviewername, $participantname) {
        $participantnameliteral = behat_context_helper::escape($participantname);
        $xpathtd = "//table[contains(concat(' ', normalize-space(@class), ' '), ' allocations ')]/".
                "tbody/tr[./td[contains(concat(' ', normalize-space(@class), ' '), ' peer ')]".
                "[contains(.,$participantnameliteral)]]/".
                "td[contains(concat(' ', normalize-space(@class), ' '), ' reviewedby ')]";
        $xpathselect = $xpathtd . "/descendant::select";
        try {
            $selectnode = $this->find('xpath', $xpathselect);
        } catch (Exception $ex) {
            $this->find_button(get_string('showallparticipants', 'workshopallocation_manual'))->press();
            $selectnode = $this->find('xpath', $xpathselect);
        }

        $selectformfield = behat_field_manager::get_form_field($selectnode, $this->getSession());
        $selectformfield->set_value($reviewername);

        if (!$this->running_javascript()) {
                        $go = behat_context_helper::escape(get_string('go'));
            $this->find('xpath', $xpathtd."/descendant::input[@value=$go]")->click();
        } else {
                        $this->getSession()->wait(self::EXTENDED_TIMEOUT, self::PAGE_READY_JS);
        }
                $allocatedtext = behat_context_helper::escape(
            get_string('allocationadded', 'workshopallocation_manual'));
        $this->find('xpath', "//*[contains(.,$allocatedtext)]");
    }

    
    public function i_allocate_submissions_in_workshop_as($workshopname, TableNode $table) {

        $this->find_link($workshopname)->click();
        $this->find_link(get_string('allocate', 'workshop'))->click();
        $rows = $table->getRows();
        $reviewer = $participant = null;
        for ($i = 0; $i < count($rows[0]); $i++) {
            if (strtolower($rows[0][$i]) === 'reviewer') {
                $reviewer = $i;
            } else if (strtolower($rows[0][$i]) === 'reviewee' || strtolower($rows[0][$i]) === 'participant') {
                $participant = $i;
            } else {
                throw new ElementTextException('Unrecognised column "'.$rows[0][$i].'"', $this->getSession());
            }
        }
        if ($reviewer === null) {
            throw new ElementTextException('Column "Reviewer" could not be located', $this->getSession());
        }
        if ($participant === null) {
            throw new ElementTextException('Neither "Participant" nor "Reviewee" column could be located', $this->getSession());
        }
        for ($i = 1; $i < count($rows); $i++) {
            $this->i_add_a_reviewer_for_workshop_participant($rows[$i][$reviewer], $rows[$i][$participant]);
        }
    }
}
