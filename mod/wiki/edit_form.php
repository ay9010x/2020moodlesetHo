<?php




if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->dirroot . '/mod/wiki/editors/wikieditor.php');

class mod_wiki_edit_form extends moodleform {

    protected function definition() {
        global $CFG;

        $mform = $this->_form;
                        $mform->updateAttributes(array('id' => 'mform1'));

        $version = $this->_customdata['version'];
        $format = $this->_customdata['format'];

        if (empty($this->_customdata['contextid'])) {
                                                debugging('You must always provide mod_wiki_edit_form with a contextid in its custom data', DEBUG_DEVELOPER);
            global $PAGE;
            $contextid = $PAGE->context->id;
        } else {
            $contextid = $this->_customdata['contextid'];
        }

        if (isset($this->_customdata['pagetitle'])) {
                        $pagetitle = get_string('editingpage', 'wiki', format_string($this->_customdata['pagetitle'], true, array('context' => context::instance_by_id($contextid, MUST_EXIST))));
        } else {
            $pagetitle = get_string('editing', 'wiki');
        }

                $mform->addElement('header', 'general', $pagetitle);

        $fieldname = get_string('format' . $format, 'wiki');
        if ($format != 'html') {
                        $extensions = file_get_typegroup('extension', 'web_image');
            $fs = get_file_storage();
            $tree = $fs->get_area_tree($contextid, 'mod_wiki', $this->_customdata['filearea'], $this->_customdata['fileitemid']);
            $files = array();
            foreach ($tree['files'] as $file) {
                $filename = $file->get_filename();
                foreach ($extensions as $ext) {
                    if (preg_match('#'.$ext.'$#i', $filename)) {
                        $files[] = $filename;
                    }
                }
            }
            $mform->addElement('wikieditor', 'newcontent', $fieldname, array('cols' => 100, 'rows' => 20, 'wiki_format' => $format, 'files'=>$files));
            $mform->addHelpButton('newcontent', 'format'.$format, 'wiki');
            $mform->setType('newcontent', PARAM_RAW);         } else {
            $mform->addElement('editor', 'newcontent_editor', $fieldname, null, page_wiki_edit::$attachmentoptions);
            $mform->addHelpButton('newcontent_editor', 'formathtml', 'wiki');
            $mform->setType('newcontent_editor', PARAM_RAW);         }

                if ($version >= 0) {
            $mform->addElement('hidden', 'version', $version);
            $mform->setType('version', PARAM_FLOAT);
        }

        $mform->addElement('hidden', 'contentformat', $format);
        $mform->setType('contentformat', PARAM_ALPHANUMEXT);

        if (core_tag_tag::is_enabled('mod_wiki', 'wiki_pages')) {
            $mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));
        }
        $mform->addElement('tags', 'tags', get_string('tags'),
                array('itemtype' => 'wiki_pages', 'component' => 'mod_wiki'));

        $buttongroup = array();
        $buttongroup[] = $mform->createElement('submit', 'editoption', get_string('save', 'wiki'), array('id' => 'save'));
        $buttongroup[] = $mform->createElement('submit', 'editoption', get_string('preview'), array('id' => 'preview'));
        $buttongroup[] = $mform->createElement('submit', 'editoption', get_string('cancel'), array('id' => 'cancel'));

        $mform->addGroup($buttongroup, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

}
