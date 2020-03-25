<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)).'/assessment_form.php');    

class workshop_numerrors_assessment_form extends workshop_assessment_form {

    
    protected function definition_inner(&$mform) {
        $fields     = $this->_customdata['fields'];
        $current    = $this->_customdata['current'];
        $nodims     = $this->_customdata['nodims'];     
        $mform->addElement('hidden', 'nodims', $nodims);
        $mform->setType('nodims', PARAM_INT);

        for ($i = 0; $i < $nodims; $i++) {
                        $dimtitle = get_string('dimensionnumber', 'workshopform_numerrors', $i+1);
            $mform->addElement('header', "dimensionhdr__idx_$i", $dimtitle);

                        $mform->addElement('hidden', 'dimensionid__idx_'.$i, $fields->{'dimensionid__idx_'.$i});
            $mform->setType('dimensionid__idx_'.$i, PARAM_INT);

                        $mform->addElement('hidden', 'gradeid__idx_'.$i);               $mform->setType('gradeid__idx_'.$i, PARAM_INT);

                        $desc = '<div id="id_dim_'.$fields->{'dimensionid__idx_'.$i}.'_desc" class="fitem description numerrors">'."\n";
            $desc .= format_text($fields->{'description__idx_'.$i}, $fields->{'description__idx_'.$i.'format'});
            $desc .= "\n</div>";
            $mform->addElement('html', $desc);

                        $label = get_string('dimensiongrade', 'workshopform_numerrors');
            $mform->addGroup(array(
                $mform->createElement('radio', 'grade__idx_' . $i, '', $fields->{'grade0__idx_'.$i}, -1),
                $mform->createElement('radio', 'grade__idx_' . $i, '', $fields->{'grade1__idx_'.$i}, 1),
            ), 'group_grade__idx_' . $i, get_string('yourassessmentfor', 'workshop', $dimtitle), '<br />', false);
            $mform->addRule('group_grade__idx_' . $i, get_string('required'), 'required');

                        $label = get_string('dimensioncommentfor', 'workshopform_numerrors', $dimtitle);
            $mform->addElement('textarea', 'peercomment__idx_' . $i, $label, array('cols' => 60, 'rows' => 5));
        }
        $this->set_data($current);
    }
}
