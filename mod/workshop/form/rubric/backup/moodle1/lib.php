<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_workshopform_rubric_handler extends moodle1_workshopform_handler {

    
    protected $elements = array();

    
    protected $rubrics = array();

    
    public function on_elements_start() {
        $this->elements = array();
        $this->rubrics = array();
    }

    
    public function process_legacy_element(array $data, array $raw) {
        $this->elements[] = $data;
        $this->rubrics[$data['id']] = array();
    }

    
    public function process_legacy_rubric($data, $raw) {
        $this->rubrics[$data['elementid']][] = $data;
    }

    
    public function on_elements_end() {

        $numofrubrics = 0;
        foreach ($this->rubrics as $itemid => $levels) {
            $numofrubrics += count($levels);
        }

        if ($numofrubrics == 0) {
            $this->convert_legacy_criterion_elements();

        } else {
            $this->convert_legacy_rubric_elements();
        }
    }

    
    protected function convert_legacy_criterion_elements() {

        $this->write_xml('workshopform_rubric_config', array('layout' => 'list'));

        $firstelement = reset($this->elements);
        if ($firstelement === false) {
                        return;
        }

                $this->xmlwriter->begin_tag('workshopform_rubric_dimension', array('id' => $firstelement['id']));
        $this->xmlwriter->full_tag('sort', 1);
        $this->xmlwriter->full_tag('description', trim(get_string('dimensionnumber', 'workshopform_rubric', '')));
        $this->xmlwriter->full_tag('descriptionformat', FORMAT_HTML);

        foreach ($this->elements as $element) {
            $this->write_xml('workshopform_rubric_level', array(
                'id'               => $element['id'],
                'grade'            => $element['maxscore'],
                'definition'       => $element['description'],
                'definitionformat' => FORMAT_HTML
            ), array('/workshopform_rubric_level/id'));
        }

        $this->xmlwriter->end_tag('workshopform_rubric_dimension');
    }

    
    protected function convert_legacy_rubric_elements() {
        $this->write_xml('workshopform_rubric_config', array('layout' => 'grid'));

        foreach ($this->elements as $element) {
            $this->xmlwriter->begin_tag('workshopform_rubric_dimension', array('id' => $element['id']));
            $this->xmlwriter->full_tag('sort', $element['elementno']);
            $this->xmlwriter->full_tag('description', $element['description']);
            $this->xmlwriter->full_tag('descriptionformat', FORMAT_HTML);

            foreach ($this->rubrics[$element['id']] as $rubric) {
                $fakerecord          = new stdClass();
                $fakerecord->rgrade  = $rubric['rubricno'];
                $fakerecord->eweight = $element['weight'];
                $fakerecord->rdesc   = $rubric['description'];
                $level = (array)workshopform_rubric_upgrade_rubric_level($fakerecord, $element['id']);
                unset($level['dimensionid']);
                $level['id'] = $this->converter->get_nextid();
                $this->write_xml('workshopform_rubric_level', $level, array('/workshopform_rubric_level/id'));
            }

            $this->xmlwriter->end_tag('workshopform_rubric_dimension');
        }
    }
}


function workshopform_rubric_upgrade_rubric_level(stdclass $old, $newdimensionid) {
    $new = new stdclass();
    $new->dimensionid = $newdimensionid;
    $new->grade = $old->rgrade * workshopform_rubric_upgrade_weight($old->eweight);
    $new->definition = $old->rdesc;
    $new->definitionformat = FORMAT_HTML;
    return $new;
}


function workshopform_rubric_upgrade_weight($oldweight) {

    switch ($oldweight) {
        case 8: $weight = 1; break;
        case 9: $weight = 2; break;
        case 10: $weight = 3; break;
        case 11: $weight = 4; break;
        case 12: $weight = 6; break;
        case 13: $weight = 8; break;
        case 14: $weight = 16; break;
        default: $weight = 0;
    }
    return $weight;
}
