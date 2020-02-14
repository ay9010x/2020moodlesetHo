<?php 
defined('MOODLE_INTERNAL') || die;

require_once __DIR__.'/../../../question/engine/bank.php';


class admin_setting_configmulticheckboxwithicon extends admin_setting_configmulticheckbox {
    
    protected $icons;

    
    public function __construct($name, $visiblename, $description, $defaultsetting, array $choices, array $icons) {
        $this->icons = $icons;
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices);
    }

    
    public function output_html($data, $query='') {
        if (!$this->load_choices() or empty($this->choices)) {
            return '';
        }
        $default = $this->get_defaultsetting();
        if (is_null($default)) {
            $default = array();
        }
        if (is_null($data)) {
            $data = array();
        }
        $options = array();
        $defaults = array();
        foreach ($this->choices as $key=>$description) {
            if (!empty($data[$key])) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            if (!empty($default[$key])) {
                $defaults[] = $description;
            }

            $options[] = '<input type="checkbox" id="'.$this->get_id().'_'.$key.'" name="'.$this->get_full_name().'['.$key.']" value="1" '.$checked.' />'
                .'<label for="'.$this->get_id().'_'.$key.'">'.$this->icons[$key].highlightfast($query, $description).'</label>';
        }

        if (is_null($default)) {
            $defaultinfo = NULL;
        } elseif (!empty($defaults)) {
            $defaultinfo = implode(', ', $defaults);
        } else {
            $defaultinfo = get_string('none');
        }

        $return = '<div class="form-multicheckbox">';
        $return .= '<input type="hidden" name="'.$this->get_full_name().'[xxxxx]" value="1" />';         if ($options) {
            $return .= '<ul>';
            foreach ($options as $option) {
                $return .= '<li>'.$option.'</li>';
            }
            $return .= '</ul>';
        }
        $return .= '</div>';

        return format_admin_setting($this, $this->visiblename, $return, $this->description, false, '', $defaultinfo, $query);
    }
}


class admin_setting_configmulticheckboxmodtypes extends admin_setting_configmulticheckboxwithicon {
    
    public function __construct($name, $visiblename, $description, $defaultsetting = null) {
        global $DB, $OUTPUT;
        $choices = array();
        $icons = array();
        foreach ($DB->get_records('modules', array(), 'name ASC') as $module) {
            $choices[$module->name] = get_string('modulename', $module->name);
            $icons[$module->name] = ' ' . $OUTPUT->pix_icon('icon', '', $module->name, array('class' => 'icon'));
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices, $icons);
    }
}


class admin_setting_configmulticheckboxqtypes extends admin_setting_configmulticheckboxwithicon {
    
    public function __construct($name, $visiblename, $description, $defaultsetting = null) {
        global $OUTPUT;
        $choices = array();
        $icons = array();
        $qtypes = question_bank::get_all_qtypes();
                unset($qtypes['missingtype']);
        unset($qtypes['random']);
                $qtypenames = array_map(function ($qtype) { return $qtype->local_name(); }, $qtypes);
        foreach (question_bank::sort_qtype_array($qtypenames) as $name => $label) {
            $choices[$name] = $label;
            $icons[$name] = ' ' . $OUTPUT->pix_icon('icon', '', $qtypes[$name]->plugin_name()) . ' ';
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices, $icons);
    }
}
