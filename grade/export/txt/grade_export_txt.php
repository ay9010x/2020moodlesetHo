<?php


require_once($CFG->dirroot.'/grade/export/lib.php');
require_once($CFG->libdir . '/csvlib.class.php');

class grade_export_txt extends grade_export {

    public $plugin = 'txt';

    public $separator; 
    
    public function __construct($course, $groupid, $formdata) {
        parent::__construct($course, $groupid, $formdata);
        $this->separator = $formdata->separator;

                $this->usercustomfields = true;
    }

    public function get_export_params() {
        $params = parent::get_export_params();
        $params['separator'] = $this->separator;
        return $params;
    }

    public function print_grades() {
        global $CFG;

        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');
        $profilefields = grade_helper::get_user_profile_fields($this->course->id, $this->usercustomfields);

        $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));
        $downloadfilename = clean_filename("$shortname $strgrades");
        $csvexport = new csv_export_writer($this->separator);
        $csvexport->set_filename($downloadfilename);

                $exporttitle = array();
        foreach ($profilefields as $field) {
            $exporttitle[] = $field->fullname;
        }

        if (!$this->onlyactive) {
            $exporttitle[] = get_string("suspended");
        }

                foreach ($this->columns as $grade_item) {
            foreach ($this->displaytype as $gradedisplayname => $gradedisplayconst) {
                $exporttitle[] = $this->format_column_name($grade_item, false, $gradedisplayname);
            }
            if ($this->export_feedback) {
                $exporttitle[] = $this->format_column_name($grade_item, true);
            }
        }
                $exporttitle[] = get_string('timeexported', 'gradeexport_txt');
        $csvexport->add_data($exporttitle);

                $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->allow_user_custom_fields($this->usercustomfields);
        $gui->init();
        while ($userdata = $gui->next_user()) {

            $exportdata = array();
            $user = $userdata->user;

            foreach ($profilefields as $field) {
                $fieldvalue = grade_helper::get_user_field_value($user, $field);
                $exportdata[] = $fieldvalue;
            }
            if (!$this->onlyactive) {
                $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                $exportdata[] = $issuspended;
            }
            foreach ($userdata->grades as $itemid => $grade) {
                if ($export_tracking) {
                    $status = $geub->track($grade);
                }

                foreach ($this->displaytype as $gradedisplayconst) {
                    $exportdata[] = $this->format_grade($grade, $gradedisplayconst);
                }

                if ($this->export_feedback) {
                    $exportdata[] = $this->format_feedback($userdata->feedbacks[$itemid]);
                }
            }
                        $exportdata[] = time();
            $csvexport->add_data($exportdata);
        }
        $gui->close();
        $geub->close();
        $csvexport->download_file();
        exit;
    }
}


