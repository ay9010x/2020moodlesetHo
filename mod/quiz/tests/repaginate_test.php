<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/classes/repaginate.php');



class mod_quiz_repaginate_testable extends \mod_quiz\repaginate {

    public function __construct($quizid = 0, $slots = null) {
        return parent::__construct($quizid, $slots);
    }
    public function get_this_slot($slots, $slotnumber) {
        return parent::get_this_slot($slots, $slotnumber);
    }
    public function get_slots_by_slotid($slots = null) {
        return parent::get_slots_by_slotid($slots);
    }
    public function get_slots_by_slot_number($slots = null) {
        return parent::get_slots_by_slot_number($slots);
    }
    public function repaginate_this_slot($slot, $newpagenumber) {
        return parent::repaginate_this_slot($slot, $newpagenumber);
    }
    public function repaginate_next_slot($nextslotnumber, $type) {
        return parent::repaginate_next_slot($nextslotnumber, $type);
    }
}


class mod_quiz_repaginate_test extends advanced_testcase {

    
    private $quizslots;
    
    private $repaginate = null;

    public function setUp() {
        $this->set_quiz_slots($this->get_quiz_object()->get_slots());
        $this->repaginate = new mod_quiz_repaginate_testable(0, $this->quizslots);
    }

    public function tearDown() {
        $this->repaginate = null;
    }

    
    private function get_quiz_object() {
        global $SITE;
        $this->resetAfterTest(true);

                $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');

        $quiz = $quizgenerator->create_instance(array(
                'course' => $SITE->id, 'questionsperpage' => 0, 'grade' => 100.0, 'sumgrades' => 2));
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $SITE->id);

                $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();

        $shortanswer = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        $numerical = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        $essay = $questiongenerator->create_question('essay', null, array('category' => $cat->id));
        $truefalse = $questiongenerator->create_question('truefalse', null, array('category' => $cat->id));
        $match = $questiongenerator->create_question('match', null, array('category' => $cat->id));

                quiz_add_quiz_question($shortanswer->id, $quiz);
        quiz_add_quiz_question($numerical->id, $quiz);
        quiz_add_quiz_question($essay->id, $quiz);
        quiz_add_quiz_question($truefalse->id, $quiz);
        quiz_add_quiz_question($match->id, $quiz);

