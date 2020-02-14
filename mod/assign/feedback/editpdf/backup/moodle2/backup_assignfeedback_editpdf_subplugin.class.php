<?php


defined('MOODLE_INTERNAL') || die();


class backup_assignfeedback_editpdf_subplugin extends backup_subplugin {

    
    protected function define_grade_subplugin_structure() {

                $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subpluginelementfiles = new backup_nested_element('feedback_editpdf_files', null, array('gradeid'));
        $subpluginelementannotations = new backup_nested_element('feedback_editpdf_annotations');
        $subpluginelementannotation = new backup_nested_element('annotation', null, array('gradeid', 'pageno', 'type', 'x', 'y', 'endx', 'endy', 'colour', 'path', 'draft'));
        $subpluginelementcomments = new backup_nested_element('feedback_editpdf_comments');
        $subpluginelementcomment = new backup_nested_element('comment', null, array('gradeid', 'pageno', 'x', 'y', 'width', 'rawtext', 'colour', 'draft'));

                $subplugin->add_child($subpluginwrapper);
        $subpluginelementannotations->add_child($subpluginelementannotation);
        $subpluginelementcomments->add_child($subpluginelementcomment);
        $subpluginwrapper->add_child($subpluginelementfiles);
        $subpluginwrapper->add_child($subpluginelementannotations);
        $subpluginwrapper->add_child($subpluginelementcomments);

                $subpluginelementfiles->set_source_sql('SELECT id AS gradeid from {assign_grades} where id = :gradeid', array('gradeid' => backup::VAR_PARENTID));
        $subpluginelementannotation->set_source_table('assignfeedback_editpdf_annot', array('gradeid' => backup::VAR_PARENTID));
        $subpluginelementcomment->set_source_table('assignfeedback_editpdf_cmnt', array('gradeid' => backup::VAR_PARENTID));
                $subpluginelementfiles->annotate_files('assignfeedback_editpdf',
            \assignfeedback_editpdf\document_services::FINAL_PDF_FILEAREA, 'gradeid');
        $subpluginelementfiles->annotate_files('assignfeedback_editpdf',
            \assignfeedback_editpdf\document_services::PAGE_IMAGE_READONLY_FILEAREA, 'gradeid');
        $subpluginelementfiles->annotate_files('assignfeedback_editpdf', 'stamps', 'gradeid');
        return $subplugin;
    }

}
