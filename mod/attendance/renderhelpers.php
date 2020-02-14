<?php



defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/renderables.php');


class user_sessions_cells_generator {
    protected $cells = array();

    protected $reportdata;
    protected $user;

    public function  __construct(attendance_report_data $reportdata, $user) {
        $this->reportdata = $reportdata;
        $this->user = $user;
    }

    public function get_cells($remarks = false) {
        $this->init_cells();
        foreach ($this->reportdata->sessions as $sess) {
            if (array_key_exists($sess->id, $this->reportdata->sessionslog[$this->user->id])) {
                $statusid = $this->reportdata->sessionslog[$this->user->id][$sess->id]->statusid;
                if (array_key_exists($statusid, $this->reportdata->statuses)) {
                    $points = format_float($this->reportdata->statuses[$statusid]->grade, 1, true, true);
                    $maxpoints = format_float($sess->maxpoints, 1, true, true);
                    $this->construct_existing_status_cell($this->reportdata->statuses[$statusid]->acronym .
                                " ({$points}/{$maxpoints})");
                } else {
                    $this->construct_hidden_status_cell($this->reportdata->allstatuses[$statusid]->acronym);
                }
                if ($remarks) {
                    $this->construct_remarks_cell($this->reportdata->sessionslog[$this->user->id][$sess->id]->remarks);
                }
            } else {
                if ($this->user->enrolmentstart > $sess->sessdate) {
                    $starttext = get_string('enrolmentstart', 'attendance', userdate($this->user->enrolmentstart, '%d.%m.%Y'));
                    $this->construct_enrolments_info_cell($starttext);
                } else if ($this->user->enrolmentend and $this->user->enrolmentend < $sess->sessdate) {
                    $endtext = get_string('enrolmentend', 'attendance', userdate($this->user->enrolmentend, '%d.%m.%Y'));
                    $this->construct_enrolments_info_cell($endtext);
                } else if (!$this->user->enrolmentend and $this->user->enrolmentstatus == ENROL_USER_SUSPENDED) {
                                        $suspendext = get_string('enrolmentsuspended', 'attendance', userdate($this->user->enrolmentend, '%d.%m.%Y'));
                    $this->construct_enrolments_info_cell($suspendext);
                } else {
                    if ($sess->groupid == 0 or array_key_exists($sess->groupid, $this->reportdata->usersgroups[$this->user->id])) {
                        $this->construct_not_taken_cell('?');
                    } else {
                        $this->construct_not_existing_for_user_session_cell('');
                    }
                }
                if ($remarks) {
                    $this->construct_remarks_cell('');
                }
            }
        }
        $this->finalize_cells();

        return $this->cells;
    }

    protected function init_cells() {

    }

    protected function construct_existing_status_cell($text) {
        $this->cells[] = $text;
    }

    protected function construct_hidden_status_cell($text) {
        $this->cells[] = $text;
    }

    protected function construct_enrolments_info_cell($text) {
        $this->cells[] = $text;
    }

    protected function construct_not_taken_cell($text) {
        $this->cells[] = $text;
    }

    protected function construct_remarks_cell($text) {
        $this->cells[] = $text;
    }

    protected function construct_not_existing_for_user_session_cell($text) {
        $this->cells[] = $text;
    }

    protected function finalize_cells() {
    }
}


class user_sessions_cells_html_generator extends user_sessions_cells_generator {
    private $cell;

    protected function construct_existing_status_cell($text) {
        $this->close_open_cell_if_needed();
        $this->cells[] = html_writer::span($text, 'attendancestatus-'.$text);
    }

    protected function construct_hidden_status_cell($text) {
        $this->cells[] = html_writer::tag('s', $text);
    }

    protected function construct_enrolments_info_cell($text) {
        if (is_null($this->cell)) {
            $this->cell = new html_table_cell($text);
            $this->cell->colspan = 1;
        } else {
            if ($this->cell->text != $text) {
                $this->cells[] = $this->cell;
                $this->cell = new html_table_cell($text);
                $this->cell->colspan = 1;
            } else {
                $this->cell->colspan++;
            }
        }
    }

    private function close_open_cell_if_needed() {
        if ($this->cell) {
            $this->cells[] = $this->cell;
            $this->cell = null;
        }
    }

    protected function construct_not_taken_cell($text) {
        $this->close_open_cell_if_needed();
        $this->cells[] = $text;
    }

