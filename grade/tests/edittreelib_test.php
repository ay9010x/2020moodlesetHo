<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/grade/edit/tree/lib.php');



class core_grade_edittreelib_testcase extends advanced_testcase {
    public function test_format_number() {
        $numinput = array(0,   1,   1.01, '1.010', 1.2345);
        $numoutput = array(0.0, 1.0, 1.01,  1.01,   1.2345);

        for ($i = 0; $i < count($numinput); $i++) {
            $msg = 'format_number() testing '.$numinput[$i].' %s';
            $this->assertEquals(grade_edit_tree::format_number($numinput[$i]), $numoutput[$i], $msg);
        }
    }

    public function test_grade_edit_tree_column_range_get_item_cell() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

                $scale = $this->getDataGenerator()->create_scale();
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', array('course' => $course->id));
        $modulecontext = context_module::instance($assign->cmid);
                $assign = new assign($modulecontext, false, false);
        $cm = $assign->get_course_module();

                $column = grade_edit_tree_column::factory('range');

        $gradeitemparams = array(
            'itemtype'     => 'mod',
            'itemmodule'   => $cm->modname,
            'iteminstance' => $cm->instance,
            'courseid'     => $cm->course,
            'itemnumber'   => 0
        );

                $instance = $assign->get_instance();
        $instance->grade = 70;
        $instance->instance = $instance->id;
        $assign->update_instance($instance);

        $gradeitem = grade_item::fetch($gradeitemparams);
        $cell = $column->get_item_cell($gradeitem, array());

        $this->assertEquals(GRADE_TYPE_VALUE, $gradeitem->gradetype);
        $this->assertEquals(null, $gradeitem->scaleid);
        $this->assertEquals(70.0, (float) $cell->text, "Grade text is 70", 0.01);

                $instance = $assign->get_instance();
        $instance->grade = -($scale->id);
        $instance->instance = $instance->id;
        $assign->update_instance($instance);

        $gradeitem = grade_item::fetch($gradeitemparams);
        $cell = $column->get_item_cell($gradeitem, array());

                $scaleitems = null;
        $scaleitems = explode(',', $scale->scale);
                        $scalestring = end($scaleitems) . ' (' .
                format_float(count($scaleitems), 2) . ')';

        $this->assertEquals(GRADE_TYPE_SCALE, $gradeitem->gradetype);
        $this->assertEquals($scale->id, $gradeitem->scaleid);
        $this->assertEquals($scalestring, $cell->text, "Grade text matches scale");

                $adminconfig = $assign->get_admin_config();
        $gradebookplugin = $adminconfig->feedback_plugin_for_gradebook;
        $gradebookplugin .= '_enabled';

        $instance = $assign->get_instance();
        $instance->grade = 0;
        $instance->$gradebookplugin = 1;
        $instance->instance = $instance->id;
        $assign->update_instance($instance);

        $gradeitem = grade_item::fetch($gradeitemparams);
        $cell = $column->get_item_cell($gradeitem, array());

        $this->assertEquals(GRADE_TYPE_TEXT, $gradeitem->gradetype);
        $this->assertEquals(null, $gradeitem->scaleid);
        $this->assertEquals(' - ', $cell->text, 'Grade text matches empty value of " - "');

                $instance = $assign->get_instance();
        $instance->grade = 0;
        $instance->$gradebookplugin = 0;
        $instance->instance = $instance->id;
        $assign->update_instance($instance);

        $gradeitem = grade_item::fetch($gradeitemparams);
        $cell = $column->get_item_cell($gradeitem, array());

        $this->assertEquals(GRADE_TYPE_NONE, $gradeitem->gradetype);
        $this->assertEquals(null, $gradeitem->scaleid);
    }
}


