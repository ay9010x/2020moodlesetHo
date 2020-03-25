<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/health/locallib.php');


class healthlib_testcase extends advanced_testcase {

    
    public static function provider_loop_categories() {
        return array(
                        0 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 1)
                ),
                array(
                    '1' => (object) array('id' => 1, 'parent' => 1)
                ),
            ),
                        1 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 0),
                    '2' => (object) array('id' => 2, 'parent' => 2)
                ),
                array(
                    '2' => (object) array('id' => 2, 'parent' => 2)
                ),
            ),
                        2 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 1)
                ),
                array(
                    '2' => (object) array('id' => 2, 'parent' => 1),
                    '1' => (object) array('id' => 1, 'parent' => 2),
                )
            ),
                        3 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 0),
                    '2' => (object) array('id' => 2, 'parent' => 3),
                    '3' => (object) array('id' => 3, 'parent' => 2),
                ),
                array(
                    '3' => (object) array('id' => 3, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 3),
                )
            ),
                        4 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 3),
                    '3' => (object) array('id' => 3, 'parent' => 1),
                ),
                array(
                    '3' => (object) array('id' => 3, 'parent' => 1),
                    '1' => (object) array('id' => 1, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 3),
                )
            ),
                        5 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 0),
                    '2' => (object) array('id' => 2, 'parent' => 3),
                    '3' => (object) array('id' => 3, 'parent' => 4),
                    '4' => (object) array('id' => 4, 'parent' => 2)
                ),
                array(
                    '4' => (object) array('id' => 4, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 3),
                    '3' => (object) array('id' => 3, 'parent' => 4),
                )
            ),
                        6 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 1),
                    '3' => (object) array('id' => 3, 'parent' => 4),
                    '4' => (object) array('id' => 4, 'parent' => 5),
                    '5' => (object) array('id' => 5, 'parent' => 3),
                    '6' => (object) array('id' => 6, 'parent' => 6),
                    '7' => (object) array('id' => 7, 'parent' => 1),
                    '8' => (object) array('id' => 8, 'parent' => 7),
                ),
                array(
                    '1' => (object) array('id' => 1, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 1),
                    '8' => (object) array('id' => 8, 'parent' => 7),
                    '7' => (object) array('id' => 7, 'parent' => 1),
                    '6' => (object) array('id' => 6, 'parent' => 6),
                    '5' => (object) array('id' => 5, 'parent' => 3),
                    '3' => (object) array('id' => 3, 'parent' => 4),
                    '4' => (object) array('id' => 4, 'parent' => 5),
                )
            ),
                        7 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 1),
                    '3' => (object) array('id' => 3, 'parent' => 2),
                    '4' => (object) array('id' => 4, 'parent' => 2),
                ),
                array(
                    '4' => (object) array('id' => 4, 'parent' => 2),
                    '3' => (object) array('id' => 3, 'parent' => 2),
                    '2' => (object) array('id' => 2, 'parent' => 1),
                    '1' => (object) array('id' => 1, 'parent' => 2),
                )
            )
        );
    }

    
    public static function provider_missing_parent_categories() {
        return array(
                       0 => array(
                array(
                    '1' => (object) array('id' => 1, 'parent' => 0),
                    '2' => (object) array('id' => 2, 'parent' => 3),
                    '4' => (object) array('id' => 4, 'parent' => 5),
                    '6' => (object) array('id' => 6, 'parent' => 2)
                ),
                array(
                    '4' => (object) array('id' => 4, 'parent' => 5),
                    '2' => (object) array('id' => 2, 'parent' => 3)
                ),
            )
        );
    }

    
    public function test_tool_health_category_find_loops($categories, $expected) {
        $loops = tool_health_category_find_loops($categories);
        $this->assertEquals($expected, $loops);
    }

    
    public function test_tool_health_category_find_missing_parents($categories, $expected) {
        $missingparent = tool_health_category_find_missing_parents($categories);
        $this->assertEquals($expected, $missingparent);
    }

    
    public function test_tool_health_category_list_missing_parents() {
        $missingparent = array((object) array('id' => 2, 'parent' => 3, 'name' => 'test'),
                               (object) array('id' => 4, 'parent' => 5, 'name' => 'test2'));
        $result = tool_health_category_list_missing_parents($missingparent);
        $this->assertRegExp('/Category 2: test/', $result);
        $this->assertRegExp('/Category 4: test2/', $result);
    }

    
    public function test_tool_health_category_list_loops() {
        $loops = array((object) array('id' => 2, 'parent' => 3, 'name' => 'test'));
        $result = tool_health_category_list_loops($loops);
        $this->assertRegExp('/Category 2: test/', $result);
    }
}
