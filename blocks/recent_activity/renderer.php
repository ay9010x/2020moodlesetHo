<?php



defined('MOODLE_INTERNAL') || die;


class block_recent_activity_renderer extends plugin_renderer_base {

    
    public function recent_activity($course, $timestart, $recentenrolments, $structuralchanges,
            $modulesrecentactivity) {

        $output = html_writer::tag('div',
                get_string('activitysince', '', userdate($timestart)),
                array('class' => 'activityhead'));

        $output .= html_writer::tag('div',
                html_writer::link(new moodle_url('/course/recent.php', array('id' => $course->id)),
                    get_string('recentactivityreport')),
                array('class' => 'activityhead'));

        $content = false;

                if ($recentenrolments) {
            $content = true;
            $context = context_course::instance($course->id);
            $viewfullnames = has_capability('moodle/site:viewfullnames', $context);
            $output .= html_writer::start_tag('div', array('class' => 'newusers'));
            $output .= $this->heading(get_string("newusers").':', 3);
                        $output .= html_writer::start_tag('ol', array('class' => 'list'));
            foreach ($recentenrolments as $user) {
                $output .= html_writer::tag('li',
                        html_writer::link(new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id)),
                                fullname($user, $viewfullnames)),
                        array('class' => 'name'));
            }
            $output .= html_writer::end_tag('ol');
            $output .= html_writer::end_tag('div');
        }

                if (!empty($structuralchanges)) {
            $content = true;
            $output .= $this->heading(get_string("courseupdates").':', 3);
            foreach ($structuralchanges as $changeinfo => $change) {
                $output .= $this->structural_change($change);
            }
        }

                foreach ($modulesrecentactivity as $modname => $moduleactivity) {
            $content = true;
            $output .= $moduleactivity;
        }

        if (! $content) {
            $output .= html_writer::tag('p', get_string('nothingnew'), array('class' => 'message'));
        }
        return $output;
    }

    
    protected function structural_change($change) {
        $cm = $change['module'];
        switch ($change['action']) {
            case 'delete mod':
                $text = get_string('deletedactivity', 'moodle', $cm->modfullname);
                break;
            case 'add mod':
                $text = get_string('added', 'moodle', $cm->modfullname). '<br />'.
                    html_writer::link($cm->url, format_string($cm->name, true));
                break;
            case 'update mod':
                $text = get_string('updated', 'moodle', $cm->modfullname). '<br />'.
                    html_writer::link($cm->url, format_string($cm->name, true));
                break;
            default:
                return '';
        }
        return html_writer::tag('p', $text, array('class' => 'activity'));
    }
}
