<?php


require_once($CFG->dirroot.'/grade/export/lib.php');
require_once($CFG->libdir.'/filelib.php');

class grade_export_xml extends grade_export {

    public $plugin = 'xml';
    public $updatedgradesonly = false; 
    
    public function print_grades($feedback = false) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $export_tracking = $this->track_exports();

        $strgrades = get_string('grades');

                $shortname = format_string($this->course->shortname, true, array('context' => context_course::instance($this->course->id)));
        $downloadfilename = clean_filename("$shortname $strgrades.xml");

        make_temp_directory('gradeexport');
        $tempfilename = $CFG->tempdir .'/gradeexport/'. md5(sesskey().microtime().$downloadfilename);
        if (!$handle = fopen($tempfilename, 'w+b')) {
            print_error('cannotcreatetempdir');
        }

                fwrite($handle,  '<results batch="xml_export_'.time().'">'."\n");

        $export_buffer = array();

        $geub = new grade_export_update_buffer();
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->init();
        while ($userdata = $gui->next_user()) {
            $user = $userdata->user;

            if (empty($user->idnumber)) {
                                continue;
            }

                        foreach ($userdata->grades as $itemid => $grade) {
                $grade_item = $this->grade_items[$itemid];
                $grade->grade_item =& $grade_item;

                                if ($export_tracking) {
                    $status = $geub->track($grade);
                    if ($this->updatedgradesonly && ($status == 'nochange' || $status == 'unknown')) {
                        continue;
                    }
                }

                fwrite($handle,  "\t<result>\n");

                if ($export_tracking) {
                    fwrite($handle,  "\t\t<state>$status</state>\n");
                }

                                fwrite($handle,  "\t\t<assignment>{$grade_item->idnumber}</assignment>\n");
                                fwrite($handle,  "\t\t<student>{$user->idnumber}</student>\n");
                                if (is_array($this->displaytype)) {
                                        foreach ($this->displaytype as $gradedisplayconst) {
                        $gradestr = $this->format_grade($grade, $gradedisplayconst);
                        fwrite($handle,  "\t\t<score>$gradestr</score>\n");
                    }
                } else {
                                        $gradestr = $this->format_grade($grade, $this->displaytype);
                    fwrite($handle,  "\t\t<score>$gradestr</score>\n");
                }

                if ($this->export_feedback) {
                    $feedbackstr = $this->format_feedback($userdata->feedbacks[$itemid]);
                    fwrite($handle,  "\t\t<feedback>$feedbackstr</feedback>\n");
                }
                fwrite($handle,  "\t</result>\n");
            }
        }
        fwrite($handle,  "</results>");
        fclose($handle);
        $gui->close();
        $geub->close();

        if (defined('BEHAT_SITE_RUNNING')) {
                        include($tempfilename);
        } else {
            @header("Content-type: text/xml; charset=UTF-8");
            send_temp_file($tempfilename, $downloadfilename, false);
        }
    }
}


