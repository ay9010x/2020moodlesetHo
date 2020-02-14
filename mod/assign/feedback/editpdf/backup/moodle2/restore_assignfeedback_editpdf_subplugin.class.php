<?php


defined('MOODLE_INTERNAL') || die();


class restore_assignfeedback_editpdf_subplugin extends restore_subplugin {

    
    protected function define_grade_subplugin_structure() {

        $paths = array();

                        $elename = $this->get_namefor('files');
        $elepath = $this->get_pathfor('/feedback_editpdf_files');
        $paths[] = new restore_path_element($elename, $elepath);

                $elename = $this->get_namefor('comment');
        $elepath = $this->get_pathfor('/feedback_editpdf_comments/comment');
        $paths[] = new restore_path_element($elename, $elepath);
        $elename = $this->get_namefor('annotation');
        $elepath = $this->get_pathfor('/feedback_editpdf_annotations/annotation');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    
    public function process_assignfeedback_editpdf_files($data) {
        $data = (object)$data;

                $this->add_related_files('assignfeedback_editpdf',
            \assignfeedback_editpdf\document_services::FINAL_PDF_FILEAREA, 'grade', null, $data->gradeid);
        $this->add_related_files('assignfeedback_editpdf',
            \assignfeedback_editpdf\document_services::PAGE_IMAGE_READONLY_FILEAREA, 'grade', null, $data->gradeid);
        $this->add_related_files('assignfeedback_editpdf', 'stamps', 'grade', null, $data->gradeid);
    }

    
    public function process_assignfeedback_editpdf_annotation($data) {
        global $DB;

        $data = (object)$data;
        $oldgradeid = $data->gradeid;
                        $data->gradeid = $this->get_mappingid('grade', $data->gradeid);

        $DB->insert_record('assignfeedback_editpdf_annot', $data);

    }

    
    public function process_assignfeedback_editpdf_comment($data) {
        global $DB;

        $data = (object)$data;
        $oldgradeid = $data->gradeid;
                        $data->gradeid = $this->get_mappingid('grade', $data->gradeid);

        $DB->insert_record('assignfeedback_editpdf_cmnt', $data);

    }

}
