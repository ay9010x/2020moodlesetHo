<?php





defined('MOODLE_INTERNAL') || die();

class license_manager {
    
    static public function add($license) {
        global $DB;
        if ($record = $DB->get_record('license', array('shortname'=>$license->shortname))) {
                        if ($record->version < $license->version) {
                                $license->enabled = $record->enabled;
                $license->id = $record->id;
                $DB->update_record('license', $license);
            }
        } else {
            $DB->insert_record('license', $license);
        }
        return true;
    }

    
    static public function get_licenses($param = null) {
        global $DB;
        if (empty($param) || !is_array($param)) {
            $param = array();
        }
                if ($records = $DB->get_records('license', $param)) {
            return $records;
        } else {
            return array();
        }
    }

    
    static public function get_license_by_shortname($name) {
        global $DB;
        if ($record = $DB->get_record('license', array('shortname'=>$name))) {
            return $record;
        } else {
            return null;
        }
    }

    
    static public function enable($license) {
        global $DB;
        if ($license = self::get_license_by_shortname($license)) {
            $license->enabled = 1;
            $DB->update_record('license', $license);
        }
        self::set_active_licenses();
        return true;
    }

    
    static public function disable($license) {
        global $DB, $CFG;
                if ($license == $CFG->sitedefaultlicense) {
            print_error('error');
        }
        if ($license = self::get_license_by_shortname($license)) {
            $license->enabled = 0;
            $DB->update_record('license', $license);
        }
        self::set_active_licenses();
        return true;
    }

    
    static private function set_active_licenses() {
                $licenses = self::get_licenses(array('enabled'=>1));
        $result = array();
        foreach ($licenses as $l) {
            $result[] = $l->shortname;
        }
        set_config('licenses', implode(',', $result));
    }

    
    static public function install_licenses() {
        $active_licenses = array();

        $license = new stdClass();

        $license->shortname = 'unknown';
        $license->fullname = 'Unknown license';
        $license->source = '';
        $license->enabled = 1;
        $license->version = '2010033100';
        $active_licenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'allrightsreserved';
        $license->fullname = 'All rights reserved';
        $license->source = 'http://en.wikipedia.org/wiki/All_rights_reserved';
        $license->enabled = 1;
        $license->version = '2010033100';
        $active_licenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'public';
        $license->fullname = 'Public Domain';
        $license->source = 'http://creativecommons.org/licenses/publicdomain/';
        $license->enabled = 1;
        $license->version = '2010033100';
        $active_licenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc';
        $license->fullname = 'Creative Commons';
        $license->source = 'http://creativecommons.org/licenses/by/3.0/';
        $license->enabled = 1;
        $license->version = '2010033100';
        $active_licenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-nd';
        $license->fullname = 'Creative Commons - NoDerivs';
        $license->source = 'http://creativecommons.org/licenses/by-nd/3.0/';
        $license->enabled = 1;
        $license->version = '2010033100';
        $active_licenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-nc-nd';
        $license->fullname = 'Creative Commons - No Commercial NoDerivs';
        $license->source = 'http://creativecommons.org/licenses/by-nc-nd/3.0/';
        $license->enabled = 1;
        $license->version = '2010033100';
        $active_licenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-nc';
        $license->fullname = 'Creative Commons - No Commercial';
        $license->source = 'http://creativecommons.org/licenses/by-nc/3.0/';
        $license->enabled = 1;
        $license->version = '2013051500';
        $active_licenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-nc-sa';
        $license->fullname = 'Creative Commons - No Commercial ShareAlike';
        $license->source = 'http://creativecommons.org/licenses/by-nc-sa/3.0/';
        $license->enabled = 1;
        $license->version = '2010033100';
        $active_licenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-sa';
        $license->fullname = 'Creative Commons - ShareAlike';
        $license->source = 'http://creativecommons.org/licenses/by-sa/3.0/';
        $license->enabled = 1;
        $license->version = '2010033100';
        $active_licenses[] = $license->shortname;
        self::add($license);

        set_config('licenses', implode(',', $active_licenses));
    }
}
