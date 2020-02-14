<?php



defined('MOODLE_INTERNAL') || die();



class imsenterprise_roles {
    
    private $imsroles;

    
    public function __construct() {
        $this->imsroles = array(
            '01' => 'Learner',
            '02' => 'Instructor',
            '03' => 'Content Developer',
            '04' => 'Member',
            '05' => 'Manager',
            '06' => 'Mentor',
            '07' => 'Administrator',
            '08' => 'TeachingAssistant',
        );
                    }

    
    public function get_imsroles() {
        return $this->imsroles;
    }

    
    public function determine_default_rolemapping($imscode) {
        global $DB;

        switch($imscode) {
            case '01':
            case '04':
                $shortname = 'student';
                break;
            case '06':
            case '08':
                $shortname = 'teacher';
                break;
            case '02':
            case '03':
                $shortname = 'editingteacher';
                break;
            case '05':
            case '07':
                $shortname = 'admin';
                break;
            default:
                return 0;         }
        return (string)$DB->get_field('role', 'id', array('shortname' => $shortname));
    }


}



class imsenterprise_courses {
    
    private $imsnames;
    
    private $courseattrs;

    
    public function __construct() {
        $this->imsnames = array(
            'short' => 'short',
            'long' => 'long',
            'full' => 'full',
            'coursecode' => 'coursecode');
        $this->courseattrs = array('shortname', 'fullname', 'summary');
    }

    
    public function get_imsnames($courseattr) {

        $values = $this->imsnames;
        if ($courseattr == 'summary') {
            $values = array_merge(array('ignore' => get_string('emptyattribute', 'enrol_imsenterprise')), $values);
        }
        return $values;
    }

    
    public function get_courseattrs() {
        return $this->courseattrs;
    }

    
    public function determine_default_coursemapping($courseattr) {
        switch($courseattr) {
            case 'fullname':
                $imsname = 'short';
                break;
            case 'shortname':
                $imsname = 'coursecode';
                break;
            default:
                $imsname = 'ignore';
        }

        return $imsname;
    }
}
