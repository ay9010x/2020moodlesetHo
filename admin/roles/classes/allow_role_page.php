<?php



defined('MOODLE_INTERNAL') || die();


abstract class core_role_allow_role_page {
    protected $tablename;
    protected $targetcolname;
    protected $roles;
    protected $allowed = null;

    
    public function __construct($tablename, $targetcolname) {
        $this->tablename = $tablename;
        $this->targetcolname = $targetcolname;
        $this->load_required_roles();
    }

    
    protected function load_required_roles() {
                $this->roles = role_fix_names(get_all_roles(), context_system::instance(), ROLENAME_ORIGINAL);
    }

    
    public function process_submission() {
        global $DB;
                $DB->delete_records($this->tablename);
        foreach ($this->roles as $fromroleid => $notused) {
            foreach ($this->roles as $targetroleid => $alsonotused) {
                if (optional_param('s_' . $fromroleid . '_' . $targetroleid, false, PARAM_BOOL)) {
                    $this->set_allow($fromroleid, $targetroleid);
                }
            }
        }
    }

    
    protected abstract function set_allow($fromroleid, $targetroleid);

    
    public function load_current_settings() {
        global $DB;
                $this->allowed = array();
        foreach ($this->roles as $role) {
                        $this->allowed[$role->id] = array_combine(array_keys($this->roles), array_fill(0, count($this->roles), false));
        }
        $rs = $DB->get_recordset($this->tablename);
        foreach ($rs as $allow) {
            $this->allowed[$allow->roleid][$allow->{$this->targetcolname}] = true;
        }
        $rs->close();
    }

    
    protected function is_allowed_target($targetroleid) {
        return true;
    }

    
    public function get_table() {
        $table = new html_table();
        $table->tablealign = 'center';
        $table->cellpadding = 5;
        $table->cellspacing = 0;
        $table->width = '90%';
        $table->align = array('left');
        $table->rotateheaders = true;
        $table->head = array('&#xa0;');
        $table->colclasses = array('');

                foreach ($this->roles as $targetrole) {
            $table->head[] = $targetrole->localname;
            $table->align[] = 'left';
            if ($this->is_allowed_target($targetrole->id)) {
                $table->colclasses[] = '';
            } else {
                $table->colclasses[] = 'dimmed_text';
            }
        }

                foreach ($this->roles as $fromrole) {
            $row = array($fromrole->localname);
            foreach ($this->roles as $targetrole) {
                $checked = '';
                $disabled = '';
                if ($this->allowed[$fromrole->id][$targetrole->id]) {
                    $checked = 'checked="checked" ';
                }
                if (!$this->is_allowed_target($targetrole->id)) {
                    $disabled = 'disabled="disabled" ';
                }
                $name = 's_' . $fromrole->id . '_' . $targetrole->id;
                $tooltip = $this->get_cell_tooltip($fromrole, $targetrole);
                $row[] = '<input type="checkbox" name="' . $name . '" id="' . $name .
                    '" title="' . $tooltip . '" value="1" ' . $checked . $disabled . '/>' .
                    '<label for="' . $name . '" class="accesshide">' . $tooltip . '</label>';
            }
            $table->data[] = $row;
        }

        return $table;
    }

    
    public abstract function get_intro_text();
}
