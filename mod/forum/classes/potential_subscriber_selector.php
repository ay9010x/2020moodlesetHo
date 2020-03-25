<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');


class mod_forum_potential_subscriber_selector extends mod_forum_subscriber_selector_base {
    
    protected $forcesubscribed = false;
    
    protected $existingsubscribers = array();

    
    public function __construct($name, $options) {
        parent::__construct($name, $options);
        if (isset($options['forcesubscribed'])) {
            $this->forcesubscribed=true;
        }
    }

    
    protected function get_options() {
        $options = parent::get_options();
        if ($this->forcesubscribed===true) {
            $options['forcesubscribed']=1;
        }
        return $options;
    }

    
    public function find_users($search) {
        global $DB;

        $whereconditions = array();
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        if ($wherecondition) {
            $whereconditions[] = $wherecondition;
        }

        if (!$this->forcesubscribed) {
            $existingids = array();
            foreach ($this->existingsubscribers as $group) {
                foreach ($group as $user) {
                    $existingids[$user->id] = 1;
                }
            }
            if ($existingids) {
                list($usertest, $userparams) = $DB->get_in_or_equal(
                        array_keys($existingids), SQL_PARAMS_NAMED, 'existing', false);
                $whereconditions[] = 'u.id ' . $usertest;
                $params = array_merge($params, $userparams);
            }
        }

        if ($whereconditions) {
            $wherecondition = 'WHERE ' . implode(' AND ', $whereconditions);
        }

        list($esql, $eparams) = get_enrolled_sql($this->context, '', $this->currentgroup, true);
        $params = array_merge($params, $eparams);

        $fields      = 'SELECT ' . $this->required_fields_sql('u');

        $sql = " FROM {user} u
                 JOIN ($esql) je ON je.id = u.id
                      $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        $cm = get_coursemodule_from_instance('forum', $this->forumid);
        $modinfo = get_fast_modinfo($cm->course);
        $info = new \core_availability\info_module($modinfo->get_cm($cm->id));
        $availableusers = $info->filter_user_list($availableusers);

        if (empty($availableusers)) {
            return array();
        }

                if (!$this->is_validating()) {
            $potentialmemberscount = count($availableusers);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        if ($this->forcesubscribed) {
            return array(get_string("existingsubscribers", 'forum') => $availableusers);
        } else {
            return array(get_string("potentialsubscribers", 'forum') => $availableusers);
        }
    }

    
    public function set_existing_subscribers(array $users) {
        $this->existingsubscribers = $users;
    }

    
    public function set_force_subscribed($setting=true) {
        $this->forcesubscribed = true;
    }
}
