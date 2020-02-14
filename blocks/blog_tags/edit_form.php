<?php




class block_blog_tags_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
                $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('blocktitle', 'blog'));
        $mform->setDefault('config_title', get_string('blogtags', 'blog'));
        $mform->setType('config_title', PARAM_TEXT);

        $numberoftags = array();
        for($i = 1; $i <= 50; $i++) {
            $numberoftags[$i] = $i;
        }
        $mform->addElement('select', 'config_numberoftags', get_string('numberoftags', 'blog'), $numberoftags);
        $mform->setDefault('config_numberoftags', BLOCK_BLOG_TAGS_DEFAULTNUMBEROFTAGS);

        $timewithin = array(
            10  => get_string('numdays', '', 10),
            30  => get_string('numdays', '', 30),
            60  => get_string('numdays', '', 60),
            90  => get_string('numdays', '', 90),
            120 => get_string('numdays', '', 120),
            240 => get_string('numdays', '', 240),
            365 => get_string('numdays', '', 365),
        );
        $mform->addElement('select', 'config_timewithin', get_string('timewithin', 'blog'), $timewithin);
        $mform->setDefault('config_timewithin', BLOCK_BLOG_TAGS_DEFAULTTIMEWITHIN);

        $sort = array(
            'name' => get_string('tagtext', 'blog'),
            'id'   => get_string('tagdatelastused', 'blog'),
        );
        $mform->addElement('select', 'config_sort', get_string('tagsort', 'blog'), $sort);
        $mform->setDefault('config_sort', BLOCK_BLOG_TAGS_DEFAULTSORT);
    }
}
