<?php



defined('MOODLE_INTERNAL') || die();


class moodle1_block_html_handler extends moodle1_block_handler {
    private $fileman = null;
    protected function convert_configdata(array $olddata) {
        $instanceid = $olddata['id'];
        $contextid  = $this->converter->get_contextid(CONTEXT_BLOCK, $olddata['id']);
        $configdata = unserialize(base64_decode($olddata['configdata']));

                $this->fileman = $this->converter->get_file_manager($contextid, 'block_html');

                $this->fileman->filearea = 'content';
        $this->fileman->itemid   = 0;
        $configdata->text = moodle1_converter::migrate_referenced_files($configdata->text, $this->fileman);
        $configdata->format = FORMAT_HTML;

        return base64_encode(serialize($configdata));
    }

    protected function write_inforef_xml($newdata, $data) {
        $this->open_xml_writer("course/blocks/{$data['name']}_{$data['id']}/inforef.xml");
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
