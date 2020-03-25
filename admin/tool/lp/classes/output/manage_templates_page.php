<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use context;
use renderable;
use templatable;
use renderer_base;
use single_button;
use stdClass;
use moodle_url;
use context_system;
use core_competency\api;
use core_competency\template;
use core_competency\external\template_exporter;


class manage_templates_page implements renderable, templatable {

    
    protected $pagecontext;

    
    protected $navigation = array();

    
    protected $templates = array();

    
    public function __construct(context $pagecontext) {
        $this->pagecontext = $pagecontext;

        if (template::can_manage_context($this->pagecontext)) {
            $addpage = new single_button(
               new moodle_url('/admin/tool/lp/edittemplate.php', array('pagecontextid' => $this->pagecontext->id)),
               get_string('addnewtemplate', 'tool_lp'),
               'get'
            );
            $this->navigation[] = $addpage;
        }

        $this->templates = api::list_templates('shortname', 'ASC', 0, 0, $this->pagecontext);
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->pagecontextid = $this->pagecontext->id;
        $data->templates = array();
        foreach ($this->templates as $template) {
            $exporter = new template_exporter($template);
            $data->templates[] = $exporter->export($output);
        }
        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(true);
        $data->navigation = array();
        foreach ($this->navigation as $button) {
            $data->navigation[] = $output->render($button);
        }
        $data->canmanage = template::can_manage_context($this->pagecontext);

        return $data;
    }
}
