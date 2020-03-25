<?php



namespace tool_lp\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;


class renderer extends plugin_renderer_base {

    
    public function render_manage_competency_frameworks_page(manage_competency_frameworks_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/manage_competency_frameworks_page', $data);
    }

    
    public function render_manage_competencies_page(manage_competencies_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/manage_competencies_page', $data);
    }

    
    public function render_course_competencies_page(course_competencies_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/course_competencies_page', $data);
    }

    
    public function render_template_competencies_page(template_competencies_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/template_competencies_page', $data);
    }

    
    public function render_manage_templates_page(manage_templates_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/manage_templates_page', $data);
    }

    
    public function render_plan_page(plan_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/plan_page', $data);
    }

    
    public function render_plans_page(plans_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/plans_page', $data);
    }

    
    public function render_related_competencies_section(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/related_competencies', $data);
    }

    
    public function render_user_competency_summary_in_course(user_competency_summary_in_course $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/user_competency_summary_in_course', $data);
    }

    
    public function render_user_competency_summary_in_plan(user_competency_summary_in_plan $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/user_competency_summary_in_plan', $data);
    }

    
    public function render_template_plans_page(renderable $page) {
        return $page->table->out(50, true);
    }

    
    public function render_template_cohorts_page(renderable $page) {
        return $page->table->out(50, true);
    }

    
    public function render_user_evidence_page(user_evidence_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/user_evidence_page', $data);
    }

    
    public function render_user_evidence_list_page(user_evidence_list_page $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/user_evidence_list_page', $data);
    }

    
    public function render_user_competency_course_navigation(user_competency_course_navigation $nav) {
        $data = $nav->export_for_template($this);
        return parent::render_from_template('tool_lp/user_competency_course_navigation', $data);
    }

    
    public function render_competency_plan_navigation(competency_plan_navigation $nav) {
        $data = $nav->export_for_template($this);
        return parent::render_from_template('tool_lp/competency_plan_navigation', $data);
    }

    
    public function render_user_competency_summary(user_competency_summary $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('tool_lp/user_competency_summary', $data);
    }

    
    public function notify_message($message) {
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_INFO);
        return $this->render($n);
    }

    
    public function notify_problem($message) {
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_ERROR);
        return $this->render($n);
    }

    
    public function notify_success($message) {
        $n = new \core\output\notification($message, \core\output\notification::NOTIFY_SUCCESS);
        return $this->render($n);
    }
}
