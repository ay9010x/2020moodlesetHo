<?php

require_once($CFG->dirroot . '/grade/import/csv/classes/load_data.php');
require_once($CFG->dirroot . '/grade/import/lib.php');


class phpunit_gradeimport_csv_load_data extends gradeimport_csv_load_data {

    
    public function test_insert_grade_record($record, $studentid) {
        $this->importcode = 00001;
        $this->insert_grade_record($record, $studentid);
    }

    
    public function get_importcode() {
        return $this->importcode;
    }

    
    public function test_import_new_grade_item($header, $key, $value) {
        $this->newgradeitems = null;
        $this->importcode = 00001;
        return $this->import_new_grade_item($header, $key, $value);
    }

    
    public function test_check_user_exists($value, $userfields) {
        return $this->check_user_exists($value, $userfields);
    }

    
    public function test_create_feedback($courseid, $itemid, $value) {
        return $this->create_feedback($courseid, $itemid, $value);
    }

    
    public function test_update_grade_item($courseid, $map, $key, $verbosescales, $value) {
        return $this->update_grade_item($courseid, $map, $key, $verbosescales, $value);
    }

    
    public function test_map_user_data_with_value($mappingidentifier, $value, $header, $map, $key, $courseid, $feedbackgradeid,
            $verbosescales) {
                $this->importcode = 00001;
        $this->map_user_data_with_value($mappingidentifier, $value, $header, $map, $key, $courseid, $feedbackgradeid,
                $verbosescales);

        switch ($mappingidentifier) {
            case 'userid':
            case 'useridnumber':
            case 'useremail':
            case 'username':
                return $this->studentid;
            break;
            case 'new':
                return $this->newgrades;
            break;
            case 'feedback':
                return $this->newfeedbacks;
            break;
            default:
                return $this->newgrades;
            break;
        }
    }
}
