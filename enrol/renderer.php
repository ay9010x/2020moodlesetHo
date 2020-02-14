<?php




class core_enrol_renderer extends plugin_renderer_base {

    
    public function render_course_enrolment_users_table(course_enrolment_users_table $table,
            moodleform $mform) {

        $table->initialise_javascript();

        $buttons = $table->get_manual_enrol_buttons();
        $buttonhtml = '';
        if (count($buttons) > 0) {
            $buttonhtml .= html_writer::start_tag('div', array('class' => 'enrol_user_buttons enrol-users-page-action'));
            foreach ($buttons as $button) {
                $buttonhtml .= $this->render($button);
            }
            $buttonhtml .= html_writer::end_tag('div');
        }

        $content = '';
        if (!empty($buttonhtml)) {
            $content .= $buttonhtml;
        }
        $content .= $mform->render();

        $content .= $this->output->render($table->get_paging_bar());

                        if ($table->has_bulk_user_enrolment_operations()) {
            $content .= html_writer::start_tag('form', array('action' => new moodle_url('/enrol/bulkchange.php'), 'method' => 'post'));
            foreach ($table->get_combined_url_params() as $key => $value) {
                if ($key == 'action') {
                    continue;
                }
                $content .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $key, 'value' => $value));
            }
            $content .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'bulkchange'));
            $content .= html_writer::table($table);
            $content .= html_writer::start_tag('div', array('class' => 'singleselect bulkuserop'));
            $content .= html_writer::start_tag('select', array('name' => 'bulkuserop'));
            $content .= html_writer::tag('option', get_string('withselectedusers', 'enrol'), array('value' => ''));
            $options = array('' => get_string('withselectedusers', 'enrol'));
            foreach ($table->get_bulk_user_enrolment_operations() as $operation) {
                $content .= html_writer::tag('option', $operation->get_title(), array('value' => $operation->get_identifier()));
            }
            $content .= html_writer::end_tag('select');
            $content .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('go')));
            $content .= html_writer::end_tag('div');

            $content .= html_writer::end_tag('form');
        } else {
            $content .= html_writer::table($table);
        }
        $content .= $this->output->render($table->get_paging_bar());
        if (!empty($buttonhtml)) {
            $content .= $buttonhtml;
        }
        return $content;
    }

    
    protected function render_enrol_user_button(enrol_user_button $button) {
        $attributes = array('type'     => 'submit',
                            'value'    => $button->label,
                            'disabled' => $button->disabled ? 'disabled' : null,
                            'title'    => $button->tooltip);

        if ($button->actions) {
            $id = html_writer::random_id('single_button');
            $attributes['id'] = $id;
            foreach ($button->actions as $action) {
                $this->add_action_handler($action, $id);
            }
        }
        $button->initialise_js($this->page);

                $output = html_writer::empty_tag('input', $attributes);

                $params = $button->url->params();
        if ($button->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $var => $val) {
            $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
        }

                $output = html_writer::tag('div', $output);

                if ($button->method === 'get') {
            $url = $button->url->out_omit_querystring(true);         } else {
            $url = $button->url->out_omit_querystring();             }
        if ($url === '') {
            $url = '#';         }
        $attributes = array('method' => $button->method,
                            'action' => $url,
                            'id'     => $button->formid);
        $output = html_writer::tag('form', $output, $attributes);

                return html_writer::tag('div', $output, array('class' => $button->class));
    }

    
    protected function render_course_enrolment_other_users_table(course_enrolment_other_users_table $table) {

        $table->initialise_javascript();

        $content = '';
        $searchbutton = $table->get_user_search_button();
        if ($searchbutton) {
            $content .= $this->output->render($searchbutton);
        }
        $content .= html_writer::tag('div', get_string('otheruserdesc', 'enrol'), array('class'=>'otherusersdesc'));
        $content .= $this->output->render($table->get_paging_bar());
        $content .= html_writer::table($table);
        $content .= $this->output->render($table->get_paging_bar());
        $searchbutton = $table->get_user_search_button();
        if ($searchbutton) {
            $content .= $this->output->render($searchbutton);
        }
        return $content;
    }

    
    public function user_roles_and_actions($userid, $roles, $assignableroles, $canassign, $pageurl) {
        $iconenrolremove = $this->output->pix_url('t/delete');



                $rolesoutput = '';
        foreach ($roles as $roleid=>$role) {
            if ($canassign and (is_siteadmin() or isset($assignableroles[$roleid])) and !$role['unchangeable']) {
                $strunassign = get_string('unassignarole', 'role', $role['text']);
                $icon = html_writer::empty_tag('img', array('alt'=>$strunassign, 'src'=>$iconenrolremove));
                $url = new moodle_url($pageurl, array('action'=>'unassign', 'roleid'=>$roleid, 'user'=>$userid));
                $rolesoutput .= html_writer::tag('div', $role['text'] . html_writer::link($url, $icon, array('class'=>'unassignrolelink', 'rel'=>$roleid, 'title'=>$strunassign)), array('class'=>'role role_'.$roleid));
            } else {
                $rolesoutput .= html_writer::tag('div', $role['text'], array('class'=>'role unchangeable', 'rel'=>$roleid));
            }
        }
        $output = '';
        if (!empty($assignableroles) && $canassign) {
            $roleids = array_keys($roles);
            $hasallroles = true;
            foreach (array_keys($assignableroles) as $key) {
                if (!in_array($key, $roleids)) {
                    $hasallroles = false;
                    break;
                }
            }
            if (!$hasallroles) {
                $url = new moodle_url($pageurl, array('action' => 'assign', 'user' => $userid));
                $roleicon = $this->output->pix_icon('i/assignroles', get_string('assignroles', 'role'));
                $link = html_writer::link($url, $roleicon, array('class' => 'assignrolelink'));
                $output = html_writer::tag('div', $link, array('class'=>'addrole'));
            }
        }
        $output .= html_writer::tag('div', $rolesoutput, array('class'=>'roles'));
        return $output;
    }

    
    public function user_groups_and_actions($userid, $groups, $allgroups, $canmanagegroups, $pageurl) {
        $iconenrolremove = $this->output->pix_url('t/delete');

        $groupicon = $this->output->pix_icon('i/group', get_string('addgroup', 'group'));

        $groupoutput = '';
        foreach($groups as $groupid=>$name) {
            if ($canmanagegroups and groups_remove_member_allowed($groupid, $userid)) {
                $icon = html_writer::empty_tag('img', array('alt'=>get_string('removefromgroup', 'group', $name), 'src'=>$iconenrolremove));
                $url = new moodle_url($pageurl, array('action'=>'removemember', 'group'=>$groupid, 'user'=>$userid));
                $groupoutput .= html_writer::tag('div', $name . html_writer::link($url, $icon), array('class'=>'group', 'rel'=>$groupid));
            } else {
                $groupoutput .= html_writer::tag('div', $name, array('class'=>'group', 'rel'=>$groupid));
            }
        }
        $output = '';
        if ($canmanagegroups && (count($groups) < count($allgroups))) {
            $url = new moodle_url($pageurl, array('action'=>'addmember', 'user'=>$userid));
            $output .= html_writer::tag('div', html_writer::link($url, $groupicon), array('class'=>'addgroup'));
        }
        $output = $output.html_writer::tag('div', $groupoutput, array('class'=>'groups'));
        return $output;
    }

    
    public function user_enrolments_and_actions($enrolments) {
        $output = '';
        foreach ($enrolments as $ue) {
            $enrolmentoutput = $ue['text'].' '.$ue['period'];
            if ($ue['dimmed']) {
                $enrolmentoutput = html_writer::tag('span', $enrolmentoutput, array('class'=>'dimmed_text'));
            } else {
                $enrolmentoutput = html_writer::tag('span', $enrolmentoutput);
            }
            foreach ($ue['actions'] as $action) {
                $enrolmentoutput .= $this->render($action);
            }
            $output .= html_writer::tag('div', $enrolmentoutput, array('class'=>'enrolment'));
        }
        return $output;
    }

    
    protected function render_user_enrolment_action(user_enrolment_action $icon) {
        return html_writer::link($icon->get_url(), $this->output->render($icon->get_icon()), $icon->get_attributes());
    }
}


