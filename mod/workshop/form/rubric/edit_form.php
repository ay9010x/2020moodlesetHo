<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)).'/edit_form.php');    

class workshop_edit_rubric_strategy_form extends workshop_edit_strategy_form {

    const MINLEVELS = 4;
    const ADDLEVELS = 2;

    
    protected function definition_inner(&$mform) {

        $norepeats          = $this->_customdata['norepeats'];                  $descriptionopts    = $this->_customdata['descriptionopts'];            $current            = $this->_customdata['current'];            
        $mform->addElement('hidden', 'norepeats', $norepeats);
        $mform->setType('norepeats', PARAM_INT);
                $mform->setConstants(array('norepeats' => $norepeats));

        $levelgrades = array();
        for ($i = 100; $i >= 0; $i--) {
            $levelgrades[$i] = $i;
        }

        for ($i = 0; $i < $norepeats; $i++) {
            $mform->addElement('header', 'dimension'.$i, get_string('dimensionnumber', 'workshopform_rubric', $i+1));
            $mform->addElement('hidden', 'dimensionid__idx_'.$i);
            $mform->setType('dimensionid__idx_'.$i, PARAM_INT);
            $mform->addElement('editor', 'description__idx_'.$i.'_editor',
                                get_string('dimensiondescription', 'workshopform_rubric'), '', $descriptionopts);
            if (isset($current->{'numoflevels__idx_' . $i})) {
                $numoflevels = max($current->{'numoflevels__idx_' . $i} + self::ADDLEVELS, self::MINLEVELS);
            } else {
                $numoflevels = self::MINLEVELS;
            }
            $prevlevel = -1;
            for ($j = 0; $j < $numoflevels; $j++) {
                $mform->addElement('hidden', 'levelid__idx_' . $i . '__idy_' . $j);
                $mform->setType('levelid__idx_' . $i . '__idy_' . $j, PARAM_INT);
                $levelgrp = array();
                $levelgrp[] = $mform->createElement('select', 'grade__idx_'.$i.'__idy_'.$j,'', $levelgrades);
                $levelgrp[] = $mform->createElement('textarea', 'definition__idx_'.$i.'__idy_'.$j, '',  array('cols' => 60, 'rows' => 3));
                $mform->addGroup($levelgrp, 'level__idx_'.$i.'__idy_'.$j, get_string('levelgroup', 'workshopform_rubric'), array(' '), false);
                $mform->setDefault('grade__idx_'.$i.'__idy_'.$j, $prevlevel + 1);
                if (isset($current->{'grade__idx_'.$i.'__idy_'.$j})) {
                    $prevlevel = $current->{'grade__idx_'.$i.'__idy_'.$j};
                } else {
                    $prevlevel++;
                }
            }
        }

        $mform->registerNoSubmitButton('adddims');
        $mform->addElement('submit', 'adddims', get_string('addmoredimensions', 'workshopform_rubric',
                workshop_rubric_strategy::ADDDIMS));
        $mform->closeHeaderBefore('adddims');

        $mform->addElement('header', 'configheader', get_string('configuration', 'workshopform_rubric'));
        $layoutgrp = array();
        $layoutgrp[] = $mform->createElement('radio', 'config_layout', '',
                get_string('layoutlist', 'workshopform_rubric'), 'list');
        $layoutgrp[] = $mform->createElement('radio', 'config_layout', '',
                get_string('layoutgrid', 'workshopform_rubric'), 'grid');
        $mform->addGroup($layoutgrp, 'layoutgrp', get_string('layout', 'workshopform_rubric'), array('<br />'), false);
        $mform->setDefault('config_layout', 'list');
        $this->set_data($current);
    }

    
    protected function validation_inner($data, $files) {

        $errors = array();

                for ($i = 0; isset($data['dimensionid__idx_'.$i]); $i++) {

            $dimgrades = array();

            if (0 == strlen(trim($data['description__idx_'.$i.'_editor']['text']))) {
                                continue;
            }

                        for ($j = 0; isset($data['levelid__idx_'.$i.'__idy_'.$j]); $j++) {
                if (0 == strlen(trim($data['definition__idx_'.$i.'__idy_'.$j]))) {
                                        continue;
                }

                $levelgrade = $data['grade__idx_'.$i.'__idy_'.$j];

                if (isset($dimgrades[$levelgrade])) {
                                        $k = $dimgrades[$levelgrade];
                    $errors['level__idx_'.$i.'__idy_'.$j] = $errors['level__idx_'.$i.'__idy_'.$k] = get_string('mustbeunique',
                        'workshopform_rubric');
                } else {
                    $dimgrades[$levelgrade] = $j;
                }
            }
        }

        return $errors;
    }
}
