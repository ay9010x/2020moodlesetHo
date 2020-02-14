<?php




class block_section_links_edit_form extends block_edit_form {

    
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $numberofsections = array();
        for ($i = 1; $i < 53; $i++){
            $numberofsections[$i] = $i;
        }

        $increments = array();

        for ($i = 1; $i < 11; $i++){
            $increments[$i] = $i;
        }

        $config = get_config('block_section_links');

        $selected = array(
            1 => array(22, 2),
            2 => array(40, 5),
        );
        if (!empty($config->numsections1)) {
            if (empty($config->incby1)) {
                $config->incby1 = $selected[1][1];
            }
            $selected[1] = array($config->numsections1, $config->incby1);
        }

        if (!empty($config->numsections2)) {
            if (empty($config->incby1)) {
                $config->incby1 = $selected[2][1];
            }
            $selected[2] = array($config->numsections2, $config->incby2);
        }

        for ($i = 1; $i < 3; $i++) {
            $mform->addElement('select', 'config_numsections'.$i, get_string('numsections'.$i, 'block_section_links'), $numberofsections);
            $mform->setDefault('config_numsections'.$i, $selected[$i][0]);
            $mform->setType('config_numsections'.$i, PARAM_INT);
            $mform->addHelpButton('config_numsections'.$i, 'numsections'.$i, 'block_section_links');

            $mform->addElement('select', 'config_incby'.$i, get_string('incby'.$i, 'block_section_links'), $increments);
            $mform->setDefault('config_incby'.$i, $selected[$i][1]);
            $mform->setType('config_incby'.$i, PARAM_INT);
            $mform->addHelpButton('config_incby'.$i, 'incby'.$i, 'block_section_links');
        }

    }
}