class course_enrolment_table extends html_table implements renderable {

    
    const PAGEVAR = 'page';

    
    const PERPAGEVAR = 'perpage';

    
    const SORTVAR = 'sort';

    
    const SORTDIRECTIONVAR = 'dir';

    
    const DEFAULTPERPAGE = 100;

    
    const DEFAULTSORT = 'lastname';

    
    const DEFAULTSORTDIRECTION = 'ASC';

    
    public $page = 0;

    
    public $pages = 0;

    
    public $perpage = 0;

    
    public $sort;

    
    public $sortdirection;

    
    protected $manager;

    
    protected $pagingbar = null;

    
    protected $totalusers = null;

    
    protected $users = null;

    
    protected $fields = array();

    
    protected $bulkoperations = array();

    
    protected static $sortablefields = array('firstname', 'lastname', 'firstnamephonetic', 'lastnamephonetic', 'middlename',
            'alternatename', 'idnumber', 'email', 'phone1', 'phone2', 'institution', 'department', 'lastaccess', 'lastcourseaccess' );

    
    public function __construct(course_enrolment_manager $manager) {

        $this->manager        = $manager;

        $this->page           = optional_param(self::PAGEVAR, 0, PARAM_INT);
        $this->perpage        = optional_param(self::PERPAGEVAR, self::DEFAULTPERPAGE, PARAM_INT);
        $this->sort           = optional_param(self::SORTVAR, self::DEFAULTSORT, PARAM_ALPHANUM);
        $this->sortdirection  = optional_param(self::SORTDIRECTIONVAR, self::DEFAULTSORTDIRECTION, PARAM_ALPHA);

        $this->attributes = array('class'=>'userenrolment');
        if (!in_array($this->sort, self::$sortablefields)) {
            $this->sort = self::DEFAULTSORT;
        }
        if ($this->page < 0) {
            $this->page = 0;
        }
        if ($this->sortdirection !== 'ASC' && $this->sortdirection !== 'DESC') {
            $this->sortdirection = self::DEFAULTSORTDIRECTION;
        }

        $this->id = html_writer::random_id();

                $plugin = $manager->get_filtered_enrolment_plugin();
        if ($plugin and enrol_is_enabled($plugin->get_name())) {
            $this->bulkoperations = $plugin->get_bulk_operations($manager);
        }
    }

    
    public function get_manual_enrol_buttons() {
        return $this->manager->get_manual_enrol_buttons();
    }

    
    public function get_field_sort_direction($field) {
        if ($field == $this->sort) {
            return ($this->sortdirection == 'ASC')?'DESC':'ASC';
        }
        return self::DEFAULTSORTDIRECTION;
    }

    
    public function set_fields($fields, $output) {
        $this->fields = $fields;
        $this->head = array();
        $this->colclasses = array();
        $this->align = array();
        $url = $this->manager->get_moodlepage()->url;

        if (!empty($this->bulkoperations)) {
                        $this->head[] = '';
            $this->colclasses[] = 'field col_bulkops';
        }

        foreach ($fields as $name => $label) {
            $newlabel = '';
            if (is_array($label)) {
                $bits = array();
                foreach ($label as $n => $l) {
                    if ($l === false) {
                        continue;
                    }
                    if (!in_array($n, self::$sortablefields)) {
                        $bits[] = $l;
                    } else {
                        $sorturl = new moodle_url($url, array(self::SORTVAR => $n, self::SORTDIRECTIONVAR => $this->get_field_sort_direction($n)));
                        $link = html_writer::link($sorturl, $fields[$name][$n]);
                        if ($this->sort == $n) {
                            $link .= $this->get_direction_icon($output, $n);
                        }
                        $bits[] = html_writer::tag('span', $link, array('class'=>'subheading_'.$n));

                    }
                }
                $newlabel = join(' / ', $bits);
            } else {
                if (!in_array($name, self::$sortablefields)) {
                    $newlabel = $label;
                } else {
                    $sorturl = new moodle_url($url, array(self::SORTVAR => $name, self::SORTDIRECTIONVAR => $this->get_field_sort_direction($name)));
                    $newlabel  = html_writer::link($sorturl, $fields[$name]);
                    if ($this->sort == $name) {
                        $newlabel .= $this->get_direction_icon($output, $name);
                    }
                }
            }
            $this->head[] = $newlabel;
            $this->colclasses[] = 'field col_'.$name;
        }
    }
    
