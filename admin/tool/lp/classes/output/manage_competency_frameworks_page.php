<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use single_button;
use stdClass;
use moodle_url;
use context;
use context_system;
use core_competency\api;
use core_competency\competency_framework;
use core_competency\external\competency_framework_exporter;


class manage_competency_frameworks_page implements renderable, templatable {

    
    protected $pagecontext;

    
    protected $navigation = array();

    
    protected $competencyframeworks = array();

    
    protected $canmanage = false;

    
    protected $pluginbaseurl = null;

    
    public function __construct(context $pagecontext) {
        $this->pagecontext = $pagecontext;

        if (competency_framework::can_manage_context($this->pagecontext)) {
            $addpage = new single_button(
                new moodle_url('/admin/tool/lp/editcompetencyframework.php', array('pagecontextid' => $this->pagecontext->id)),
                get_string('addnewcompetencyframework', 'tool_lp'),
                'get'
            );
            $this->navigation[] = $addpage;
        }

        $this->competencyframeworks = api::list_frameworks('shortname', 'ASC', 0, 0, $this->pagecontext);
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->competencyframeworks = array();
        $data->pagecontextid = $this->pagecontext->id;
        foreach ($this->competencyframeworks as $framework) {
            $exporter = new competency_framework_exporter($framework);
            $data->competencyframeworks[] = $exporter->export($output);
        }
        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(true);
        $data->navigation = array();
        foreach ($this->navigation as $button) {
            $data->navigation[] = $output->render($button);
        }

        return $data;
    }
}