                $quizobj = new quiz($quiz, $cm, $SITE);
        return \mod_quiz\structure::create_for_quiz($quizobj);
    }

    
    private function set_quiz_slots($slots = null) {
        if (!$slots) {
            $this->quizslots = $this->get_quiz_object()->get_slots();
        } else {
            $this->quizslots = $slots;
        }
    }

    
    public function test_get_this_slot() {
        $this->set_quiz_slots();
        $actual = array();
        $expected = $this->repaginate->get_slots_by_slot_number();
        $this->assertEquals($expected, $actual);

        $slotsbyno = $this->repaginate->get_slots_by_slot_number($this->quizslots);
        $slotnumber = 5;
        $thisslot = $this->repaginate->get_this_slot($this->quizslots, $slotnumber);
        $this->assertEquals($slotsbyno[$slotnumber], $thisslot);
    }

    public function test_get_slots_by_slotnumber() {
        $this->set_quiz_slots();
        $expected = array();
        $actual = $this->repaginate->get_slots_by_slot_number();
        $this->assertEquals($expected, $actual);

        foreach ($this->quizslots as $slot) {
            $expected[$slot->slot] = $slot;
        }
        $actual = $this->repaginate->get_slots_by_slot_number($this->quizslots);
        $this->assertEquals($expected, $actual);
    }

    public function test_get_slots_by_slotid() {
        $this->set_quiz_slots();
        $actual = $this->repaginate->get_slots_by_slotid();
        $this->assertEquals(array(), $actual);

        $slotsbyno = $this->repaginate->get_slots_by_slot_number($this->quizslots);
        $actual = $this->repaginate->get_slots_by_slotid($slotsbyno);
        $this->assertEquals($this->quizslots, $actual);
    }

    public function test_repaginate_n_questions_per_page() {
        $this->set_quiz_slots();

                $expected = array();
        foreach ($this->quizslots as $slot) {
                        if ($slot->slot >= 1 && $slot->slot <= 2) {
                $slot->page = 1;
            }
                        if ($slot->slot >= 3 && $slot->slot <= 4) {
                $slot->page = 2;
            }
                        if ($slot->slot >= 5 && $slot->slot <= 6) {
                $slot->page = 3;
            }
            $expected[$slot->id] = $slot;
        }
        $actual = $this->repaginate->repaginate_n_question_per_page($this->quizslots, 2);
        $this->assertEquals($expected, $actual);

                $expected = array();
        foreach ($this->quizslots as $slot) {
                        if ($slot->slot >= 1 && $slot->slot <= 3) {
                $slot->page = 1;
            }
                        if ($slot->slot >= 4 && $slot->slot <= 6) {
                $slot->page = 2;
            }
            $expected[$slot->id] = $slot;
        }
        $actual = $this->repaginate->repaginate_n_question_per_page($this->quizslots, 3);
        $this->assertEquals($expected, $actual);

                $expected = array();
        foreach ($this->quizslots as $slot) {
                        if ($slot->slot > 0 && $slot->slot < 6) {
                $slot->page = 1;
            }
                        if ($slot->slot > 5 && $slot->slot < 11) {
                $slot->page = 2;
            }
            $expected[$slot->id] = $slot;
        }
        $actual = $this->repaginate->repaginate_n_question_per_page($this->quizslots, 5);
        $this->assertEquals($expected, $actual);

                $expected = array();
        foreach ($this->quizslots as $slot) {
                        if ($slot->slot >= 1 && $slot->slot <= 10) {
                $slot->page = 1;
            }
                        if ($slot->slot >= 11 && $slot->slot <= 20) {
                $slot->page = 2;
            }
            $expected[$slot->id] = $slot;
        }
        $actual = $this->repaginate->repaginate_n_question_per_page($this->quizslots, 10);
        $this->assertEquals($expected, $actual);

                $expected = array();
        $page = 1;
        foreach ($this->quizslots as $slot) {
            $slot->page = $page++;
            $expected[$slot->id] = $slot;
        }
        $actual = $this->repaginate->repaginate_n_question_per_page($this->quizslots, 1);
        $this->assertEquals($expected, $actual);
    }

    public function test_repaginate_this_slot() {
        $this->set_quiz_slots();
        $slotsbyslotno = $this->repaginate->get_slots_by_slot_number($this->quizslots);
        $slotnumber = 3;
        $newpagenumber = 2;
        $thisslot = $slotsbyslotno[3];
        $thisslot->page = $newpagenumber;
        $expected = $thisslot;
        $actual = $this->repaginate->repaginate_this_slot($slotsbyslotno[3], $newpagenumber);
        $this->assertEquals($expected, $actual);
    }

    public function test_repaginate_the_rest() {
        $this->set_quiz_slots();
        $slotfrom = 1;
        $type = \mod_quiz\repaginate::LINK;
        $expected = array();
        foreach ($this->quizslots as $slot) {
            if ($slot->slot > $slotfrom) {
                $slot->page = $slot->page - 1;
                $expected[$slot->id] = $slot;
            }
        }
        $actual = $this->repaginate->repaginate_the_rest($this->quizslots, $slotfrom, $type, false);
        $this->assertEquals($expected, $actual);

        $slotfrom = 2;
        $newslots = array();
        foreach ($this->quizslots as $s) {
            if ($s->slot === $slotfrom) {
                $s->page = $s->page - 1;
            }
            $newslots[$s->id] = $s;
        }

        $type = \mod_quiz\repaginate::UNLINK;
        $expected = array();
        foreach ($this->quizslots as $slot) {
            if ($slot->slot > ($slotfrom - 1)) {
                $slot->page = $slot->page - 1;
                $expected[$slot->id] = $slot;
            }
        }
        $actual = $this->repaginate->repaginate_the_rest($newslots, $slotfrom, $type, false);
        $this->assertEquals($expected, $actual);
    }

}
