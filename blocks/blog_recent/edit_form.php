<?php






class block_blog_recent_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
                $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $numberofentries = array();
        for ($i = 1; $i <= 20; $i++) {
            $numberofentries[$i] = $i;
        }

        $mform->addElement('select', 'config_numberofrecentblogentries', get_string('numentriestodisplay', 'block_blog_recent'), $numberofentries);
        $mform->setDefault('config_numberofrecentblogentries', 4);


        $intervals = array(
                7200 => get_string('numhours', '', 2),
                14400 => get_string('numhours', '', 4),
                21600 => get_string('numhours', '', 6),
                43200 => get_string('numhours', '', 12),
                86400 => get_string('numhours', '', 24),
                172800 => get_string('numdays', '', 2),
                604800 => get_string('numdays', '', 7)
                );

        $mform->addElement('select', 'config_recentbloginterval', get_string('recentinterval', 'block_blog_recent'), $intervals);
        $mform->setDefault('config_recentbloginterval', 86400);
    }
}
