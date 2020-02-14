<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/workshop/locallib.php');
require_once($CFG->dirroot . '/mod/workshop/eval/best/lib.php');
require_once($CFG->libdir . '/gradelib.php');


class workshopeval_best_evaluation_testcase extends advanced_testcase {

    
    protected $workshop;

    
    protected $evaluator;

    
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $workshop = $this->getDataGenerator()->create_module('workshop', array('evaluation' => 'best', 'course' => $course));
        $cm = get_fast_modinfo($course)->instances['workshop'][$workshop->id];
        $this->workshop = new workshop($workshop, $cm, $course);
        $this->evaluator = new testable_workshop_best_evaluation($this->workshop);
    }

    protected function tearDown() {
        $this->workshop = null;
        $this->evaluator = null;
        parent::tearDown();
    }

    public function test_normalize_grades() {
                $assessments = array();
        $assessments[1] = (object)array(
            'dimgrades' => array(3 => 1.0000, 4 => 13.42300),
        );
        $assessments[3] = (object)array(
            'dimgrades' => array(3 => 2.0000, 4 => 19.1000),
        );
        $assessments[7] = (object)array(
            'dimgrades' => array(3 => 3.0000, 4 => 0.00000),
        );
        $diminfo = array(
            3 => (object)array('min' => 1, 'max' => 3),
            4 => (object)array('min' => 0, 'max' => 20),
        );
                $norm = $this->evaluator->normalize_grades($assessments, $diminfo);
                $this->assertEquals(gettype($norm), 'array');
                $this->assertEquals($norm[1]->dimgrades[3], 0);
        $this->assertEquals($norm[3]->dimgrades[3], 50);
        $this->assertEquals($norm[7]->dimgrades[3], 100);
                $this->assertEquals($norm[1]->dimgrades[4], grade_floatval(13.423 / 20 * 100));
        $this->assertEquals($norm[3]->dimgrades[4], grade_floatval(19.1 / 20 * 100));
        $this->assertEquals($norm[7]->dimgrades[4], 0);
    }

    public function test_normalize_grades_max_equals_min() {
                $assessments = array();
        $assessments[1] = (object)array(
            'dimgrades' => array(3 => 100.0000),
        );
        $diminfo = array(
            3 => (object)array('min' => 100, 'max' => 100),
        );
                $norm = $this->evaluator->normalize_grades($assessments, $diminfo);
                $this->assertEquals(gettype($norm), 'array');
        $this->assertEquals($norm[1]->dimgrades[3], 100);
    }

    public function test_average_assessment_same_weights() {
                $assessments = array();
        $assessments[18] = (object)array(
            'weight'        => 1,
            'dimgrades'     => array(1 => 50, 2 => 33.33333),
        );
        $assessments[16] = (object)array(
            'weight'        => 1,
            'dimgrades'     => array(1 => 0, 2 => 66.66667),
        );
                $average = $this->evaluator->average_assessment($assessments);
                $this->assertEquals(gettype($average->dimgrades), 'array');
        $this->assertEquals(grade_floatval($average->dimgrades[1]), grade_floatval(25));
        $this->assertEquals(grade_floatval($average->dimgrades[2]), grade_floatval(50));
    }

    public function test_average_assessment_different_weights() {
                $assessments = array();
        $assessments[11] = (object)array(
            'weight'        => 1,
            'dimgrades'     => array(3 => 10.0, 4 => 13.4, 5 => 95.0),
        );
        $assessments[13] = (object)array(
            'weight'        => 3,
            'dimgrades'     => array(3 => 11.0, 4 => 10.1, 5 => 92.0),
        );
        $assessments[17] = (object)array(
            'weight'        => 1,
            'dimgrades'     => array(3 => 11.0, 4 => 8.1, 5 => 88.0),
        );
                $average = $this->evaluator->average_assessment($assessments);
                $this->assertEquals(gettype($average->dimgrades), 'array');
        $this->assertEquals(grade_floatval($average->dimgrades[3]), grade_floatval((10.0 + 11.0*3 + 11.0)/5));
        $this->assertEquals(grade_floatval($average->dimgrades[4]), grade_floatval((13.4 + 10.1*3 + 8.1)/5));
        $this->assertEquals(grade_floatval($average->dimgrades[5]), grade_floatval((95.0 + 92.0*3 + 88.0)/5));
    }

    public function test_average_assessment_noweight() {
                $assessments = array();
        $assessments[11] = (object)array(
            'weight'        => 0,
            'dimgrades'     => array(3 => 10.0, 4 => 13.4, 5 => 95.0),
        );
        $assessments[17] = (object)array(
            'weight'        => 0,
            'dimgrades'     => array(3 => 11.0, 4 => 8.1, 5 => 88.0),
        );
                $average = $this->evaluator->average_assessment($assessments);
                $this->assertNull($average);
    }

    public function test_weighted_variance() {
                $assessments[11] = (object)array(
            'weight'        => 1,
            'dimgrades'     => array(3 => 11, 4 => 2),
        );
        $assessments[13] = (object)array(
            'weight'        => 3,
            'dimgrades'     => array(3 => 11, 4 => 4),
        );
        $assessments[17] = (object)array(
            'weight'        => 2,
            'dimgrades'     => array(3 => 11, 4 => 5),
        );
        $assessments[20] = (object)array(
            'weight'        => 1,
            'dimgrades'     => array(3 => 11, 4 => 7),
        );
        $assessments[25] = (object)array(
            'weight'        => 1,
            'dimgrades'     => array(3 => 11, 4 => 9),
        );
                $variance = $this->evaluator->weighted_variance($assessments);
                        $this->assertEquals($variance[3], 0);
                $this->assertEquals($variance[4], 4);
    }

    public function test_assessments_distance_zero() {
                $diminfo = array(
            3 => (object)array('weight' => 1, 'min' => 0, 'max' => 100, 'variance' => 12.34567),
            4 => (object)array('weight' => 1, 'min' => 1, 'max' => 5,   'variance' => 98.76543),
        );
        $assessment1 = (object)array('dimgrades' => array(3 => 15, 4 => 2));
        $assessment2 = (object)array('dimgrades' => array(3 => 15, 4 => 2));
        $settings = (object)array('comparison' => 5);
                $this->assertEquals($this->evaluator->assessments_distance($assessment1, $assessment2, $diminfo, $settings), 0);
    }

    public function test_assessments_distance_equals() {
        
                $diminfo = array(
            1 => (object)array('min' => 0, 'max' => 2, 'weight' => 1, 'variance' => 625),
            2 => (object)array('min' => 0, 'max' => 3, 'weight' => 1, 'variance' => 277.7778888889),
        );
        $assessment1 = (object)array('dimgrades' => array(1 => 0,  2 => 66.66667));
        $assessment2 = (object)array('dimgrades' => array(1 => 50, 2 => 33.33333));
        $referential = (object)array('dimgrades' => array(1 => 25, 2 => 50));
        $settings = (object)array('comparison' => 9);
                $this->assertEquals($this->evaluator->assessments_distance($assessment1, $referential, $diminfo, $settings),
            $this->evaluator->assessments_distance($assessment2, $referential, $diminfo, $settings));

    }

    public function test_assessments_distance_zero_variance() {
                        $diminfo = array(
            1 => (object)array('min' => 0, 'max' => 1, 'weight' => 1),
            2 => (object)array('min' => 0, 'max' => 1, 'weight' => 1),
            3 => (object)array('min' => 0, 'max' => 1, 'weight' => 1),
        );

                $assessments = array(
                        10 => (object)array(
                'assessmentid' => 10,
                'weight' => 0,
                'reviewerid' => 56,
                'gradinggrade' => null,
                'submissionid' => 99,
                'dimgrades' => array(
                    1 => 0,
                    2 => 0,
                    3 => 0,
                ),
            ),
                        20 => (object)array(
                'assessmentid' => 20,
                'weight' => 1,
                'reviewerid' => 76,
                'gradinggrade' => null,
                'submissionid' => 99,
                'dimgrades' => array(
                    1 => 1,
                    2 => 1,
                    3 => 1,
                ),
            ),
                        30 => (object)array(
                'assessmentid' => 30,
                'weight' => 1,
                'reviewerid' => 97,
                'gradinggrade' => null,
                'submissionid' => 99,
                'dimgrades' => array(
                    1 => 1,
                    2 => 1,
                    3 => 1,
                ),
            ),
        );

                $assessments = $this->evaluator->normalize_grades($assessments, $diminfo);
        $average = $this->evaluator->average_assessment($assessments);
        $variances = $this->evaluator->weighted_variance($assessments);
        foreach ($variances as $dimid => $variance) {
            $diminfo[$dimid]->variance = $variance;
        }

                $settings = (object)array('comparison' => 5);

                $distances = array();
        foreach ($assessments as $asid => $assessment) {
            $distances[$asid] = $this->evaluator->assessments_distance($assessment, $average, $diminfo, $settings);
        }

                $this->assertTrue($distances[10] > 0);
                $this->assertTrue($distances[20] == 0);
        $this->assertTrue($distances[30] == 0);
    }
}



class testable_workshop_best_evaluation extends workshop_best_evaluation {

    public function normalize_grades(array $assessments, array $diminfo) {
        return parent::normalize_grades($assessments, $diminfo);
    }
    public function average_assessment(array $assessments) {
        return parent::average_assessment($assessments);
    }
    public function weighted_variance(array $assessments) {
        return parent::weighted_variance($assessments);
    }
    public function assessments_distance(stdclass $assessment, stdclass $referential, array $diminfo, stdclass $settings) {
        return parent::assessments_distance($assessment, $referential, $diminfo, $settings);
    }
}
