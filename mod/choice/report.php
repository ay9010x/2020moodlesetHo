<?php

    require_once("../../config.php");
    require_once("lib.php");

    $id         = required_param('id', PARAM_INT);       $format     = optional_param('format', CHOICE_PUBLISH_NAMES, PARAM_INT);
    $download   = optional_param('download', '', PARAM_ALPHA);
    $action     = optional_param('action', '', PARAM_ALPHA);
    $attemptids = optional_param_array('attemptid', array(), PARAM_INT); 
    $url = new moodle_url('/mod/choice/report.php', array('id'=>$id));
    if ($format !== CHOICE_PUBLISH_NAMES) {
        $url->param('format', $format);
    }
    if ($download !== '') {
        $url->param('download', $download);
    }
    if ($action !== '') {
        $url->param('action', $action);
    }
    $PAGE->set_url($url);

    if (! $cm = get_coursemodule_from_id('choice', $id)) {
        print_error("invalidcoursemodule");
    }

    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error("coursemisconf");
    }

    require_login($course, false, $cm);

    $context = context_module::instance($cm->id);

    require_capability('mod/choice:readresponses', $context);

    if (!$choice = choice_get_choice($cm->instance)) {
        print_error('invalidcoursemodule');
    }

    $strchoice = get_string("modulename", "choice");
    $strchoices = get_string("modulenameplural", "choice");
    $strresponses = get_string("responses", "choice");

    $eventdata = array();
    $eventdata['objectid'] = $choice->id;
    $eventdata['context'] = $context;
    $eventdata['courseid'] = $course->id;
    $eventdata['other']['content'] = 'choicereportcontentviewed';

    $event = \mod_choice\event\report_viewed::create($eventdata);
    $event->trigger();

    if (data_submitted() && $action == 'delete' && has_capability('mod/choice:deleteresponses',$context) && confirm_sesskey()) {
        choice_delete_responses($attemptids, $choice, $cm, $course);         redirect("report.php?id=$cm->id");
    }

    if (!$download) {
        $PAGE->navbar->add($strresponses);
        $PAGE->set_title(format_string($choice->name).": $strresponses");
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($choice->name, 2, null);
                $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode) {
            groups_get_activity_group($cm, true);
            groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/choice/report.php?id='.$id);
        }
    } else {
        $groupmode = groups_get_activity_groupmode($cm);

                $eventdata = array();
        $eventdata['context'] = $context;
        $eventdata['courseid'] = $course->id;
        $eventdata['other']['content'] = 'choicereportcontentviewed';
        $eventdata['other']['format'] = $download;
        $eventdata['other']['choiceid'] = $choice->id;
        $event = \mod_choice\event\report_downloaded::create($eventdata);
        $event->trigger();

    }

        $onlyactive = $choice->includeinactive ? false : true;

    $users = choice_get_response_data($choice, $cm, $groupmode, $onlyactive);

    if ($download == "ods" && has_capability('mod/choice:downloadresponses', $context)) {
        require_once("$CFG->libdir/odslib.class.php");

            $filename = clean_filename("$course->shortname ".strip_tags(format_string($choice->name,true))).'.ods';
            $workbook = new MoodleODSWorkbook("-");
            $workbook->send($filename);
            $myxls = $workbook->add_worksheet($strresponses);

            $myxls->write_string(0,0,get_string("lastname"));
        $myxls->write_string(0,1,get_string("firstname"));
        $myxls->write_string(0,2,get_string("idnumber"));
        $myxls->write_string(0,3,get_string("group"));
        $myxls->write_string(0,4,get_string("choice","choice"));

            $i=0;
        $row=1;
        if ($users) {
            foreach ($users as $option => $userid) {
                $option_text = choice_get_option_text($choice, $option);
                foreach($userid as $user) {
                    $myxls->write_string($row,0,$user->lastname);
                    $myxls->write_string($row,1,$user->firstname);
                    $studentid=(!empty($user->idnumber) ? $user->idnumber : " ");
                    $myxls->write_string($row,2,$studentid);
                    $ug2 = '';
                    if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                        foreach ($usergrps as $ug) {
                            $ug2 = $ug2. $ug->name;
                        }
                    }
                    $myxls->write_string($row,3,$ug2);

                    if (isset($option_text)) {
                        $myxls->write_string($row,4,format_string($option_text,true));
                    }
                    $row++;
                    $pos=4;
                }
            }
        }
                $workbook->close();

        exit;
    }

        if ($download == "xls" && has_capability('mod/choice:downloadresponses', $context)) {
        require_once("$CFG->libdir/excellib.class.php");

            $filename = clean_filename("$course->shortname ".strip_tags(format_string($choice->name,true))).'.xls';
            $workbook = new MoodleExcelWorkbook("-");
            $workbook->send($filename);
            $myxls = $workbook->add_worksheet($strresponses);

            $myxls->write_string(0,0,get_string("lastname"));
        $myxls->write_string(0,1,get_string("firstname"));
        $myxls->write_string(0,2,get_string("idnumber"));
        $myxls->write_string(0,3,get_string("group"));
        $myxls->write_string(0,4,get_string("choice","choice"));


            $i=0;
        $row=1;
        if ($users) {
            foreach ($users as $option => $userid) {
                $option_text = choice_get_option_text($choice, $option);
                foreach($userid as $user) {
                    $myxls->write_string($row,0,$user->lastname);
                    $myxls->write_string($row,1,$user->firstname);
                    $studentid=(!empty($user->idnumber) ? $user->idnumber : " ");
                    $myxls->write_string($row,2,$studentid);
                    $ug2 = '';
                    if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                        foreach ($usergrps as $ug) {
                            $ug2 = $ug2. $ug->name;
                        }
                    }
                    $myxls->write_string($row,3,$ug2);
                    if (isset($option_text)) {
                        $myxls->write_string($row,4,format_string($option_text,true));
                    }
                    $row++;
                }
            }
            $pos=4;
        }
                $workbook->close();
        exit;
    }

        if ($download == "txt" && has_capability('mod/choice:downloadresponses', $context)) {
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($choice->name,true))).'.txt';

        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

        
        echo get_string("lastname")."\t".get_string("firstname") . "\t". get_string("idnumber") . "\t";
        echo get_string("group"). "\t";
        echo get_string("choice","choice"). "\n";

                $i=0;
        if ($users) {
            foreach ($users as $option => $userid) {
                $option_text = choice_get_option_text($choice, $option);
                foreach($userid as $user) {
                    echo $user->lastname;
                    echo "\t".$user->firstname;
                    $studentid = " ";
                    if (!empty($user->idnumber)) {
                        $studentid = $user->idnumber;
                    }
                    echo "\t". $studentid."\t";
                    $ug2 = '';
                    if ($usergrps = groups_get_all_groups($course->id, $user->id)) {
                        foreach ($usergrps as $ug) {
                            $ug2 = $ug2. $ug->name;
                        }
                    }
                    echo $ug2. "\t";
                    if (isset($option_text)) {
                        echo format_string($option_text,true);
                    }
                    echo "\n";
                }
            }
        }
        exit;
    }
        if (!empty($choice->showunanswered)) {
        $choice->option[0] = get_string('notanswered', 'choice');
        $choice->maxanswers[0] = 0;
    }

    $results = prepare_choice_show_results($choice, $course, $cm, $users);
    $renderer = $PAGE->get_renderer('mod_choice');
    echo $renderer->display_result($results, has_capability('mod/choice:readresponses', $context));

       if (!empty($users) && has_capability('mod/choice:downloadresponses',$context)) {
        $downloadoptions = array();
        $options = array();
        $options["id"] = "$cm->id";
        $options["download"] = "ods";
        $button =  $OUTPUT->single_button(new moodle_url("report.php", $options), get_string("downloadods"));
        $downloadoptions[] = html_writer::tag('li', $button, array('class'=>'reportoption'));

        $options["download"] = "xls";
        $button = $OUTPUT->single_button(new moodle_url("report.php", $options), get_string("downloadexcel"));
        $downloadoptions[] = html_writer::tag('li', $button, array('class'=>'reportoption'));

        $options["download"] = "txt";
        $button = $OUTPUT->single_button(new moodle_url("report.php", $options), get_string("downloadtext"));
        $downloadoptions[] = html_writer::tag('li', $button, array('class'=>'reportoption'));

        $downloadlist = html_writer::tag('ul', implode('', $downloadoptions));
        $downloadlist .= html_writer::tag('div', '', array('class'=>'clearfloat'));
        echo html_writer::tag('div',$downloadlist, array('class'=>'downloadreport'));
    }
    echo $OUTPUT->footer();

