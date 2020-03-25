<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class mod_data_external_testcase extends externallib_advanced_testcase {

    
    public function test_mod_data_get_databases_by_courses() {
        global $DB;

        $this->resetAfterTest(true);

                $student = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                self::setUser($student);

                $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->introformat = FORMAT_HTML;
        $record->course = $course1->id;
        $database1 = self::getDataGenerator()->create_module('data', $record);

                $record = new stdClass();
        $record->introformat = FORMAT_HTML;
        $record->course = $course2->id;
        $database2 = self::getDataGenerator()->create_module('data', $record);

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));

                $this->getDataGenerator()->enrol_user($student->id, $course1->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course1->id, $teacherrole->id, 'manual');

                $enrol = enrol_get_plugin('manual');
        $enrolinstances = enrol_get_instances($course2->id, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "manual") {
                $instance2 = $courseenrolinstance;
                break;
            }
        }
        $enrol->enrol_user($instance2, $student->id, $studentrole->id);

                        $expectedfields = array('id', 'coursemodule', 'course', 'name', 'comments', 'timeavailablefrom',
                            'timeavailableto', 'timeviewfrom', 'timeviewto', 'requiredentries', 'requiredentriestoview',
                            'intro', 'introformat');

                $database1->coursemodule = $database1->cmid;
        $database2->coursemodule = $database2->cmid;

        $expected1 = array();
        $expected2 = array();
        foreach ($expectedfields as $field) {
            $expected1[$field] = $database1->{$field};
            $expected2[$field] = $database2->{$field};
        }
        $expected1['comments'] = (bool) $expected1['comments'];
        $expected2['comments'] = (bool) $expected2['comments'];

        $expecteddatabases = array();
        $expecteddatabases[] = $expected2;
        $expecteddatabases[] = $expected1;

                $result = mod_data_external::get_databases_by_courses(array($course2->id, $course1->id));
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        $this->assertEquals($expecteddatabases, $result['databases']);

                $result = mod_data_external::get_databases_by_courses();
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        $this->assertEquals($expecteddatabases, $result['databases']);

                $enrol->unenrol_user($instance2, $student->id);
        array_shift($expecteddatabases);

                $result = mod_data_external::get_databases_by_courses();
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        $this->assertEquals($expecteddatabases, $result['databases']);

                $result = mod_data_external::get_databases_by_courses(array($course2->id));
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('1', $result['warnings'][0]['warningcode']);
        $this->assertEquals($course2->id, $result['warnings'][0]['itemid']);

                self::setUser($teacher);

        $additionalfields = array('maxentries', 'rssarticles', 'singletemplate', 'listtemplate', 'timemodified',
                                'listtemplateheader', 'listtemplatefooter', 'addtemplate', 'rsstemplate', 'rsstitletemplate',
                                'csstemplate', 'jstemplate', 'asearchtemplate', 'approval', 'scale', 'assessed', 'assesstimestart',
                                'assesstimefinish', 'defaultsort', 'defaultsortdir', 'editany', 'notification', 'manageapproved');

        foreach ($additionalfields as $field) {
            if ($field == 'approval' or $field == 'editany') {
                $expecteddatabases[0][$field] = (bool) $database1->{$field};
            } else {
                $expecteddatabases[0][$field] = $database1->{$field};
            }
        }
        $result = mod_data_external::get_databases_by_courses();
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        $this->assertEquals($expecteddatabases, $result['databases']);

                self::setAdminUser();

        $result = mod_data_external::get_databases_by_courses(array($course1->id));
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        $this->assertEquals($expecteddatabases, $result['databases']);
    }
}