    public function set_total_users($totalusers) {
        $this->totalusers = $totalusers;
        $this->pages = ceil($this->totalusers / $this->perpage);
        if ($this->page > $this->pages) {
            $this->page = $this->pages;
        }
    }
    
    public function set_users(array $users) {
        $this->users = $users;
        $hasbulkops = !empty($this->bulkoperations);
        foreach ($users as $userid=>$user) {
            $user = (array)$user;
            $row = new html_table_row();
            $row->attributes = array('class' => 'userinforow');
            $row->id = 'user_'.$userid;
            $row->cells = array();
            if ($hasbulkops) {
                                $input = html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'bulkuser[]', 'value' => $userid));
                $row->cells[] = new html_table_cell($input);
            }
            foreach ($this->fields as $field => $label) {
                if (is_array($label)) {
                    $bits = array();
                    foreach (array_keys($label) as $subfield) {
                        if (array_key_exists($subfield, $user)) {
                            $bits[] = html_writer::tag('div', $user[$subfield], array('class'=>'subfield subfield_'.$subfield));
                        }
                    }
                    if (empty($bits)) {
                        $bits[] = '&nbsp;';
                    }
                    $row->cells[] = new html_table_cell(join(' ', $bits));
                } else {
                    if (!array_key_exists($field, $user)) {
                        $user[$field] = '&nbsp;';
                    }
                    $row->cells[] = new html_table_cell($user[$field]);
                }
            }
            $this->data[] = $row;
        }
    }

    public function initialise_javascript() {
        if (has_capability('moodle/role:assign', $this->manager->get_context())) {
            $this->manager->get_moodlepage()->requires->strings_for_js(array(
                'assignroles',
                'confirmunassign',
                'confirmunassigntitle',
                'confirmunassignyes',
                'confirmunassignno'
            ), 'role');
            $modules = array('moodle-enrol-rolemanager', 'moodle-enrol-rolemanager-skin');
            $function = 'M.enrol.rolemanager.init';
            $arguments = array(
                'containerId'=>$this->id,
                'userIds'=>array_keys($this->users),
                'courseId'=>$this->manager->get_course()->id,
                'otherusers'=>isset($this->otherusers));
            $this->manager->get_moodlepage()->requires->yui_module($modules, $function, array($arguments));
        }
    }

    
    public function get_paging_bar() {
        if ($this->pagingbar == null) {
            $this->pagingbar = new paging_bar($this->totalusers, $this->page, $this->perpage, $this->manager->get_moodlepage()->url, self::PAGEVAR);
        }
        return $this->pagingbar;
    }

    
    protected function get_direction_icon($output, $field) {
        $direction = self::DEFAULTSORTDIRECTION;
        if ($this->sort == $field) {
            $direction = $this->sortdirection;
        }
        if ($direction === 'ASC') {
            return html_writer::empty_tag('img', array('alt' => '', 'class' => 'iconsort',
                'src' => $output->pix_url('t/sort_asc')));
        } else {
            return html_writer::empty_tag('img', array('alt' => '', 'class' => 'iconsort',
                'src' => $output->pix_url('t/sort_desc')));
        }
    }

    
    public function get_url_params() {
        return array(
            self::PAGEVAR => $this->page,
            self::PERPAGEVAR => $this->perpage,
            self::SORTVAR => $this->sort,
            self::SORTDIRECTIONVAR => $this->sortdirection
        );
    }

    
    public function get_combined_url_params() {
        return $this->get_url_params() + $this->manager->get_url_params();
    }

    
    public function set_bulk_user_enrolment_operations(array $bulkoperations) {
        $this->bulkoperations = $bulkoperations;
    }

    
    public function get_bulk_user_enrolment_operations() {
        return $this->bulkoperations;
    }

    
    public function has_bulk_user_enrolment_operations() {
        return !empty($this->bulkoperations);
    }
}


