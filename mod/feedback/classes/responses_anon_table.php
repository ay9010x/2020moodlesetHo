<?php



defined('MOODLE_INTERNAL') || die();


class mod_feedback_responses_anon_table extends mod_feedback_responses_table {

    
    protected $showallparamname = 'ashowall';

    
    protected $downloadparamname = 'adownload';

    
    public function init() {

        $cm = $this->feedbackstructure->get_cm();
        $this->uniqueid = 'feedback-showentry-anon-list-' . $cm->instance;

                                $this->request[TABLE_VAR_PAGE] = 'apage';

        $tablecolumns = ['random_response'];
        $tableheaders = [get_string('response_nr', 'feedback')];

        if ($this->feedbackstructure->get_feedback()->course == SITEID && !$this->feedbackstructure->get_courseid()) {
            $tablecolumns[] = 'courseid';
            $tableheaders[] = get_string('course');
        }

        $this->define_columns($tablecolumns);
        $this->define_headers($tableheaders);

        $this->sortable(true, 'random_response');
        $this->collapsible(true);
        $this->set_attribute('id', 'showentryanontable');

        $params = ['instance' => $cm->instance,
            'anon' => FEEDBACK_ANONYMOUS_YES,
            'courseid' => $this->feedbackstructure->get_courseid()];

        $fields = 'c.id, c.random_response, c.courseid';
        $from = '{feedback_completed} c';
        $where = 'c.anonymous_response = :anon AND c.feedback = :instance';
        if ($this->feedbackstructure->get_courseid()) {
            $where .= ' AND c.courseid = :courseid';
        }

        $group = groups_get_activity_group($this->feedbackstructure->get_cm(), true);
        if ($group) {
            $where .= ' AND c.userid IN (SELECT g.userid FROM {groups_members} g WHERE g.groupid = :group)';
            $params['group'] = $group;
        }

        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(c.id) FROM $from WHERE $where", $params);
    }

    
    protected function get_link_single_entry($row) {
        return new moodle_url($this->baseurl, ['showcompleted' => $row->id]);
    }

    
    public function col_random_response($row) {
        if ($this->is_downloading()) {
            return $row->random_response;
        } else {
            return html_writer::link($this->get_link_single_entry($row),
                    get_string('response_nr', 'feedback').': '. $row->random_response);
        }
    }
}
