<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)).'/assessment_form.php');    

class workshop_comments_assessment_form extends workshop_assessment_form {

    
    protected function definition_inner(&$mform) {
        $fields     = $this->_customdata['fields'];
        $current    = $this->_customdata['current'];
        $nodims     = $this->_customdata['nodims'];     
        $mform->addElement('hidden', 'nodims', $nodims);
        $mform->setType('nodims', PARAM_INT);

        for ($i = 0; $i < $nodims; $i++) {
                        $dimtitle = get_string('dimensionnumber', 'workshopform_comments', $i+1);
            $mform->addElement('header', 'dimensionhdr__idx_'.$i, $dimtitle);

                        $mform->addElement('hidden', 'dimensionid__idx_'.$i, $fields->{'dimensionid__idx_'.$i});
            $mform->setType('dimensionid__idx_'.$i, PARAM_INT);

                        $mform->addElement('hidden', 'gradeid__idx_'.$i);               $mform->setType('gradeid__idx_'.$i, PARAM_INT);

                        $desc = '<div id="id_dim_'.$fields->{'dimensionid__idx_'.$i}.'_desc" class="fitem description comments">'."\n";
            $desc .= format_text($fields->{'description__idx_'.$i}, $fields->{'description__idx_'.$i.'format'});
            $desc .= "\n</div>";
            $mform->addElement('html', $desc);

                        $label = get_string('dimensioncommentfor', 'workshopform_comments', $dimtitle);
                        $mform->addElement('textarea', 'peercomment__idx_' . $i, $label, array('cols' => 60, 'rows' => 10));
            $mform->addRule('peercomment__idx_' . $i, null, 'required', null, 'client');
        }
        $this->set_data($current);
    }
}
