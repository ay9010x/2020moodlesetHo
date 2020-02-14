<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/scheduler/locallib.php');


class mod_scheduler_scheduler_testcase extends advanced_testcase {

    protected $moduleid;        protected $courseid;        protected $schedulerid;     protected $slotid;      
    protected function setUp() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $this->courseid  = $course->id;

        $options = array();
        $options['slottimes'] = array();
        $options['slotstudents'] = array();
        for ($c = 0; $c < 4; $c++) {
            $options['slottimes'][$c] = time() + ($c + 1) * DAYSECS;
            $options['slotstudents'][$c] = array($this->getDataGenerator()->create_user()->id);
        }
        $options['slottimes'][4] = time() + 10 * DAYSECS;
        $options['slottimes'][5] = time() + 11 * DAYSECS;
        $options['slotstudents'][5] = array(
                                        $this->getDataGenerator()->create_user()->id,
                                        $this->getDataGenerator()->create_user()->id
                                      );

        $scheduler = $this->getDataGenerator()->create_module('scheduler', array('course' => $course->id), $options);
        $coursemodule = $DB->get_record('course_modules', array('id' => $scheduler->cmid));

        $this->schedulerid = $scheduler->id;
        $this->moduleid  = $coursemodule->id;

        $recs = $DB->get_records('scheduler_slots', array('schedulerid' => $scheduler->id), 'id DESC');
        $this->slotid = array_keys($recs)[0];
        $this->appointmentids = array_keys($DB->get_records('scheduler_appointment', array('slotid' => $this->slotid)));
    }

    private function create_student($courseid = 0) {
        if ($courseid == 0) {
            $courseid = $this->courseid;
        }
        $userid = $this->getDataGenerator()->create_user()->id;
        $this->getDataGenerator()->enrol_user($userid, $courseid);
        return $userid;
    }

    private function assert_record_count($table, $field, $value, $expect) {
        global $DB;

        $act = $DB->count_records($table, array($field => $value));
        $this->assertEquals($expect, $act, "Checking whether table $table has $expect records with $field equal to $value");
    }

    public function test_scheduler_instance() {
        global $DB;

        $dbdata = $DB->get_record('scheduler', array('id' => $this->schedulerid));

        $instance = scheduler_instance::load_by_coursemodule_id($this->moduleid);

        $this->assertEquals($dbdata->name, $instance->get_name());

    }

    public function test_load_slots() {
        global $DB;

        $instance = scheduler_instance::load_by_coursemodule_id($this->moduleid);

        

        $slotcount = $instance->get_slot_count();
        $this->assertEquals(6, $slotcount);

        $slots = $instance->get_all_slots(2, 3);
        $this->assertEquals(3, count($slots));

        $slots = $instance->get_slots_without_appointment();
        $this->assertEquals(1, count($slots));

        $allslots = $instance->get_all_slots();
        $this->assertEquals(6, count($allslots));

        $cnt = 0;
        foreach ($allslots as $slot) {
            $this->assertTrue($slot instanceof scheduler_slot);

            if ($cnt == 5) {
                $expectedapp = 2;
            } else if ($cnt == 4) {
                $expectedapp = 0;
            } else {
                $expectedapp = 1;
            }
            $this->assertEquals($expectedapp, $slot->get_appointment_count());

            $apps = $slot->get_appointments();
            $this->assertEquals($expectedapp, count($apps));

            foreach ($apps as $app) {
                $this->assertTrue($app instanceof scheduler_appointment);
            }
            $cnt++;
        }

    }

    public function test_add_slot() {

        $scheduler = scheduler_instance::load_by_coursemodule_id($this->moduleid);

        $newslot = $scheduler->create_slot();
        $newslot->teacherid = $this->getDataGenerator()->create_user()->id;
        $newslot->starttime = time() + MINSECS;
        $newslot->duration = 10;

        $allslots = $scheduler->get_slots();
        $this->assertEquals(7, count($allslots));

        $scheduler->save();

    }

    public function test_delete_scheduler() {

        $options = array();
        $options['slottimes'] = array();
        $options['slotstudents'] = array();
        for ($c = 0; $c < 10; $c++) {
            $options['slottimes'][$c] = time() + ($c + 1) * DAYSECS;
            $options['slotstudents'][$c] = array($this->getDataGenerator()->create_user()->id);
        }

        $delrec = $this->getDataGenerator()->create_module('scheduler', array('course' => $this->courseid), $options);
        $delid = $delrec->id;

        $delsched = scheduler_instance::load_by_id($delid);

        $this->assert_record_count('scheduler', 'id', $this->schedulerid, 1);
        $this->assert_record_count('scheduler_slots', 'schedulerid', $this->schedulerid, 6);
        $this->assert_record_count('scheduler_appointment', 'slotid', $this->slotid, 2);

        $this->assert_record_count('scheduler', 'id', $delid, 1);
        $this->assert_record_count('scheduler_slots', 'schedulerid', $delid, 10);

        $delsched->delete();

        $this->assert_record_count('scheduler', 'id', $this->schedulerid, 1);
        $this->assert_record_count('scheduler_slots', 'schedulerid', $this->schedulerid, 6);
        $this->assert_record_count('scheduler_appointment', 'slotid', $this->slotid, 2);

        $this->assert_record_count('scheduler', 'id', $delid, 0);
        $this->assert_record_count('scheduler_slots', 'schedulerid', $delid, 0);

    }

    private function assert_slot_times($expected, $actual, $options, $message) {
        $this->assertEquals(count($expected), count($actual), "Slot count - $message");
        $slottimes = array();
        foreach ($expected as $e) {
            $slottimes[] = $options['slottimes'][$e];
        }
        foreach ($actual as $a) {
            $this->assertTrue( in_array($a->starttime, $slottimes), "Slot at {$a->starttime} - $message");
        }
    }

    private function check_timed_slots($schedulerid, $studentid, $slotoptions,
                                       $expattended, $expupcoming, $expavailable, $expbookable) {

        $sched = scheduler_instance::load_by_id($schedulerid);

        $attended = $sched->get_attended_slots_for_student($studentid);
        $this->assert_slot_times($expattended, $attended, $slotoptions, 'Attended slots');

        $upcoming = $sched->get_upcoming_slots_for_student($studentid);
        $this->assert_slot_times($expupcoming, $upcoming, $slotoptions, 'Upcoming slots');

        $available = $sched->get_slots_available_to_student($studentid, false);
        $this->assert_slot_times($expavailable, $available, $slotoptions, 'Available slots (incl. booked)');

        $bookable = $sched->get_slots_available_to_student($studentid, true);
        $this->assert_slot_times($expbookable, $bookable, $slotoptions, 'Booked slots');

    }

    public function test_load_slot_timing() {

        global $DB;

        $currentstud = $this->getDataGenerator()->create_user()->id;
        $otherstud   = $this->getDataGenerator()->create_user()->id;

        $options = array();
        $options['slottimes'] = array();
        $options['slotstudents'] = array();
        $options['slotattended'] = array();

                for ($c = 0; $c <= 5; $c++) {
            $options['slottimes'][$c] = time() + $c * DAYSECS + 12 * HOURSECS;
            $options['slotstudents'][$c] = $currentstud;
            $options['slotattended'][$c] = false;
        }

                $options['slottimes'][6] = time() - 3 * DAYSECS;
        $options['slotstudents'][6] = $currentstud;
        $options['slotattended'][6] = false;

                $options['slottimes'][7] = time() - 4 * DAYSECS;
        $options['slotstudents'][7] = $currentstud;
        $options['slotattended'][7] = true;

                $options['slottimes'][8] = time() + 8 * HOURSECS;
        $options['slotstudents'][8] = $currentstud;
        $options['slotattended'][8] = true;

                $options['slottimes'][9] = time() + 10 * DAYSECS + 9 * HOURSECS;
        $options['slotstudents'][9] = $otherstud;
        $options['slotattended'][9] = false;
        $options['slotexclusivity'][9] = 1;

                for ($c = 10; $c <= 14; $c++) {
            $options['slottimes'][$c] = time() + ($c - 10) * DAYSECS + 10 * HOURSECS;
        }

        $schedrec = $this->getDataGenerator()->create_module('scheduler', array('course' => $this->courseid), $options);
        $schedid = $schedrec->id;

        $schedrec->guardtime = 0;
        $DB->update_record('scheduler', $schedrec);

        $this->check_timed_slots($schedid, $currentstud, $options,
                     array(7, 8),
                     array(0, 1, 2, 3, 4, 5, 6),
                     array(10, 11, 12, 13, 14),
                     array(10, 11, 12, 13, 14, 9) );

        $schedrec->guardtime = DAYSECS;
        $DB->update_record('scheduler', $schedrec);

        $this->check_timed_slots($schedid, $currentstud, $options,
                     array(7, 8),
                     array(0, 1, 2, 3, 4, 5, 6),
                     array(11, 12, 13, 14),
                     array(11, 12, 13, 14, 9) );

        $schedrec->guardtime = 4 * DAYSECS;
        $DB->update_record('scheduler', $schedrec);

        $this->check_timed_slots($schedid, $currentstud, $options,
                     array(7, 8),
                     array(0, 1, 2, 3, 4, 5, 6),
                     array(14),
                     array(14, 9) );

        $schedrec->guardtime = 20 * DAYSECS;
        $DB->update_record('scheduler', $schedrec);

        $this->check_timed_slots($schedid, $currentstud, $options,
                     array(7, 8),
                     array(0, 1, 2, 3, 4, 5, 6),
                     array(),
                     array() );

    }

    private function assert_bookable_appointments($expectedwithchangeables, $expectedwithoutchangeables,
                                                  $schedid, $studentid) {
        $scheduler = scheduler_instance::load_by_id($schedid);

        $actualwithchangeables = $scheduler->count_bookable_appointments($studentid, true);
        $this->assertEquals($expectedwithchangeables, $actualwithchangeables,
                        'Checking number of bookable appointments (including changeable bookings)');

        $actualwithoutchangeables = $scheduler->count_bookable_appointments($studentid, false);
        $this->assertEquals($expectedwithoutchangeables, $actualwithoutchangeables,
                        'Checking number of bookable appointments (excluding changeable bookings)');

        $studs = $scheduler->get_students_for_scheduling();
        if ($expectedwithoutchangeables != 0) {
            $this->assertTrue(is_array($studs), 'Checking that get_students_for_scheduling returns an array');
        }
        $actualnum = count($studs);
        $expectednum = ($expectedwithoutchangeables > 0) ? 3 : 2;
        $this->assertEquals($expectednum, $actualnum, 'Checking number of students available for scheduling');
    }

    
    private function create_data_for_bookable_appointments($schedulermode, $maxbookings, $guardtime, $studentid,
                                                           array $bookedslots, array $attendedslots) {

        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($studentid, $course->id);

        $options['slottimes'] = array();
        for ($c = 0; $c < 10; $c++) {
            $options['slottimes'][$c] = time() + $c * DAYSECS + HOURSECS;
            if (in_array($c, $bookedslots) || in_array($c, $attendedslots)) {
                $options['slotstudents'][$c] = $studentid;
            }
        }

        $schedrec = $this->getDataGenerator()->create_module('scheduler', array('course' => $course->id), $options);

        $scheduler = scheduler_instance::load_by_id($schedrec->id);

        $scheduler->schedulermode = $schedulermode;
        $scheduler->maxbookings = $maxbookings;
        $scheduler->guardtime = $guardtime;
        $scheduler->save();

        $slotrecs = $DB->get_records('scheduler_slots', array('schedulerid' => $scheduler->id), 'starttime ASC');
        $slotrecs = array_values($slotrecs);

        foreach ($attendedslots as $id) {
            $DB->set_field('scheduler_appointment', 'attended', 1, array('slotid' => $slotrecs[$id]->id));
        }

        for ($i = 0; $i < 2; $i++) {
            $dummystud = $this->create_student($course->id);
        }

        return $scheduler->id;
    }

    public function test_bookable_appointments() {

        $studid = $this->create_student();

        $sid = $this->create_data_for_bookable_appointments('oneonly', 1, 0, $studid, array(), array());
        $this->assert_bookable_appointments(1, 1, $sid, $studid);

        $sid = $this->create_data_for_bookable_appointments('oneonly', 1, 0, $studid, array(5), array());
        $this->assert_bookable_appointments(1, 0, $sid, $studid);

        $sid = $this->create_data_for_bookable_appointments('oneonly', 1, 0, $studid, array(5, 6, 7), array());
        $this->assert_bookable_appointments(1, 0, $sid, $studid);

        $sid = $this->create_data_for_bookable_appointments('oneonly', 1, 0, $studid, array(5, 6), array(8));
        $this->assert_bookable_appointments(0, 0, $sid, $studid);

                $sid = $this->create_data_for_bookable_appointments('oneonly', 1, 5 * DAYSECS, $studid, array(1), array());
        $this->assert_bookable_appointments(0, 0, $sid, $studid);

                $sid = $this->create_data_for_bookable_appointments('oneonly', 5, 0, $studid, array(2, 3), array(4));
        $this->assert_bookable_appointments(4, 2, $sid, $studid);

                $sid = $this->create_data_for_bookable_appointments('oneonly', 5, 5 * DAYSECS, $studid, array(2, 7, 8), array());
        $this->assert_bookable_appointments(4, 2, $sid, $studid);

                $sid = $this->create_data_for_bookable_appointments('oneonly', 5, 5 * DAYSECS, $studid, array(2, 7), array(1, 8));
        $this->assert_bookable_appointments(2, 1, $sid, $studid);

                $sid = $this->create_data_for_bookable_appointments('onetime', 1, 5 * DAYSECS, $studid, array(), array(3, 7));
        $this->assert_bookable_appointments(1, 1, $sid, $studid);

                $sid = $this->create_data_for_bookable_appointments('onetime', 1, 5 * DAYSECS, $studid, array(7), array());
        $this->assert_bookable_appointments(1, 0, $sid, $studid);

                $sid = $this->create_data_for_bookable_appointments('onetime', 1, 5 * DAYSECS, $studid, array(2), array());
        $this->assert_bookable_appointments(0, 0, $sid, $studid);

    }

}
