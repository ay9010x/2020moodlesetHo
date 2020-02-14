<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');

class core_role_admins_potential_selector extends user_selector_base {
    
    public function __construct($name = null, $options = array()) {
        global $CFG;
        if (is_null($name)) {
            $name = 'addselect';
        }
        $options['multiselect'] = false;
        $options['exclude'] = explode(',', $CFG->siteadmins);
        parent::__construct($name, $options);
    }

    public function find_users($search) {
        global $CFG, $DB;
        list($wherecondition, $params) = $this->search_sql($search, '');

        $fields      = 'SELECT ' . $this->required_fields_sql('');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user}
                WHERE $wherecondition AND mnethostid = :localmnet";

                $params['localmnet'] = $CFG->mnet_localhost_id;

        list($sort, $sortparams) = users_order_by_sql('', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

                if (!$this->is_validating()) {
            $potentialcount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialcount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialcount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potusersmatching', 'core_role', $search);
        } else {
            $groupname = get_string('potusers', 'core_role');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = $CFG->admin . '/roles/lib.php';
        return $options;
    }
}
