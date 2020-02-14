<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException;


class behat_mod_feedback extends behat_base {

    
    public function i_add_question_to_the_feedback_with($questiontype, TableNode $questiondata) {

        $questiontype = $this->escape($questiontype);
        $additem = $this->escape(get_string('add_item', 'feedback'));

        $this->execute('behat_forms::i_select_from_the_singleselect', array($questiontype, $additem));

                $this->execute('behat_general::i_wait_to_be_redirected');

        $rows = $questiondata->getRows();
        $modifiedrows = array();
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                $row[$key] = preg_replace('|\\\\n|', "\n", $value);
            }
            $modifiedrows[] = $row;
        }
        $newdata = new TableNode($modifiedrows);

        $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $newdata);

        $saveitem = $this->escape(get_string('save_item', 'feedback'));
        $this->execute("behat_forms::press_button", $saveitem);
    }

    
    public function i_log_in_as_and_complete_feedback_in_course($username, $feedbackname, $coursename, TableNode $answers) {
        $username = $this->escape($username);
        $coursename = $this->escape($coursename);
        $feedbackname = $this->escape($feedbackname);
        $completeform = $this->escape(get_string('complete_the_form', 'feedback'));

                $this->execute('behat_auth::i_log_in_as', $username);

                $this->execute('behat_general::click_link', $coursename);
        $this->execute('behat_general::click_link', $feedbackname);
        $this->execute('behat_general::click_link', $completeform);

                $this->execute("behat_forms::i_set_the_following_fields_to_these_values", $answers);
        $this->execute("behat_forms::press_button", 'Submit your answers');

                $this->execute('behat_auth::i_log_out');
    }

    
    public function following_should_export_feedback_identical_to($link, $filename) {
        global $CFG;
        $exception = new ExpectationException('Error while downloading data from ' . $link, $this->getSession());

                $behatgeneralcontext = behat_context_helper::get('behat_general');
        $result = $this->spin(
            function($context, $args) use ($behatgeneralcontext) {
                $link = $args['link'];
                return $behatgeneralcontext->download_file_from_link($link);
            },
            array('link' => $link),
            self::EXTENDED_TIMEOUT,
            $exception
        );

        $this->compare_exports(file_get_contents($CFG->dirroot . '/' . $filename), $result);
    }

    
    protected function compare_exports($expected, $actual) {
        $dataexpected = xmlize($expected, 1, 'UTF-8');
        $dataexpected = $dataexpected['FEEDBACK']['#']['ITEMS'][0]['#']['ITEM'];
        $dataactual = xmlize($actual, 1, 'UTF-8');
        $dataactual = $dataactual['FEEDBACK']['#']['ITEMS'][0]['#']['ITEM'];

        if (count($dataexpected) != count($dataactual)) {
            throw new ExpectationException('Expected ' . count($dataexpected) .
                    ' items in the export file, found ' . count($dataactual), $this->getSession());
        }

        $itemmapping = array();
        $itemactual = reset($dataactual);
        foreach ($dataexpected as $idx => $itemexpected) {
                        $itemmapping[intval($itemactual['#']['ITEMID'][0]['#'])] = intval($itemexpected['#']['ITEMID'][0]['#']);
            $itemactual['#']['ITEMID'][0]['#'] = $itemexpected['#']['ITEMID'][0]['#'];
            $expecteddependitem = $actualdependitem = 0;
            if (isset($itemexpected['#']['DEPENDITEM'][0]['#'])) {
                $expecteddependitem = intval($itemexpected['#']['DEPENDITEM'][0]['#']);
            }
            if (isset($itemactual['#']['DEPENDITEM'][0]['#'])) {
                $actualdependitem = intval($itemactual['#']['DEPENDITEM'][0]['#']);
            }
            if ($expecteddependitem && !$actualdependitem) {
                throw new ExpectationException('Expected DEPENDITEM in ' . ($idx + 1) . 'th item', $this->getSession());
            }
            if (!$expecteddependitem && $actualdependitem) {
                throw new ExpectationException('Unexpected DEPENDITEM in ' . ($idx + 1) . 'th item', $this->getSession());
            }
            if ($expecteddependitem && $actualdependitem) {
                if (!isset($itemmapping[$actualdependitem]) || $itemmapping[$actualdependitem] != $expecteddependitem) {
                    throw new ExpectationException('Unknown DEPENDITEM in ' . ($idx + 1) . 'th item', $this->getSession());
                }
                $itemactual['#']['DEPENDITEM'][0]['#'] = $itemexpected['#']['DEPENDITEM'][0]['#'];
            }
                        if (json_encode($itemexpected) !== json_encode($itemactual)) {
                throw new ExpectationException('Actual ' . ($idx + 1) . 'th item does not match expected', $this->getSession());
            }
                        $itemactual = next($dataactual);
        }
    }
}
