<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/grade/import/csv/tests/fixtures/phpunit_gradeimport_csv_load_data.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/grade/grade_item.php');
require_once($CFG->libdir . '/grade/tests/fixtures/lib.php');


class gradeimport_csv_load_data_testcase extends grade_base_testcase {

    
    protected $oktext = '"First name",Surname,"ID number",Institution,Department,"Email address","Assignment: Assignment for grape group", "Feedback: Assignment for grape group","Assignment: Second new grade item","Course total"
Anne,Able,,"Moodle HQ","Rock on!",student7@example.com,56.00,"We welcome feedback",,56.00
Bobby,Bunce,,"Moodle HQ","Rock on!",student5@example.com,75.00,,45.0,75.00';

    
    protected $badtext = '"First name",Surname,"ID number",Institution,Department,"Email address","Assignment: Assignment for grape group","Course total"
Anne,Able,,"Moodle HQ","Rock on!",student7@example.com,56.00,56.00,78.00
Bobby,Bunce,,"Moodle HQ","Rock on!",student5@example.com,75.00,75.00';

    
    protected $csvtext = '"First name",Surname,"ID number",Institution,Department,"Email address","Assignment: Assignment for grape group", "Feedback: Assignment for grape group","Course total","Last downloaded from this course"
Anne,Able,,"Moodle HQ","Rock on!",student7@example.com,56.00,"We welcome feedback",56.00,{exportdate}
Bobby,Bunce,,"Moodle HQ","Rock on!",student5@example.com,75.00,,75.00,{exportdate}';

    
    protected $iid;

    
    protected $csvimport;

    
    protected $columns;

