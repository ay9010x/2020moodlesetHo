<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/gradelib.php'); 

class moodle1_workshopform_numerrors_handler extends moodle1_workshopform_handler {

    
    protected $mappings = array();

    
    protected $dimensions = array();

    
    public function on_elements_start() {
        $this->mappings = array();
        $this->dimensions = array();
    }

    
    public function process_legacy_element(array $data, array $raw) {

        $workshop = $this->parenthandler->get_current_workshop();

        $mapping = array();
        $mapping['id'] = $data['id'];
        $mapping['nonegative'] = $data['elementno'];
        if ($workshop['grade'] == 0 or $data['maxscore'] == 0) {
            $mapping['grade'] = 0;
        } else {
            $mapping['grade'] = grade_floatval($data['maxscore'] / $workshop['grade'] * 100);
        }
        $this->mappings[] = $mapping;

        $converted = null;

        if (trim($data['description']) and $data['description'] <> '@@ GRADE_MAPPING_ELEMENT @@') {
                        $fakerecord = (object)$data;
            $converted = (array)workshopform_numerrors_upgrade_element($fakerecord, 12345678);
            unset($converted['workshopid']);

            $converted['id'] = $data['id'];
            $this->dimensions[] = $converted;
        }

        return $converted;
    }

    
    public function on_elements_end() {

        foreach ($this->mappings as $mapping) {
            $this->write_xml('workshopform_numerrors_map', $mapping, array('/workshopform_numerrors_map/id'));
        }

        foreach ($this->dimensions as $dimension) {
            $this->write_xml('workshopform_numerrors_dimension', $dimension, array('/workshopform_numerrors_dimension/id'));
        }
    }
}


function workshopform_numerrors_upgrade_element(stdclass $old, $newworkshopid) {
    $new = new stdclass();
    $new->workshopid = $newworkshopid;
    $new->sort = $old->elementno;
    $new->description = $old->description;
    $new->descriptionformat = FORMAT_HTML;
    $new->grade0 = get_string('grade0default', 'workshopform_numerrors');
    $new->grade1 = get_string('grade1default', 'workshopform_numerrors');
                    switch ($old->weight) {
        case 8: $new->weight = 1; break;
        case 9: $new->weight = 2; break;
        case 10: $new->weight = 3; break;
        case 11: $new->weight = 4; break;
        case 12: $new->weight = 6; break;
        case 13: $new->weight = 8; break;
        case 14: $new->weight = 16; break;
        default: $new->weight = 0;
    }
    return $new;
}
