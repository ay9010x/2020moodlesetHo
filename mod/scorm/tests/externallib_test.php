<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/scorm/lib.php');


class mod_scorm_external_testcase extends externallib_advanced_testcase {

    
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

                $this->course = $this->getDataGenerator()->create_course();
        $this->scorm = $this->getDataGenerator()->create_module('scorm', array('course' => $this->course->id));
        $this->context = context_module::instance($this->scorm->cmid);
        $this->cm = get_coursemodule_from_instance('scorm', $this->scorm->id);

                $this->student = self::getDataGenerator()->create_user();
        $this->teacher = self::getDataGenerator()->create_user();

                $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $this->teacherrole->id, 'manual');
    }

    
    public function test_view_scorm() {
        global $DB;

                try {
            mod_scorm_external::view_scorm(0);
            $this->fail('Exception expected due to invalid mod_scorm instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_scorm_external::view_scorm($this->scorm->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $this->course->id, $this->studentrole->id);

                $sink = $this->redirectEvents();

        $result = mod_scorm_external::view_scorm($this->scorm->id);
        $result = external_api::clean_returnvalue(mod_scorm_external::view_scorm_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_scorm\event\course_module_viewed', $event);
        $this->assertEquals($this->context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/scorm/view.php', array('id' => $this->cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_mod_scorm_get_scorm_attempt_count_own_empty() {
                self::setUser($this->student);

                $result = mod_scorm_external::get_scorm_attempt_count($this->scorm->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_attempt_count_returns(), $result);
        $this->assertEquals(0, $result['attemptscount']);
    }

    public function test_mod_scorm_get_scorm_attempt_count_own_with_complete() {
                self::setUser($this->student);

                $scoes = scorm_get_scoes($this->scorm->id);
        $sco = array_shift($scoes);
        scorm_insert_track($this->student->id, $this->scorm->id, $sco->id, 1, 'cmi.core.lesson_status', 'completed');
        scorm_insert_track($this->student->id, $this->scorm->id, $sco->id, 2, 'cmi.core.lesson_status', 'completed');

        $result = mod_scorm_external::get_scorm_attempt_count($this->scorm->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_attempt_count_returns(), $result);
        $this->assertEquals(2, $result['attemptscount']);
    }

    public function test_mod_scorm_get_scorm_attempt_count_own_incomplete() {
                self::setUser($this->student);

                $scoes = scorm_get_scoes($this->scorm->id);
        $sco = array_shift($scoes);
        scorm_insert_track($this->student->id, $this->scorm->id, $sco->id, 1, 'cmi.core.lesson_status', 'completed');
        scorm_insert_track($this->student->id, $this->scorm->id, $sco->id, 2, 'cmi.core.credit', '0');

        $result = mod_scorm_external::get_scorm_attempt_count($this->scorm->id, $this->student->id, true);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_attempt_count_returns(), $result);
        $this->assertEquals(1, $result['attemptscount']);
    }

    public function test_mod_scorm_get_scorm_attempt_count_others_as_teacher() {
                self::setUser($this->teacher);

                $scoes = scorm_get_scoes($this->scorm->id);
        $sco = array_shift($scoes);
        scorm_insert_track($this->student->id, $this->scorm->id, $sco->id, 1, 'cmi.core.lesson_status', 'completed');

                $result = mod_scorm_external::get_scorm_attempt_count($this->scorm->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_attempt_count_returns(), $result);
        $this->assertEquals(1, $result['attemptscount']);
    }

    public function test_mod_scorm_get_scorm_attempt_count_others_as_student() {
                $student2 = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student2->id, $this->course->id, $this->studentrole->id, 'manual');

                self::setUser($student2);

                $this->setExpectedException('required_capability_exception');
        mod_scorm_external::get_scorm_attempt_count($this->scorm->id, $this->student->id);
    }

    public function test_mod_scorm_get_scorm_attempt_count_invalid_instanceid() {
                self::setUser($this->student);

                $this->setExpectedException('moodle_exception');
        mod_scorm_external::get_scorm_attempt_count(0, $this->student->id);
    }

    public function test_mod_scorm_get_scorm_attempt_count_invalid_userid() {
                self::setUser($this->student);

        $this->setExpectedException('moodle_exception');
        mod_scorm_external::get_scorm_attempt_count($this->scorm->id, -1);
    }

    
    public function test_mod_scorm_get_scorm_scoes() {
        global $DB;

        $this->resetAfterTest(true);

                $student = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                self::setUser($student);

                $course = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course->id;
        $record->timeopen = time() + DAYSECS;
        $record->timeclose = $record->timeopen + DAYSECS;
        $scorm = self::getDataGenerator()->create_module('scorm', $record);

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

                try {
             mod_scorm_external::get_scorm_scoes($scorm->id);
            $this->fail('Exception expected due to invalid dates.');
        } catch (moodle_exception $e) {
            $this->assertEquals('notopenyet', $e->errorcode);
        }

        $scorm->timeopen = time() - DAYSECS;
        $scorm->timeclose = time() - HOURSECS;
        $DB->update_record('scorm', $scorm);

        try {
             mod_scorm_external::get_scorm_scoes($scorm->id);
            $this->fail('Exception expected due to invalid dates.');
        } catch (moodle_exception $e) {
            $this->assertEquals('expired', $e->errorcode);
        }

                self::setUser($teacher);
        $result = mod_scorm_external::get_scorm_scoes($scorm->id);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_scoes_returns(), $result);
        $this->assertCount(2, $result['scoes']);
        $this->assertCount(0, $result['warnings']);

        $scoes = scorm_get_scoes($scorm->id);
        $sco = array_shift($scoes);
        $sco->extradata = array();
        $this->assertEquals((array) $sco, $result['scoes'][0]);

        $sco = array_shift($scoes);
        $sco->extradata = array();
        $sco->extradata[] = array(
            'element' => 'isvisible',
            'value' => $sco->isvisible
        );
        $sco->extradata[] = array(
            'element' => 'parameters',
            'value' => $sco->parameters
        );
        unset($sco->isvisible);
        unset($sco->parameters);

                usort($result['scoes'][1]['extradata'], function($a, $b) {
            return strcmp($a['element'], $b['element']);
        });

        $this->assertEquals((array) $sco, $result['scoes'][1]);

                $organization = 'golf_sample_default_org';
        $result = mod_scorm_external::get_scorm_scoes($scorm->id, $organization);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_scoes_returns(), $result);
        $this->assertCount(1, $result['scoes']);
        $this->assertEquals($organization, $result['scoes'][0]['organization']);
        $this->assertCount(0, $result['warnings']);

                try {
             mod_scorm_external::get_scorm_scoes(0);
            $this->fail('Exception expected due to invalid instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

    }

    
    public function test_mod_scorm_get_scorm_scoes_complex_package() {
        global $CFG;

                self::setUser($this->student);

        $record = new stdClass();
        $record->course = $this->course->id;
        $record->packagefilepath = $CFG->dirroot.'/mod/scorm/tests/packages/complexscorm.zip';
        $scorm = self::getDataGenerator()->create_module('scorm', $record);

        $result = mod_scorm_external::get_scorm_scoes($scorm->id);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_scoes_returns(), $result);
        $this->assertCount(9, $result['scoes']);
        $this->assertCount(0, $result['warnings']);

        $expectedscoes = array();
        $scoreturnstructure = mod_scorm_external::get_scorm_scoes_returns();
        $scoes = scorm_get_scoes($scorm->id);
        foreach ($scoes as $sco) {
            $sco->extradata = array();
            foreach ($sco as $element => $value) {
                                if (!isset($scoreturnstructure->keys['scoes']->content->keys[$element])) {
                    $sco->extradata[] = array(
                        'element' => $element,
                        'value' => $value
                    );
                    unset($sco->{$element});
                }
            }
            $expectedscoes[] = (array) $sco;
        }

        $this->assertEquals($expectedscoes, $result['scoes']);
    }

    
    public function test_mod_scorm_get_scorm_user_data() {
        global $DB;

        $this->resetAfterTest(true);

                $student1 = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                self::setUser($student1);

                $course = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course->id;
        $scorm = self::getDataGenerator()->create_module('scorm', $record);

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $this->getDataGenerator()->enrol_user($student1->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

                $scoes = scorm_get_scoes($scorm->id);
        $sco = array_shift($scoes);
        scorm_insert_track($student1->id, $scorm->id, $sco->id, 1, 'cmi.core.lesson_status', 'completed');
        scorm_insert_track($student1->id, $scorm->id, $sco->id, 1, 'cmi.core.score.raw', '80');
        scorm_insert_track($student1->id, $scorm->id, $sco->id, 2, 'cmi.core.lesson_status', 'completed');

        $result = mod_scorm_external::get_scorm_user_data($scorm->id, 1);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_user_data_returns(), $result);
        $this->assertCount(2, $result['data']);
                $found = 0;
        foreach ($result['data'] as $scodata) {
            foreach ($scodata['userdata'] as $userdata) {
                if ($userdata['element'] == 'cmi.core.lesson_status' and $userdata['value'] == 'completed') {
                    $found++;
                }
                if ($userdata['element'] == 'cmi.core.score.raw' and $userdata['value'] == '80') {
                    $found++;
                }
            }
        }
        $this->assertEquals(2, $found);

                try {
             mod_scorm_external::get_scorm_user_data(0, 1);
            $this->fail('Exception expected due to invalid instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }
    }

    
    public function test_mod_scorm_insert_scorm_tracks() {
        global $DB;

        $this->resetAfterTest(true);

                $student = self::getDataGenerator()->create_user();

                self::setUser($student);

                $course = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course->id;
        $record->timeopen = time() + DAYSECS;
        $record->timeclose = $record->timeopen + DAYSECS;
        $scorm = self::getDataGenerator()->create_module('scorm', $record);

                $scoes = scorm_get_scoes($scorm->id);
        $sco = array_shift($scoes);

                $tracks = array();
        $tracks[] = array(
            'element' => 'cmi.core.lesson_status',
            'value' => 'completed'
        );
        $tracks[] = array(
            'element' => 'cmi.core.score.raw',
            'value' => '80'
        );

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');

                try {
            mod_scorm_external::insert_scorm_tracks($sco->id, 1, $tracks);
            $this->fail('Exception expected due to dates');
        } catch (moodle_exception $e) {
            $this->assertEquals('notopenyet', $e->errorcode);
        }

        $scorm->timeopen = time() - DAYSECS;
        $scorm->timeclose = time() - HOURSECS;
        $DB->update_record('scorm', $scorm);

        try {
            mod_scorm_external::insert_scorm_tracks($sco->id, 1, $tracks);
            $this->fail('Exception expected due to dates');
        } catch (moodle_exception $e) {
            $this->assertEquals('expired', $e->errorcode);
        }

                try {
             mod_scorm_external::insert_scorm_tracks(0, 1, $tracks);
            $this->fail('Exception expected due to invalid sco id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('cannotfindsco', $e->errorcode);
        }

        $scorm->timeopen = 0;
        $scorm->timeclose = 0;
        $DB->update_record('scorm', $scorm);

                $result = mod_scorm_external::insert_scorm_tracks($sco->id, 1, $tracks);
        $result = external_api::clean_returnvalue(mod_scorm_external::insert_scorm_tracks_returns(), $result);
        $this->assertCount(0, $result['warnings']);

        $trackids = $DB->get_records('scorm_scoes_track', array('userid' => $student->id, 'scoid' => $sco->id,
                                                                'scormid' => $scorm->id, 'attempt' => 1));
                $expectedkeys = array_keys($trackids);
        $this->assertEquals(asort($expectedkeys), asort($result['trackids']));
    }

    
    public function test_mod_scorm_get_scorm_sco_tracks() {
        global $DB;

        $this->resetAfterTest(true);

                $student = self::getDataGenerator()->create_user();
        $otherstudent = self::getDataGenerator()->create_user();
        $teacher = self::getDataGenerator()->create_user();

                self::setUser($student);

                $course = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course->id;
        $scorm = self::getDataGenerator()->create_module('scorm', $record);

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id, 'manual');

                $scoes = scorm_get_scoes($scorm->id);
        $sco = array_shift($scoes);
        scorm_insert_track($student->id, $scorm->id, $sco->id, 1, 'cmi.core.lesson_status', 'completed');
        scorm_insert_track($student->id, $scorm->id, $sco->id, 1, 'cmi.core.score.raw', '80');
        scorm_insert_track($student->id, $scorm->id, $sco->id, 2, 'cmi.core.lesson_status', 'completed');

        $result = mod_scorm_external::get_scorm_sco_tracks($sco->id, $student->id, 1);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_sco_tracks_returns(), $result);
                $this->assertCount(9, $result['data']['tracks']);
        $this->assertEquals(1, $result['data']['attempt']);
        $this->assertCount(0, $result['warnings']);
                $found = 0;
        foreach ($result['data']['tracks'] as $userdata) {
            if ($userdata['element'] == 'cmi.core.lesson_status' and $userdata['value'] == 'completed') {
                $found++;
            }
            if ($userdata['element'] == 'cmi.core.score.raw' and $userdata['value'] == '80') {
                $found++;
            }
        }
        $this->assertEquals(2, $found);

                $result = mod_scorm_external::get_scorm_sco_tracks($sco->id, $student->id, 10);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_sco_tracks_returns(), $result);
        $this->assertCount(0, $result['data']['tracks']);
        $this->assertEquals(10, $result['data']['attempt']);
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('notattempted', $result['warnings'][0]['warningcode']);

                try {
             mod_scorm_external::get_scorm_sco_tracks($sco->id, $otherstudent->id);
            $this->fail('Exception expected due to invalid instance id.');
        } catch (required_capability_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

        self::setUser($teacher);
                $result = mod_scorm_external::get_scorm_sco_tracks($sco->id, $student->id);
        $result = external_api::clean_returnvalue(mod_scorm_external::get_scorm_sco_tracks_returns(), $result);
                $this->assertCount(8, $result['data']['tracks']);
        $this->assertEquals(2, $result['data']['attempt']);

                try {
             mod_scorm_external::get_scorm_sco_tracks(0, 1);
            $this->fail('Exception expected due to invalid instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('cannotfindsco', $e->errorcode);
        }
                try {
             mod_scorm_external::get_scorm_sco_tracks($sco->id, 0);
            $this->fail('Exception expected due to invalid instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invaliduser', $e->errorcode);
        }
    }

    
    public function test_mod_scorm_get_scorms_by_courses() {
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
        $record->hidetoc = 2;
        $record->displayattemptstatus = 2;
        $record->skipview = 2;
        $scorm1 = self::getDataGenerator()->create_module('scorm', $record);

                $record = new stdClass();
        $record->introformat = FORMAT_HTML;
        $record->course = $course2->id;
        $scorm2 = self::getDataGenerator()->create_module('scorm', $record);

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

        $returndescription = mod_scorm_external::get_scorms_by_courses_returns();

        
        $timenow = time();
        $scorm1->timeopen = $timenow - DAYSECS;
        $scorm1->timeclose = $timenow - HOURSECS;
        $DB->update_record('scorm', $scorm1);

        $result = mod_scorm_external::get_scorms_by_courses(array($course1->id));
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertCount(1, $result['warnings']);
                $this->assertCount(6, $result['scorms'][0]);
        $this->assertEquals('expired', $result['warnings'][0]['warningcode']);

        $scorm1->timeopen = $timenow + DAYSECS;
        $scorm1->timeclose = $scorm1->timeopen + DAYSECS;
        $DB->update_record('scorm', $scorm1);

        $result = mod_scorm_external::get_scorms_by_courses(array($course1->id));
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertCount(1, $result['warnings']);
                $this->assertCount(6, $result['scorms'][0]);
        $this->assertEquals('notopenyet', $result['warnings'][0]['warningcode']);

                $scorm1->timeopen = 0;
        $scorm1->timeclose = 0;
        $DB->update_record('scorm', $scorm1);

                        $expectedfields = array('id', 'coursemodule', 'course', 'name', 'intro', 'introformat', 'version', 'maxgrade',
                                'grademethod', 'whatgrade', 'maxattempt', 'forcecompleted', 'forcenewattempt', 'lastattemptlock',
                                'displayattemptstatus', 'displaycoursestructure', 'sha1hash', 'md5hash', 'revision', 'launch',
                                'skipview', 'hidebrowse', 'hidetoc', 'nav', 'navpositionleft', 'navpositiontop', 'auto',
                                'popup', 'width', 'height', 'timeopen', 'timeclose', 'displayactivityname', 'packagesize',
                                'packageurl', 'scormtype', 'reference');

                $scorm1->coursemodule = $scorm1->cmid;
        $scorm1->section = 0;
        $scorm1->visible = true;
        $scorm1->groupmode = 0;
        $scorm1->groupingid = 0;

        $scorm2->coursemodule = $scorm2->cmid;
        $scorm2->section = 0;
        $scorm2->visible = true;
        $scorm2->groupmode = 0;
        $scorm2->groupingid = 0;

                $scormcontext1 = context_module::instance($scorm1->cmid);
        $scormcontext2 = context_module::instance($scorm2->cmid);
        $fs = get_file_storage();
        $packagefile = $fs->get_file($scormcontext1->id, 'mod_scorm', 'package', 0, '/', $scorm1->reference);
        $packagesize = $packagefile->get_filesize();

        $packageurl1 = moodle_url::make_webservice_pluginfile_url(
                            $scormcontext1->id, 'mod_scorm', 'package', 0, '/', $scorm1->reference)->out(false);
        $packageurl2 = moodle_url::make_webservice_pluginfile_url(
                            $scormcontext2->id, 'mod_scorm', 'package', 0, '/', $scorm2->reference)->out(false);

        $scorm1->packagesize = $packagesize;
        $scorm1->packageurl = $packageurl1;
        $scorm2->packagesize = $packagesize;
        $scorm2->packageurl = $packageurl2;

                $protectpackages = (bool)get_config('scorm', 'protectpackagedownloads');
        $expected1 = array('protectpackagedownloads' => $protectpackages);
        $expected2 = array('protectpackagedownloads' => $protectpackages);
        foreach ($expectedfields as $field) {

                                    $fieldtype = $returndescription->keys['scorms']->content->keys[$field]->type;
            if ($fieldtype == PARAM_BOOL) {
                $expected1[$field] = (bool) $scorm1->{$field};
                $expected2[$field] = (bool) $scorm2->{$field};
            } else {
                $expected1[$field] = $scorm1->{$field};
                $expected2[$field] = $scorm2->{$field};
            }
        }

        $expectedscorms = array();
        $expectedscorms[] = $expected2;
        $expectedscorms[] = $expected1;

                $result = mod_scorm_external::get_scorms_by_courses(array($course2->id, $course1->id));
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedscorms, $result['scorms']);

                $result = mod_scorm_external::get_scorms_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedscorms, $result['scorms']);

                $enrol->unenrol_user($instance2, $student->id);
        array_shift($expectedscorms);

                $result = mod_scorm_external::get_scorms_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedscorms, $result['scorms']);

                $result = mod_scorm_external::get_scorms_by_courses(array($course2->id));
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('1', $result['warnings'][0]['warningcode']);
        $this->assertEquals($course2->id, $result['warnings'][0]['itemid']);

                self::setUser($teacher);

        $additionalfields = array('updatefreq', 'timemodified', 'options',
                                    'completionstatusrequired', 'completionscorerequired', 'autocommit',
                                    'section', 'visible', 'groupmode', 'groupingid');

        foreach ($additionalfields as $field) {
            $fieldtype = $returndescription->keys['scorms']->content->keys[$field]->type;

            if ($fieldtype == PARAM_BOOL) {
                $expectedscorms[0][$field] = (bool) $scorm1->{$field};
            } else {
                $expectedscorms[0][$field] = $scorm1->{$field};
            }
        }

        $result = mod_scorm_external::get_scorms_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedscorms, $result['scorms']);

                $scorm1->timeopen = $timenow - DAYSECS;
        $scorm1->timeclose = $timenow - HOURSECS;
        $DB->update_record('scorm', $scorm1);

        $expectedscorms[0]['timeopen'] = $scorm1->timeopen;
        $expectedscorms[0]['timeclose'] = $scorm1->timeclose;

        $result = mod_scorm_external::get_scorms_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedscorms, $result['scorms']);

                self::setAdminUser();

        $result = mod_scorm_external::get_scorms_by_courses(array($course1->id));
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedscorms, $result['scorms']);
    }

    
    public function test_launch_sco() {
        global $DB;

                try {
            mod_scorm_external::launch_sco(0);
            $this->fail('Exception expected due to invalid mod_scorm instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_scorm_external::launch_sco($this->scorm->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $scoes = scorm_get_scoes($this->scorm->id);
        foreach ($scoes as $sco) {
                        if ($sco->launch != '') {
                break;
            }
        }

        $result = mod_scorm_external::launch_sco($this->scorm->id, $sco->id);
        $result = external_api::clean_returnvalue(mod_scorm_external::launch_sco_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_scorm\event\sco_launched', $event);
        $this->assertEquals($this->context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/scorm/player.php', array('id' => $this->cm->id, 'scoid' => $sco->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                try {
            mod_scorm_external::launch_sco($this->scorm->id, -1);
            $this->fail('Exception expected due to invalid SCO id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('cannotfindsco', $e->errorcode);
        }
    }
}
