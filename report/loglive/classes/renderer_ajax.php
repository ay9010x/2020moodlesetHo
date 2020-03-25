<?php



class report_loglive_renderer_ajax extends plugin_renderer_base {

    
    public function render_report_loglive_renderable(report_loglive_renderable $reportloglive) {
        debugging('Do not call this method. Please call $renderer->render($reportloglive) instead.', DEBUG_DEVELOPER);
        return $this->render($reportloglive);
    }

    
    protected function render_report_loglive(report_loglive_renderable $reportloglive) {
        if (empty($reportloglive->selectedlogreader)) {
            return null;
        }
        $table = $reportloglive->get_table(true);
        return $table->out($reportloglive->perpage, false);
    }
}
