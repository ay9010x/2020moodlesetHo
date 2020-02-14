<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use single_button;
use stdClass;
use moodle_url;
use context_system;
use core_competency\api;
use core_competency\competency;
use core_competency\competency_framework;
use core_competency\external\competency_framework_exporter;


class manage_competencies_page implements renderable, templatable {

    
    protected $framework = null;

    
    protected $competencies = array();

    
    protected $search = '';

    
    protected $canmanage = false;

    
    protected $pluginbaseurl = null;

    
    protected $pagecontext = null;

    
    public function __construct($framework, $search, $pagecontext) {
        $this->framework = $framework;
        $this->pagecontext = $pagecontext;
        $this->search = $search;
        $addpage = new single_button(
           new moodle_url('/admin/tool/lp/editcompetencyframework.php'),
           get_string('addnewcompetency', 'tool_lp')
        );
        $this->navigation[] = $addpage;

        $this->canmanage = has_capability('moodle/competency:competencymanage', $framework->get_context());
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $exporter = new competency_framework_exporter($this->framework);
        $data->framework = $exporter->export($output);
        $data->canmanage = $this->canmanage;
        $data->search = $this->search;
        $data->pagecontextid = $this->pagecontext->id;
        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(true);

        $rulesmodules = array();
        $rules = competency::get_available_rules();
        foreach ($rules as $type => $rulename) {

            $amd = null;
            if ($type == 'core_competency\\competency_rule_all') {
                $amd = 'tool_lp/competency_rule_all';
            } else if ($type == 'core_competency\\competency_rule_points') {
                $amd = 'tool_lp/competency_rule_points';
            } else {
                                continue;
            }

            $rulesmodules[] = [
                'name' => (string) $rulename,
                'type' => $type,
                'amd' => $amd,
            ];
        }
        $data->rulesmodules = json_encode(array_values($rulesmodules));

        return $data;
    }
}
