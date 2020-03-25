<?php



namespace block_lp\output;
defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;


class renderer extends plugin_renderer_base {

    
    public function render_competencies_to_review_page(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_lp/competencies_to_review_page', $data);
    }

    
    public function render_plans_to_review_page(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('block_lp/plans_to_review_page', $data);
    }

    
    public function render_summary(renderable $summary) {
        $data = $summary->export_for_template($this);
        return parent::render_from_template('block_lp/summary', $data);
    }

}
