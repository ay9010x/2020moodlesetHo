<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/workshop/locallib.php');
require_once($CFG->dirroot . '/mod/workshop/allocation/random/lib.php');


class workshopallocation_random_testcase extends advanced_testcase {

    
    protected $workshop;

    
    protected $allocator;

    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $workshop = $this->getDataGenerator()->create_module('workshop', array('course' => $course));
        $cm = get_fast_modinfo($course)->instances['workshop'][$workshop->id];
        $this->workshop = new workshop($workshop, $cm, $course);
        $this->allocator = new testable_workshop_random_allocator($this->workshop);
    }

    protected function tearDown() {
        $this->allocator    = null;
        $this->workshop     = null;
        parent::tearDown();
    }

    public function test_self_allocation_empty_values() {
                $this->assertEquals(array(), $this->allocator->self_allocation());
    }

    public function test_self_allocation_equal_user_groups() {
                $authors    = array(0 => array_fill_keys(array(4, 6, 10), new stdclass()));
        $reviewers  = array(0 => array_fill_keys(array(4, 6, 10), new stdclass()));
                $newallocations = $this->allocator->self_allocation($authors, $reviewers);
                $this->assertEquals(array(array(4 => 4), array(6 => 6), array(10 => 10)), $newallocations);
    }

    public function test_self_allocation_different_user_groups() {
                $authors    = array(0 => array_fill_keys(array(1, 4, 5, 10, 13), new stdclass()));
        $reviewers  = array(0 => array_fill_keys(array(4, 7, 10), new stdclass()));
                $newallocations = $this->allocator->self_allocation($authors, $reviewers);
                $this->assertEquals(array(array(4 => 4), array(10 => 10)), $newallocations);
    }

    public function test_self_allocation_skip_existing() {
                $authors        = array(0 => array_fill_keys(array(3, 4, 10), new stdclass()));
        $reviewers      = array(0 => array_fill_keys(array(3, 4, 10), new stdclass()));
        $assessments    = array(23 => (object)array('authorid' => 3, 'reviewerid' => 3));
                $newallocations = $this->allocator->self_allocation($authors, $reviewers, $assessments);
                $this->assertEquals(array(array(4 => 4), array(10 => 10)), $newallocations);
    }

    public function test_get_author_ids() {
                $newallocations = array(array(1 => 3), array(2 => 1), array(3 => 1));
                $this->assertEquals(array(3, 1), $this->allocator->get_author_ids($newallocations));
    }

    public function test_index_submissions_by_authors() {
                $submissions = array(
            676 => (object)array('id' => 676, 'authorid' => 23),
            121 => (object)array('id' => 121, 'authorid' => 56),
        );
                $submissions = $this->allocator->index_submissions_by_authors($submissions);
                $this->assertEquals(array(
            23 => (object)array('id' => 676, 'authorid' => 23),
            56 => (object)array('id' => 121, 'authorid' => 56),
        ), $submissions);
    }

    public function test_index_submissions_by_authors_duplicate_author() {
                $submissions = array(
            14 => (object)array('id' => 676, 'authorid' => 3),
            87 => (object)array('id' => 121, 'authorid' => 3),
        );
                $this->setExpectedException('moodle_exception');
                $submissions = $this->allocator->index_submissions_by_authors($submissions);
    }

    public function test_get_unique_allocations() {
                $newallocations = array(array(4 => 5), array(6 => 6), array(1 => 16), array(4 => 5), array(16 => 1));
                $newallocations = $this->allocator->get_unique_allocations($newallocations);
                $this->assertEquals(array(array(4 => 5), array(6 => 6), array(1 => 16), array(16 => 1)), $newallocations);
    }

    public function test_get_unkept_assessments_no_keep_selfassessments() {
                $assessments = array(
            23 => (object)array('authorid' => 3, 'reviewerid' => 3),
            45 => (object)array('authorid' => 5, 'reviewerid' => 11),
            12 => (object)array('authorid' => 6, 'reviewerid' => 3),
        );
        $newallocations = array(array(4 => 5), array(11 => 5), array(1 => 16), array(4 => 5), array(16 => 1));
                $delassessments = $this->allocator->get_unkept_assessments($assessments, $newallocations, false);
                        $this->assertEquals(array(23, 12), $delassessments);
    }

    public function test_get_unkept_assessments_keep_selfassessments() {
                $assessments = array(
            23 => (object)array('authorid' => 3, 'reviewerid' => 3),
            45 => (object)array('authorid' => 5, 'reviewerid' => 11),
            12 => (object)array('authorid' => 6, 'reviewerid' => 3),
        );
        $newallocations = array(array(4 => 5), array(11 => 5), array(1 => 16), array(4 => 5), array(16 => 1));
                $delassessments = $this->allocator->get_unkept_assessments($assessments, $newallocations, true);
                                $this->assertEquals(array(12), $delassessments);
    }

    
    public function test_convert_assessments_to_links() {
                $assessments = array(
            23 => (object)array('authorid' => 3, 'reviewerid' => 3),
            45 => (object)array('authorid' => 5, 'reviewerid' => 11),
            12 => (object)array('authorid' => 5, 'reviewerid' => 3),
        );
                list($authorlinks, $reviewerlinks) = $this->allocator->convert_assessments_to_links($assessments);
                $this->assertEquals(array(3 => array(3), 5 => array(11, 3)), $authorlinks);
        $this->assertEquals(array(3 => array(3, 5), 11 => array(5)), $reviewerlinks);
    }

    
    public function test_convert_assessments_to_links_empty() {
                $assessments = array();
                list($authorlinks, $reviewerlinks) = $this->allocator->convert_assessments_to_links($assessments);
                $this->assertEquals(array(), $authorlinks);
        $this->assertEquals(array(), $reviewerlinks);
    }

    
    public function test_get_element_with_lowest_workload_deterministic() {
                $workload = array(4 => 6, 9 => 1, 10 => 2);
                $chosen = $this->allocator->get_element_with_lowest_workload($workload);
                $this->assertEquals(9, $chosen);
    }

    
    public function test_get_element_with_lowest_workload_impossible() {
                $workload = array();
                $chosen = $this->allocator->get_element_with_lowest_workload($workload);
                $this->assertTrue($chosen === false);
    }

    
    public function test_get_element_with_lowest_workload_random() {
                $workload = array(4 => 6, 9 => 2, 10 => 2);
                $elements = $this->allocator->get_element_with_lowest_workload($workload);
                                                $counts = array(4 => 0, 9 => 0, 10 => 0);
        for ($i = 0; $i < 100; $i++) {
            $chosen = $this->allocator->get_element_with_lowest_workload($workload);
            if (!in_array($chosen, array(4, 9, 10))) {
                $this->fail('Invalid element ' . var_export($chosen, true) . ' chosen');
                break;
            } else {
                $counts[$this->allocator->get_element_with_lowest_workload($workload)]++;
            }
        }
        $this->assertTrue(($counts[9] > 0) && ($counts[10] > 0));
    }

    
    public function test_get_element_with_lowest_workload_random_floats() {
                $workload = array(1 => 1/13, 2 => 0.0769230769231);                 $elements = $this->allocator->get_element_with_lowest_workload($workload);
                $counts = array(1 => 0, 2 => 0);
        for ($i = 0; $i < 100; $i++) {
            $chosen = $this->allocator->get_element_with_lowest_workload($workload);
            if (!in_array($chosen, array(1, 2))) {
                $this->fail('Invalid element ' . var_export($chosen, true) . ' chosen');
                break;
            } else {
                $counts[$this->allocator->get_element_with_lowest_workload($workload)]++;
            }
        }
        $this->assertTrue(($counts[1] > 0) && ($counts[2] > 0));

    }

    
    public function test_filter_current_assessments() {
                $newallocations = array(array(3 => 5), array(11 => 5), array(2 => 9), array(3 => 5));
        $assessments = array(
            23 => (object)array('authorid' => 3, 'reviewerid' => 3),
            45 => (object)array('authorid' => 5, 'reviewerid' => 11),
            12 => (object)array('authorid' => 5, 'reviewerid' => 3),
        );
                $this->allocator->filter_current_assessments($newallocations, $assessments);
                $this->assertEquals(array_values($newallocations), array(array(2 => 9)));
    }


}



class testable_workshop_random_allocator extends workshop_random_allocator {
    public function self_allocation($authors=array(), $reviewers=array(), $assessments=array()) {
        return parent::self_allocation($authors, $reviewers, $assessments);
    }
    public function get_author_ids($newallocations) {
        return parent::get_author_ids($newallocations);
    }
    public function index_submissions_by_authors($submissions) {
        return parent::index_submissions_by_authors($submissions);
    }
    public function get_unique_allocations($newallocations) {
        return parent::get_unique_allocations($newallocations);
    }
    public function get_unkept_assessments($assessments, $newallocations, $keepselfassessments) {
        return parent::get_unkept_assessments($assessments, $newallocations, $keepselfassessments);
    }
    public function convert_assessments_to_links($assessments) {
        return parent::convert_assessments_to_links($assessments);
    }
    public function get_element_with_lowest_workload($workload) {
        return parent::get_element_with_lowest_workload($workload);
    }
    public function filter_current_assessments(&$newallocations, $assessments) {
        return parent::filter_current_assessments($newallocations, $assessments);
    }
}
