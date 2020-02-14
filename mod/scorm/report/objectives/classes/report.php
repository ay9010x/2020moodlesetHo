<?php


namespace scormreport_objectives;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/scorm/report/objectives/responsessettings_form.php');


class report extends \mod_scorm\report {
    
    public function display($scorm, $cm, $course, $download) {
        global $CFG, $DB, $OUTPUT, $PAGE;

        $contextmodule = \context_module::instance($cm->id);
        $action = optional_param('action', '', PARAM_ALPHA);
        $attemptids = optional_param_array('attemptid', array(), PARAM_RAW);
        $attemptsmode = optional_param('attemptsmode', SCORM_REPORT_ATTEMPTS_ALL_STUDENTS, PARAM_INT);
        $PAGE->set_url(new \moodle_url($PAGE->url, array('attemptsmode' => $attemptsmode)));

        if ($action == 'delete' && has_capability('mod/scorm:deleteresponses', $contextmodule) && confirm_sesskey()) {
            if (scorm_delete_responses($attemptids, $scorm)) {                 echo $OUTPUT->notification(get_string('scormresponsedeleted', 'scorm'), 'notifysuccess');
            }
        }
                $currentgroup = groups_get_activity_group($cm, true);

                $mform = new \mod_scorm_report_objectives_settings($PAGE->url, compact('currentgroup'));
        if ($fromform = $mform->get_data()) {
            $pagesize = $fromform->pagesize;
            $showobjectivescore = $fromform->objectivescore;
            set_user_preference('scorm_report_pagesize', $pagesize);
            set_user_preference('scorm_report_objectives_score', $showobjectivescore);
        } else {
            $pagesize = get_user_preferences('scorm_report_pagesize', 0);
            $showobjectivescore = get_user_preferences('scorm_report_objectives_score', 0);
        }
        if ($pagesize < 1) {
            $pagesize = SCORM_REPORT_DEFAULT_PAGE_SIZE;
        }

                $displayoptions = array();
        $displayoptions['attemptsmode'] = $attemptsmode;
        $displayoptions['objectivescore'] = $showobjectivescore;

        $mform->set_data($displayoptions + array('pagesize' => $pagesize));
        if ($groupmode = groups_get_activity_groupmode($cm)) {               if (!$download) {
                groups_print_activity_menu($cm, new \moodle_url($PAGE->url, $displayoptions));
            }
        }
        $formattextoptions = array('context' => \context_course::instance($course->id));

                        $candelete = has_capability('mod/scorm:deleteresponses', $contextmodule)
                && ($attemptsmode != SCORM_REPORT_ATTEMPTS_STUDENTS_WITH_NO);
                $nostudents = false;

        if (empty($currentgroup)) {
                        if (!$students = get_users_by_capability($contextmodule, 'mod/scorm:savetrack', 'u.id', '', '', '', '', '', false)) {
                echo $OUTPUT->notification(get_string('nostudentsyet'));
                $nostudents = true;
                $allowedlist = '';
            } else {
                $allowedlist = array_keys($students);
            }
            unset($students);
        } else {
                        $groupstudents = get_users_by_capability($contextmodule, 'mod/scorm:savetrack',
                                                     'u.id', '', '', '', $currentgroup, '', false);
            if (!$groupstudents) {
                echo $OUTPUT->notification(get_string('nostudentsingroup'));
                $nostudents = true;
                $groupstudents = array();
            }
            $allowedlist = array_keys($groupstudents);
            unset($groupstudents);
        }
        if ( !$nostudents ) {
                        $coursecontext = \context_course::instance($course->id);
            if ($download) {
                $filename = clean_filename("$course->shortname ".format_string($scorm->name, true, $formattextoptions));
            }

                        $columns = array();
            $headers = array();
            if (!$download && $candelete) {
                $columns[] = 'checkbox';
                $headers[] = null;
            }
            if (!$download && $CFG->grade_report_showuserimage) {
                $columns[] = 'picture';
                $headers[] = '';
            }
            $columns[] = 'fullname';
            $headers[] = get_string('name');

            $extrafields = get_extra_user_fields($coursecontext);
            foreach ($extrafields as $field) {
                $columns[] = $field;
                $headers[] = get_user_field_name($field);
            }
            $columns[] = 'attempt';
            $headers[] = get_string('attempt', 'scorm');
            $columns[] = 'start';
            $headers[] = get_string('started', 'scorm');
            $columns[] = 'finish';
            $headers[] = get_string('last', 'scorm');
            $columns[] = 'score';
            $headers[] = get_string('score', 'scorm');
            $scoes = $DB->get_records('scorm_scoes', array("scorm" => $scorm->id), 'sortorder, id');
            foreach ($scoes as $sco) {
                if ($sco->launch != '') {
                    $columns[] = 'scograde'.$sco->id;
                    $headers[] = format_string($sco->title, '', $formattextoptions);
                }
            }

            $params = array();
            list($usql, $params) = $DB->get_in_or_equal($allowedlist, SQL_PARAMS_NAMED);
                        $select = 'SELECT DISTINCT '.$DB->sql_concat('u.id', '\'#\'', 'COALESCE(st.attempt, 0)').' AS uniqueid, ';
            $select .= 'st.scormid AS scormid, st.attempt AS attempt, ' .
                    \user_picture::fields('u', array('idnumber'), 'userid') .
                    get_extra_user_fields_sql($coursecontext, 'u', '', array('email', 'idnumber')) . ' ';

                        $from = 'FROM {user} u ';
            $from .= 'LEFT JOIN {scorm_scoes_track} st ON st.userid = u.id AND st.scormid = '.$scorm->id;
            switch ($attemptsmode) {
                case SCORM_REPORT_ATTEMPTS_STUDENTS_WITH:
                                        $where = ' WHERE u.id ' .$usql. ' AND st.userid IS NOT NULL';
                    break;
                case SCORM_REPORT_ATTEMPTS_STUDENTS_WITH_NO:
                                        $where = ' WHERE u.id ' .$usql. ' AND st.userid IS NULL';
                    break;
                case SCORM_REPORT_ATTEMPTS_ALL_STUDENTS:
                                        $where = ' WHERE u.id ' .$usql. ' AND (st.userid IS NOT NULL OR st.userid IS NULL)';
                    break;
            }

            $countsql = 'SELECT COUNT(DISTINCT('.$DB->sql_concat('u.id', '\'#\'', 'COALESCE(st.attempt, 0)').')) AS nbresults, ';
            $countsql .= 'COUNT(DISTINCT('.$DB->sql_concat('u.id', '\'#\'', 'st.attempt').')) AS nbattempts, ';
            $countsql .= 'COUNT(DISTINCT(u.id)) AS nbusers ';
            $countsql .= $from.$where;

            $nbmaincolumns = count($columns); 
            $objectives = get_scorm_objectives($scorm->id);
            $nosort = array();
            foreach ($objectives as $scoid => $sco) {
                foreach ($sco as $id => $objectivename) {
                    $colid = $scoid.'objectivestatus' . $id;
                    $columns[] = $colid;
                    $nosort[] = $colid;

                    if (!$displayoptions['objectivescore']) {
                                                $headers[] = $objectivename;
                    } else {
                                                $headers[] = $objectivename. ' '. get_string('status', 'scormreport_objectives');

                                                $colid = $scoid.'objectivescore' . $id;
                        $columns[] = $colid;
                        $nosort[] = $colid;
                        $headers[] = $objectivename. ' '. get_string('score', 'scormreport_objectives');
                    }
                }
            }

            $emptycell = '';             if (!$download) {
                $emptycell = '&nbsp;';
                $table = new \flexible_table('mod-scorm-report');

                $table->define_columns($columns);
                $table->define_headers($headers);
                $table->define_baseurl($PAGE->url);

                $table->sortable(true);
                $table->collapsible(true);

                                $table->column_suppress('picture');
                $table->column_suppress('fullname');
                foreach ($extrafields as $field) {
                    $table->column_suppress($field);
                }
                foreach ($nosort as $field) {
                    $table->no_sorting($field);
                }

                $table->no_sorting('start');
                $table->no_sorting('finish');
                $table->no_sorting('score');
                $table->no_sorting('checkbox');
                $table->no_sorting('picture');

                foreach ($scoes as $sco) {
                    if ($sco->launch != '') {
                        $table->no_sorting('scograde'.$sco->id);
                    }
                }

                $table->column_class('picture', 'picture');
                $table->column_class('fullname', 'bold');
                $table->column_class('score', 'bold');

                $table->set_attribute('cellspacing', '0');
                $table->set_attribute('id', 'attempts');
                $table->set_attribute('class', 'generaltable generalbox');

                                $table->setup();
            } else if ($download == 'ODS') {
                require_once("$CFG->libdir/odslib.class.php");

                $filename .= ".ods";
                                $workbook = new \MoodleODSWorkbook("-");
                                $workbook->send($filename);
                                $sheettitle = get_string('report', 'scorm');
                $myxls = $workbook->add_worksheet($sheettitle);
                                $format = $workbook->add_format();
                $format->set_bold(0);
                $formatbc = $workbook->add_format();
                $formatbc->set_bold(1);
                $formatbc->set_align('center');
                $formatb = $workbook->add_format();
                $formatb->set_bold(1);
                $formaty = $workbook->add_format();
                $formaty->set_bg_color('yellow');
                $formatc = $workbook->add_format();
                $formatc->set_align('center');
                $formatr = $workbook->add_format();
                $formatr->set_bold(1);
                $formatr->set_color('red');
                $formatr->set_align('center');
                $formatg = $workbook->add_format();
                $formatg->set_bold(1);
                $formatg->set_color('green');
                $formatg->set_align('center');
                
                $colnum = 0;
                foreach ($headers as $item) {
                    $myxls->write(0, $colnum, $item, $formatbc);
                    $colnum++;
                }
                $rownum = 1;
            } else if ($download == 'Excel') {
                require_once("$CFG->libdir/excellib.class.php");

                $filename .= ".xls";
                                $workbook = new \MoodleExcelWorkbook("-");
                                $workbook->send($filename);
                                $sheettitle = get_string('report', 'scorm');
                $myxls = $workbook->add_worksheet($sheettitle);
                                $format = $workbook->add_format();
                $format->set_bold(0);
                $formatbc = $workbook->add_format();
                $formatbc->set_bold(1);
                $formatbc->set_align('center');
                $formatb = $workbook->add_format();
                $formatb->set_bold(1);
                $formaty = $workbook->add_format();
                $formaty->set_bg_color('yellow');
                $formatc = $workbook->add_format();
                $formatc->set_align('center');
                $formatr = $workbook->add_format();
                $formatr->set_bold(1);
                $formatr->set_color('red');
                $formatr->set_align('center');
                $formatg = $workbook->add_format();
                $formatg->set_bold(1);
                $formatg->set_color('green');
                $formatg->set_align('center');

                $colnum = 0;
                foreach ($headers as $item) {
                    $myxls->write(0, $colnum, $item, $formatbc);
                    $colnum++;
                }
                $rownum = 1;
            } else if ($download == 'CSV') {
                $csvexport = new \csv_export_writer("tab");
                $csvexport->set_filename($filename, ".txt");
                $csvexport->add_data($headers);
            }

            if (!$download) {
                $sort = $table->get_sql_sort();
            } else {
                $sort = '';
            }
                        if (empty($sort)) {
                $sort = ' ORDER BY uniqueid';
            } else {
                $sort = ' ORDER BY '.$sort;
            }

            if (!$download) {
                                list($twhere, $tparams) = $table->get_sql_where();
                if ($twhere) {
                    $where .= ' AND '.$twhere;                     $params = array_merge($params, $tparams);
                }

                if (!empty($countsql)) {
                    $count = $DB->get_record_sql($countsql, $params);
                    $totalinitials = $count->nbresults;
                    if ($twhere) {
                        $countsql .= ' AND '.$twhere;
                    }
                    $count = $DB->get_record_sql($countsql, $params);
                    $total  = $count->nbresults;
                }

                $table->pagesize($pagesize, $total);

                echo \html_writer::start_div('scormattemptcounts');
                if ( $count->nbresults == $count->nbattempts ) {
                    echo get_string('reportcountattempts', 'scorm', $count);
                } else if ( $count->nbattempts > 0 ) {
                    echo get_string('reportcountallattempts', 'scorm', $count);
                } else {
                    echo $count->nbusers.' '.get_string('users');
                }
                echo \html_writer::end_div();
            }

                        if (!$download) {
                $attempts = $DB->get_records_sql($select.$from.$where.$sort, $params,
                $table->get_page_start(), $table->get_page_size());
                echo \html_writer::start_div('', array('id' => 'scormtablecontainer'));
                if ($candelete) {
                                        $strreallydel  = addslashes_js(get_string('deleteattemptcheck', 'scorm'));
                    echo \html_writer::start_tag('form', array('id' => 'attemptsform', 'method' => 'post',
                                                                'action' => $PAGE->url->out(false),
                                                                'onsubmit' => 'return confirm("'.$strreallydel.'");'));
                    echo \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'delete'));
                    echo \html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
                    echo \html_writer::start_div('', array('style' => 'display: none;'));
                    echo \html_writer::input_hidden_params($PAGE->url);
                    echo \html_writer::end_div();
                    echo \html_writer::start_div();
                }
                $table->initialbars($totalinitials > 20);             } else {
                $attempts = $DB->get_records_sql($select.$from.$where.$sort, $params);
            }
            if ($attempts) {
                foreach ($attempts as $scouser) {
                    $row = array();
                    if (!empty($scouser->attempt)) {
                        $timetracks = scorm_get_sco_runtime($scorm->id, false, $scouser->userid, $scouser->attempt);
                    } else {
                        $timetracks = '';
                    }
                    if (in_array('checkbox', $columns)) {
                        if ($candelete && !empty($timetracks->start)) {
                            $row[] = \html_writer::checkbox('attemptid[]', $scouser->userid . ':' . $scouser->attempt, false);
                        } else if ($candelete) {
                            $row[] = '';
                        }
                    }
                    if (in_array('picture', $columns)) {
                        $user = new \stdClass();
                        $additionalfields = explode(',', \user_picture::fields());
                        $user = username_load_fields_from_object($user, $scouser, null, $additionalfields);
                        $user->id = $scouser->userid;
                        $row[] = $OUTPUT->user_picture($user, array('courseid' => $course->id));
                    }
                    if (!$download) {
                        $url = new \moodle_url('/user/view.php', array('id' => $scouser->userid, 'course' => $course->id));
                        $row[] = \html_writer::link($url, fullname($scouser));
                    } else {
                        $row[] = fullname($scouser);
                    }
                    foreach ($extrafields as $field) {
                        $row[] = s($scouser->{$field});
                    }
                    if (empty($timetracks->start)) {
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = '-';
                    } else {
                        if (!$download) {
                            $url = new \moodle_url('/mod/scorm/report/userreport.php', array('id' => $cm->id,
                                    'user' => $scouser->userid, 'attempt' => $scouser->attempt));
                            $row[] = \html_writer::link($url, $scouser->attempt);
                        } else {
                            $row[] = $scouser->attempt;
                        }
                        if ($download == 'ODS' || $download == 'Excel' ) {
                            $row[] = userdate($timetracks->start, get_string("strftimedatetime", "langconfig"));
                        } else {
                            $row[] = userdate($timetracks->start);
                        }
                        if ($download == 'ODS' || $download == 'Excel' ) {
                            $row[] = userdate($timetracks->finish, get_string('strftimedatetime', 'langconfig'));
                        } else {
                            $row[] = userdate($timetracks->finish);
                        }
                        $row[] = scorm_grade_user_attempt($scorm, $scouser->userid, $scouser->attempt);
                    }
                                        foreach ($scoes as $sco) {
                        if ($sco->launch != '') {
                            if ($trackdata = scorm_get_tracks($sco->id, $scouser->userid, $scouser->attempt)) {
                                if ($trackdata->status == '') {
                                    $trackdata->status = 'notattempted';
                                }
                                $strstatus = get_string($trackdata->status, 'scorm');

                                if ($trackdata->score_raw != '') {                                     $score = $trackdata->score_raw;
                                                                        if (isset($trackdata->score_max)) {
                                        $score .= '/'.$trackdata->score_max;
                                    }

                                } else {                                     $score = $strstatus;
                                }
                                if (!$download) {
                                    $url = new \moodle_url('/mod/scorm/report/userreporttracks.php', array('id' => $cm->id,
                                        'scoid' => $sco->id, 'user' => $scouser->userid, 'attempt' => $scouser->attempt));
                                    $row[] = \html_writer::img($OUTPUT->pix_url($trackdata->status, 'scorm'), $strstatus,
                                        array('title' => $strstatus)) . \html_writer::empty_tag('br') .
                                        \html_writer::link($url, $score, array('title' => get_string('details', 'scorm')));
                                } else {
                                    $row[] = $score;
                                }
                                                                $scorm2004 = false;
                                if (scorm_version_check($scorm->version, SCORM_13)) {
                                    $scorm2004 = true;
                                    $objectiveprefix = "cmi.objectives.";
                                } else {
                                    $objectiveprefix = "cmi.objectives_";
                                }

                                $keywords = array(".id", $objectiveprefix);
                                $objectivestatus = array();
                                $objectivescore = array();
                                foreach ($trackdata as $name => $value) {
                                    if (strpos($name, $objectiveprefix) === 0 && strrpos($name, '.id') !== false) {
                                        $num = trim(str_ireplace($keywords, '', $name));
                                        if (is_numeric($num)) {
                                            if ($scorm2004) {
                                                $element = $objectiveprefix.$num.'.completion_status';
                                            } else {
                                                $element = $objectiveprefix.$num.'.status';
                                            }
                                            if (isset($trackdata->$element)) {
                                                $objectivestatus[$value] = $trackdata->$element;
                                            } else {
                                                $objectivestatus[$value] = '';
                                            }
                                            if ($displayoptions['objectivescore']) {
                                                $element = $objectiveprefix.$num.'.score.raw';
                                                if (isset($trackdata->$element)) {
                                                    $objectivescore[$value] = $trackdata->$element;
                                                } else {
                                                    $objectivescore[$value] = '';
                                                }
                                            }
                                        }
                                    }
                                }

                                                                if (!empty($objectives[$trackdata->scoid])) {
                                    foreach ($objectives[$trackdata->scoid] as $name) {
                                        if (isset($objectivestatus[$name])) {
                                            $row[] = s($objectivestatus[$name]);
                                        } else {
                                            $row[] = $emptycell;
                                        }
                                        if ($displayoptions['objectivescore']) {
                                            if (isset($objectivescore[$name])) {
                                                $row[] = s($objectivescore[$name]);
                                            } else {
                                                $row[] = $emptycell;
                                            }

                                        }
                                    }
                                }
                                                            } else {
                                                                $strstatus = get_string('notattempted', 'scorm');
                                if (!$download) {
                                    $row[] = \html_writer::img($OUTPUT->pix_url('notattempted', 'scorm'), $strstatus,
                                                array('title' => $strstatus)).\html_writer::empty_tag('br').$strstatus;
                                } else {
                                    $row[] = $strstatus;
                                }
                                                                for ($i = 0; $i < count($columns) - $nbmaincolumns; $i++) {
                                    $row[] = $emptycell;
                                }
                            }
                        }
                    }

                    if (!$download) {
                        $table->add_data($row);
                    } else if ($download == 'Excel' or $download == 'ODS') {
                        $colnum = 0;
                        foreach ($row as $item) {
                            $myxls->write($rownum, $colnum, $item, $format);
                            $colnum++;
                        }
                        $rownum++;
                    } else if ($download == 'CSV') {
                        $csvexport->add_data($row);
                    }
                }
                if (!$download) {
                    $table->finish_output();
                    if ($candelete) {
                        echo \html_writer::start_tag('table', array('id' => 'commands'));
                        echo \html_writer::start_tag('tr').\html_writer::start_tag('td');
                        echo \html_writer::link('javascript:select_all_in(\'DIV\', null, \'scormtablecontainer\');',
                                                    get_string('selectall', 'scorm')).' / ';
                        echo \html_writer::link('javascript:deselect_all_in(\'DIV\', null, \'scormtablecontainer\');',
                                                    get_string('selectnone', 'scorm'));
                        echo '&nbsp;&nbsp;';
                        echo \html_writer::empty_tag('input', array('type' => 'submit',
                                                                    'value' => get_string('deleteselected', 'scorm')));
                        echo \html_writer::end_tag('td').\html_writer::end_tag('tr').\html_writer::end_tag('table');
                                                echo \html_writer::end_tag('div');
                        echo \html_writer::end_tag('form');
                    }
                    echo \html_writer::end_div();
                    if (!empty($attempts)) {
                        echo \html_writer::start_tag('table', array('class' => 'boxaligncenter')).\html_writer::start_tag('tr');
                        echo \html_writer::start_tag('td');
                        echo $OUTPUT->single_button(new \moodle_url($PAGE->url,
                                                                   array('download' => 'ODS') + $displayoptions),
                                                                   get_string('downloadods'));
                        echo \html_writer::end_tag('td');
                        echo \html_writer::start_tag('td');
                        echo $OUTPUT->single_button(new \moodle_url($PAGE->url,
                                                                   array('download' => 'Excel') + $displayoptions),
                                                                   get_string('downloadexcel'));
                        echo \html_writer::end_tag('td');
                        echo \html_writer::start_tag('td');
                        echo $OUTPUT->single_button(new \moodle_url($PAGE->url,
                                                                   array('download' => 'CSV') + $displayoptions),
                                                                   get_string('downloadtext'));
                        echo \html_writer::end_tag('td');
                        echo \html_writer::start_tag('td');
                        echo \html_writer::end_tag('td');
                        echo \html_writer::end_tag('tr').\html_writer::end_tag('table');
                    }
                }
            } else {
                if ($candelete && !$download) {
                    echo \html_writer::end_div();
                    echo \html_writer::end_tag('form');
                    $table->finish_output();
                }
                echo \html_writer::end_div();
            }
                        if (!$download) {
                $mform->set_data(compact('detailedrep', 'pagesize', 'attemptsmode'));
                $mform->display();
            }
            if ($download == 'Excel' or $download == 'ODS') {
                $workbook->close();
                exit;
            } else if ($download == 'CSV') {
                $csvexport->download_file();
                exit;
            }
        } else {
            echo $OUTPUT->notification(get_string('noactivity', 'scorm'));
        }
    }}


function get_scorm_objectives($scormid) {
    global $DB;
    $objectives = array();
    $params = array();
    $select = "scormid = ? AND ";
    $select .= $DB->sql_like("element", "?", false);
    $params[] = $scormid;
    $params[] = "cmi.objectives%.id";
    $value = $DB->sql_compare_text('value');
    $rs = $DB->get_recordset_select("scorm_scoes_track", $select, $params, 'value', "DISTINCT $value AS value, scoid");
    if ($rs->valid()) {
        foreach ($rs as $record) {
            $objectives[$record->scoid][] = $record->value;
        }
                foreach ($objectives as $scoid => $sco) {
            natsort($objectives[$scoid]);
        }
    }
    $rs->close();
    return $objectives;
}
