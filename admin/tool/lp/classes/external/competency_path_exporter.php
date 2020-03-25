<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;


class competency_path_exporter extends \core_competency\external\exporter {

    
    public function __construct($related) {
        parent::__construct([], $related);
    }

    
    protected static function define_related() {
        return [
            'ancestors' => 'core_competency\\competency[]',
            'framework' => 'core_competency\\competency_framework',
            'context' => 'context'
        ];
    }

    
    protected static function define_other_properties() {
        return [
            'ancestors' => [
                'type' => path_node_exporter::read_properties_definition(),
                'multiple' => true,
            ],
            'framework' => [
                'type' => path_node_exporter::read_properties_definition()
            ],
            'pluginbaseurl' => [
                'type' => PARAM_TEXT
            ],
            'pagecontextid' => [
                'type' => PARAM_INT
            ]
        ];
    }

    
    protected function get_other_values(renderer_base $output) {
        $result = new \stdClass();
        $ancestors = [];
        $nodescount = count($this->related['ancestors']);
        $i = 1;
        foreach ($this->related['ancestors'] as $competency) {
            $exporter = new path_node_exporter([
                    'id' => $competency->get_id(),
                    'name' => $competency->get_idnumber(),
                    'position' => $i,
                    'first' => $i == 1,
                    'last' => $i == $nodescount
                ], [
                    'context' => $this->related['context'],
                ]
            );
            $ancestors[] = $exporter->export($output);
            $i++;
        }
        $result->ancestors = $ancestors;
        $exporter = new path_node_exporter([
                'id' => $this->related['framework']->get_id(),
                'name' => $this->related['framework']->get_shortname(),
                'first' => 0,
                'last' => 0,
                'position' => -1
            ], [
                'context' => $this->related['context']
            ]
        );
        $result->framework = $exporter->export($output);
        $result->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(true);
        $result->pagecontextid = $this->related['context']->id;
        return (array) $result;
    }
}
