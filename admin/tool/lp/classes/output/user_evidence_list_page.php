<?php


namespace tool_lp\output;

use renderable;
use templatable;
use renderer_base;
use stdClass;
use single_button;
use moodle_url;
use core_competency\api;
use tool_lp\external\user_evidence_summary_exporter;
use core_competency\user_evidence;
use context_user;


class user_evidence_list_page implements renderable, templatable {

    
    protected $navigation = array();

    
    protected $evidence = array();

    
    protected $context = null;

    
    protected $userid = null;

    
    protected $canmanage;

    
    public function __construct($userid) {
        $this->userid = $userid;
        $this->context = context_user::instance($userid);
        $this->evidence = api::list_user_evidence($userid);
        $this->canmanage = user_evidence::can_manage_user($this->userid);

        if ($this->canmanage) {
            $addevidence = new single_button(
               new moodle_url('/admin/tool/lp/user_evidence_edit.php', array('userid' => $userid)),
               get_string('addnewuserevidence', 'tool_lp'), 'get'
            );
            $this->navigation[] = $addevidence;
        }
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->userid = $this->userid;
        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(true);
        $data->canmanage = $this->canmanage;

        $data->evidence = array();
        if ($this->evidence) {
            foreach ($this->evidence as $evidence) {
                $userevidencesummaryexporter = new user_evidence_summary_exporter($evidence, array(
                    'context' => $this->context
                ));
                $data->evidence[] = $userevidencesummaryexporter->export($output);
            }
        }

        $data->navigation = array();
        foreach ($this->navigation as $button) {
            $data->navigation[] = $output->render($button);
        }

        return $data;
    }
}
