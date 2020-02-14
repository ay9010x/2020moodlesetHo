<?php



defined('MOODLE_INTERNAL') || die;


class report_loglive_table_log_ajax extends report_loglive_table_log {

    
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {
        $this->query_db($pagesize, false);
        $html = '';
        $until = time();
        if ($this->rawdata && $this->columns) {
            foreach ($this->rawdata as $row) {
                $formatedrow = $this->format_row($row, "newrow time$until");
                $formatedrow = $this->get_row_from_keyed($formatedrow);
                $html .= $this->get_row_html($formatedrow, "newrow time$until");
            }
        }
        $result = array('logs' => $html, 'until' => $until);
        return json_encode($result);
    }
}
