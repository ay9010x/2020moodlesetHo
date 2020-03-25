<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/workshop/locallib.php');
require_once($CFG->dirroot . '/mod/workshop/form/numerrors/lib.php');


class workshopform_numerrors_strategy_testcase extends advanced_testcase {

    
    protected $workshop;

    
    protected $strategy;

    
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $workshop = $this->getDataGenerator()->create_module('workshop', array('strategy' => 'numerrors', 'course' => $course));
        $cm = get_fast_modinfo($course)->instances['workshop'][$workshop->id];
        $this->workshop = new workshop($workshop, $cm, $course);
        $this->strategy = new testable_workshop_numerrors_strategy($this->workshop);
    }

    protected function tearDown() {
        $this->workshop = null;
        $this->strategy = null;
        parent::tearDown();
    }

    public function test_calculate_peer_grade_null_grade() {
                $this->strategy->dimensions   = array();
        $this->strategy->mappings     = array();
        $grades = array();
                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertNull($suggested);
    }

    public function test_calculate_peer_grade_no_error() {
                $this->strategy->dimensions      = array();
        $this->strategy->dimensions[108] = (object)array('weight' => '1');
        $this->strategy->dimensions[109] = (object)array('weight' => '1');
        $this->strategy->dimensions[111] = (object)array('weight' => '1');
        $this->strategy->mappings        = array();
        $grades = array();
        $grades[] = (object)array('dimensionid' => 108, 'grade' => '1.00000');
        $grades[] = (object)array('dimensionid' => 111, 'grade' => '1.00000');
        $grades[] = (object)array('dimensionid' => 109, 'grade' => '1.00000');
                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals($suggested, 100.00000);
    }

    public function test_calculate_peer_grade_one_error() {
                $this->strategy->dimensions      = array();
        $this->strategy->dimensions[108] = (object)array('weight' => '1');
        $this->strategy->dimensions[109] = (object)array('weight' => '1');
        $this->strategy->dimensions[111] = (object)array('weight' => '1');

        $this->strategy->mappings        = array(
            1 => (object)array('grade' => '80.00000'),
            2 => (object)array('grade' => '60.00000'),
        );

        $grades = array();
        $grades[] = (object)array('dimensionid' => 108, 'grade' => '1.00000');
        $grades[] = (object)array('dimensionid' => 111, 'grade' => '0.00000');
        $grades[] = (object)array('dimensionid' => 109, 'grade' => '1.00000');

                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals($suggested, 80.00000);
    }

    public function test_calculate_peer_grade_three_errors_same_weight_a() {
                $this->strategy->dimensions      = array();
        $this->strategy->dimensions[108] = (object)array('weight' => '1.00000');
        $this->strategy->dimensions[109] = (object)array('weight' => '1.00000');
        $this->strategy->dimensions[111] = (object)array('weight' => '1.00000');

        $this->strategy->mappings        = array(
            1 => (object)array('grade' => '80.00000'),
            2 => (object)array('grade' => '60.00000'),
            3 => (object)array('grade' => '10.00000'),
        );

        $grades = array();
        $grades[] = (object)array('dimensionid' => 108, 'grade' => '0.00000');
        $grades[] = (object)array('dimensionid' => 111, 'grade' => '0.00000');
        $grades[] = (object)array('dimensionid' => 109, 'grade' => '0.00000');

                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals($suggested, 10.00000);
    }

    public function test_calculate_peer_grade_three_errors_same_weight_b() {
                $this->strategy->dimensions      = array();
        $this->strategy->dimensions[108] = (object)array('weight' => '1.00000');
        $this->strategy->dimensions[109] = (object)array('weight' => '1.00000');
        $this->strategy->dimensions[111] = (object)array('weight' => '1.00000');

        $this->strategy->mappings        = array(
            1 => (object)array('grade' => '80.00000'),
            2 => (object)array('grade' => '60.00000'),
            3 => (object)array('grade' => '0.00000'),
        );

        $grades = array();
        $grades[] = (object)array('dimensionid' => 108, 'grade' => '0.00000');
        $grades[] = (object)array('dimensionid' => 111, 'grade' => '0.00000');
        $grades[] = (object)array('dimensionid' => 109, 'grade' => '0.00000');

                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals($suggested, 0.00000);
    }

    public function test_calculate_peer_grade_one_error_weighted() {
                $this->strategy->dimensions      = array();
        $this->strategy->dimensions[108] = (object)array('weight' => '1');
        $this->strategy->dimensions[109] = (object)array('weight' => '2');
        $this->strategy->dimensions[111] = (object)array('weight' => '0');

        $this->strategy->mappings        = array(
            1 => (object)array('grade' => '66.00000'),
            2 => (object)array('grade' => '33.00000'),
            3 => (object)array('grade' => '0.00000'),
        );

        $grades = array();
        $grades[] = (object)array('dimensionid' => 108, 'grade' => '1.00000');
        $grades[] = (object)array('dimensionid' => 111, 'grade' => '1.00000');
        $grades[] = (object)array('dimensionid' => 109, 'grade' => '0.00000');

                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals($suggested, 33.00000);
    }

    public function test_calculate_peer_grade_zero_weight() {
                $this->strategy->dimensions      = array();
        $this->strategy->dimensions[108] = (object)array('weight' => '1');
        $this->strategy->dimensions[109] = (object)array('weight' => '2');
        $this->strategy->dimensions[111] = (object)array('weight' => '0');

        $this->strategy->mappings        = array(
            1 => (object)array('grade' => '66.00000'),
            2 => (object)array('grade' => '33.00000'),
            3 => (object)array('grade' => '0.00000'),
        );

        $grades = array();
        $grades[] = (object)array('dimensionid' => 108, 'grade' => '1.00000');
        $grades[] = (object)array('dimensionid' => 111, 'grade' => '0.00000');
        $grades[] = (object)array('dimensionid' => 109, 'grade' => '1.00000');

                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals($suggested, 100.00000);
    }

    public function test_calculate_peer_grade_sum_weight() {
                $this->strategy->dimensions      = array();
        $this->strategy->dimensions[108] = (object)array('weight' => '1');
        $this->strategy->dimensions[109] = (object)array('weight' => '2');
        $this->strategy->dimensions[111] = (object)array('weight' => '3');

        $this->strategy->mappings        = array(
            1 => (object)array('grade' => '90.00000'),
            2 => (object)array('grade' => '80.00000'),
            3 => (object)array('grade' => '70.00000'),
            4 => (object)array('grade' => '60.00000'),
            5 => (object)array('grade' => '30.00000'),
            6 => (object)array('grade' => '5.00000'),
            7 => (object)array('grade' => '0.00000'),
        );

        $grades = array();
        $grades[] = (object)array('dimensionid' => 108, 'grade' => '0.00000');
        $grades[] = (object)array('dimensionid' => 111, 'grade' => '0.00000');
        $grades[] = (object)array('dimensionid' => 109, 'grade' => '0.00000');

                $suggested = $this->strategy->calculate_peer_grade($grades);
                $this->assertEquals($suggested, 5.00000);
    }
}



class testable_workshop_numerrors_strategy extends workshop_numerrors_strategy {

    
    public $dimensions = array();

    
    public $mappings = array();

    
    public function calculate_peer_grade(array $grades) {
        return parent::calculate_peer_grade($grades);
    }
}
