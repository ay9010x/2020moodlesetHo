<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();

use context_course;
use renderer_base;
use stdClass;
use core_competency\competency_framework;
use core_competency\external\competency_exporter;
use core_competency\external\competency_framework_exporter;


class competency_summary_exporter extends \core_competency\external\exporter {

    protected static function define_related() {
                return array('context' => '\\context',
                     'competency' => '\\core_competency\\competency',
                     'framework' => '\\core_competency\\competency_framework',
                     'linkedcourses' => '\\stdClass[]',
                     'relatedcompetencies' => '\\core_competency\\competency[]');
    }

    protected static function define_other_properties() {
        return array(
            'linkedcourses' => array(
                'type' => course_summary_exporter::read_properties_definition(),
                'multiple' => true
            ),
            'relatedcompetencies' => array(
                'type' => competency_exporter::read_properties_definition(),
                'multiple' => true
            ),
            'competency' => array(
                'type' => competency_exporter::read_properties_definition()
            ),
            'framework' => array(
                'type' => competency_framework_exporter::read_properties_definition()
            ),
            'hascourses' => array(
                'type' => PARAM_BOOL
            ),
            'hasrelatedcompetencies' => array(
                'type' => PARAM_BOOL
            ),
            'scaleid' => array(
                'type' => PARAM_INT
            ),
            'scaleconfiguration' => array(
                'type' => PARAM_RAW
            ),
            'taxonomyterm' => array(
                'type' => PARAM_TEXT
            ),
            'comppath' => array(
                'type' => competency_path_exporter::read_properties_definition(),
            )
        );
    }

    protected function get_other_values(renderer_base $output) {
        $result = new stdClass();
        $context = $this->related['context'];

        $courses = $this->related['linkedcourses'];
        $linkedcourses = array();
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            $exporter = new course_summary_exporter($course, array('context' => $context));
            $courseexport = $exporter->export($output);
            array_push($linkedcourses, $courseexport);
        }
        $result->linkedcourses = $linkedcourses;
        $result->hascourses = count($linkedcourses) > 0;

        $relatedcompetencies = array();
        foreach ($this->related['relatedcompetencies'] as $competency) {
            $exporter = new competency_exporter($competency, array('context' => $context));
            $competencyexport = $exporter->export($output);
            array_push($relatedcompetencies, $competencyexport);
        }
        $result->relatedcompetencies = $relatedcompetencies;
        $result->hasrelatedcompetencies = count($relatedcompetencies) > 0;

        $competency = $this->related['competency'];
        $exporter = new competency_exporter($competency, array('context' => $context));
        $result->competency = $exporter->export($output);

        $exporter = new competency_framework_exporter($this->related['framework']);
        $result->framework = $exporter->export($output);
        $scaleconfiguration = $this->related['framework']->get_scaleconfiguration();
        $scaleid = $this->related['framework']->get_scaleid();
        if ($competency->get_scaleid()) {
            $scaleconfiguration = $competency->get_scaleconfiguration();
            $scaleid = $competency->get_scaleid();
        }
        $result->scaleconfiguration = $scaleconfiguration;
        $result->scaleid = $scaleid;

        $level = $competency->get_level();
        $taxonomy = $this->related['framework']->get_taxonomy($level);
        $result->taxonomyterm = (string) (competency_framework::get_taxonomies_list()[$taxonomy]);

                $exporter = new competency_path_exporter([
            'ancestors' => $competency->get_ancestors(),
            'framework' => $this->related['framework'],
            'context' => $context
        ]);
        $result->comppath = $exporter->export($output);

        return (array) $result;
    }
}
