<?php


require_once($CFG->dirroot.'/grade/export/lib.php');

class grade_export_ods extends grade_export {

    public $plugin = 'ods';

    
    public function __construct($course, $groupid, $formdata) {
        parent::__construct($course, $groupid, $formdata);

                $this->usercustomfields = true;
    }

    
    function print_grades() {
        global $CFG;
        require_once($CFG->dirroot.'/lib/odslib.class.php');

        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));

                $downloadfilename = clean_filename("$shortname $strgrades.ods");
                $workbook = new MoodleODSWorkbook("-");
                $workbook->send($downloadfilename);
                $myxls = $workbook->add_worksheet($strgrades);


                $profilefields = grade_helper::get_user_profile_fields($this->course->id, $this->usercustomfields);
        foreach ($profilefields as $id => $field) {
            $myxls->write_string(0, $id, $field->fullname);
        }
        $pos = count($profilefields);
        if (!$this->onlyactive) {
            $myxls->write_string(0, $pos++, get_string("suspended"));
        }
        foreach ($this->columns as $grade_item) {
            foreach ($this->displaytype as $gradedisplayname => $gradedisplayconst) {
                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, false, $gradedisplayname));
            }

                        if ($this->export_feedback) {
                $myxls->write_string(0, $pos++, $this->format_column_name($grade_item, true));
            }
        }
                $myxls->write_string(0, $pos++, get_string('timeexported', 'gradeexport_ods'));

                $i = 0;
        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->allow_user_custom_fields($this->usercustomfields);
        $gui->init();
        while ($userdata = $gui->next_user()) {
            $i++;
            $user = $userdata->user;

            foreach($profilefields as $id => $field) {
                $fieldvalue = grade_helper::get_user_field_value($user, $field);
                $myxls->write_string($i, $id, $fieldvalue);
            }
            $j = count($profilefields);

            if (!$this->onlyactive) {
                $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                $myxls->write_string($i, $j++, $issuspended);
            }
            foreach ($userdata->grades as $itemid => $grade) {
                if ($export_tracking) {
                    $status = $geub->track($grade);
                }

                foreach ($this->displaytype as $gradedisplayconst) {
                    $gradestr = $this->format_grade($grade, $gradedisplayconst);
                    if (is_numeric($gradestr)) {
                        $myxls->write_number($i, $j++, $gradestr);
                    } else {
                        $myxls->write_string($i, $j++, $gradestr);
                    }
                }

                                if ($this->export_feedback) {
                    $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid]));
                }
            }
                        $myxls->write_string($i, $j++, time());
        }
        $gui->close();
        $geub->close();

                $workbook->close();

        exit;
    }
}


