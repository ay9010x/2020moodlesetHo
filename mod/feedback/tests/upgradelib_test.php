<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/feedback/db/upgradelib.php');


class mod_feedback_upgradelib_testcase extends advanced_testcase {

    
    protected $testsql = "SELECT COUNT(v.id) FROM {feedback_completed} c, {feedback_value} v
            WHERE c.id = v.completed AND c.courseid <> v.course_id";
    
    protected $testsqltmp = "SELECT COUNT(v.id) FROM {feedback_completedtmp} c, {feedback_valuetmp} v
            WHERE c.id = v.completed AND c.courseid <> v.course_id";
    
    protected $course1;
    
    protected $course2;
    
    protected $feedback;
    
    protected $user;

    
    public function setUp() {
        parent::setUp();
        $this->resetAfterTest(true);

        $this->course1 = $this->getDataGenerator()->create_course();
        $this->course2 = $this->getDataGenerator()->create_course();
        $this->feedback = $this->getDataGenerator()->create_module('feedback', array('course' => SITEID));

        $this->user = $this->getDataGenerator()->create_user();
    }

    public function test_upgrade_courseid_completed() {
        global $DB;

                $completed1 = $DB->insert_record('feedback_completed',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
            'item' => 1, 'value' => 1]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
            'item' => 2, 'value' => 2]);

        $this->assertCount(1, $DB->get_records('feedback_completed'));
        $this->assertEquals(2, $DB->count_records_sql($this->testsql));         mod_feedback_upgrade_courseid(true);         $this->assertCount(1, $DB->get_records('feedback_completed'));
        $this->assertEquals(2, $DB->count_records_sql($this->testsql));         mod_feedback_upgrade_courseid();
        $this->assertCount(1, $DB->get_records('feedback_completed'));         $this->assertEquals(0, $DB->count_records_sql($this->testsql));     }

    public function test_upgrade_courseid_completed_with_errors() {
        global $DB;

                $completed1 = $DB->insert_record('feedback_completed',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
            'item' => 1, 'value' => 1]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course2->id,
            'item' => 1, 'value' => 2]);

        $this->assertCount(1, $DB->get_records('feedback_completed'));
        $this->assertEquals(2, $DB->count_records_sql($this->testsql));         mod_feedback_upgrade_courseid(true);         $this->assertCount(1, $DB->get_records('feedback_completed'));
        $this->assertEquals(2, $DB->count_records_sql($this->testsql));         mod_feedback_upgrade_courseid();
        $this->assertCount(2, $DB->get_records('feedback_completed'));         $this->assertEquals(0, $DB->count_records_sql($this->testsql));     }

    public function test_upgrade_courseid_completedtmp() {
        global $DB;

                $completed1 = $DB->insert_record('feedback_completedtmp',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
            'item' => 1, 'value' => 1]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
            'item' => 2, 'value' => 2]);

        $this->assertCount(1, $DB->get_records('feedback_completedtmp'));
        $this->assertEquals(2, $DB->count_records_sql($this->testsqltmp));         mod_feedback_upgrade_courseid();         $this->assertCount(1, $DB->get_records('feedback_completedtmp'));
        $this->assertEquals(2, $DB->count_records_sql($this->testsqltmp));         mod_feedback_upgrade_courseid(true);
        $this->assertCount(1, $DB->get_records('feedback_completedtmp'));         $this->assertEquals(0, $DB->count_records_sql($this->testsqltmp));     }

    public function test_upgrade_courseid_completedtmp_with_errors() {
        global $DB;

                $completed1 = $DB->insert_record('feedback_completedtmp',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
            'item' => 1, 'value' => 1]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course2->id,
            'item' => 1, 'value' => 2]);

        $this->assertCount(1, $DB->get_records('feedback_completedtmp'));
        $this->assertEquals(2, $DB->count_records_sql($this->testsqltmp));         mod_feedback_upgrade_courseid();         $this->assertCount(1, $DB->get_records('feedback_completedtmp'));
        $this->assertEquals(2, $DB->count_records_sql($this->testsqltmp));         mod_feedback_upgrade_courseid(true);
        $this->assertCount(2, $DB->get_records('feedback_completedtmp'));         $this->assertEquals(0, $DB->count_records_sql($this->testsqltmp));     }

    public function test_upgrade_courseid_empty_completed() {
        global $DB;

                $DB->insert_record('feedback_completed',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);

        $this->assertCount(1, $DB->get_records('feedback_completed'));
        $record1 = $DB->get_record('feedback_completed', []);
        mod_feedback_upgrade_courseid();
        $this->assertCount(1, $DB->get_records('feedback_completed'));         $record2 = $DB->get_record('feedback_completed', []);
        $this->assertEquals($record1, $record2);
    }

    public function test_upgrade_remove_duplicates_no_duplicates() {
        global $DB;

        $completed1 = $DB->insert_record('feedback_completed',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 1, 'value' => 1]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 2, 'value' => 2]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 3, 'value' => 1]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course2->id,
                'item' => 3, 'value' => 2]);

        $this->assertCount(1, $DB->get_records('feedback_completed'));
        $this->assertEquals(4, $DB->count_records('feedback_value'));
        mod_feedback_upgrade_delete_duplicate_values();
        $this->assertCount(1, $DB->get_records('feedback_completed'));
        $this->assertEquals(4, $DB->count_records('feedback_value'));     }

    public function test_upgrade_remove_duplicates() {
        global $DB;

                $dbman = $DB->get_manager();
        $table = new xmldb_table('feedback_value');
        $index = new xmldb_index('completed_item', XMLDB_INDEX_UNIQUE, array('completed', 'item', 'course_id'));
        $dbman->drop_index($table, $index);

                $completed1 = $DB->insert_record('feedback_completed',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 1, 'value' => 1]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 1, 'value' => 2]);         $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 3, 'value' => 1]);
        $DB->insert_record('feedback_value',
            ['completed' => $completed1, 'course_id' => $this->course2->id,
                'item' => 3, 'value' => 2]); 
        $this->assertCount(1, $DB->get_records('feedback_completed'));
        $this->assertEquals(4, $DB->count_records('feedback_value'));
        mod_feedback_upgrade_delete_duplicate_values(true);         $this->assertCount(1, $DB->get_records('feedback_completed'));
        $this->assertEquals(4, $DB->count_records('feedback_value'));         mod_feedback_upgrade_delete_duplicate_values();
        $this->assertCount(1, $DB->get_records('feedback_completed'));         $this->assertEquals(3, $DB->count_records('feedback_value'));         $this->assertEquals(1, $DB->get_field('feedback_value', 'value', ['item' => 1]));

        $dbman->add_index($table, $index);
    }

    public function test_upgrade_remove_duplicates_no_duplicates_tmp() {
        global $DB;

        $completed1 = $DB->insert_record('feedback_completedtmp',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 1, 'value' => 1]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 2, 'value' => 2]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 3, 'value' => 1]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course2->id,
                'item' => 3, 'value' => 2]);

        $this->assertCount(1, $DB->get_records('feedback_completedtmp'));
        $this->assertEquals(4, $DB->count_records('feedback_valuetmp'));
        mod_feedback_upgrade_delete_duplicate_values(true);
        $this->assertCount(1, $DB->get_records('feedback_completedtmp'));
        $this->assertEquals(4, $DB->count_records('feedback_valuetmp'));     }

    public function test_upgrade_remove_duplicates_tmp() {
        global $DB;

                $dbman = $DB->get_manager();
        $table = new xmldb_table('feedback_valuetmp');
        $index = new xmldb_index('completed_item', XMLDB_INDEX_UNIQUE, array('completed', 'item', 'course_id'));
        $dbman->drop_index($table, $index);

                $completed1 = $DB->insert_record('feedback_completedtmp',
            ['feedback' => $this->feedback->id, 'userid' => $this->user->id]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 1, 'value' => 1]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 1, 'value' => 2]);         $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course1->id,
                'item' => 3, 'value' => 1]);
        $DB->insert_record('feedback_valuetmp',
            ['completed' => $completed1, 'course_id' => $this->course2->id,
                'item' => 3, 'value' => 2]); 
        $this->assertCount(1, $DB->get_records('feedback_completedtmp'));
        $this->assertEquals(4, $DB->count_records('feedback_valuetmp'));
        mod_feedback_upgrade_delete_duplicate_values();         $this->assertCount(1, $DB->get_records('feedback_completedtmp'));
        $this->assertEquals(4, $DB->count_records('feedback_valuetmp'));         mod_feedback_upgrade_delete_duplicate_values(true);
        $this->assertCount(1, $DB->get_records('feedback_completedtmp'));         $this->assertEquals(3, $DB->count_records('feedback_valuetmp'));         $this->assertEquals(1, $DB->get_field('feedback_valuetmp', 'value', ['item' => 1]));

        $dbman->add_index($table, $index);
    }
}