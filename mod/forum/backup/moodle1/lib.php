<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_forum_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path('forum', '/MOODLE_BACKUP/COURSE/MODULES/MOD/FORUM',
                array(
                    'renamefields' => array(
                        'format' => 'messageformat',
                    ),
                    'newfields' => array(
                        'completiondiscussions' => 0,
                        'completionreplies' => 0,
                        'completionpost' => 0,
                        'maxattachments' => 1,
                        'introformat' => 0,
                    ),
                )
            ),
        );
    }

    
    public function process_forum($data) {
        global $CFG;

                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_forum');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                if ($CFG->texteditors !== 'textarea') {
            $data['intro'] = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $this->open_xml_writer("activities/forum_{$this->moduleid}/forum.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'forum', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('forum', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        $this->xmlwriter->begin_tag('discussions');

        return $data;
    }

    
    public function on_forum_end() {
                $this->xmlwriter->end_tag('discussions');
        $this->xmlwriter->end_tag('forum');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/forum_{$this->moduleid}/inforef.xml");
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
