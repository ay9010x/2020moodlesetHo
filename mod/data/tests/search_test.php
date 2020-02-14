<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/data/lib.php');
require_once($CFG->dirroot . '/lib/csvlib.class.php');



class data_advanced_search_sql_test extends advanced_testcase {
    
    public $recorddata = null;
    
    public $recordcontentid = null;
    
    public $recordrecordid = null;
    
    public $recordfieldid = null;
    
    public $recordsearcharray = null;

    
    
    public $datarecordcount = 100;

    
    public $groupdatarecordcount = 75;

    
    public $datarecordset = array('0' => '6');

    
    public $finalrecord = array();

    
    public $approvedatarecordcount = 89;

    
    protected function setUp() {
        global $DB, $CFG;
        parent::setUp();

        $this->resetAfterTest(true);


                                $DB->get_manager()->reset_sequence('user');
        for($i=3;$i<=100;$i++) {
            $this->getDataGenerator()->create_user();
        }

                $course = $this->getDataGenerator()->create_course();
        $data = $this->getDataGenerator()->create_module('data', array('course'=>$course->id));
        $this->recorddata = $data;

                $files = array(
            'data_fields'  => __DIR__.'/fixtures/test_data_fields.csv',
            'data_records' => __DIR__.'/fixtures/test_data_records.csv',
            'data_content' => __DIR__.'/fixtures/test_data_content.csv',
        );
        $this->loadDataSet($this->createCsvDataSet($files));
                $DB->execute('UPDATE {data_fields} SET dataid = ?', array($data->id));
        $DB->execute('UPDATE {data_records} SET dataid = ?', array($data->id));

                $fieldinfo = array('0' => new stdClass(),
            '1' => new stdClass(),
            '2' => new stdClass(),
            '3' => new stdClass(),
            '4' => new stdClass());
        $fieldinfo['0']->id = 1;
        $fieldinfo['0']->data = '3.721,46.6126';
        $fieldinfo['1']->id = 2;
        $fieldinfo['1']->data = 'Hahn Premium';
        $fieldinfo['2']->id = 5;
        $fieldinfo['2']->data = 'Female';
        $fieldinfo['3']->id = 7;
        $fieldinfo['3']->data = 'kel';
        $fieldinfo['4']->id = 9;
        $fieldinfo['4']->data = 'VIC';

        foreach($fieldinfo as $field) {
            $searchfield = data_get_field_from_id($field->id, $data);
            if ($field->id == 2) {
                $searchfield->field->param1 = 'Hahn Premium';
                $val = array();
                $val['selected'] = array('0' => 'Hahn Premium');
                $val['allrequired'] = 0;
            } else {
                $val = $field->data;
            }
            $search_array[$field->id] = new stdClass();
            list($search_array[$field->id]->sql, $search_array[$field->id]->params) = $searchfield->generate_sql('c' . $field->id, $val);
        }

        $this->recordsearcharray = $search_array;

                $user = $DB->get_record('user', array('id'=>6));
        $this->finalrecord[6] = new stdClass();
        $this->finalrecord[6]->id = 6;
        $this->finalrecord[6]->approved = 1;
        $this->finalrecord[6]->timecreated = 1234567891;
        $this->finalrecord[6]->timemodified = 1234567892;
        $this->finalrecord[6]->userid = 6;
        $this->finalrecord[6]->firstname = $user->firstname;
        $this->finalrecord[6]->lastname = $user->lastname;
        $this->finalrecord[6]->firstnamephonetic = $user->firstnamephonetic;
        $this->finalrecord[6]->lastnamephonetic = $user->lastnamephonetic;
        $this->finalrecord[6]->middlename = $user->middlename;
        $this->finalrecord[6]->alternatename = $user->alternatename;
        $this->finalrecord[6]->picture = $user->picture;
        $this->finalrecord[6]->imagealt = $user->imagealt;
        $this->finalrecord[6]->email = $user->email;
    }

    
    function test_advanced_search_sql_section() {
        global $DB;

                $recordids = data_get_all_recordids($this->recorddata->id);
        $this->assertEquals(count($recordids), $this->datarecordcount);

                $key = array_keys($this->recordsearcharray);
        $alias = $key[0];
        $newrecordids = data_get_recordids($alias, $this->recordsearcharray, $this->recorddata->id, $recordids);
        $this->assertEquals($this->datarecordset, $newrecordids);

                $newrecordids = data_get_advance_search_ids($recordids, $this->recordsearcharray, $this->recorddata->id);
        $this->assertEquals($this->datarecordset, $newrecordids);

                $resultrecordids = data_get_advance_search_ids(array(), $this->recordsearcharray, $this->recorddata->id);
        $this->assertEmpty($resultrecordids);

                $sortorder = 'ORDER BY r.timecreated ASC , r.id ASC';
        $html = data_get_advanced_search_sql('0', $this->recorddata, $newrecordids, '', $sortorder);
        $allparams = array_merge($html['params'], array('dataid' => $this->recorddata->id));
        $records = $DB->get_records_sql($html['sql'], $allparams);
        $this->assertEquals($records, $this->finalrecord);

                $groupsql = " AND (r.groupid = :currentgroup OR r.groupid = 0)";
        $params = array('currentgroup' => 1);
        $recordids = data_get_all_recordids($this->recorddata->id, $groupsql, $params);
        $this->assertEquals($this->groupdatarecordcount, count($recordids));

                $approvesql = ' AND r.approved=1 ';
        $recordids = data_get_all_recordids($this->recorddata->id, $approvesql, $params);
        $this->assertEquals($this->approvedatarecordcount, count($recordids));
    }
}
