<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use context;
use context_system;
use moodle_url;
use core_competency\external\template_exporter;
use core_competency\template;
use core_competency\api;
use tool_lp\external\competency_summary_exporter;
use tool_lp\external\template_statistics_exporter;
use tool_lp\template_statistics;


class template_competencies_page implements renderable, templatable {

    
    protected $template = null;

    
    protected $competencies = array();

    
    protected $canmanagecompetencyframeworks = false;

    
    protected $canmanagecoursecompetencies = false;

    
    protected $manageurl = null;

    
    protected $pagecontext = null;

    
    protected $templatestatistics = null;

    
    public function __construct(template $template, context $pagecontext) {
        $this->pagecontext = $pagecontext;
        $this->template = $template;
        $this->templatestatistics = new template_statistics($template->get_id());
        $this->competencies = api::list_competencies_in_template($template);
        $this->canmanagecompetencyframeworks = has_capability('moodle/competency:competencymanage', $this->pagecontext);
        $this->canmanagetemplatecompetencies = has_capability('moodle/competency:templatemanage', $this->pagecontext);
        $this->manageurl = new moodle_url('/admin/tool/lp/competencyframeworks.php',
            array('pagecontextid' => $this->pagecontext->id));
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->template = (new template_exporter($this->template))->export($output);
        $data->pagecontextid = $this->pagecontext->id;
        $data->competencies = array();
        $contextcache = array();
        $frameworkcache = array();
        foreach ($this->competencies as $competency) {
            if (!isset($contextcache[$competency->get_competencyframeworkid()])) {
                $contextcache[$competency->get_competencyframeworkid()] = $competency->get_context();
            }
            $context = $contextcache[$competency->get_competencyframeworkid()];
            if (!isset($frameworkcache[$competency->get_competencyframeworkid()])) {
                $frameworkcache[$competency->get_competencyframeworkid()] = $competency->get_framework();
            }
            $framework = $frameworkcache[$competency->get_competencyframeworkid()];

            $courses = api::list_courses_using_competency($competency->get_id());
            $relatedcompetencies = api::list_related_competencies($competency->get_id());

            $related = array(
                'competency' => $competency,
                'linkedcourses' => $courses,
                'context' => $context,
                'relatedcompetencies' => $relatedcompetencies,
                'framework' => $framework
            );
            $exporter = new competency_summary_exporter(null, $related);
            $record = $exporter->export($output);

            array_push($data->competencies, $record);
        }

        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(false);
        $data->canmanagecompetencyframeworks = $this->canmanagecompetencyframeworks;
        $data->canmanagetemplatecompetencies = $this->canmanagetemplatecompetencies;
        $data->manageurl = $this->manageurl->out(true);
        $exporter = new template_statistics_exporter($this->templatestatistics);
        $data->statistics = $exporter->export($output);
        $data->showcompetencylinks = true;

        return $data;
    }
}
