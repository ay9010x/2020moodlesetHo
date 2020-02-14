<?php







class restore_scheduler_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $scheduler = new restore_path_element('scheduler', '/activity/scheduler');
        $paths[] = $scheduler;

        if ($userinfo) {
            $slot = new restore_path_element('scheduler_slot', '/activity/scheduler/slots/slot');
            $paths[] = $slot;

            $appointment = new restore_path_element('scheduler_appointment',
                                                    '/activity/scheduler/slots/slot/appointments/appointment');
            $paths[] = $appointment;
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_scheduler($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        if ($data->scale < 0) {             $data->scale = -($this->get_mappingid('scale', abs($data->scale)));
        }

        if (is_null($data->gradingstrategy)) {             $data->gradingstrategy = 0;
        }

        if ($data->bookingrouping > 0) {
            $data->bookingrouping = $this->get_mappingid('grouping', $data->bookingrouping);
        }

                $newitemid = $DB->insert_record('scheduler', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_scheduler_slot($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->schedulerid = $this->get_new_parentid('scheduler');
        $data->starttime = $this->apply_date_offset($data->starttime);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->emaildate = $this->apply_date_offset($data->emaildate);
        $data->hideuntil = $this->apply_date_offset($data->hideuntil);

        $data->teacherid = $this->get_mappingid('user', $data->teacherid);

        $newitemid = $DB->insert_record('scheduler_slots', $data);
        $this->set_mapping('scheduler_slot', $oldid, $newitemid, true);
    }

    protected function process_scheduler_appointment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->slotid = $this->get_new_parentid('scheduler_slot');

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $data->studentid = $this->get_mappingid('user', $data->studentid);

        $newitemid = $DB->insert_record('scheduler_appointment', $data);
        $this->set_mapping('scheduler_appointment', $oldid, $newitemid, true);
    }

    protected function after_execute() {
                $this->add_related_files('mod_scheduler', 'intro', null);
        $this->add_related_files('mod_scheduler', 'slotnote', 'scheduler_slot');
        $this->add_related_files('mod_scheduler', 'appointmentnote', 'scheduler_appointment');
        $this->add_related_files('mod_scheduler', 'teachernote', 'scheduler_appointment');
    }
}
