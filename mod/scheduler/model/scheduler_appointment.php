<?php




defined('MOODLE_INTERNAL') || die();

require_once('modellib.php');



class scheduler_appointment extends mvc_child_record_model {


    protected function get_table() {
        return 'scheduler_appointment';
    }

    public function __construct(scheduler_slot $slot) {
        parent::__construct();
        $this->data = new stdClass();
        $this->set_parent($slot);
        $this->data->slotid = $slot->get_id();
        $this->data->attended = 0;
        $this->data->appointmentnoteformat = FORMAT_HTML;
        $this->data->teachernoteformat = FORMAT_HTML;
    }

    public function save() {
        $this->data->slotid = $this->get_parent()->get_id();
        parent::save();
        $scheddata = $this->get_scheduler()->get_data();
        scheduler_update_grades($scheddata, $this->studentid);
    }

    public function delete() {
        $studid = $this->studentid;
        parent::delete();
        $scheddata = $this->get_scheduler()->get_data();
        scheduler_update_grades($scheddata, $studid);
    }

    public function get_slot() {
        return $this->get_parent();
    }

    public function get_scheduler() {
        return $this->get_parent()->get_parent();
    }

    
    public function get_student() {
        global $DB;
        if ($this->data->studentid) {
            return $DB->get_record('user', array('id' => $this->data->studentid), '*', MUST_EXIST);
        } else {
            return null;
        }
    }

    
    public function is_attended() {
        return (boolean) $this->data->attended;
    }

}

class scheduler_appointment_factory extends mvc_child_model_factory {
    public function create_child(mvc_record_model $parent) {
        return new scheduler_appointment($parent);
    }
}