    protected function construct_remarks_cell($text) {
        global $OUTPUT;

        if (!trim($text)) {
            return;
        }

                $icon = $OUTPUT->pix_icon('i/info', '');
        $remark = html_writer::span($text, 'remarkcontent');
        $remark = html_writer::span($icon.$remark, 'remarkholder');

                $markcell = array_pop($this->cells);
        $markcell .= ' '.$remark;
        $this->cells[] = $markcell;
    }

    protected function construct_not_existing_for_user_session_cell($text) {
        $this->close_open_cell_if_needed();
        $this->cells[] = $text;
    }

    protected function finalize_cells() {
        if ($this->cell) {
            $this->cells[] = $this->cell;
        }
    }
}


class user_sessions_cells_text_generator extends user_sessions_cells_generator {
    private $enrolmentsinfocelltext;

    protected function construct_hidden_status_cell($text) {
        $this->cells[] = '-'.$text;
    }

    protected function construct_enrolments_info_cell($text) {
        if ($this->enrolmentsinfocelltext != $text) {
            $this->enrolmentsinfocelltext = $text;
            $this->cells[] = $text;
        } else {
            $this->cells[] = '←';
        }
    }
}

function construct_session_time($datetime, $duration) {
    $starttime = userdate($datetime, get_string('strftimehm', 'attendance'));
    $endtime = userdate($datetime + $duration, get_string('strftimehm', 'attendance'));

    return $starttime . ($duration > 0 ? ' - ' . $endtime : '');
}

function construct_session_full_date_time($datetime, $duration) {
    $sessinfo = userdate($datetime, get_string('strftimedmyw', 'attendance'));
    $sessinfo .= ' '.construct_session_time($datetime, $duration);

    return $sessinfo;
}

function construct_user_data_stat($usersummary, $view) {
    $stattable = new html_table();
    $stattable->attributes['class'] = 'attlist';
    $row = new html_table_row();
    $row->attributes['class'] = 'normal';
    $row->cells[] = get_string('sessionscompleted', 'attendance') . ':';
    $row->cells[] = $usersummary->numtakensessions;
    $stattable->data[] = $row;

    $row = new html_table_row();
    $row->attributes['class'] = 'normal';
    $row->cells[] = get_string('pointssessionscompleted', 'attendance') . ':';
    $row->cells[] = format_float($usersummary->takensessionspoints, 1, true, true) . ' / ' .
                        format_float($usersummary->takensessionsmaxpoints, 1, true, true);
    $stattable->data[] = $row;

    $row = new html_table_row();
    $row->attributes['class'] = 'normal';
    $row->cells[] = get_string('percentagesessionscompleted', 'attendance') . ':';
    $row->cells[] = format_float($usersummary->takensessionspercentage * 100) . '%';
    $stattable->data[] = $row;

    if ($view == ATT_VIEW_ALL) {
        $row = new html_table_row();
        $row->attributes['class'] = 'highlight';
        $row->cells[] = get_string('sessionstotal', 'attendance') . ':';
        $row->cells[] = $usersummary->numallsessions;
        $stattable->data[] = $row;

        $row = new html_table_row();
        $row->attributes['class'] = 'highlight';
        $row->cells[] = get_string('pointsallsessions', 'attendance') . ':';
        $row->cells[] = format_float($usersummary->takensessionspoints, 1, true, true) . ' / ' .
                            format_float($usersummary->allsessionsmaxpoints, 1, true, true);
        $stattable->data[] = $row;

        $row = new html_table_row();
        $row->attributes['class'] = 'highlight';
        $row->cells[] = get_string('percentageallsessions', 'attendance') . ':';
        $row->cells[] = format_float($usersummary->allsessionspercentage * 100) . '%';
        $stattable->data[] = $row;

        $row = new html_table_row();
        $row->attributes['class'] = 'normal';
        $row->cells[] = get_string('maxpossiblepoints', 'attendance') . ':';
        $row->cells[] = format_float($usersummary->maxpossiblepoints, 1, true, true) . ' / ' .
                            format_float($usersummary->allsessionsmaxpoints, 1, true, true);
        $stattable->data[] = $row;

        $row = new html_table_row();
        $row->attributes['class'] = 'normal';
        $row->cells[] = get_string('maxpossiblepercentage', 'attendance') . ':';
        $row->cells[] = format_float($usersummary->maxpossiblepercentage * 100) . '%';
        $stattable->data[] = $row;
    }

    return html_writer::table($stattable);
}

function construct_full_user_stat_html_table($attendance, $user) {
    $summary = new mod_attendance_summary($attendance->id, $user->id);
    return construct_user_data_stat($summary->get_all_sessions_summary_for($user->id), ATT_VIEW_ALL);
}
