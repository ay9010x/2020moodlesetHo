<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');


class mod_forum_existing_subscriber_selector extends mod_forum_subscriber_selector_base {

    
    public function find_users($search) {
        global $DB;
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['forumid'] = $this->forumid;

                list($esql, $eparams) = get_enrolled_sql($this->context, '', $this->currentgroup, true);
        $fields = $this->required_fields_sql('u');
        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $params = array_merge($params, $eparams, $sortparams);

        $subscribers = $DB->get_records_sql("SELECT $fields
                                               FROM {user} u
                                               JOIN ($esql) je ON je.id = u.id
                                               JOIN {forum_subscriptions} s ON s.userid = u.id
                                              WHERE $wherecondition AND s.forum = :forumid
                                           ORDER BY $sort", $params);

        $cm = get_coursemodule_from_instance('forum', $this->forumid);
        $modinfo = get_fast_modinfo($cm->course);
        $info = new \core_availability\info_module($modinfo->get_cm($cm->id));
        $subscribers = $info->filter_user_list($subscribers);

        return array(get_string("existingsubscribers", 'forum') => $subscribers);
    }

}
