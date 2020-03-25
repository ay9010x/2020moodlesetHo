<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_wiki_handler extends moodle1_mod_handler {

    
    protected $initialcontent;

    
    protected $initialcontentfilename;

    
    protected $needinitpage = false;

    
    protected $databuf = array();

    
    protected $fileman = null;

    
    protected $moduleid = null;

    
    public function get_paths() {
        return array(
            new convert_path(
                'wiki', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WIKI',
                array(
                    'newfields' => array(
                        'introformat' => '0',
                        'defaultformat' => 'html',                         'forceformat' => '1',
                        'editbegin' => '0',
                        'editend' => '0',
                        'timecreated' => time(),                     ),
                    'renamefields' => array(
                        'summary' => 'intro',
                        'format' => 'introformat',
                        'firstpagetitle' => 'pagename',
                        'wtype' => 'wikimode'
                    ),
                    'dropfields' => array(
                        'pagename', 'scaleid', 'ewikiprinttitle', 'htmlmode', 'ewikiacceptbinary', 'disablecamelcase',
                        'setpageflags', 'strippages', 'removepages', 'revertchanges'
                    )
                )
            ),
            new convert_path(
                'wiki_entries', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WIKI/ENTRIES',
                array(
                    'newfields' => array(
                        'synonyms' => '0',
                        'links' => 'collaborative',
                    ),
                    'dropfields' => array(
                        'pagename' ,'timemodified'
                    )
                )
            ),
            new convert_path(
                'wiki_entry', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WIKI/ENTRIES/ENTRY'
            ),
            new convert_path(
                'wiki_pages', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WIKI/ENTRIES/ENTRY/PAGES'
            ),
            new convert_path(
                'wiki_entry_page', '/MOODLE_BACKUP/COURSE/MODULES/MOD/WIKI/ENTRIES/ENTRY/PAGES/PAGE',
                array(
                    'newfields' => array(
                        'cachedcontent' => '**reparse needed**',
                        'timerendered' => '0',
                        'readonly' => '0',
                        'tags' => ''
                    ),
                    'renamefields' => array(
                        'pagename' => 'title',
                        'created' => 'timecreated',
                        'lastmodified' => 'timemodified',
                        'hits' => 'pageviews'
                    ),
                    'dropfields' => array(
                        'version', 'flags', 'author', 'refs',                         'meta'
                    )
                )
            )
        );
    }

    
    public function process_wiki($data) {
        global $CFG;    
        if (!empty($data['initialcontent'])) {
                        $temppath = $this->converter->get_tempdir_path();
            $this->initialcontent = file_get_contents($temppath.'/course_files/'.$data['initialcontent']);
            $this->initialcontentfilename = $data['initialcontent'];
            $this->needinitpage = true;
        }
        unset($data['initialcontent']);
        if ($data['wikimode'] !== 'group') {
            $data['wikimode'] = 'individual';
                                } else {
            $data['wikimode'] = 'collaborative';
        }

        if (empty($data['name'])) {
            $data['name'] = 'Wiki';
        }
                $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_wiki');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

                if ($CFG->texteditors !== 'textarea') {
            $data['intro'] = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                $this->open_xml_writer("activities/wiki_{$this->moduleid}/wiki.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'wiki', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('wiki', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }

        return $data;
    }

    public function on_wiki_entries_start() {
        $this->xmlwriter->begin_tag('subwikis');
        $this->needinitpage = false;     }

    public function on_wiki_entries_end() {
        $this->xmlwriter->end_tag('subwikis');
    }

    public function process_wiki_entry($data) {
        $this->xmlwriter->begin_tag('subwiki', array('id' => $data['id']));
        unset($data['id']);

        unset($data['pagename']);
        unset($data['timemodified']);

        foreach ($data as $field => $value) {
            $this->xmlwriter->full_tag($field, $value);
        }
    }

    public function on_wiki_entry_end() {
        $this->xmlwriter->end_tag('subwiki');
    }

    public function on_wiki_pages_start() {
        $this->xmlwriter->begin_tag('pages');
    }

    public function on_wiki_pages_end() {
        $this->xmlwriter->end_tag('pages');
    }

    public function process_wiki_entry_page($data) {
                $this->databuf['id'] = $this->converter->get_nextid();
        $this->databuf['content'] = $data['content'];
        unset($data['content']);
        $this->databuf['contentformat'] = 'html';
        $this->databuf['version'] = 0;
        $this->databuf['timecreated'] = $data['timecreated'];         $this->databuf['userid'] = $data['userid']; 
                $this->xmlwriter->begin_tag('page', array('id' => $data['id']));
        unset($data['id']);         foreach ($data as $field => $value) {
            $this->xmlwriter->full_tag($field, $value);
        }

                $this->xmlwriter->begin_tag('versions');
        $this->write_xml('version', $this->databuf, array('/version/id'));         $this->xmlwriter->end_tag('versions');
    }
    public function on_wiki_entry_page_end() {
        $this->xmlwriter->end_tag('page');
    }

    
    public function on_wiki_end() {
        global $USER;

                if ($this->initialcontentfilename && $this->needinitpage) {
                        $data_entry = array(
                'id'        => $this->converter->get_nextid(),                 'groupid'   => 0,
                'userid'    => 0,
                'synonyms'  => '',
                'links'     => ''
            );
            $data_page = array(
                'id'            => $this->converter->get_nextid(),                 'title'         => $this->initialcontentfilename,
                'content'       => $this->initialcontent,
                'userid'        => $USER->id,
                'timecreated'   => time(),
                'timemodified'  => 0,
                'pageviews'     => 0,
                'cachedcontent' => '**reparse needed**',
                'timerendered'  => 0,
                'readonly'      => 0,
                'tags'          => ''
            );
                        $this->on_wiki_entries_start();
            $this->process_wiki_entry($data_entry);
            $this->on_wiki_pages_start();
            $this->process_wiki_entry_page($data_page);
            $this->on_wiki_entry_page_end();
            $this->on_wiki_pages_end();
            $this->on_wiki_entry_end();
            $this->on_wiki_entries_end();
        }

                $this->xmlwriter->end_tag('wiki');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/wiki_{$this->moduleid}/inforef.xml");
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
