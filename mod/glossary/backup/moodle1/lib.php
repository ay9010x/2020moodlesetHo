<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_glossary_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path(
                'glossary', '/MOODLE_BACKUP/COURSE/MODULES/MOD/GLOSSARY',
                array(
                    'newfields' => array(
                        'introformat'       => FORMAT_MOODLE,
                        'completionentries' => 0,
                    ),
                )
            ),
            new convert_path('glossary_categories', '/MOODLE_BACKUP/COURSE/MODULES/MOD/GLOSSARY/CATEGORIES'),
            new convert_path(
                'glossary_category', '/MOODLE_BACKUP/COURSE/MODULES/MOD/GLOSSARY/CATEGORIES/CATEGORY',
                array(
                    'dropfields' => array(
                        'glossaryid'
                    )
                )
            )
        );
    }

    
    public function process_glossary($data) {
        global $CFG;

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                if ($CFG->texteditors !== 'textarea') {
            $data['intro']       = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_glossary');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                $this->open_xml_writer("activities/glossary_{$this->moduleid}/glossary.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'glossary', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('glossary', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $data;
    }

    
    public function on_glossary_categories_start() {
        $this->xmlwriter->begin_tag('categories');
    }

    
    public function process_glossary_category($data) {
        $this->write_xml('category', $data, array('/category/id'));
    }

    
    public function on_glossary_categories_end() {
        $this->xmlwriter->end_tag('categories');
    }

    
    public function on_glossary_end() {
                $this->xmlwriter->end_tag('glossary');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/glossary_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }
}
