<?php



require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException as ExpectationException;


class behat_gradingform_guide extends behat_base {

    
    public function i_define_the_following_marking_guide(TableNode $guide) {
        $steptableinfo = '| Criterion name | Description for students | Description for markers | Maximum score |';

        if ($criteria = $guide->getHash()) {
            $addcriterionbutton = $this->find_button(get_string('addcriterion', 'gradingform_guide'));

            foreach ($criteria as $index => $criterion) {
                                if (count($criterion) != 4) {
                    throw new ExpectationException(
                        'The criterion definition should contain name, description for students and markers, and maximum points. ' .
                        'Please follow this format: ' . $steptableinfo,
                        $this->getSession()
                    );
                }

                                $shortnamevisible = false;
                if ($index > 0) {
                                        $addcriterionbutton->click();
                    $shortnamevisible = true;
                }

                $criterionroot = 'guide[criteria][NEWID' . ($index + 1) . ']';

                                $this->set_guide_field_value($criterionroot . '[shortname]', $criterion['Criterion name'], $shortnamevisible);

                                $this->set_guide_field_value($criterionroot . '[description]', $criterion['Description for students']);

                                $this->set_guide_field_value($criterionroot . '[descriptionmarkers]', $criterion['Description for markers']);

                                $this->set_guide_field_value($criterionroot . '[maxscore]', $criterion['Maximum score']);
            }
        }
    }

    
    public function i_define_the_following_frequently_used_comments(TableNode $commentstable) {
        $steptableinfo = '| Comment |';

        if ($comments = $commentstable->getRows()) {
            $addcommentbutton = $this->find_button(get_string('addcomment', 'gradingform_guide'));

            foreach ($comments as $index => $comment) {
                                if (count($comment) != 1) {
                    throw new ExpectationException(
                        'The comment cannot be empty. Please follow this format: ' . $steptableinfo,
                        $this->getSession()
                    );
                }

                                $commentfieldvisible = false;
                if ($index > 0) {
                                        $addcommentbutton->click();
                    $commentfieldvisible = true;
                }

                $commentroot = 'guide[comments][NEWID' . ($index + 1) . ']';

                                $this->set_guide_field_value($commentroot . '[description]', $comment[0], $commentfieldvisible);
            }
        }
    }

    
    public function i_grade_by_filling_the_marking_guide_with(TableNode $guide) {

        $criteria = $guide->getRowsHash();

        $stepusage = '"I grade by filling the rubric with:" step needs you to provide a table where each row is a criterion' .
            ' and each criterion has 3 different values: | Criterion name | Number of points | Remark text |';

                foreach ($criteria as $name => $criterion) {

                        if (count($criterion) !== 2) {
                throw new ExpectationException($stepusage, $this->getSession());
            }

                        $points = $criterion[0];
            if (!is_numeric($points)) {
                throw new ExpectationException($stepusage, $this->getSession());
            }

            $criterionid = 0;
            if ($criterionnamediv = $this->find('xpath', "//div[@class='criterionshortname'][text()='$name']")) {
                $criteriondivname = $criterionnamediv->getAttribute('name');
                                                if ($nameparts = explode('][', $criteriondivname)) {
                    $criterionid = $nameparts[1];
                }
            }

            if ($criterionid) {
                $criterionroot = 'advancedgrading[criteria]' . '[' . $criterionid . ']';

                $this->execute('behat_forms::i_set_the_field_to', array($criterionroot . '[score]', $points));

                $this->execute('behat_forms::i_set_the_field_to', array($criterionroot . '[remark]', $criterion[1]));
            }
        }
    }

    
    protected function set_guide_field_value($name, $value, $visible = false) {
                if ($this->running_javascript() && $visible === false) {
            $xpath = "//*[@name='$name']/following-sibling::*[contains(concat(' ', normalize-space(@class), ' '), ' plainvalue ')]";
            $textnode = $this->find('xpath', $xpath);
            $textnode->click();
        }

                $field = $this->find_field($name);
        $field->setValue($value);
    }
}
