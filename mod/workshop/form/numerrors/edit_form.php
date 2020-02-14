<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');   require_once(dirname(dirname(__FILE__)).'/edit_form.php');    

class workshop_edit_numerrors_strategy_form extends workshop_edit_strategy_form {

    
    protected function definition_inner(&$mform) {

        $plugindefaults     = get_config('workshopform_numerrors');
        $nodimensions       = $this->_customdata['nodimensions'];               $norepeats          = $this->_customdata['norepeats'];                  $descriptionopts    = $this->_customdata['descriptionopts'];            $current            = $this->_customdata['current'];            
        $mform->addElement('hidden', 'norepeats', $norepeats);
        $mform->setType('norepeats', PARAM_INT);
                $mform->setConstants(array('norepeats' => $norepeats));

        for ($i = 0; $i < $norepeats; $i++) {
            $mform->addElement('header', 'dimension'.$i, get_string('dimensionnumber', 'workshopform_numerrors', $i+1));
            $mform->addElement('hidden', 'dimensionid__idx_'.$i);               $mform->setType('dimensionid__idx_'.$i, PARAM_INT);
            $mform->addElement('editor', 'description__idx_'.$i.'_editor',
                    get_string('dimensiondescription', 'workshopform_numerrors'), '', $descriptionopts);
            $mform->addElement('text', 'grade0__idx_'.$i, get_string('grade0', 'workshopform_numerrors'), array('size'=>'15'));
            $mform->setDefault('grade0__idx_'.$i, $plugindefaults->grade0);
            $mform->setType('grade0__idx_'.$i, PARAM_TEXT);
            $mform->addElement('text', 'grade1__idx_'.$i, get_string('grade1', 'workshopform_numerrors'), array('size'=>'15'));
            $mform->setDefault('grade1__idx_'.$i, $plugindefaults->grade1);
            $mform->setType('grade1__idx_'.$i, PARAM_TEXT);
            $mform->addElement('select', 'weight__idx_'.$i,
                    get_string('dimensionweight', 'workshopform_numerrors'), workshop::available_dimension_weights_list());
            $mform->setDefault('weight__idx_'.$i, 1);
        }

        $mform->addElement('header', 'mappingheader', get_string('grademapping', 'workshopform_numerrors'));
        $mform->addElement('static', 'mappinginfo', get_string('maperror', 'workshopform_numerrors'),
                                                            get_string('mapgrade', 'workshopform_numerrors'));

                $totalweight = 0;
        for ($i = 0; $i < $norepeats; $i++) {
            if (!empty($current->{'weight__idx_'.$i})) {
                $totalweight += $current->{'weight__idx_'.$i};
            }
        }
        $totalweight = max($totalweight, $nodimensions);

        $percents = array();
        $percents[''] = '';
        for ($i = 100; $i >= 0; $i--) {
            $percents[$i] = get_string('percents', 'workshopform_numerrors', $i);
        }
        $mform->addElement('static', 'mappingzero', 0, get_string('percents', 'workshopform_numerrors', 100));
        for ($i = 1; $i <= $totalweight; $i++) {
            $selects = array();
            $selects[] = $mform->createElement('select', 'map__idx_'.$i, $i, $percents);
            $selects[] = $mform->createElement('static', 'mapdefault__idx_'.$i, '',
                                        get_string('percents', 'workshopform_numerrors', floor(100 - $i * 100 / $totalweight)));
            $mform->addGroup($selects, 'grademapping'.$i, $i, array(' '), false);
            $mform->setDefault('map__idx_'.$i, '');
        }

        $mform->registerNoSubmitButton('noadddims');
        $mform->addElement('submit', 'noadddims', get_string('addmoredimensions', 'workshopform_numerrors',
                workshop_numerrors_strategy::ADDDIMS));
        $mform->closeHeaderBefore('noadddims');
        $this->set_data($current);

    }

}
