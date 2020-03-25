<?php



defined('MOODLE_INTERNAL') || die();


class moodle1_mod_book_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path('book', '/MOODLE_BACKUP/COURSE/MODULES/MOD/BOOK',
                    array(
                        'renamefields' => array(
                            'summary' => 'intro',
                        ),
                        'newfields' => array(
                            'introformat' => FORMAT_MOODLE,
                        ),
                        'dropfields' => array(
                            'disableprinting'
                        ),
                    )
                ),
            new convert_path('book_chapters', '/MOODLE_BACKUP/COURSE/MODULES/MOD/BOOK/CHAPTERS/CHAPTER',
                    array(
                        'newfields' => array(
                            'contentformat' => FORMAT_HTML,
                        ),
                    )
                ),
        );
    }

    
    public function process_book($data) {
        global $CFG;

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                if ($CFG->texteditors !== 'textarea') {
            $data['intro']       = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_book');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                $this->open_xml_writer("activities/book_{$this->moduleid}/book.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'book', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('book', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }
    }

    
    public function process_book_chapters($data) {
                $this->fileman->filearea = 'chapter';
        $this->fileman->itemid   = $data['id'];
        $data['content'] = moodle1_converter::migrate_referenced_files($data['content'], $this->fileman);

        $this->write_xml('chapter', $data, array('/chapter/id'));
    }

    
    public function on_book_chapters_start() {
        $this->xmlwriter->begin_tag('chapters');
    }

    
    public function on_book_chapters_end() {
        $this->xmlwriter->end_tag('chapters');
    }

    
    public function on_book_end() {
                $this->xmlwriter->end_tag('book');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/book_{$this->moduleid}/inforef.xml");
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
