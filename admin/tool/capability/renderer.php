<?php




class tool_capability_renderer extends plugin_renderer_base {

    
    protected function get_permission_strings() {
        static $strpermissions;
        if (!$strpermissions) {
            $strpermissions = array(
                CAP_INHERIT => new lang_string('inherit', 'role'),
                CAP_ALLOW => new lang_string('allow', 'role'),
                CAP_PREVENT => new lang_string('prevent', 'role'),
                CAP_PROHIBIT => new lang_string('prohibit', 'role')
            );
        }
        return $strpermissions;
    }

    
    protected function get_permission_classes() {
        static $permissionclasses;
        if (!$permissionclasses) {
            $permissionclasses = array(
                CAP_INHERIT => 'inherit',
                CAP_ALLOW => 'allow',
                CAP_PREVENT => 'prevent',
                CAP_PROHIBIT => 'prohibit',
            );
        }
        return $permissionclasses;
    }

    
    public function capability_comparison_table(array $capabilities, $contextid, array $roles) {

        $strpermissions = $this->get_permission_strings();
        $permissionclasses = $this->get_permission_classes();

        if ($contextid === context_system::instance()->id) {
            $strpermissions[CAP_INHERIT] = new lang_string('notset', 'role');
        }

        $table = new html_table();
        $table->attributes['class'] = 'comparisontable';
        $table->head = array('&nbsp;');
        foreach ($roles as $role) {
            $url = new moodle_url('/admin/roles/define.php', array('action' => 'view', 'roleid' => $role->id));
            $table->head[] = html_writer::div(html_writer::link($url, $role->localname));
        }
        $table->data = array();

        foreach ($capabilities as $capability) {
            $contexts = tool_capability_calculate_role_data($capability, $roles);
            $captitle = new html_table_cell(get_capability_string($capability) . html_writer::span($capability));
            $captitle->header = true;

            $row = new html_table_row(array($captitle));

            foreach ($roles as $role) {
                if (isset($contexts[$contextid]->rolecapabilities[$role->id])) {
                    $permission = $contexts[$contextid]->rolecapabilities[$role->id];
                } else {
                    $permission = CAP_INHERIT;
                }
                $cell = new html_table_cell($strpermissions[$permission]);
                $cell->attributes['class'] = $permissionclasses[$permission];
                $row->cells[] = $cell;
            }

            $table->data[] = $row;
        }

                if ($contextid == context_system::instance()->id) {
            $url = new moodle_url('/admin/roles/manage.php');
            $title = get_string('changeroles', 'tool_capability');
        } else {
            $url = new moodle_url('/admin/roles/override.php', array('contextid' => $contextid));
            $title = get_string('changeoverrides', 'tool_capability');
        }
        $context = context::instance_by_id($contextid);
        $html = $this->output->heading(html_writer::link($url, $context->get_context_name(), array('title' => $title)), 3);
        $html .= html_writer::table($table);
                if (!empty($contexts[$contextid]->children)) {
            foreach ($contexts[$contextid]->children as $childcontextid) {
                $html .= $this->capability_comparison_table($capabilities, $childcontextid, $roles, true);
            }
        }
        return $html;
    }

}