<?php



namespace mod_scheduler\task;

require_once(dirname(__FILE__).'/../../model/scheduler_instance.php');
require_once(dirname(__FILE__).'/../../model/scheduler_slot.php');
require_once(dirname(__FILE__).'/../../model/scheduler_appointment.php');
require_once(dirname(__FILE__).'/../../mailtemplatelib.php');

class send_reminders extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('sendreminders', 'mod_scheduler');
    }

    public function execute() {

        global $DB;

        $date = make_timestamp(date('Y'), date('m'), date('d'), date('H'), date('i'));

                $select = 'emaildate > 0 AND emaildate <= ? AND starttime > ?';
        $slots = $DB->get_records_select('scheduler_slots', $select, array($date, $date), 'starttime');

        foreach ($slots as $slot) {
                        $teacher = $DB->get_record('user', array('id' => $slot->teacherid));

                        $scheduler = \scheduler_instance::load_by_id($slot->schedulerid);
            $slotm = $scheduler->get_slot($slot->id);
            $course = $scheduler->get_courserec();

                        $slot->emaildate = -1;
            $DB->update_record('scheduler_slots', $slot);

                        foreach ($slotm->get_appointments() as $appointment) {
                $student = $DB->get_record('user', array('id' => $appointment->studentid));
                cron_setup_user($student, $course);
                \scheduler_messenger::send_slot_notification($slotm,
                        'reminder', 'reminder', $teacher, $student, $teacher, $student, $course);
            }
        }
        cron_setup_user();
    }

}