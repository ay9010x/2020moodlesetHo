<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/workshop/locallib.php');
require_once($CFG->dirroot . '/mod/workshop/form/rubric/lib.php');


class workshopform_rubric_strategy_test extends advanced_testcase {

    
    protected $workshop;

    
    protected $strategy;

    
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $workshop = $this->getDataGenerator()->create_module('workshop', array('strategy' => 'rubric', 'course' => $course));
        $cm = get_fast_modinfo($course)->instances['workshop'][$workshop->id];
        $this->workshop = new workshop($workshop, $cm, $course);
        $this->strategy = new testable_workshop_rubric_strategy($this->workshop);

                $dim = new stdclass();
        $dim->id = 6;
        $dim->levels[10] = (object)array('id' => 10, 'grade' => 0);
        $dim->levels[13] = (object)array('id' => 13, 'grade' => 2);
        $dim->levels[14] = (object)array('id' => 14, 'grade' => 6);
        $dim->levels[16] = (object)array('id' => 16, 'grade' => 8);
        $this->strategy->dimensions[$dim->id] = $dim;

        $dim = new stdclass();
        $dim->id = 8;
        $dim->levels[17] = (object)array('id' => 17, 'grade' => 0);
        $dim->levels[18] = (object)array('id' => 18, 'grade' => 1);
        $dim->levels[19] = (object)array('id' => 19, 'grade' => 2);
        $dim->levels[20] = (object)array('id' => 20, 'grade' => 3);
        $this->strategy->dimensions[$dim->id] = $dim;

        $dim = new stdclass();
        $dim->id = 10;
        $dim->levels[27] = (object)array('id' => 27, 'grade' => 10);
        $dim->levels[28] = (object)array('id' => 28, 'grade' => 20);
        $dim->levels[29] = (object)array('id' => 29, 'grade' => 30);
        $dim->levels[30] = (object)array('id' => 30, 'grade' => 40);
        $this->strategy->dimensions[$dim->id] = $dim;

    }

    protected function tearDown() {
        $this->strategy = null;
        $this->workshop = null;
        parent::tearDown();
    }

    public function test_calculate_peer_grade_null_grade() {
                $grades = array();
                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertNull($suggested);
    }

    public function test_calculate_peer_grade_worst_possible() {
                $grades[6] = (object)array('dimensionid' => 6, 'grade' => 0);
        $grades[8] = (object)array('dimensionid' => 8, 'grade' => 0);
        $grades[10] = (object)array('dimensionid' => 10, 'grade' => 10);
                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals(grade_floatval($suggested), 0.00000);
    }

    public function test_calculate_peer_grade_best_possible() {
                $grades[6] = (object)array('dimensionid' => 6, 'grade' => 8);
        $grades[8] = (object)array('dimensionid' => 8, 'grade' => 3);
        $grades[10] = (object)array('dimensionid' => 10, 'grade' => 40);
                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals(grade_floatval($suggested), 100.00000);
    }

    public function test_calculate_peer_grade_something() {
                $grades[6] = (object)array('dimensionid' => 6, 'grade' => 2);
        $grades[8] = (object)array('dimensionid' => 8, 'grade' => 2);
        $grades[10] = (object)array('dimensionid' => 10, 'grade' => 30);
                $suggested = $this->strategy->calculate_peer_grade($grades);
                        $this->assertEquals(grade_floatval($suggested), grade_floatval(100 * 24 / 41));
    }
}



class testable_workshop_rubric_strategy extends workshop_rubric_strategy {

    
    public $dimensions = array();

    
    public function calculate_peer_grade(array $grades) {
        return parent::calculate_peer_grade($grades);
    }
}
