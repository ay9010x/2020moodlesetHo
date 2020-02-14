<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/datetimeselector.php');
require_once($CFG->libdir.'/formslib.php');


class core_form_datetimeselector_testcase extends advanced_testcase {
    
    private $mform;
    
    private $testvals;

    
    protected function setUp() {
        global $CFG;
        parent::setUp();

        $this->resetAfterTest();
        $this->setAdminUser();

        $this->setTimezone('Australia/Perth');

                $form = new temp_form_datetime();
        $this->mform = $form->getform();

                $this->testvals = array(
            array (
                'minute' => 0,
                'hour' => 0,
                'day' => 1,
                'month' => 7,
                'year' => 2011,
                'usertimezone' => 'America/Moncton',
                'timezone' => 'America/Moncton',
                'timestamp' => 1309489200
            ),
            array (
                'minute' => 0,
                'hour' => 0,
                'day' => 1,
                'month' => 7,
                'year' => 2011,
                'usertimezone' => 'America/Moncton',
                'timezone' => 99,
                'timestamp' => 1309489200
            ),
            array (
                'minute' => 0,
                'hour' => 23,
                'day' => 30,
                'month' => 6,
                'year' => 2011,
                'usertimezone' => 'America/Moncton',
                'timezone' => -4,
                'timestamp' => 1309489200
            ),
            array (
                'minute' => 0,
                'hour' => 23,
                'day' => 30,
                'month' => 6,
                'year' => 2011,
                'usertimezone' => -4,
                'timezone' => 99,
                'timestamp' => 1309489200
            ),
            array (
                'minute' => 0,
                'hour' => 0,
                'day' => 1,
                'month' => 7,
                'year' => 2011,
                'usertimezone' => 0.0,
                'timezone' => 0.0,
                'timestamp' => 1309478400             ),
            array (
                'minute' => 0,
                'hour' => 0,
                'day' => 1,
                'month' => 7,
                'year' => 2011,
                'usertimezone' => 0.0,
                'timezone' => 99,
                'timestamp' => 1309478400             )
        );
    }

    
    public function test_exportvalue() {
        global $USER;
        $testvals = $this->testvals;

        foreach ($testvals as $vals) {
                        $USER->timezone = $vals['usertimezone'];

                        $elparams = array('optional'=>false, 'timezone' => $vals['timezone']);
            $el = new MoodleQuickForm_date_time_selector('dateselector', null, $elparams);
            $el->_createElements();
            $submitvalues = array('dateselector' => $vals);

            $this->assertSame(array('dateselector' => $vals['timestamp']), $el->exportValue($submitvalues, true),
                    "Please check if timezones are updated (Site adminstration -> location -> update timezone)");
        }
    }

    
    public function test_onquickformevent() {
        global $USER;
        $testvals = $this->testvals;
                $mform = $this->mform;

        foreach ($testvals as $vals) {
                        $USER->timezone = $vals['usertimezone'];

                        $elparams = array('optional'=>false, 'timezone' => $vals['timezone']);
            $el = new MoodleQuickForm_date_time_selector('dateselector', null, $elparams);
            $el->_createElements();
            $expectedvalues = array(
                'day' => array($vals['day']),
                'month' => array($vals['month']),
                'year' => array($vals['year']),
                'hour' => array($vals['hour']),
                'minute' => array($vals['minute'])
                );
            $mform->_submitValues = array('dateselector' => $vals['timestamp']);
            $el->onQuickFormEvent('updateValue', null, $mform);
            $this->assertSame($expectedvalues, $el->getValue());
        }
    }
}


class temp_form_datetime extends moodleform {
    
    public function definition() {
            }
    
    public function getform() {
        $mform = $this->_form;
                $mform->_flagSubmitted = true;
        return $mform;
    }
}