class course_enrolment_users_table extends course_enrolment_table {

}


class course_enrolment_other_users_table extends course_enrolment_table {

    public $otherusers = true;

    
    public function __construct(course_enrolment_manager $manager) {
        parent::__construct($manager);
        $this->attributes = array('class'=>'userenrolment otheruserenrolment');
    }

    
    public function get_user_search_button() {
        static $count = 0;
        if (!has_capability('moodle/role:assign', $this->manager->get_context())) {
            return false;
        }
        $count++;
        $url = new moodle_url('/admin/roles/assign.php', array('contextid'=>$this->manager->get_context()->id, 'sesskey'=>sesskey()));
        $control = new single_button($url, get_string('assignroles', 'role'), 'get');
        $control->class = 'singlebutton assignuserrole instance'.$count;
        if ($count == 1) {
            $this->manager->get_moodlepage()->requires->strings_for_js(array(
                    'ajaxoneuserfound',
                    'ajaxxusersfound',
                    'ajaxnext25',
                    'enrol',
                    'enrolmentoptions',
                    'enrolusers',
                    'enrolxusers',
                    'errajaxfailedenrol',
                    'errajaxsearch',
                    'foundxcohorts',
                    'none',
                    'usersearch',
                    'unlimitedduration',
                    'startdatetoday',
                    'durationdays',
                    'enrolperiod'), 'enrol');
            $this->manager->get_moodlepage()->requires->string_for_js('assignrole', 'role');

            $modules = array('moodle-enrol-otherusersmanager', 'moodle-enrol-otherusersmanager-skin');
            $function = 'M.enrol.otherusersmanager.init';
            $arguments = array(
                'courseId'=> $this->manager->get_course()->id,
                'ajaxUrl' => '/enrol/ajax.php',
                'url' => $this->manager->get_moodlepage()->url->out(false));
            $this->manager->get_moodlepage()->requires->yui_module($modules, $function, array($arguments));
        }
        return $control;
    }
}
