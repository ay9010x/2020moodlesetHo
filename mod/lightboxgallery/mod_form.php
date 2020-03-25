<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_lightboxgallery_mod_form extends moodleform_mod {

    public function definition() {

        global $CFG;

        $mform =& $this->_form;

        
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '48', 'maxlength' => '255'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        if ($CFG->branch < 29) {
            $this->add_intro_editor(true, get_string('description'));
        } else {
            $this->standard_intro_elements();
        }

        
        $mform->addElement('header', 'galleryoptions', get_string('advanced'));

        $mform->addElement('select', 'perpage', get_string('imagesperpage', 'lightboxgallery'), $this->get_perpage_options());
        $mform->setType('perpage', PARAM_INT);

        $yesno = array(0 => get_string('no'), 1 => get_string('yes'));

        $mform->addElement('select', 'captionfull', get_string('captionfull', 'lightboxgallery'), $yesno);

        $captionposopts = array(
            '0' => get_string('position_bottom', 'lightboxgallery'),
            '1' => get_string('position_top', 'lightboxgallery'),
            '2' => get_string('hide'),
        );
        $mform->addElement('select', 'captionpos', get_string('captionpos', 'lightboxgallery'), $captionposopts);

        $autoresize = $mform->createElement('select', 'autoresize', get_string('autoresize', 'lightboxgallery'),
                                $this->get_autoresize_options());
        $autoresizegroup = array();
        $autoresizegroup[] = $mform->createElement('select', 'autoresize', get_string('autoresize', 'lightboxgallery'),
                                $this->get_autoresize_options());
        $autoresizegroup[] = $mform->createElement('checkbox', 'autoresizedisabled', null, get_string('disable'));
        $mform->addGroup($autoresizegroup, 'autoresizegroup', get_string('autoresize', 'lightboxgallery'), ' ', false);
        $mform->setType('autoresize', PARAM_INT);
        $mform->disabledIf('autoresizegroup', 'autoresizedisabled', 'checked');
        $mform->addHelpButton('autoresizegroup', 'autoresize', 'lightboxgallery');

        $mform->addElement('select', 'resize', sprintf('%s (%s)', get_string('edit_resize', 'lightboxgallery'),
                            core_text::strtolower(get_string('upload'))), lightboxgallery_resize_options());
        $mform->setType('resize', PARAM_INT);
        $mform->disabledIf('resize', 'autoresize', 'eq', 1);
        $mform->disabledIf('resize', 'autoresizedisabled', 'checked');

        $mform->addElement('select', 'comments', get_string('allowcomments', 'lightboxgallery'), $yesno);
        $mform->setType('comments', PARAM_INT);

        $mform->addElement('select', 'ispublic', get_string('makepublic', 'lightboxgallery'), $yesno);
        $mform->setType('ispublic', PARAM_INT);

        if (lightboxgallery_rss_enabled()) {
            $mform->addElement('select', 'rss', get_string('allowrss', 'lightboxgallery'), $yesno);
            $mform->setType('rss', PARAM_INT);
        } else {
            $mform->addElement('static', 'rssdisabled', get_string('allowrss', 'lightboxgallery'),
                                get_string('rssglobaldisabled', 'admin'));
        }

        $mform->addElement('select', 'extinfo', get_string('extendedinfo', 'lightboxgallery'), $yesno);
        $mform->setType('extinfo', PARAM_INT);

        
        $features = array('groups' => false, 'groupings' => false, 'groupmembersonly' => false,
                          'outcomes' => false, 'gradecat' => false, 'idnumber' => false);

        $this->standard_coursemodule_elements($features);

        $this->add_action_buttons();

    }

    public function data_preprocessing(&$defaults) {
        if (!isset($this->current->add)) {
            $defaults['autoresizedisabled'] = isset($defaults['autoresize']) && $defaults['autoresize'] ? 0 : 1;
        }
    }

    
    private function get_perpage_options() {
        $perpages = array(10, 25, 50, 100, 200);
        $result = array(0 => get_string('showall', 'lightboxgallery'));
        foreach ($perpages as $perpage) {
            $result[$perpage] = $perpage;
        }
        return $result;
    }

    private function get_autoresize_options() {
        $screen = get_string('screen', 'lightboxgallery');
        $upload = get_string('upload');
        return array(AUTO_RESIZE_SCREEN => $screen,
                     AUTO_RESIZE_UPLOAD => $upload,
                     AUTO_RESIZE_BOTH   => $screen . ' &amp; ' . $upload);
    }
}

