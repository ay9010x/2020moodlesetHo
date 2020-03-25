<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/enrol/imsenterprise/locallib.php');
require_once($CFG->dirroot . '/enrol/imsenterprise/lib.php');


class enrol_imsenterprise_testcase extends advanced_testcase {

    
    public $imsplugin;

    
    protected function setUp() {
        $this->resetAfterTest(true);
        $this->imsplugin = enrol_get_plugin('imsenterprise');
        $this->set_test_config();
    }

    
    public function test_emptyfile() {
        global $DB;

        $prevncourses = $DB->count_records('course');
        $prevnusers = $DB->count_records('user');

        $this->set_xml_file(false, false);
        $this->imsplugin->cron();

        $this->assertEquals($prevncourses, $DB->count_records('course'));
        $this->assertEquals($prevnusers, $DB->count_records('user'));
    }

    
    public function test_users_existing() {
        global $DB;

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $prevnusers = $DB->count_records('user');

        $users = array($user1, $user2);
        $this->set_xml_file($users);
        $this->imsplugin->cron();

        $this->assertEquals($prevnusers, $DB->count_records('user'));
    }

    
    public function test_users_add() {
        global $DB;

        $prevnusers = $DB->count_records('user');

        $user1 = new StdClass();
        $user1->username = 'u1';
        $user1->email = 'u1@example.com';
        $user1->firstname = 'U';
        $user1->lastname = '1';

        $users = array($user1);
        $this->set_xml_file($users);
        $this->imsplugin->cron();

        $this->assertEquals(($prevnusers + 1), $DB->count_records('user'));
    }

    
    public function test_courses_existing() {
        global $DB;

        $course1 = $this->getDataGenerator()->create_course(array('idnumber' => 'id1'));
        $course2 = $this->getDataGenerator()->create_course(array('idnumber' => 'id2'));

                $course1->imsshort = $course1->fullname;
        $course2->imsshort = $course2->fullname;

        $prevncourses = $DB->count_records('course');

        $courses = array($course1, $course2);
        $this->set_xml_file(false, $courses);
        $this->imsplugin->cron();

        $this->assertEquals($prevncourses, $DB->count_records('course'));
    }

    
    public function test_courses_add() {
        global $DB;

        $prevncourses = $DB->count_records('course');

        $course1 = new StdClass();
        $course1->idnumber = 'id1';
        $course1->imsshort = 'id1';
        $course1->category = 'DEFAULT CATNAME';

        $course2 = new StdClass();
        $course2->idnumber = 'id2';
        $course2->imsshort = 'id2';
        $course2->category = 'DEFAULT CATNAME';

        $courses = array($course1, $course2);
        $this->set_xml_file(false, $courses);
        $this->imsplugin->cron();

        $this->assertEquals(($prevncourses + 2), $DB->count_records('course'));
    }

    
    public function test_course_add_default_category() {
        global $DB, $CFG;
        require_once($CFG->libdir.'/coursecatlib.php');

        $this->imsplugin->set_config('createnewcategories', false);

                $defaultcat = coursecat::get_default();
        $defaultcat->delete_full(false);

                $course1 = new stdClass();
        $course1->idnumber = 'id1';
        $course1->imsshort = 'id1';
        $course1->category = '';
        $this->set_xml_file(false, array($course1));
        $this->imsplugin->cron();

                $dbcourse = $DB->get_record('course', array('idnumber' => $course1->idnumber), '*', MUST_EXIST);
                $this->assertTrue($DB->record_exists('course_categories', array('id' => $dbcourse->category)));
    }

    
    public function test_courses_attrmapping() {
        global $DB;

                $this->imsplugin->set_config('imscoursemapshortname', 'coursecode');
        $this->imsplugin->set_config('imscoursemapfullname', 'coursecode');
        $this->imsplugin->set_config('imscoursemapsummary', 'coursecode');

        $course1 = new StdClass();
        $course1->idnumber = 'id1';
        $course1->imsshort = 'description_short1';
        $course1->imslong = 'description_long';
        $course1->imsfull = 'description_full';
        $course1->category = 'DEFAULT CATNAME';

        $this->set_xml_file(false, array($course1));
        $this->imsplugin->cron();

        $dbcourse = $DB->get_record('course', array('idnumber' => $course1->idnumber));
        $this->assertFalse(!$dbcourse);
        $this->assertEquals($dbcourse->shortname, $course1->idnumber);
        $this->assertEquals($dbcourse->fullname, $course1->idnumber);
        $this->assertEquals($dbcourse->summary, $course1->idnumber);

                $this->imsplugin->set_config('imscoursemapshortname', 'short');
        $this->imsplugin->set_config('imscoursemapfullname', 'long');
        $this->imsplugin->set_config('imscoursemapsummary', 'full');

        $course2 = new StdClass();
        $course2->idnumber = 'id2';
        $course2->imsshort = 'description_short2';
        $course2->imslong = 'description_long';
        $course2->imsfull = 'description_full';
        $course2->category = 'DEFAULT CATNAME';

        $this->set_xml_file(false, array($course2));
        $this->imsplugin->cron();

        $dbcourse = $DB->get_record('course', array('idnumber' => $course2->idnumber));
        $this->assertFalse(!$dbcourse);
        $this->assertEquals($dbcourse->shortname, $course2->imsshort);
        $this->assertEquals($dbcourse->fullname, $course2->imslong);
        $this->assertEquals($dbcourse->summary, $course2->imsfull);

                $this->imsplugin->set_config('imscoursemapshortname', 'short');
        $this->imsplugin->set_config('imscoursemapfullname', 'long');
        $this->imsplugin->set_config('imscoursemapsummary', 'full');

        $course3 = new StdClass();
        $course3->idnumber = 'id3';
        $course3->imsshort = 'description_short3';
        $course3->category = 'DEFAULT CATNAME';

        $this->set_xml_file(false, array($course3));
        $this->imsplugin->cron();

        $dbcourse = $DB->get_record('course', array('idnumber' => $course3->idnumber));
        $this->assertFalse(!$dbcourse);
        $this->assertEquals($dbcourse->shortname, $course3->imsshort);
        $this->assertEquals($dbcourse->fullname, $course3->idnumber);
        $this->assertEquals($dbcourse->summary, $course3->idnumber);

    }

    
    public function set_test_config() {
        $this->imsplugin->set_config('mailadmins', false);
        $this->imsplugin->set_config('prev_path', '');
        $this->imsplugin->set_config('createnewusers', true);
        $this->imsplugin->set_config('createnewcourses', true);
        $this->imsplugin->set_config('createnewcategories', true);
    }

    
    public function set_xml_file($users = false, $courses = false) {

        $xmlcontent = '<enterprise>';

                if (!empty($users)) {
            foreach ($users as $user) {
                $xmlcontent .= '
  <person>
    <sourcedid>
      <source>TestSource</source>
      <id>'.$user->username.'</id>
    </sourcedid>
    <userid>'.$user->username.'</userid>
    <name>
      <fn>'.$user->firstname.' '.$user->lastname.'</fn>
      <n>
        <family>'.$user->lastname.'</family>
        <given>'.$user->firstname.'</given>
      </n>
    </name>
    <email>'.$user->email.'</email>
  </person>';
            }
        }

                        if (!empty($courses)) {
            foreach ($courses as $course) {

                $xmlcontent .= '
  <group>
    <sourcedid>
      <source>TestSource</source>
      <id>'.$course->idnumber.'</id>
    </sourcedid>
    <description>';

                                if (!empty($course->imsshort)) {
                    $xmlcontent .= '
      <short>'.$course->imsshort.'</short>';
                }

                                if (!empty($course->imslong)) {
                    $xmlcontent .= '
      <long>'.$course->imslong.'</long>';
                }

                                if (!empty($course->imsfull)) {
                    $xmlcontent .= '
      <full>'.$course->imsfull.'</full>';
                }

                                $xmlcontent .= '
    </description>
    <org>
      <orgunit>'.$course->category.'</orgunit>
    </org>
  </group>';
            }
        }

        $xmlcontent .= '
</enterprise>';

                $filename = 'ims_' . rand(1000, 9999) . '.xml';
        $tmpdir = make_temp_directory('enrol_imsenterprise');
        $xmlfilepath = $tmpdir . '/' . $filename;
        file_put_contents($xmlfilepath, $xmlcontent);

                $this->imsplugin->set_config('imsfilelocation', $xmlfilepath);
    }

    
    public function test_imsenterprise_cron_task() {
        global $DB;
        $prevnusers = $DB->count_records('user');

        $user1 = new StdClass();
        $user1->username = 'u1';
        $user1->email = 'u1@example.com';
        $user1->firstname = 'U';
        $user1->lastname = '1';

        $users = array($user1);
        $this->set_xml_file($users);

        $task = new enrol_imsenterprise\task\cron_task();
        $task->execute();

        $this->assertEquals(($prevnusers + 1), $DB->count_records('user'));
    }
}
