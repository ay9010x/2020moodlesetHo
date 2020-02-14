<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/grade/lib.php');


class core_grade_lib_test extends advanced_testcase {

    
    public function test_can_output_item() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();

                $course = $generator->create_course();
                        $gradetree = grade_category::fetch_course_tree($course->id);
        $this->assertTrue(grade_tree::can_output_item($gradetree));

                $generator->create_grade_category(array('courseid' => $course->id));
                                $gradetree = grade_category::fetch_course_tree($course->id);
        $this->assertNotEmpty($gradetree['children']);
        foreach ($gradetree['children'] as $child) {
            $this->assertTrue(grade_tree::can_output_item($child));
        }

                $nototalcategory = 'No total category';
        $nototalparams = [
            'courseid' => $course->id,
            'fullname' => $nototalcategory,
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN
        ];
        $nototal = $generator->create_grade_category($nototalparams);
        $catnototal = grade_category::fetch(array('id' => $nototal->id));
                $catitemnototal = $catnototal->load_grade_item();
        $catitemnototal->gradetype = GRADE_TYPE_NONE;
        $catitemnototal->update();

                                        $gradetree = grade_category::fetch_course_tree($course->id);
        foreach ($gradetree['children'] as $child) {
            if ($child['object']->fullname == $nototalcategory) {
                $this->assertFalse(grade_tree::can_output_item($child));
            } else {
                $this->assertTrue(grade_tree::can_output_item($child));
            }
        }

                $normalinnototalparams = [
            'courseid' => $course->id,
            'fullname' => 'Normal category in no total category',
            'parent' => $nototal->id
        ];
        $generator->create_grade_category($normalinnototalparams);

                                                $gradetree = grade_category::fetch_course_tree($course->id);
        foreach ($gradetree['children'] as $child) {
                        $this->assertTrue(grade_tree::can_output_item($child));
            if (!empty($child['children'])) {
                foreach ($child['children'] as $grandchild) {
                    $this->assertTrue(grade_tree::can_output_item($grandchild));
                }
            }
        }

                $nototalcategory2 = 'No total category 2';
        $nototal2params = [
            'courseid' => $course->id,
            'fullname' => $nototalcategory2,
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN
        ];
        $nototal2 = $generator->create_grade_category($nototal2params);
        $catnototal2 = grade_category::fetch(array('id' => $nototal2->id));
                $catitemnototal2 = $catnototal2->load_grade_item();
        $catitemnototal2->gradetype = GRADE_TYPE_NONE;
        $catitemnototal2->update();

                $nototalinnototalcategory = 'Category with no total in no total category';
        $nototalinnototalparams = [
            'courseid' => $course->id,
            'fullname' => $nototalinnototalcategory,
            'aggregation' => GRADE_AGGREGATE_WEIGHTED_MEAN,
            'parent' => $nototal2->id
        ];
        $nototalinnototal = $generator->create_grade_category($nototalinnototalparams);
        $catnototalinnototal = grade_category::fetch(array('id' => $nototalinnototal->id));
                $catitemnototalinnototal = $catnototalinnototal->load_grade_item();
        $catitemnototalinnototal->gradetype = GRADE_TYPE_NONE;
        $catitemnototalinnototal->update();

                                                                $gradetree = grade_category::fetch_course_tree($course->id);
        foreach ($gradetree['children'] as $child) {
            if ($child['object']->fullname == $nototalcategory2) {
                $this->assertFalse(grade_tree::can_output_item($child));
            } else {
                $this->assertTrue(grade_tree::can_output_item($child));
            }
            if (!empty($child['children'])) {
                foreach ($child['children'] as $grandchild) {
                    if ($grandchild['object']->fullname == $nototalinnototalcategory) {
                        $this->assertFalse(grade_tree::can_output_item($grandchild));
                    } else {
                        $this->assertTrue(grade_tree::can_output_item($grandchild));
                    }
                }
            }
        }
    }
}
