<?php



defined('MOODLE_INTERNAL') || die();


class gradeimport_csv_renderer extends plugin_renderer_base {

    
    public function standard_upload_file_form($course, $mform) {

        $output = groups_print_course_menu($course, 'index.php?id=' . $course->id, true);
        $output .= html_writer::start_tag('div', array('class' => 'clearer'));
        $output .= html_writer::end_tag('div');

                ob_start();
        $mform->display();
        $output .= ob_get_contents();
        ob_end_clean();

        return $output;
    }

    
    public function import_preview_page($header, $data) {

        $html = $this->output->heading(get_string('importpreview', 'grades'));

        $table = new html_table();
        $table->head = $header;
        $table->data = $data;
        $html .= html_writer::table($table);

        return $html;
    }

    
    public function errors($errors) {
        $html = '';
        foreach ($errors as $error) {
            $html .= $this->output->notification($error);
        }
        return $html;
    }
}
