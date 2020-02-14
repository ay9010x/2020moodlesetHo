<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)).'/assessment_form.php');    

abstract class workshop_rubric_assessment_form extends workshop_assessment_form {

    public function validation($data, $files) {

        $errors = parent::validation($data, $files);
        for ($i = 0; isset($data['dimensionid__idx_'.$i]); $i++) {
            if (empty($data['chosenlevelid__idx_'.$i])) {
                $errors['chosenlevelid__idx_'.$i] = get_string('mustchooseone', 'workshopform_rubric');                 $errors['levelgrp__idx_'.$i] = get_string('mustchooseone', 'workshopform_rubric');                  }
        }
        return $errors;
    }
}


class workshop_rubric_list_assessment_form extends workshop_rubric_assessment_form {

    
    protected function definition_inner(&$mform) {
        $workshop   = $this->_customdata['workshop'];
        $fields     = $this->_customdata['fields'];
        $current    = $this->_customdata['current'];
        $nodims     = $this->_customdata['nodims'];     
        for ($i = 0; $i < $nodims; $i++) {
                        $dimtitle = get_string('dimensionnumber', 'workshopform_rubric', $i+1);
            $mform->addElement('header', 'dimensionhdr__idx_'.$i, $dimtitle);

                        $mform->addElement('hidden', 'dimensionid__idx_'.$i, $fields->{'dimensionid__idx_'.$i});
            $mform->setType('dimensionid__idx_'.$i, PARAM_INT);

                        $mform->addElement('hidden', 'gradeid__idx_'.$i);               $mform->setType('gradeid__idx_'.$i, PARAM_INT);

                        $desc = '<div id="id_dim_'.$fields->{'dimensionid__idx_'.$i}.'_desc" class="fitem description rubric">'."\n";
            $desc .= format_text($fields->{'description__idx_'.$i}, $fields->{'description__idx_'.$i.'format'});
            $desc .= "\n</div>";
            $mform->addElement('html', $desc);

            $numoflevels = $fields->{'numoflevels__idx_'.$i};
            $levelgrp   = array();
            for ($j = 0; $j < $numoflevels; $j++) {
                $levelid = $fields->{'levelid__idx_'.$i.'__idy_'.$j};
                $definition = $fields->{'definition__idx_'.$i.'__idy_'.$j};
                $definitionformat = $fields->{'definition__idx_'.$i.'__idy_'.$j.'format'};
                $levelgrp[] = $mform->createElement('radio', 'chosenlevelid__idx_'.$i, '',
                        format_text($definition, $definitionformat, null, $workshop->course->id), $levelid);
            }
            $mform->addGroup($levelgrp, 'levelgrp__idx_'.$i, '', "<br />\n", false);
        }
        $this->set_data($current);
    }
}


class workshop_rubric_grid_assessment_form extends workshop_rubric_assessment_form {

    
    protected function definition_inner(&$mform) {
        $workshop   = $this->_customdata['workshop'];
        $fields     = $this->_customdata['fields'];
        $current    = $this->_customdata['current'];
        $nodims     = $this->_customdata['nodims'];     
                $levelcounts = array();
        for ($i = 0; $i < $nodims; $i++) {
            if ($fields->{'numoflevels__idx_'.$i} > 0) {
                $levelcounts[] = $fields->{'numoflevels__idx_'.$i};
            }
        }
        $numofcolumns = array_reduce($levelcounts, 'workshop::lcm', 1);

        $mform->addElement('header', 'rubric-grid-wrapper', get_string('layoutgrid', 'workshopform_rubric'));

        $mform->addElement('html', '<table class="rubric-grid">' . "\n");
        $mform->addElement('html', '<th class="header">' . get_string('criteria', 'workshopform_rubric') . '</th>');
        $mform->addElement('html', '<th class="header" colspan="'.$numofcolumns.'">'.get_string('levels', 'workshopform_rubric').'</th>');

        for ($i = 0; $i < $nodims; $i++) {

            $mform->addElement('html', '<tr class="r'. $i % 2  .'"><td class="criterion">' . "\n");

                        $mform->addElement('hidden', 'dimensionid__idx_'.$i, $fields->{'dimensionid__idx_'.$i});
            $mform->setType('dimensionid__idx_'.$i, PARAM_INT);

                        $mform->addElement('hidden', 'gradeid__idx_'.$i);               $mform->setType('gradeid__idx_'.$i, PARAM_INT);

                        $desc = format_text($fields->{'description__idx_'.$i}, $fields->{'description__idx_'.$i.'format'});
            $desc .= "</td>\n";
            $mform->addElement('html', $desc);

            $numoflevels = $fields->{'numoflevels__idx_'.$i};
            for ($j = 0; $j < $numoflevels; $j++) {
                $colspan = $numofcolumns / $numoflevels;
                $mform->addElement('html', '<td class="level c' . $j % 2  . '" colspan="' . $colspan . '">' . "\n");
                $levelid = $fields->{'levelid__idx_'.$i.'__idy_'.$j};
                $definition = $fields->{'definition__idx_'.$i.'__idy_'.$j};
                $definitionformat = $fields->{'definition__idx_'.$i.'__idy_'.$j.'format'};
                $mform->addElement('radio', 'chosenlevelid__idx_'.$i, '',
                        format_text($definition, $definitionformat, null, $workshop->course->id), $levelid);
                $mform->addElement('html', '</td>' . "\n");
            }
            $mform->addElement('html', '</tr>' . "\n");
        }
        $mform->addElement('html', '</table>' . "\n");

        $this->set_data($current);
    }
}
