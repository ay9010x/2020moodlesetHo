<?php




namespace ltiservice_profile\local\service;

defined('MOODLE_INTERNAL') || die();


class profile extends \mod_lti\local\ltiservice\service_base {

    
    public function __construct() {

        parent::__construct();
        $this->id = 'profile';
        $this->name = 'Tool Consumer Profile';
        $this->unsigned = true;

    }

    
    public function get_resources() {

        if (empty($this->resources)) {
            $this->resources = array();
            $this->resources[] = new \ltiservice_profile\local\resource\profile($this);
        }

        return $this->resources;

    }

}
