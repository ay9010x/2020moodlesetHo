<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_page_handler extends moodle1_resource_successor_handler {

    
    protected $fileman = null;

    
    public function process_legacy_resource(array $data, array $raw = null) {

                $instanceid = $data['id'];
        $cminfo     = $this->get_cminfo($instanceid, 'resource');
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

                $page                       = array();
        $page['id']                 = $data['id'];
        $page['name']               = $data['name'];
        $page['intro']              = $data['intro'];
        $page['introformat']        = $data['introformat'];
        $page['content']            = $data['alltext'];

        if ($data['type'] === 'html') {
                        $page['contentformat'] = FORMAT_HTML;

        } else {
                        $page['contentformat'] = (int)$data['reference'];

            if ($page['contentformat'] < 0 or $page['contentformat'] > 4) {
                $page['contentformat'] = FORMAT_MOODLE;
            }
        }

        $page['legacyfiles']        = RESOURCELIB_LEGACYFILES_ACTIVE;
        $page['legacyfileslast']    = null;
        $page['revision']           = 1;
        $page['timemodified']       = $data['timemodified'];

                $options = array('printheading' => 1, 'printintro' => 0);
        if ($data['popup']) {
            $page['display'] = RESOURCELIB_DISPLAY_POPUP;
            $rawoptions = explode(',', $data['popup']);
            foreach ($rawoptions as $rawoption) {
                list($name, $value) = explode('=', trim($rawoption), 2);
                if ($value > 0 and ($name == 'width' or $name == 'height')) {
                    $options['popup'.$name] = $value;
                    continue;
                }
            }
        } else {
            $page['display'] = RESOURCELIB_DISPLAY_OPEN;
        }
        $page['displayoptions'] = serialize($options);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_page');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $page['intro'] = moodle1_converter::migrate_referenced_files($page['intro'], $this->fileman);

                $this->fileman->filearea = 'content';
        $this->fileman->itemid   = 0;
        $page['content'] = moodle1_converter::migrate_referenced_files($page['content'], $this->fileman);

                $this->open_xml_writer("activities/page_{$moduleid}/page.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'page', 'contextid' => $contextid));
        $this->write_xml('page', $page, array('/page/id'));
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/page_{$moduleid}/inforef.xml");
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
