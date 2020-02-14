<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');


class mod_feedback_templates_table extends flexible_table {

    
    public function __construct($uniqueid, $baseurl) {
        parent::__construct($uniqueid);

        $tablecolumns = array('template', 'action');
        $tableheaders = array(get_string('template', 'feedback'), '');

        $this->set_attribute('class', 'templateslist');

        $this->define_columns($tablecolumns);
        $this->define_headers($tableheaders);
        $this->define_baseurl($baseurl);
        $this->column_class('template', 'template');
        $this->column_class('action', 'action');

        $this->sortable(false);
    }

    
    public function display($templates) {
        global $OUTPUT;
        if (empty($templates)) {
            echo $OUTPUT->box(get_string('no_templates_available_yet', 'feedback'),
                             'generalbox boxaligncenter');
            return;
        }

        $this->setup();
        $strdeletefeedback = get_string('delete_template', 'feedback');

        foreach ($templates as $template) {
            $data = array();
            $data[] = format_string($template->name);
            $url = new moodle_url($this->baseurl, array('deletetempl' => $template->id, 'sesskey' => sesskey()));

            $deleteaction = new confirm_action(get_string('confirmdeletetemplate', 'feedback'));
            $data[] = $OUTPUT->action_icon($url, new pix_icon('t/delete', $strdeletefeedback), $deleteaction);
            $this->add_data($data);
        }
        $this->finish_output();
    }
}
