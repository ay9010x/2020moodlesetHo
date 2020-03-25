<?php



namespace mod_scheduler\event;

defined('MOODLE_INTERNAL') || die();


abstract class appointment_base extends \core\event\base {

    protected $appointment;

    protected static function base_data(\scheduler_appointment $appointment) {
        return array(
            'context' => $appointment->get_parent()->get_context(),
            'objectid' => $appointment->id
        );
    }

    protected function set_appointment(\scheduler_appointment $appointment) {
        $this->add_record_snapshot('scheduler_appointment', $appointment->data);
        $this->add_record_snapshot('scheduler_slots', $appointment->get_parent()->data);
        $this->add_record_snapshot('scheduler', $appointment->get_parent()->get_parent()->data);
        $this->appointment = $appointment;
        $this->data['objecttable'] = 'scheduler_appointments';
    }

    
    public function get_appointment() {
        if ($this->is_restored()) {
            throw new \coding_exception('get_appointment() is intended for event observers only');
        }
        return $this->appointment;
    }

    
    public function get_url() {
        return new \moodle_url('/mod/scheduler/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
