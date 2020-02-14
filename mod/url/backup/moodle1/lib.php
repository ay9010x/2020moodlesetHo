<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_url_handler extends moodle1_resource_successor_handler {

    
    protected $fileman = null;

    
    public function process_legacy_resource(array $data, array $raw = null) {

                $instanceid = $data['id'];
        $cminfo     = $this->get_cminfo($instanceid, 'resource');
        $moduleid   = $cminfo['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

                $url                 = array();
        $url['id']           = $data['id'];
        $url['name']         = $data['name'];
        $url['intro']        = $data['intro'];
        $url['introformat']  = $data['introformat'];
        $url['externalurl']  = $data['reference'];
        $url['timemodified'] = $data['timemodified'];

                $options = array('printintro' => 1);
        if ($data['options'] == 'frame') {
            $url['display'] = RESOURCELIB_DISPLAY_FRAME;

        } else if ($data['options'] == 'objectframe') {
            $url['display'] = RESOURCELIB_DISPLAY_EMBED;

        } else if ($data['popup']) {
            $url['display'] = RESOURCELIB_DISPLAY_POPUP;
            $rawoptions = explode(',', $data['popup']);
            foreach ($rawoptions as $rawoption) {
                list($name, $value) = explode('=', trim($rawoption), 2);
                if ($value > 0 and ($name == 'width' or $name == 'height')) {
                    $options['popup'.$name] = $value;
                    continue;
                }
            }

        } else {
            $url['display'] = RESOURCELIB_DISPLAY_AUTO;
        }
        $url['displayoptions'] = serialize($options);

                $parameters = array();
        if ($data['alltext']) {
            $rawoptions = explode(',', $data['alltext']);
            foreach ($rawoptions as $rawoption) {
                list($variable, $parameter) = explode('=', trim($rawoption), 2);
                $parameters[$parameter] = $variable;
            }
        }
        $url['parameters'] = serialize($parameters);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_url', 'intro');
        $url['intro'] = moodle1_converter::migrate_referenced_files($url['intro'], $this->fileman);

                $this->open_xml_writer("activities/url_{$moduleid}/url.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'url', 'contextid' => $contextid));
        $this->write_xml('url', $url, array('/url/id'));
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/url_{$moduleid}/inforef.xml");
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
