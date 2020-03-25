<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../question/tests/behat/behat_question_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

use Behat\Mink\Exception\ExpectationException as ExpectationException;


class behat_mod_quiz extends behat_question_base {

    
    public function quiz_contains_the_following_questions($quizname, TableNode $data) {
        global $DB;

        $quiz = $DB->get_record('quiz', array('name' => $quizname), '*', MUST_EXIST);

                $firstrow = $data->getRow(0);
        if (!in_array('question', $firstrow) && !in_array('page', $firstrow)) {
            if (count($firstrow) == 2) {
                $headings = array('question', 'page');
            } else if (count($firstrow) == 3) {
                $headings = array('question', 'page', 'maxmark');
            } else {
                throw new ExpectationException('When adding questions to a quiz, you should give 2 or three 3 things: ' .
                        ' the question name, the page number, and optionally the maxiumum mark. ' .
                        count($firstrow) . ' values passed.', $this->getSession());
            }
            $rows = $data->getRows();
            array_unshift($rows, $headings);
            $data = new TableNode($rows);
        }

                $lastpage = 0;
        foreach ($data->getHash() as $questiondata) {
            if (!array_key_exists('question', $questiondata)) {
                throw new ExpectationException('When adding questions to a quiz, ' .
                        'the question name column is required.', $this->getSession());
            }
            if (!array_key_exists('page', $questiondata)) {
                throw new ExpectationException('When adding questions to a quiz, ' .
                        'the page number column is required.', $this->getSession());
            }

                        $questionid = $DB->get_field('question', 'id',
                    array('name' => $questiondata['question']), MUST_EXIST);

                        $page = clean_param($questiondata['page'], PARAM_INT);
            if ($page <= 0 || (string) $page !== $questiondata['page']) {
                throw new ExpectationException('The page number for question "' .
                         $questiondata['question'] . '" must be a positive integer.',
                        $this->getSession());
            }
            if ($page < $lastpage || $page > $lastpage + 1) {
                throw new ExpectationException('When adding questions to a quiz, ' .
                        'the page number for each question must either be the same, ' .
                        'or one more, then the page number for the previous question.',
                        $this->getSession());
            }
            $lastpage = $page;

                        if (!array_key_exists('maxmark', $questiondata) || $questiondata['maxmark'] === '') {
                $maxmark = null;
            } else {
                $maxmark = clean_param($questiondata['maxmark'], PARAM_FLOAT);
                if (!is_numeric($questiondata['maxmark']) || $maxmark < 0) {
                    throw new ExpectationException('The max mark for question "' .
                            $questiondata['question'] . '" must be a positive number.',
                            $this->getSession());
                }
            }

                        quiz_add_quiz_question($questionid, $quiz, $page, $maxmark);

                        if (array_key_exists('requireprevious', $questiondata)) {
                if ($questiondata['requireprevious'] === '1') {
                    $slot = $DB->get_field('quiz_slots', 'MAX(slot)', array('quizid' => $quiz->id));
                    $DB->set_field('quiz_slots', 'requireprevious', 1,
                            array('quizid' => $quiz->id, 'slot' => $slot));
                } else if ($questiondata['requireprevious'] !== '' && $questiondata['requireprevious'] !== '0') {
                    throw new ExpectationException('Require previous for question "' .
                            $questiondata['question'] . '" should be 0, 1 or blank.',
                            $this->getSession());
                }
            }
        }

        quiz_update_sumgrades($quiz);
    }

    
    public function quiz_contains_the_following_sections($quizname, TableNode $data) {
        global $DB;

        $quiz = $DB->get_record('quiz', array('name' => $quizname), '*', MUST_EXIST);

                $previousfirstslot = 0;
        foreach ($data->getHash() as $rownumber => $sectiondata) {
            if (!array_key_exists('heading', $sectiondata)) {
                throw new ExpectationException('When adding sections to a quiz, ' .
                        'the heading name column is required.', $this->getSession());
            }
            if (!array_key_exists('firstslot', $sectiondata)) {
                throw new ExpectationException('When adding sections to a quiz, ' .
                        'the firstslot name column is required.', $this->getSession());
            }
            if (!array_key_exists('shuffle', $sectiondata)) {
                throw new ExpectationException('When adding sections to a quiz, ' .
                        'the shuffle name column is required.', $this->getSession());
            }

            if ($rownumber == 0) {
                $section = $DB->get_record('quiz_sections', array('quizid' => $quiz->id), '*', MUST_EXIST);
            } else {
                $section = new stdClass();
                $section->quizid = $quiz->id;
            }

                        $section->heading = $sectiondata['heading'];

                        $section->firstslot = clean_param($sectiondata['firstslot'], PARAM_INT);
            if ($section->firstslot <= $previousfirstslot ||
                    (string) $section->firstslot !== $sectiondata['firstslot']) {
                throw new ExpectationException('The firstslot number for section "' .
                        $sectiondata['heading'] . '" must an integer greater than the previous section firstslot.',
                        $this->getSession());
            }
            if ($rownumber == 0 && $section->firstslot != 1) {
                throw new ExpectationException('The first section must have firstslot set to 1.',
                        $this->getSession());
            }

                        $section->shufflequestions = clean_param($sectiondata['shuffle'], PARAM_INT);
            if ((string) $section->shufflequestions !== $sectiondata['shuffle']) {
                throw new ExpectationException('The shuffle value for section "' .
                        $sectiondata['heading'] . '" must be 0 or 1.',
                        $this->getSession());
            }

            if ($rownumber == 0) {
                $DB->update_record('quiz_sections', $section);
            } else {
                $DB->insert_record('quiz_sections', $section);
            }
        }

        if ($section->firstslot > $DB->count_records('quiz_slots', array('quizid' => $quiz->id))) {
            throw new ExpectationException('The section firstslot must be less than the total number of slots in the quiz.',
                    $this->getSession());
        }
    }

    
    public function i_add_question_to_the_quiz_with($questiontype, $quizname, TableNode $questiondata) {
        $quizname = $this->escape($quizname);
        $editquiz = $this->escape(get_string('editquiz', 'quiz'));
        $quizadmin = $this->escape(get_string('pluginadministration', 'quiz'));
        $addaquestion = $this->escape(get_string('addaquestion', 'quiz'));
        $menuxpath = "//div[contains(@class, ' page-add-actions ')][last()]//a[contains(@class, ' textmenu')]";
        $itemxpath = "//div[contains(@class, ' page-add-actions ')][last()]//a[contains(@class, ' addquestion ')]";

        $this->execute('behat_general::click_link', $quizname);

        $this->execute("behat_navigation::i_navigate_to_node_in", array($editquiz, $quizadmin));

        $this->execute("behat_general::i_click_on", array($menuxpath, "xpath_element"));
        $this->execute("behat_general::i_click_on", array($itemxpath, "xpath_element"));

        $this->finish_adding_question($questiontype, $questiondata);
    }

    
    public function i_set_the_max_mark_for_quiz_question($questionname, $newmark) {
        $this->execute('behat_general::click_link', $this->escape(get_string('editmaxmark', 'quiz')));

        $this->execute('behat_general::wait_until_exists', array("li input[name=maxmark]", "css_element"));

        $this->execute('behat_general::assert_page_contains_text', $this->escape(get_string('edittitleinstructions')));

        $this->execute('behat_forms::i_set_the_field_to', array('maxmark', $this->escape($newmark) . chr(10)));
    }

    
    public function i_open_the_add_to_quiz_menu_for($pageorlast) {

        if (!$this->running_javascript()) {
            throw new DriverException('Activities actions menu not available when Javascript is disabled');
        }

        if ($pageorlast == 'last') {
            $xpath = "//div[@class = 'last-add-menu']//a[contains(@class, 'textmenu') and contains(., 'Add')]";
        } else if (preg_match('~Page (\d+)~', $pageorlast, $matches)) {
            $xpath = "//li[@id = 'page-{$matches[1]}']//a[contains(@class, 'textmenu') and contains(., 'Add')]";
        } else {
            throw new ExpectationException("The I open the add to quiz menu step must specify either 'Page N' or 'last'.");
        }
        $this->find('xpath', $xpath)->click();
    }

    
    public function i_follow_in_the_open_menu($linkstring) {
        $openmenuxpath = "//div[contains(@class, 'moodle-actionmenu') and contains(@class, 'show')]";

        $this->execute('behat_general::i_click_on_in_the',
            array($linkstring, "link", $openmenuxpath, "xpath_element")
        );

    }

    
    public function i_should_see_on_quiz_page($questionname, $pagenumber) {
        $xpath = "//li[contains(., '" . $this->escape($questionname) .
                "')][./preceding-sibling::li[contains(@class, 'pagenumber')][1][contains(., 'Page " .
                $pagenumber . "')]]";

        $this->execute('behat_general::should_exist', array($xpath, 'xpath_element'));
    }

    
    public function i_should_not_see_on_quiz_page($questionname, $pagenumber) {
        $xpath = "//li[contains(., '" . $this->escape($questionname) .
                "')][./preceding-sibling::li[contains(@class, 'pagenumber')][1][contains(., 'Page " .
                $pagenumber . "')]]";

        $this->execute('behat_general::should_not_exist', array($xpath, 'xpath_element'));
    }

    
    public function i_should_see_before_on_the_edit_quiz_page($firstquestionname, $secondquestionname) {
        $xpath = "//li[contains(@class, ' slot ') and contains(., '" . $this->escape($firstquestionname) .
                "')]/following-sibling::li[contains(@class, ' slot ')][1]" .
                "[contains(., '" . $this->escape($secondquestionname) . "')]";

        $this->execute('behat_general::should_exist', array($xpath, 'xpath_element'));
    }

    
    public function should_have_number_on_the_edit_quiz_page($questionname, $number) {
        $xpath = "//li[contains(@class, 'slot') and contains(., '" . $this->escape($questionname) .
                "')]//span[contains(@class, 'slotnumber') and normalize-space(text()) = '" . $this->escape($number) . "']";

        $this->execute('behat_general::should_exist', array($xpath, 'xpath_element'));
    }

    
    protected function get_xpath_page_break_icon_after_question($addorremoves, $questionname) {
        return "//li[contains(@class, 'slot') and contains(., '" . $this->escape($questionname) .
                "')]//a[contains(@class, 'page_split_join') and @title = '" . $addorremoves . " page break']";
    }

    
    public function i_click_on_the_page_break_icon_after_question($addorremoves, $questionname) {
        $xpath = $this->get_xpath_page_break_icon_after_question($addorremoves, $questionname);

        $this->execute("behat_general::i_click_on", array($xpath, "xpath_element"));
    }

    
    public function the_page_break_icon_after_question_should_exist($addorremoves, $questionname) {
        $xpath = $this->get_xpath_page_break_icon_after_question($addorremoves, $questionname);

        $this->execute('behat_general::should_exist', array($xpath, 'xpath_element'));
    }

    
    public function the_page_break_icon_after_question_should_not_exist($addorremoves, $questionname) {
        $xpath = $this->get_xpath_page_break_icon_after_question($addorremoves, $questionname);

        $this->execute('behat_general::should_not_exist', array($xpath, 'xpath_element'));
    }

    
    public function the_page_break_link_after_question_should_contain($addorremoves, $questionname, $paramdata) {
        $xpath = $this->get_xpath_page_break_icon_after_question($addorremoves, $questionname);

        $this->execute("behat_general::i_click_on", array($xpath, "xpath_element"));
    }

    
    public function i_click_on_shuffle_for_section($heading) {
        $xpath = $this->get_xpath_for_shuffle_checkbox($heading);
        $checkbox = $this->find('xpath', $xpath);
        $this->ensure_node_is_visible($checkbox);
        $checkbox->click();
    }

    
    public function shuffle_for_section_should_be($heading, $value) {
        $xpath = $this->get_xpath_for_shuffle_checkbox($heading);
        $checkbox = $this->find('xpath', $xpath);
        $this->ensure_node_is_visible($checkbox);
        if ($value == 'On' && !$checkbox->isChecked()) {
            $msg = "Shuffle for section '$heading' is not checked, but you are expecting it to be checked ($value). " .
                    "Check the line with: \nshuffle for section \"$heading\" should be \"$value\" on the quiz edit page" .
                    "\nin your behat script";
            throw new ExpectationException($msg, $this->getSession());
        } else if ($value == 'Off' && $checkbox->isChecked()) {
            $msg = "Shuffle for section '$heading' is checked, but you are expecting it not to be ($value). " .
                    "Check the line with: \nshuffle for section \"$heading\" should be \"$value\" on the quiz edit page" .
                    "\nin your behat script";
            throw new ExpectationException($msg, $this->getSession());
        }
    }

    
    protected function get_xpath_for_shuffle_checkbox($heading) {
         return "//div[contains(@class, 'section-heading') and contains(., '" . $this->escape($heading) .
                "')]//input[@type = 'checkbox']";
    }

    
    public function i_move_question_after_item_by_clicking_the_move_icon($questionname, $target) {
        $iconxpath = "//li[contains(@class, ' slot ') and contains(., '" . $this->escape($questionname) .
                "')]//span[contains(@class, 'editing_move')]";

        $this->execute("behat_general::i_click_on", array($iconxpath, "xpath_element"));
        $this->execute("behat_general::i_click_on", array($this->escape($target), "text"));
    }

    
    public function i_move_question_after_item_by_dragging($questionname, $target) {
        $iconxpath = "//li[contains(@class, ' slot ') and contains(., '" . $this->escape($questionname) .
                "')]//span[contains(@class, 'editing_move')]//img";
        $destinationxpath = "//li[contains(@class, ' slot ') or contains(@class, 'pagenumber ')]" .
                "[contains(., '" . $this->escape($target) . "')]";

        $this->execute('behat_general::i_drag_and_i_drop_it_in',
            array($iconxpath, 'xpath_element', $destinationxpath, 'xpath_element')
        );
    }

    
    public function i_delete_question_by_clicking_the_delete_icon($questionname) {
        $slotxpath = "//li[contains(@class, ' slot ') and contains(., '" . $this->escape($questionname) .
                "')]";
        $deletexpath = "//a[contains(@class, 'editing_delete')]";

        $this->execute("behat_general::i_click_on", array($slotxpath . $deletexpath, "xpath_element"));

        $this->execute('behat_general::i_click_on_in_the',
            array('Yes', "button", "Confirm", "dialogue")
        );
    }

    
    public function i_set_the_section_heading_for($sectionname, $sectionheading) {
        $this->execute('behat_general::click_link', $this->escape("Edit heading '{$sectionname}'"));

        $this->execute('behat_general::assert_page_contains_text', $this->escape(get_string('edittitleinstructions')));

        $this->execute('behat_forms::i_set_the_field_to', array('section', $this->escape($sectionheading) . chr(10)));
    }

    
    public function i_should_see_question_in_section_in_the_quiz_navigation($questionnumber, $sectionheading) {

                $questionnumberliteral = behat_context_helper::escape('Question ' . $questionnumber);
        $headingliteral = behat_context_helper::escape($sectionheading);

                $exception = new ExpectationException('Question "' . $questionnumber . '" is not in section "' .
                $sectionheading . '" in the quiz navigation.', $this->getSession());
        $xpath = "//div[@id = 'mod_quiz_navblock']//*[contains(concat(' ', normalize-space(@class), ' '), ' qnbutton ') and " .
                "contains(., {$questionnumberliteral}) and contains(preceding-sibling::h3[1], {$headingliteral})]";
        $this->find('xpath', $xpath);
    }
}
