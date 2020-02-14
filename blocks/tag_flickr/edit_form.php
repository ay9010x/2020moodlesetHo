<?php




class block_tag_flickr_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_tag_flickr'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('text', 'config_numberofphotos', get_string('numberofphotos', 'block_tag_flickr'), array('size' => 5));
        $mform->setType('config_numberofphotos', PARAM_INT);

        $mform->addElement('selectyesno', 'config_includerelatedtags', get_string('includerelatedtags', 'block_tag_flickr'));
        $mform->setDefault('config_includerelatedtags', 0);

        $sortoptions = array(
            'date-posted-asc'  => get_string('date-posted-asc', 'block_tag_flickr'),
            'date-posted-desc' => get_string('date-posted-desc', 'block_tag_flickr'),
            'date-taken-asc' => get_string('date-taken-asc', 'block_tag_flickr'),
            'date-taken-desc' => get_string('date-taken-desc', 'block_tag_flickr'),
            'interestingness-asc' => get_string('interestingness-asc', 'block_tag_flickr'),
            'interestingness-desc' => get_string('interestingness-desc', 'block_tag_flickr'),
            'relevance' => get_string('relevance', 'block_tag_flickr'),
        );
        $mform->addElement('select', 'config_sortby', get_string('sortby', 'block_tag_flickr'), $sortoptions);
        $mform->setDefault('config_sortby', 'relevance');

        $mform->addElement('text', 'config_photoset', get_string('getfromphotoset', 'block_tag_flickr'));
        $mform->setType('config_photoset', PARAM_ALPHANUM);
    }
}
