<?php



defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/completionlib.php");


class block_completionstatus extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_completionstatus');
    }

    public function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
    }

    public function get_content() {
        global $USER;

        $rows = array();
        $srows = array();
        $prows = array();
                if ($this->content !== null) {
            return $this->content;
        }

        $course = $this->page->course;
        $context = context_course::instance($course->id);

                $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

                $can_edit = has_capability('moodle/course:update', $context);

                $info = new completion_info($course);

                if (!completion_info::is_enabled_for_site()) {
            if ($can_edit) {
                $this->content->text .= get_string('completionnotenabledforsite', 'completion');
            }
            return $this->content;

        } else if (!$info->is_enabled()) {
            if ($can_edit) {
                $this->content->text .= get_string('completionnotenabledforcourse', 'completion');
            }
            return $this->content;
        }

                $completions = $info->get_completions($USER->id);

                if (empty($completions)) {
            if ($can_edit) {
                $this->content->text .= get_string('nocriteriaset', 'completion');
            }
            return $this->content;
        }

                if ($info->is_tracked_user($USER->id)) {

                        $data = '';

                        $activities = array();
            $activities_complete = 0;

                        $prerequisites = array();
            $prerequisites_complete = 0;

                        $pending_update = false;

                        foreach ($completions as $completion) {
                $criteria = $completion->get_criteria();
                $complete = $completion->is_complete();

                if (!$pending_update && $criteria->is_pending($completion)) {
                    $pending_update = true;
                }

                                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
                    $activities[$criteria->moduleinstance] = $complete;

                    if ($complete) {
                        $activities_complete++;
                    }

                    continue;
                }

                                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_COURSE) {
                    $prerequisites[$criteria->courseinstance] = $complete;

                    if ($complete) {
                        $prerequisites_complete++;
                    }

                    continue;
                }
                $row = new html_table_row();
                $row->cells[0] = new html_table_cell($criteria->get_title());
                $row->cells[1] = new html_table_cell($completion->get_status());
                $row->cells[1]->style = 'text-align: right;';
                $srows[] = $row;
            }

                        if (!empty($activities)) {
                $a = new stdClass();
                $a->first = $activities_complete;
                $a->second = count($activities);

                $row = new html_table_row();
                $row->cells[0] = new html_table_cell(get_string('activitiescompleted', 'completion'));
                $row->cells[1] = new html_table_cell(get_string('firstofsecond', 'block_completionstatus', $a));
                $row->cells[1]->style = 'text-align: right;';
                $srows[] = $row;
            }

                        if (!empty($prerequisites)) {
                $a = new stdClass();
                $a->first = $prerequisites_complete;
                $a->second = count($prerequisites);

                $row = new html_table_row();
                $row->cells[0] = new html_table_cell(get_string('dependenciescompleted', 'completion'));
                $row->cells[1] = new html_table_cell(get_string('firstofsecond', 'block_completionstatus', $a));
                $row->cells[1]->style = 'text-align: right;';
                $prows[] = $row;

                $srows = array_merge($prows, $srows);
            }

                        $table = new html_table();
            $table->width = '100%';
            $table->attributes = array('style'=>'font-size: 90%;', 'class'=>'');

            $row = new html_table_row();
            $content = html_writer::tag('b', get_string('status').': ');

                        $coursecomplete = $info->is_course_complete($USER->id);

                        $params = array(
                'userid' => $USER->id,
                'course' => $course->id
            );
            $ccompletion = new completion_completion($params);

                        $criteriacomplete = $info->count_course_user_data($USER->id);

            if ($pending_update) {
                $content .= html_writer::tag('i', get_string('pending', 'completion'));
            } else if ($coursecomplete) {
                $content .= get_string('complete');
            } else if (!$criteriacomplete && !$ccompletion->timestarted) {
                $content .= html_writer::tag('i', get_string('notyetstarted', 'completion'));
            } else {
                $content .= html_writer::tag('i', get_string('inprogress', 'completion'));
            }

            $row->cells[0] = new html_table_cell($content);
            $row->cells[0]->colspan = '2';

            $rows[] = $row;
            $row = new html_table_row();
            $content = "";
                        $overall = $info->get_aggregation_method();
            if ($overall == COMPLETION_AGGREGATION_ALL) {
                $content .= get_string('criteriarequiredall', 'completion');
            } else {
                $content .= get_string('criteriarequiredany', 'completion');
            }
            $content .= ':';
            $row->cells[0] = new html_table_cell($content);
            $row->cells[0]->colspan = '2';
            $rows[] = $row;

            $row = new html_table_row();
            $row->cells[0] = new html_table_cell(html_writer::tag('b', get_string('requiredcriteria', 'completion')));
            $row->cells[1] = new html_table_cell(html_writer::tag('b', get_string('status')));
            $row->cells[1]->style = 'text-align: right;';
            $rows[] = $row;

                        $rows = array_merge($rows, $srows);

            $table->data = $rows;
            $this->content->text .= html_writer::table($table);

                        $details = new moodle_url('/blocks/completionstatus/details.php', array('course' => $course->id));
            $this->content->footer .= html_writer::link($details, get_string('moredetails', 'completion'));
        } else {
                        $this->content->text = get_string('nottracked', 'completion');
        }

        if (has_capability('report/completion:view', $context)) {
            $report = new moodle_url('/report/completion/index.php', array('course' => $course->id));
            if (empty($this->content->footer)) {
                $this->content->footer = '';
            }
            $this->content->footer .= html_writer::empty_tag('br');
            $this->content->footer .= html_writer::link($report, get_string('viewcoursereport', 'completion'));
        }

        return $this->content;
    }
}
