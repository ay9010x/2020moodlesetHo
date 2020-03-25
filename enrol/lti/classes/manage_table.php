<?php



namespace enrol_lti;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');


class manage_table extends \table_sql {

    
    protected $ltiplugin;

    
    protected $ltienabled;

    
    protected $canconfig;

    
    protected $courseid;

    
    public function __construct($courseid) {
        parent::__construct('enrol_lti_manage_table');

        $this->define_columns(array(
            'name',
            'url',
            'secret',
            'edit'
        ));
        $this->define_headers(array(
            get_string('name'),
            get_string('url'),
            get_string('secret', 'enrol_lti'),
            get_string('edit')
        ));
        $this->collapsible(false);
        $this->sortable(false);

                $this->ltiplugin = enrol_get_plugin('lti');
        $this->ltienabled = enrol_is_enabled('lti');
        $this->canconfig = has_capability('moodle/course:enrolconfig', \context_course::instance($courseid));
        $this->courseid = $courseid;
    }

    
    public function col_name($tool) {
        if (empty($tool->name)) {
            $toolcontext = \context::instance_by_id($tool->contextid);
            $name = $toolcontext->get_context_name();
        } else {
            $name = $tool->name;
        };

        return $this->get_display_text($tool, $name);
    }

    
    public function col_url($tool) {
        $url = new \moodle_url('/enrol/lti/tool.php', array('id' => $tool->id));
        return $this->get_display_text($tool, $url);
    }

    
    public function col_secret($tool) {
        return $this->get_display_text($tool, $tool->secret);
    }


    
    public function col_edit($tool) {
        global $OUTPUT;

        $buttons = array();

        $instance = new \stdClass();
        $instance->id = $tool->enrolid;
        $instance->courseid = $tool->courseid;
        $instance->enrol = 'lti';
        $instance->status = $tool->status;

        $strdelete = get_string('delete');
        $strenable = get_string('enable');
        $strdisable = get_string('disable');

        $url = new \moodle_url('/enrol/lti/index.php', array('sesskey' => sesskey(), 'courseid' => $this->courseid));

        if ($this->ltiplugin->can_delete_instance($instance)) {
            $aurl = new \moodle_url($url, array('action' => 'delete', 'instanceid' => $instance->id));
            $buttons[] = $OUTPUT->action_icon($aurl, new \pix_icon('t/delete', $strdelete, 'core',
                array('class' => 'iconsmall')));
        }

        if ($this->ltienabled && $this->ltiplugin->can_hide_show_instance($instance)) {
            if ($instance->status == ENROL_INSTANCE_ENABLED) {
                $aurl = new \moodle_url($url, array('action' => 'disable', 'instanceid' => $instance->id));
                $buttons[] = $OUTPUT->action_icon($aurl, new \pix_icon('t/hide', $strdisable, 'core',
                    array('class' => 'iconsmall')));
            } else if ($instance->status == ENROL_INSTANCE_DISABLED) {
                $aurl = new \moodle_url($url, array('action' => 'enable', 'instanceid' => $instance->id));
                $buttons[] = $OUTPUT->action_icon($aurl, new \pix_icon('t/show', $strenable, 'core',
                    array('class' => 'iconsmall')));
            }
        }

        if ($this->ltienabled && $this->canconfig) {
            $linkparams = array(
                'courseid' => $instance->courseid,
                'id' => $instance->id, 'type' => $instance->enrol,
                'returnurl' => new \moodle_url('/enrol/lti/index.php', array('courseid' => $this->courseid))
            );
            $editlink = new \moodle_url("/enrol/editinstance.php", $linkparams);
            $buttons[] = $OUTPUT->action_icon($editlink, new \pix_icon('t/edit', get_string('edit'), 'core',
                array('class' => 'iconsmall')));
        }

        return implode(' ', $buttons);
    }

    
    public function query_db($pagesize, $useinitialsbar = true) {
        $total = \enrol_lti\helper::count_lti_tools(array('courseid' => $this->courseid));
        $this->pagesize($pagesize, $total);
        $tools = \enrol_lti\helper::get_lti_tools(array('courseid' => $this->courseid), $this->get_page_start(),
            $this->get_page_size());
        $this->rawdata = $tools;
                if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    
    protected function get_display_text($tool, $text) {
        if ($tool->status != ENROL_INSTANCE_ENABLED) {
            return \html_writer::tag('span', $text, array('class' => 'dimmed_text'));
        }

        return $text;
    }
}