    public function tearDown() {
        $this->csvimport = null;
    }

    
    protected function csv_load($content) {
                $this->iid = csv_import_reader::get_new_iid('grade');
        $this->csvimport = new csv_import_reader($this->iid, 'grade');

        $this->csvimport->load_csv_content($content, 'utf8', 'comma');
        $this->columns = $this->csvimport->get_columns();

        $this->csvimport->init();
        while ($line = $this->csvimport->next()) {
            $testarray[] = $line;
        }

        return $testarray;
    }

    
    public function test_load_csv_content() {
        $encoding = 'utf8';
        $separator = 'comma';
        $previewrows = 5;
        $csvpreview = new phpunit_gradeimport_csv_load_data();
        $csvpreview->load_csv_content($this->oktext, $encoding, $separator, $previewrows);

        $expecteddata = array(array(
                'Anne',
                'Able',
                '',
                'Moodle HQ',
                'Rock on!',
                'student7@example.com',
                56.00,
                'We welcome feedback',
                '',
                56.00
            ),
            array(
                'Bobby',
                'Bunce',
                '',
                'Moodle HQ',
                'Rock on!',
                'student5@example.com',
                75.00,
                '',
                45.0,
                75.00
            )
        );

        $expectedheaders = array(
            'First name',
            'Surname',
            'ID number',
            'Institution',
            'Department',
            'Email address',
            'Assignment: Assignment for grape group',
            'Feedback: Assignment for grape group',
            'Assignment: Second new grade item',
            'Course total'
        );
                $this->assertEquals($csvpreview->get_previewdata(), $expecteddata);
                $this->assertEquals($csvpreview->get_headers(), $expectedheaders);

                $csvpreview = new phpunit_gradeimport_csv_load_data();
        $csvpreview->load_csv_content($this->badtext, $encoding, $separator, $previewrows);
                $this->assertEquals($csvpreview->get_error(), get_string('csvweirdcolumns', 'error'));
    }

    
    public function test_fetch_grade_items() {

        $gradeitemsarray = grade_item::fetch_all(array('courseid' => $this->courseid));
        $gradeitems = phpunit_gradeimport_csv_load_data::fetch_grade_items($this->courseid);

                foreach ($gradeitems as $key => $gradeitem) {
            $this->assertArrayHasKey($key, $gradeitemsarray);
        }

                $quizkey = null;
        foreach ($gradeitemsarray as $key => $value) {
            if ($value->itemname == "Quiz grade item") {
                $quizkey = $key;
            }
        }

                $testitemname = get_string('modulename', $gradeitemsarray[$quizkey]->itemmodule) . ': ' .
                $gradeitemsarray[$quizkey]->itemname;
                $this->assertEquals($testitemname, $gradeitems[$quizkey]);
    }

    
    public function test_insert_grade_record() {
        global $DB, $USER;

        $user = $this->getDataGenerator()->create_user();
        $this->setAdminUser();

        $record = new stdClass();
        $record->itemid = 4;
        $record->newgradeitem = 25;
        $record->finalgrade = 62.00;
        $record->feedback = 'Some test feedback';

        $testobject = new phpunit_gradeimport_csv_load_data();
        $testobject->test_insert_grade_record($record, $user->id);

        $gradeimportvalues = $DB->get_records('grade_import_values');
                $key = key($gradeimportvalues);

        $testarray = array();
        $testarray[$key] = new stdClass();
        $testarray[$key]->id = $key;
        $testarray[$key]->itemid = $record->itemid;
        $testarray[$key]->newgradeitem = $record->newgradeitem;
        $testarray[$key]->userid = $user->id;
        $testarray[$key]->finalgrade = $record->finalgrade;
        $testarray[$key]->feedback = $record->feedback;
        $testarray[$key]->importcode = $testobject->get_importcode();
        $testarray[$key]->importer = $USER->id;
        $testarray[$key]->importonlyfeedback = 0;

                $this->assertEquals($gradeimportvalues, $testarray);
    }

    
    public function test_import_new_grade_item() {
        global $DB;

        $this->setAdminUser();
        $this->csv_load($this->oktext);
        $columns = $this->columns;

                $key = 6;
        $testobject = new phpunit_gradeimport_csv_load_data();

                $this->csvimport->init();
        $testarray = array();
        while ($line = $this->csvimport->next()) {
            $testarray[] = $testobject->test_import_new_grade_item($columns, $key, $line[$key]);
        }

                $newgradeimportitems = $DB->get_records('grade_import_newitem');
        $this->assertEquals(count($testarray), count($newgradeimportitems));
    }

    
    public function test_check_user_exists() {

                $user = new stdClass();
        $user->firstname = 'Anne';
        $user->lastname = 'Able';
        $user->email = 'student7@example.com';
        $userdetail = $this->getDataGenerator()->create_user($user);

        $testobject = new phpunit_gradeimport_csv_load_data();

        $testarray = $this->csv_load($this->oktext);

        $userfields = array('field' => 'email', 'label' => 'Email address');
                $userid = $testobject->test_check_user_exists($testarray[0][5] , $userfields);
                $this->assertEquals($userid, $userdetail->id);

                        $userfields = array('field' => 'id', 'label' => 'userid');
        $userid = $testobject->test_check_user_exists($testarray[0][0], $userfields);
                $this->assertNull($userid);

                $mappingobject = new stdClass();
        $mappingobject->field = $userfields['label'];
        $mappingobject->value = $testarray[0][0];
        $expectederrormessage = get_string('usermappingerror', 'grades', $mappingobject);
                $gradebookerrors = $testobject->get_gradebookerrors();
        $this->assertEquals($expectederrormessage, $gradebookerrors[0]);

                $userid = $testobject->test_check_user_exists($testarray[1][5], $userfields);
                $this->assertNull($userid);

                $mappingobject = new stdClass();
        $mappingobject->field = $userfields['label'];
        $mappingobject->value = $testarray[1][5];
        $expectederrormessage = get_string('usermappingerror', 'grades', $mappingobject);
                $gradebookerrors = $testobject->get_gradebookerrors();
                $this->assertEquals($expectederrormessage, $gradebookerrors[1]);
    }

    
    public function test_create_feedback() {

        $testarray = $this->csv_load($this->oktext);
        $testobject = new phpunit_gradeimport_csv_load_data();

                $feedback = $testobject->test_create_feedback($this->courseid, 1, $testarray[0][7]);

                $expectedfeedback = array('itemid' => 1, 'feedback' => $testarray[0][7]);
        $this->assertEquals((array)$feedback, $expectedfeedback);
    }

    
    public function test_update_grade_item() {

        $testarray = $this->csv_load($this->oktext);
        $testobject = new phpunit_gradeimport_csv_load_data();

                $verbosescales = 0;
                $map = array(1);
        $key = 0;
                $newgrades = $testobject->test_update_grade_item($this->courseid, $map, $key, $verbosescales, $testarray[0][6]);

        $expectedresult = array();
        $expectedresult[0] = new stdClass();
        $expectedresult[0]->itemid = 1;
        $expectedresult[0]->finalgrade = $testarray[0][6];

        $this->assertEquals($newgrades, $expectedresult);

                $newgrades = $testobject->test_update_grade_item($this->courseid, $map, $key, $verbosescales, 'A');
                $this->assertNull($newgrades);
        $expectederrormessage = get_string('badgrade', 'grades');
                $gradebookerrors = $testobject->get_gradebookerrors();
        $this->assertEquals($expectederrormessage, $gradebookerrors[0]);
    }

    
    public function test_map_user_data_with_value() {
                $user = new stdClass();
        $user->firstname = 'Anne';
        $user->lastname = 'Able';
        $user->email = 'student7@example.com';
        $userdetail = $this->getDataGenerator()->create_user($user);

        $testarray = $this->csv_load($this->oktext);
        $testobject = new phpunit_gradeimport_csv_load_data();

                $verbosescales = 0;
                $map = array(1);
        $key = 0;

                $userid = $testobject->test_map_user_data_with_value('useremail', $testarray[0][5], $this->columns, $map, $key,
                $this->courseid, $map[$key], $verbosescales);
        $this->assertEquals($userid, $userdetail->id);

        $newgrades = $testobject->test_map_user_data_with_value('new', $testarray[0][6], $this->columns, $map, $key,
                $this->courseid, $map[$key], $verbosescales);
                $this->assertEquals($testarray[0][6], $newgrades[0]->finalgrade);

        $newgrades = $testobject->test_map_user_data_with_value('new', $testarray[0][8], $this->columns, $map, $key,
                $this->courseid, $map[$key], $verbosescales);
                        $this->assertEquals(2, count($newgrades));
                $this->assertNull($newgrades[1]->finalgrade);

        $feedback = $testobject->test_map_user_data_with_value('feedback', $testarray[0][7], $this->columns, $map, $key,
                $this->courseid, $map[$key], $verbosescales);
                $resultarray = array();
        $resultarray[0] = new stdClass();
        $resultarray[0]->itemid = 1;
        $resultarray[0]->feedback = $testarray[0][7];
        $this->assertEquals($feedback, $resultarray);

                $newgrades = $testobject->test_map_user_data_with_value('default', $testarray[0][6], $this->columns, $map, $key,
                $this->courseid, $map[$key], $verbosescales);
        $this->assertEquals($testarray[0][6], $newgrades[0]->finalgrade);
    }

    
    public function test_prepare_import_grade_data() {
        global $DB;

                $user = new stdClass();
        $user->firstname = 'Anne';
        $user->lastname = 'Able';
        $user->email = 'student7@example.com';
                $this->getDataGenerator()->create_user($user);
        $user = new stdClass();
        $user->firstname = 'Bobby';
        $user->lastname = 'Bunce';
        $user->email = 'student5@example.com';
                $this->getDataGenerator()->create_user($user);

        $this->csv_load($this->oktext);

        $importcode = 007;
        $verbosescales = 0;

                $formdata = new stdClass();
        $formdata->mapfrom = 5;
        $formdata->mapto = 'useremail';
        $formdata->mapping_0 = 0;
        $formdata->mapping_1 = 0;
        $formdata->mapping_2 = 0;
        $formdata->mapping_3 = 0;
        $formdata->mapping_4 = 0;
        $formdata->mapping_5 = 0;
        $formdata->mapping_6 = 'new';
        $formdata->mapping_7 = 'feedback_2';
        $formdata->mapping_8 = 0;
        $formdata->mapping_9 = 0;
        $formdata->map = 1;
        $formdata->id = 2;
        $formdata->iid = $this->iid;
        $formdata->importcode = $importcode;
        $formdata->forceimport = false;

                $testobject = new phpunit_gradeimport_csv_load_data();
        $dataloaded = $testobject->prepare_import_grade_data($this->columns, $formdata, $this->csvimport, $this->courseid, '', '',
                $verbosescales);
                $this->assertTrue($dataloaded);
    }

    
    public function test_force_import_option () {

                $user = new stdClass();
        $user->firstname = 'Anne';
        $user->lastname = 'Able';
        $user->email = 'student7@example.com';
        $user->id_number = 1;
        $user1 = $this->getDataGenerator()->create_user($user);
        $user = new stdClass();
        $user->firstname = 'Bobby';
        $user->lastname = 'Bunce';
        $user->email = 'student5@example.com';
        $user->id_number = 2;
        $user2 = $this->getDataGenerator()->create_user($user);

                $params = array(
            'itemtype'  => 'manual',
            'itemname'  => 'Grade item 1',
            'gradetype' => GRADE_TYPE_VALUE,
            'courseid'  => $this->courseid
        );
        $gradeitem = new grade_item($params, false);
        $gradeitemid = $gradeitem->insert();

        $importcode = 001;
        $verbosescales = 0;

                $formdata = new stdClass();
        $formdata->mapfrom = 5;
        $formdata->mapto = 'useremail';
        $formdata->mapping_0 = 0;
        $formdata->mapping_1 = 0;
        $formdata->mapping_2 = 0;
        $formdata->mapping_3 = 0;
        $formdata->mapping_4 = 0;
        $formdata->mapping_5 = 0;
        $formdata->mapping_6 = $gradeitemid;
        $formdata->mapping_7 = 'feedback_2';
        $formdata->mapping_8 = 0;
        $formdata->mapping_9 = 0;
        $formdata->map = 1;
        $formdata->id = 2;
        $formdata->iid = $this->iid;
        $formdata->importcode = $importcode;
        $formdata->forceimport = false;

                $exportdate = time();
        $newcsvdata = str_replace('{exportdate}', $exportdate, $this->csvtext);
        $this->csv_load($newcsvdata);
        $testobject = new phpunit_gradeimport_csv_load_data();
        $dataloaded = $testobject->prepare_import_grade_data($this->columns, $formdata, $this->csvimport,
                $this->courseid, '', '', $verbosescales);
        $this->assertTrue($dataloaded);

                grade_import_commit($this->courseid, $importcode, false, false);

                $pastdate = strtotime('-1 day', time());
        $newcsvdata = str_replace('{exportdate}', $pastdate, $this->csvtext);
        $this->csv_load($newcsvdata);
        $testobject = new phpunit_gradeimport_csv_load_data();
        $dataloaded = $testobject->prepare_import_grade_data($this->columns, $formdata, $this->csvimport,
                $this->courseid, '', '', $verbosescales);
        $this->assertFalse($dataloaded);
        $errors = $testobject->get_gradebookerrors();
        $this->assertEquals($errors[0], get_string('gradealreadyupdated', 'grades', fullname($user1)));

                $formdata->forceimport = true;
        $testobject = new phpunit_gradeimport_csv_load_data();
        $dataloaded = $testobject->prepare_import_grade_data($this->columns, $formdata, $this->csvimport,
                $this->courseid, '', '', $verbosescales);
        $this->assertTrue($dataloaded);

                $formdata->forceimport = false;
        $twoyearsago = strtotime('-2 year', time());
        $newcsvdata = str_replace('{exportdate}', $twoyearsago, $this->csvtext);
        $this->csv_load($newcsvdata);
        $testobject = new phpunit_gradeimport_csv_load_data();
        $dataloaded = $testobject->prepare_import_grade_data($this->columns, $formdata, $this->csvimport,
                $this->courseid, '', '', $verbosescales);
        $this->assertFalse($dataloaded);
        $errors = $testobject->get_gradebookerrors();
        $this->assertEquals($errors[0], get_string('invalidgradeexporteddate', 'grades'));

                $baddate = '0123A56B89';
        $newcsvdata = str_replace('{exportdate}', $baddate, $this->csvtext);
        $this->csv_load($newcsvdata);
        $formdata->mapping_6 = $gradeitemid;
        $testobject = new phpunit_gradeimport_csv_load_data();
        $dataloaded = $testobject->prepare_import_grade_data($this->columns, $formdata, $this->csvimport,
                $this->courseid, '', '', $verbosescales);
        $this->assertFalse($dataloaded);
        $errors = $testobject->get_gradebookerrors();
        $this->assertEquals($errors[0], get_string('invalidgradeexporteddate', 'grades'));

                $oneyearahead = strtotime('+1 year', time());
        $oldcsv = str_replace('{exportdate}', $oneyearahead, $this->csvtext);
        $this->csv_load($oldcsv);
        $formdata->mapping_6 = $gradeitemid;
        $testobject = new phpunit_gradeimport_csv_load_data();
        $dataloaded = $testobject->prepare_import_grade_data($this->columns, $formdata, $this->csvimport,
            $this->courseid, '', '', $verbosescales);
        $this->assertFalse($dataloaded);
        $errors = $testobject->get_gradebookerrors();
        $this->assertEquals($errors[0], get_string('invalidgradeexporteddate', 'grades'));
    }
}
