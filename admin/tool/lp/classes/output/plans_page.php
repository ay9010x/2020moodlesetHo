<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use single_button;
use moodle_url;
use core_competency\api;
use core_competency\external\plan_exporter;
use core_competency\plan;
use core_competency\user_evidence;
use context_user;


class plans_page implements renderable, templatable {

    
    protected $navigation = array();

    
    protected $plans = array();

    
    protected $context = null;

    
    protected $userid = null;

    
    public function __construct($userid) {
        $this->userid = $userid;
        $this->plans = api::list_user_plans($userid);
        $this->context = context_user::instance($userid);

        if (plan::can_manage_user($userid) || plan::can_manage_user_draft($userid)) {
            $addplan = new single_button(
                new moodle_url('/admin/tool/lp/editplan.php', array('userid' => $userid)),
                get_string('addnewplan', 'tool_lp'), 'get'
            );
            $this->navigation[] = $addplan;
        }
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->userid = $this->userid;
        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(true);
        $data->canreaduserevidence = user_evidence::can_read_user($this->userid);
        $data->canmanageuserplans = plan::can_manage_user($this->userid);

                $data->plans = array();
        if ($this->plans) {
            foreach ($this->plans as $plan) {
                $exporter = new plan_exporter($plan, array('template' => $plan->get_template()));
                $record = $exporter->export($output);
                $data->plans[] = $record;
            }
        }

        $data->navigation = array();
        foreach ($this->navigation as $button) {
            $data->navigation[] = $output->render($button);
        }

        return $data;
    }
}
