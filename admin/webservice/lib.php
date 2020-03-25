<?php



require_once($CFG->dirroot . '/user/selector/lib.php');


class service_user_selector extends user_selector_base {
    protected $serviceid;
    protected $displayallowedusers;                                                                                                             
    public function __construct($name, $options) {
        parent::__construct($name, $options);
        if (!empty($options['serviceid'])) {
            $this->serviceid = $options['serviceid'];
        } else {
            throw new moodle_exception('serviceidnotfound');
        }
        $this->displayallowedusers = !empty($options['displayallowedusers']);
    }

    
    public function find_users($search) {
        global $DB;
                        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['serviceid'] = $this->serviceid;


        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        if ($this->displayallowedusers) {
                        $sql = " FROM {user} u, {external_services_users} esu
                 WHERE $wherecondition
                       AND u.deleted = 0
                       AND esu.userid = u.id
                       AND esu.externalserviceid = :serviceid";
        }
        else {
                        $sql = " FROM {user} u WHERE $wherecondition AND u.deleted = 0
                 AND NOT EXISTS (SELECT esu.userid FROM {external_services_users} esu
                                                  WHERE esu.externalserviceid = :serviceid
                                                        AND esu.userid = u.id)";
        }

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = ($this->displayallowedusers) ?
                get_string('serviceusersmatching', 'webservice', $search)
                : get_string('potusersmatching', 'webservice', $search);
        }
        else {
            $groupname = ($this->displayallowedusers) ?
                get_string('serviceusers', 'webservice')
                : get_string('potusers', 'webservice');
        }

        return array($groupname => $availableusers);
    }

    
    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = $CFG->admin.'/webservice/lib.php';                                                                                                                         $options['serviceid'] = $this->serviceid;
        $options['displayallowedusers'] = $this->displayallowedusers;
        return $options;
    }
}
