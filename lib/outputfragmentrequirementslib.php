<?php



defined('MOODLE_INTERNAL') || die();


class fragment_requirements_manager extends page_requirements_manager {

    
    public function __construct() {
        parent::__construct();
                $this->headdone = true;
    }

    
    protected function get_amd_footercode() {
        global $CFG;
        $output = '';

                $prefix = "require(['core/first'], function() {\n";
        $suffix = "\n});";
        $output .= html_writer::script($prefix . implode(";\n", $this->amdjscode) . $suffix);
        return $output;
    }


    
    public function get_end_code() {
        global $CFG;

        $output = '';

                $output .= $this->get_amd_footercode();

                $output .= $this->get_extra_modules_code();

                if ($this->jsincludes['footer']) {
            foreach ($this->jsincludes['footer'] as $url) {
                $output .= html_writer::script('', $url);
            }
        }

        if (!empty($this->stringsforjs)) {
                        $strings = array();
            foreach ($this->stringsforjs as $component => $v) {
                foreach ($v as $indentifier => $langstring) {
                    $strings[$component][$indentifier] = $langstring->out();
                }
            }
                        $output .= html_writer::script('require(["jquery"], function($) {
                M.str = $.extend(true, M.str, ' . json_encode($strings) . ');
            });');
        }

                if ($this->jsinitvariables['footer']) {
            $js = '';
            foreach ($this->jsinitvariables['footer'] as $data) {
                list($var, $value) = $data;
                $js .= js_writer::set_variable($var, $value, true);
            }
            $output .= html_writer::script($js);
        }

        $inyuijs = $this->get_javascript_code(false);
        $ondomreadyjs = $this->get_javascript_code(true);
                $jsinit = $this->get_javascript_init_code();
        $handlersjs = $this->get_event_handler_code();

                $js = "(function() {{$inyuijs}{$ondomreadyjs}{$jsinit}{$handlersjs}})();";

        $output .= html_writer::script($js);

        return $output;
    }
}
