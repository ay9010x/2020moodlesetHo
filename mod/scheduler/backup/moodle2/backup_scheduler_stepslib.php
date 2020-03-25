<?php







class backup_scheduler_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $scheduler = new backup_nested_element('scheduler', array('id'), array(
            'name', 'intro', 'introformat', 'schedulermode', 'maxbookings',
            'guardtime', 'defaultslotduration', 'allownotifications', 'staffrolename',
            'scale', 'gradingstrategy', 'bookingrouping', 'usenotes', 'timemodified'));

        $slots = new backup_nested_element('slots');

        $slot = new backup_nested_element('slot', array('id'), array(
            'starttime', 'duration', 'teacherid', 'appointmentlocation',
            'timemodified', 'notes', 'notesformat', 'exclusivity',
            'emaildate', 'hideuntil'));

        $appointments = new backup_nested_element('appointments');

        $appointment = new backup_nested_element('appointment', array('id'), array(
            'studentid', 'attended', 'grade',
            'appointmentnote', 'appointmentnoteformat', 'teachernote', 'teachernoteformat',
            'timecreated', 'timemodified'));

        
        $scheduler->add_child($slots);
        $slots->add_child($slot);

        $slot->add_child($appointments);
        $appointments->add_child($appointment);

                $scheduler->set_source_table('scheduler', array('id' => backup::VAR_ACTIVITYID));
        $scheduler->annotate_ids('grouping', 'bookingrouping');

                if ($userinfo) {
            $slot->set_source_table('scheduler_slots', array('schedulerid' => backup::VAR_PARENTID));
            $appointment->set_source_table('scheduler_appointment', array('slotid' => backup::VAR_PARENTID));
        }

                $scheduler->annotate_ids('scale', 'scale');

        if ($userinfo) {
            $slot->annotate_ids('user', 'teacherid');
            $appointment->annotate_ids('user', 'studentid');
        }

                $scheduler->annotate_files('mod_scheduler', 'intro', null);         $slot->annotate_files('mod_scheduler', 'slotnote', 'id');         $appointment->annotate_files('mod_scheduler', 'appointmentnote', 'id');         $appointment->annotate_files('mod_scheduler', 'teachernote', 'id'); 
                return $this->prepare_activity_structure($scheduler);
    }
}
