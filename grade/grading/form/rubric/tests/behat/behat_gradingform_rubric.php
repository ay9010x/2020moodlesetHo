<?php




require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException as ExpectationException;


class behat_gradingform_rubric extends behat_base {

    
    const DEFAULT_RUBRIC_LEVELS = 3;

    
    public function i_define_the_following_rubric(TableNode $rubric) {

                        
        $steptableinfo = '| criterion description | level1 name  | level1 points | level2 name | level2 points | ...';

        $criteria = $rubric->getRows();

        $addcriterionbutton = $this->find_button(get_string('addcriterion', 'gradingform_rubric'));

                $deletebuttons = $this->find_all('css', "input[value='" . get_string('criteriondelete', 'gradingform_rubric') . "']");
        if ($deletebuttons) {

                                    $deletebuttons = array_reverse($deletebuttons, true);
            foreach ($deletebuttons as $button) {
                $this->click_and_confirm($button);
            }
        }

                $levelnumber = 1;

                $defaultnumberoflevels = self::DEFAULT_RUBRIC_LEVELS;

        if ($criteria) {
            foreach ($criteria as $criterionit => $criterion) {
                                foreach ($criterion as $i => $value) {
                    if (empty($value)) {
                        unset($criterion[$i]);
                    }
                }

                                $newcriterion = array();
                foreach ($criterion as $k => $c) {
                    if (!empty($c)) {
                        $newcriterion[$k] = $c;
                    }
                }
                $criterion = $newcriterion;

                                if (count($criterion) % 2 === 0) {
                    throw new ExpectationException(
                        'The criterion levels should contain both definition and points, follow this format:' . $steptableinfo,
                        $this->getSession()
                    );
                }

                                                if (count($criterion) < 5) {
                    throw new ExpectationException(
                        get_string('err_mintwolevels', 'gradingform_rubric'),
                        $this->getSession()
                    );

                }

                                $addcriterionbutton->click();

                $criterionroot = 'rubric[criteria][NEWID' . ($criterionit + 1) . ']';

                                $this->set_rubric_field_value($criterionroot . '[description]', array_shift($criterion), true);

                                if (!$this->running_javascript()) {
                    $levelnumber = 0;
                }

                                $nlevels = count($criterion) / 2;
                if ($nlevels < $defaultnumberoflevels) {

                                                            $lastcriteriondefaultlevel = $defaultnumberoflevels + $levelnumber - 1;
                    $lastcriterionlevel = $nlevels + $levelnumber - 1;
                    for ($i = $lastcriteriondefaultlevel; $i > $lastcriterionlevel; $i--) {

                                                if ($this->running_javascript()) {
                            $deletelevel = $this->find_button($criterionroot . '[levels][NEWID' . $i . '][delete]');
                            $this->click_and_confirm($deletelevel);

                        } else {
                                                        $buttonname = $criterionroot . '[levels][NEWID' . $i . '][delete]';
                            if ($deletelevel = $this->getSession()->getPage()->findButton($buttonname)) {
                                $deletelevel->click();
                            }
                        }
                    }
                } else if ($nlevels > $defaultnumberoflevels) {
                                        $addlevel = $this->find_button($criterionroot . '[levels][addlevel]');
                    for ($i = ($defaultnumberoflevels + 1); $i <= $nlevels; $i++) {
                        $addlevel->click();
                    }
                }

                                if ($nlevels > self::DEFAULT_RUBRIC_LEVELS) {
                    $defaultnumberoflevels = $nlevels;
                } else {
                                                            $defaultnumberoflevels = self::DEFAULT_RUBRIC_LEVELS;
                }

                foreach ($criterion as $i => $value) {

                    $levelroot = $criterionroot . '[levels][NEWID' . $levelnumber . ']';

                    if ($i % 2 === 0) {
                                                $fieldname = $levelroot . '[definition]';
                        $this->set_rubric_field_value($fieldname, $value);

                    } else {
                        
                                                if (!is_numeric($value)) {
                            throw new ExpectationException(
                                'The points cells should contain numeric values, follow this format: ' . $steptableinfo,
                                $this->getSession()
                            );
                        }

                        $fieldname = $levelroot . '[score]';
                        $this->set_rubric_field_value($fieldname, $value, true);

                                                $levelnumber++;
                    }

                }
            }
        }
    }

    
    public function i_replace_rubric_level_with($currentvalue, $value, $criterionname) {

        $currentvalueliteral = behat_context_helper::escape($currentvalue);
        $criterionliteral = behat_context_helper::escape($criterionname);

        $criterionxpath = "//div[@id='rubric-rubric']" .
            "/descendant::td[contains(concat(' ', normalize-space(@class), ' '), ' description ')]";
                if ($this->running_javascript()) {
            $criterionxpath .= "/descendant::span[@class='textvalue'][text()=$criterionliteral]" .
                "/ancestor::tr[contains(concat(' ', normalize-space(@class), ' '), ' criterion ')]";
        } else {
            $criterionxpath .= "/descendant::textarea[text()=$criterionliteral]" .
                "/ancestor::tr[contains(concat(' ', normalize-space(@class), ' '), ' criterion ')]";
        }

        $inputxpath = $criterionxpath .
            "/descendant::input[@type='text'][@value=$currentvalueliteral]";
        $textareaxpath = $criterionxpath .
            "/descendant::textarea[text()=$currentvalueliteral]";

        if ($this->running_javascript()) {

            $spansufix = "/ancestor::div[@class='level-wrapper']" .
                "/descendant::div[@class='definition']" .
                "/descendant::span[@class='textvalue']";

                        $spannode = $this->find('xpath', $inputxpath . $spansufix . '|' . $textareaxpath . $spansufix);
            $spannode->click();

            $inputfield = $this->find('xpath', $inputxpath . '|' . $textareaxpath);
            $inputfield->setValue($value);

        } else {
            $fieldnode = $this->find('xpath', $inputxpath . '|' . $textareaxpath);
            $this->set_rubric_field_value($fieldnode->getAttribute('name'), $value);
        }

    }

    
    public function i_grade_by_filling_the_rubric_with(TableNode $rubric) {

        $criteria = $rubric->getRowsHash();

        $stepusage = '"I grade by filling the rubric with:" step needs you to provide a table where each row is a criterion' .
            ' and each criterion has 3 different values: | Criterion name | Number of points | Remark text |';

                if ($this->running_javascript()) {
            $this->execute('behat_general::click_link', get_string('togglezoom', 'mod_assign'));
        }

                foreach ($criteria as $name => $criterion) {

                        if (count($criterion) !== 2) {
                throw new ExpectationException($stepusage, $this->getSession());
            }

                        $points = $criterion[0];
            if (!is_numeric($points)) {
                throw new ExpectationException($stepusage, $this->getSession());
            }

                                    $selectedlevelxpath = $this->get_level_xpath($points);
            if ($this->running_javascript()) {

                                $levelnode = $this->find('xpath', $selectedlevelxpath);

                                if (!in_array('checked', explode(' ', $levelnode->getAttribute('class')))) {
                    $this->execute('behat_general::i_click_on_in_the',
                        array($selectedlevelxpath, "xpath_element", $this->escape($name), "table_row")
                    );
                }

            } else {

                                $radioxpath = $this->get_criterion_xpath($name) .
                    $selectedlevelxpath . "/descendant::input[@type='radio']";
                $radionode = $this->find('xpath', $radioxpath);
                                $radionode->setValue($radionode->getAttribute('value'));
            }

            
                        $textarea = $this->get_node_in_container('css_element', 'textarea', 'table_row', $name);
            $this->execute('behat_forms::i_set_the_field_to', array($textarea->getAttribute('name'), $criterion[1]));
        }

                if ($this->running_javascript()) {
            $this->execute('behat_general::click_link', get_string('togglezoom', 'mod_assign'));
        }
    }

    
    public function the_level_with_points_was_previously_selected_for_the_rubric_criterion($points, $criterionname) {

        $levelxpath = $this->get_criterion_xpath($criterionname) .
            $this->get_level_xpath($points) .
            "[contains(concat(' ', normalize-space(@class), ' '), ' currentchecked ')]";

                                        $levelxpath .= "[not(contains(concat(' ', normalize-space(@class), ' '), ' checked '))]" .
            "[not(/descendant::input[@type='radio'][@checked!='checked'])]";

        try {
            $this->find('xpath', $levelxpath);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"' . $points . '" points level was not previously selected', $this->getSession());
        }
    }

    
    public function the_level_with_points_is_selected_for_the_rubric_criterion($points, $criterionname) {

        $levelxpath = $this->get_criterion_xpath($criterionname) .
            $this->get_level_xpath($points);

                                        $levelxpath .= "[" .
            "contains(concat(' ', normalize-space(@class), ' '), ' checked ')" .
            " or " .
            "/descendant::input[@type='radio'][@checked='checked']" .
            "]";

        try {
            $this->find('xpath', $levelxpath);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"' . $points . '" points level is not selected', $this->getSession());
        }
    }

    
    public function the_level_with_points_is_not_selected_for_the_rubric_criterion($points, $criterionname) {

        $levelxpath = $this->get_criterion_xpath($criterionname) .
            $this->get_level_xpath($points);

                                        $levelxpath .= "[not(contains(concat(' ', normalize-space(@class), ' '), ' checked '))]" .
            "[./descendant::input[@type='radio'][@checked!='checked'] or not(./descendant::input[@type='radio'])]";

        try {
            $this->find('xpath', $levelxpath);
        } catch (ElementNotFoundException $e) {
            throw new ExpectationException('"' . $points . '" points level is selected', $this->getSession());
        }
    }


    
    protected function set_rubric_field_value($name, $value, $visible = false) {

                if ($this->running_javascript() == true && $visible === false) {
            $xpath = "//*[@name='$name']/following-sibling::*[contains(concat(' ', normalize-space(@class), ' '), ' plainvalue ')]";
            $textnode = $this->find('xpath', $xpath);
            $textnode->click();
        }

                $description = $this->find_field($name);
        $description->setValue($value);
    }

    
    protected function click_and_confirm($node) {

                $node->click();

                if ($this->running_javascript()) {
            $confirmbutton = $this->get_node_in_container(
                'button',
                get_string('yes'),
                'dialogue',
                get_string('confirmation', 'admin')
            );
            $confirmbutton->click();
        }
    }

    
    protected function get_level_xpath($points) {
        return "//td[contains(concat(' ', normalize-space(@class), ' '), ' level ')]" .
            "[./descendant::span[@class='scorevalue'][text()='$points']]";
    }

    
    protected function get_criterion_xpath($criterionname) {
        $literal = behat_context_helper::escape($criterionname);
        return "//tr[contains(concat(' ', normalize-space(@class), ' '), ' criterion ')]" .
            "[./descendant::td[@class='description'][text()=$literal]]";
    }
}
