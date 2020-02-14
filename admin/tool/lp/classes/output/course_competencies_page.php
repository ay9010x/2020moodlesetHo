<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use context_system;
use context_course;
use core_competency\api;
use tool_lp\course_competency_statistics;
use core_competency\competency;
use core_competency\course_competency;
use core_competency\external\competency_exporter;
use core_competency\external\course_competency_exporter;
use core_competency\external\course_competency_settings_exporter;
use core_competency\external\user_competency_course_exporter;
use core_competency\external\user_competency_exporter;
use tool_lp\external\competency_path_exporter;
use tool_lp\external\course_competency_statistics_exporter;
use tool_lp\external\course_module_summary_exporter;


class course_competencies_page implements renderable, templatable {

    
    protected $courseid = null;

    
    protected $context = null;

    
    protected $coursecompetencylist = array();

    
    protected $canmanagecompetencyframeworks = false;

    
    protected $canmanagecoursecompetencies = false;

    
    protected $manageurl = null;

    
    public function __construct($courseid) {
        $this->context = context_course::instance($courseid);
        $this->courseid = $courseid;
        $this->coursecompetencylist = api::list_course_competencies($courseid);
        $this->canmanagecoursecompetencies = has_capability('moodle/competency:coursecompetencymanage', $this->context);
        $this->canconfigurecoursecompetencies = has_capability('moodle/competency:coursecompetencyconfigure', $this->context);
        $this->cangradecompetencies = has_capability('moodle/competency:competencygrade', $this->context);
        $this->coursecompetencysettings = api::read_course_competency_settings($courseid);
        $this->coursecompetencystatistics = new course_competency_statistics($courseid);

                $this->manageurl = null;
        $this->canmanagecompetencyframeworks = false;
        $contexts = array_reverse($this->context->get_parent_contexts(true));
        foreach ($contexts as $context) {
            $canmanage = has_capability('moodle/competency:competencymanage', $context);
            if ($canmanage) {
                $this->manageurl = new moodle_url('/admin/tool/lp/competencyframeworks.php',
                    array('pagecontextid' => $context->id));
                $this->canmanagecompetencyframeworks = true;
                break;
            }
        }
    }

    
    public function export_for_template(renderer_base $output) {
        global $USER;

        $data = new stdClass();
        $data->courseid = $this->courseid;
        $data->pagecontextid = $this->context->id;
        $data->competencies = array();
        $contextcache = array();

        $gradable = is_enrolled($this->context, $USER, 'moodle/competency:coursecompetencygradable');
        if ($gradable) {
            $usercompetencycourses = api::list_user_competencies_in_course($this->courseid, $USER->id);
            $data->gradableuserid = $USER->id;
        }

        $ruleoutcomelist = course_competency::get_ruleoutcome_list();
        $ruleoutcomeoptions = array();
        foreach ($ruleoutcomelist as $value => $text) {
            $ruleoutcomeoptions[$value] = array('value' => $value, 'text' => (string) $text, 'selected' => false);
        }

        foreach ($this->coursecompetencylist as $coursecompetencyelement) {
            $coursecompetency = $coursecompetencyelement['coursecompetency'];
            $competency = $coursecompetencyelement['competency'];
            if (!isset($contextcache[$competency->get_competencyframeworkid()])) {
                $contextcache[$competency->get_competencyframeworkid()] = $competency->get_context();
            }
            $context = $contextcache[$competency->get_competencyframeworkid()];

            $compexporter = new competency_exporter($competency, array('context' => $context));
            $ccexporter = new course_competency_exporter($coursecompetency, array('context' => $context));

            $ccoutcomeoptions = (array) (object) $ruleoutcomeoptions;
            $ccoutcomeoptions[$coursecompetency->get_ruleoutcome()]['selected'] = true;

            $coursemodules = api::list_course_modules_using_competency($competency->get_id(), $this->courseid);

            $fastmodinfo = get_fast_modinfo($this->courseid);
            $exportedmodules = array();
            foreach ($coursemodules as $cmid) {
                $cminfo = $fastmodinfo->cms[$cmid];
                $cmexporter = new course_module_summary_exporter(null, array('cm' => $cminfo));
                $exportedmodules[] = $cmexporter->export($output);
            }
                        $pathexporter = new competency_path_exporter([
                'ancestors' => $competency->get_ancestors(),
                'framework' => $competency->get_framework(),
                'context' => $context
            ]);

            $onerow = array(
                'competency' => $compexporter->export($output),
                'coursecompetency' => $ccexporter->export($output),
                'ruleoutcomeoptions' => $ccoutcomeoptions,
                'coursemodules' => $exportedmodules,
                'comppath' => $pathexporter->export($output)
            );
            if ($gradable) {
                $foundusercompetencycourse = false;
                foreach ($usercompetencycourses as $usercompetencycourse) {
                    if ($usercompetencycourse->get_competencyid() == $competency->get_id()) {
                        $foundusercompetencycourse = $usercompetencycourse;
                    }
                }
                if ($foundusercompetencycourse) {
                    $related = array(
                        'scale' => $competency->get_scale()
                    );
                    $exporter = new user_competency_course_exporter($foundusercompetencycourse, $related);
                    $onerow['usercompetencycourse'] = $exporter->export($output);
                }
            }
            array_push($data->competencies, $onerow);
        }

        $data->canmanagecompetencyframeworks = $this->canmanagecompetencyframeworks;
        $data->canmanagecoursecompetencies = $this->canmanagecoursecompetencies;
        $data->canconfigurecoursecompetencies = $this->canconfigurecoursecompetencies;
        $data->cangradecompetencies = $this->cangradecompetencies;
        $exporter = new course_competency_settings_exporter($this->coursecompetencysettings);
        $data->settings = $exporter->export($output);
        $related = array('context' => $this->context);
        $exporter = new course_competency_statistics_exporter($this->coursecompetencystatistics, $related);
        $data->statistics = $exporter->export($output);
        $data->manageurl = null;
        if ($this->canmanagecompetencyframeworks) {
            $data->manageurl = $this->manageurl->out(true);
        }

        return $data;
    }

}
