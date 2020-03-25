<?php



defined('MOODLE_INTERNAL') || die();


abstract class core_role_capability_table_base {
    
    protected $context;

    
    protected $capabilities = array();

    
    protected $id;

    
    protected $classes = array('rolecap');

    
    const NUM_CAPS_FOR_SEARCH = 12;

    
    public function __construct(context $context, $id) {
        $this->context = $context;
        $this->capabilities = $context->get_capabilities();
        $this->id = $id;
    }

    
    public function add_classes($classnames) {
        $this->classes = array_unique(array_merge($this->classes, $classnames));
    }

    
    public function display() {
        if (count($this->capabilities) > self::NUM_CAPS_FOR_SEARCH) {
            global $PAGE;
            $jsmodule = array(
                'name' => 'rolescapfilter',
                'fullpath' => '/admin/roles/module.js',
                'strings' => array(
                    array('filter', 'moodle'),
                    array('clear', 'moodle'),                ),
                'requires' => array('node', 'cookie', 'escape')
            );
            $PAGE->requires->js_init_call('M.core_role.init_cap_table_filter', array($this->id, $this->context->id), false,
                $jsmodule);
        }
        echo '<table class="' . implode(' ', $this->classes) . '" id="' . $this->id . '">' . "\n<thead>\n";
        echo '<tr><th class="name" align="left" scope="col">' . get_string('capability', 'core_role') . '</th>';
        $this->add_header_cells();
        echo "</tr>\n</thead>\n<tbody>\n";

                $contextlevel = 0;
        $component = '';
        foreach ($this->capabilities as $capability) {
            if ($this->skip_row($capability)) {
                continue;
            }

                        if (component_level_changed($capability, $component, $contextlevel)) {
                $this->print_heading_row($capability);
            }
            $contextlevel = $capability->contextlevel;
            $component = $capability->component;

                        $rowattributes = $this->get_row_attributes($capability);
                        $rowclasses = array_unique(array_merge(array('rolecap'), $this->get_row_classes($capability)));
            if (array_key_exists('class', $rowattributes)) {
                $rowclasses = array_unique(array_merge($rowclasses, array($rowattributes['class'])));
            }
            $rowattributes['class']  = implode(' ', $rowclasses);

                        $contents = '<th scope="row" class="name"><span class="cap-desc">' . get_capability_docs_link($capability) .
                '<span class="cap-name">' . $capability->name . '</span></span></th>';

                        $contents .= $this->add_row_cells($capability);

            echo html_writer::tag('tr', $contents, $rowattributes);
        }

                echo "</tbody>\n</table>\n";
    }

    
    protected function print_heading_row($capability) {
        echo '<tr class="rolecapheading header"><td colspan="' . (1 + $this->num_extra_columns()) . '" class="header"><strong>' .
            get_component_string($capability->component, $capability->contextlevel) .
            '</strong></td></tr>';

    }

    
    protected abstract function add_header_cells();

    
    protected abstract function num_extra_columns();

    
    protected function skip_row($capability) {
        return false;
    }

    
    protected function get_row_classes($capability) {
        return array();
    }

    
    protected function get_row_attributes($capability) {
        return array();
    }

    
    protected abstract function add_row_cells($capability);
}
