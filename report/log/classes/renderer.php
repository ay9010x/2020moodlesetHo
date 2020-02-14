<?php


defined('MOODLE_INTERNAL') || die;


class report_log_renderer extends plugin_renderer_base {

    
    public function render_report_log_renderable(report_log_renderable $reportlog) {
        debugging('Do not call this method. Please call $renderer->render($reportlog) instead.', DEBUG_DEVELOPER);
        return $this->render($reportlog);
    }

    
    protected function render_report_log(report_log_renderable $reportlog) {
        if (empty($reportlog->selectedlogreader)) {
            echo $this->output->notification(get_string('nologreaderenabled', 'report_log'), 'notifyproblem');
            return;
        }
        if ($reportlog->showselectorform) {
            $this->report_selector_form($reportlog);
        }

        if ($reportlog->showreport) {
            $reportlog->tablelog->out($reportlog->perpage, true);
        }
    }

    
    public function reader_selector(report_log_renderable $reportlog) {
        $readers = $reportlog->get_readers(true);
        if (empty($readers)) {
            $readers = array(get_string('nologreaderenabled', 'report_log'));
        }
        $url = fullclone ($reportlog->url);
        $url->remove_params(array('logreader'));
        $select = new single_select($url, 'logreader', $readers, $reportlog->selectedlogreader, null);
        $select->set_label(get_string('selectlogreader', 'report_log'));
        echo $this->output->render($select);
    }

    
    public function report_selector_form(report_log_renderable $reportlog) {
        echo html_writer::start_tag('form', array('class' => 'logselecform', 'action' => $reportlog->url, 'method' => 'get'));
        echo html_writer::start_div();
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'chooselog', 'value' => '1'));
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'showusers', 'value' => $reportlog->showusers));
        echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'showcourses',
            'value' => $reportlog->showcourses));

        $selectedcourseid = empty($reportlog->course) ? 0 : $reportlog->course->id;

                $sitecontext = context_system::instance();
        $courses = $reportlog->get_course_list();
        if (!empty($courses) && $reportlog->showcourses) {
            echo html_writer::label(get_string('selectacourse'), 'menuid', false, array('class' => 'accesshide'));
            echo html_writer::select($courses, "id", $selectedcourseid, null);
        } else {
            $courses = array();
            $courses[$selectedcourseid] = get_course_display_name_for_list($reportlog->course) . (($selectedcourseid == SITEID) ?
                ' (' . get_string('site') . ') ' : '');
            echo html_writer::label(get_string('selectacourse'), 'menuid', false, array('class' => 'accesshide'));
            echo html_writer::select($courses, "id", $selectedcourseid, false);
                        if (has_capability('report/log:view', $sitecontext)) {
                $a = new stdClass();
                $a->url = new moodle_url('/report/log/index.php', array('chooselog' => 0,
                    'group' => $reportlog->get_selected_group(), 'user' => $reportlog->userid,
                    'id' => $selectedcourseid, 'date' => $reportlog->date, 'modid' => $reportlog->modid,
                    'showcourses' => 1, 'showusers' => $reportlog->showusers));
                $a->url = $a->url->out(false);
                print_string('logtoomanycourses', 'moodle', $a);
            }
        }

                $groups = $reportlog->get_group_list();
        if (!empty($groups)) {
            echo html_writer::label(get_string('selectagroup'), 'menugroup', false, array('class' => 'accesshide'));
            echo html_writer::select($groups, "group", $reportlog->groupid, get_string("allgroups"));
        }

                $users = $reportlog->get_user_list();

        if ($reportlog->showusers) {
            echo html_writer::label(get_string('selctauser'), 'menuuser', false, array('class' => 'accesshide'));
            echo html_writer::select($users, "user", $reportlog->userid, get_string("allparticipants"));
        } else {
            $users = array();
            if (!empty($reportlog->userid)) {
                $users[$reportlog->userid] = $reportlog->get_selected_user_fullname();
            } else {
                $users[0] = get_string('allparticipants');
            }
            echo html_writer::label(get_string('selctauser'), 'menuuser', false, array('class' => 'accesshide'));
            echo html_writer::select($users, "user", $reportlog->userid, false);
            $a = new stdClass();
            $a->url = new moodle_url('/report/log/index.php', array('chooselog' => 0,
                'group' => $reportlog->get_selected_group(), 'user' => $reportlog->userid,
                'id' => $selectedcourseid, 'date' => $reportlog->date, 'modid' => $reportlog->modid,
                'showusers' => 1, 'showcourses' => $reportlog->showcourses));
            $a->url = $a->url->out(false);
            print_string('logtoomanyusers', 'moodle', $a);
        }

                $dates = $reportlog->get_date_options();
        echo html_writer::label(get_string('date'), 'menudate', false, array('class' => 'accesshide'));
        echo html_writer::select($dates, "date", $reportlog->date, get_string("alldays"));

                $activities = $reportlog->get_activities_list();
        echo html_writer::label(get_string('activities'), 'menumodid', false, array('class' => 'accesshide'));
        echo html_writer::select($activities, "modid", $reportlog->modid, get_string("allactivities"));

                echo html_writer::label(get_string('actions'), 'menumodaction', false, array('class' => 'accesshide'));
        echo html_writer::select($reportlog->get_actions(), 'modaction', $reportlog->action, get_string("allactions"));

                $edulevel = $reportlog->get_edulevel_options();
        echo html_writer::label(get_string('edulevel'), 'menuedulevel', false, array('class' => 'accesshide'));
        echo html_writer::select($edulevel, 'edulevel', $reportlog->edulevel, false).$this->help_icon('edulevel');

                        $readers = $reportlog->get_readers(true);
        if (!empty($readers)) {
            if (count($readers) == 1) {
                $attributes = array('type' => 'hidden', 'name' => 'logreader', 'value' => key($readers));
                echo html_writer::empty_tag('input', $attributes);
            } else {
                echo html_writer::label(get_string('selectlogreader', 'report_log'), 'menureader', false,
                        array('class' => 'accesshide'));
                echo html_writer::select($readers, 'logreader', $reportlog->selectedlogreader, false);
            }
            echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('gettheselogs')));
        }
        echo html_writer::end_div();
        echo html_writer::end_tag('form');
    }
}

