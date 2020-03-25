<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use core_user;
use renderer_base;
use renderable;
use templatable;
use core_competency\api;
use core_competency\user_competency;
use tool_lp\external\user_competency_summary_exporter;


class user_competency_summary implements renderable, templatable {

    
    protected $usercompetency;
    
    protected $related;

    
    public function __construct(user_competency $usercompetency, array $related = array()) {
        $this->usercompetency = $usercompetency;
        $this->related = $related;
    }

    
    public function export_for_template(renderer_base $output) {
        if (!isset($related['user'])) {
            $related['user'] = core_user::get_user($this->usercompetency->get_userid());
        }
        if (!isset($related['competency'])) {
            $related['competency'] = $this->usercompetency->get_competency();
        }

        $related += array(
            'usercompetency' => $this->usercompetency,
            'usercompetencyplan' => null,
            'usercompetencycourse' => null,
            'evidence' => api::list_evidence($this->usercompetency->get_userid(), $this->usercompetency->get_competencyid()),
            'relatedcompetencies' => api::list_related_competencies($this->usercompetency->get_competencyid())
        );
        $exporter = new user_competency_summary_exporter(null, $related);
        $data = $exporter->export($output);

        return $data;
    }
}
