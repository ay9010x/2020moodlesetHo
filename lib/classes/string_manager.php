<?php



defined('MOODLE_INTERNAL') || die();


interface core_string_manager {
    
    public function get_string($identifier, $component = '', $a = null, $lang = null);

    
    public function string_exists($identifier, $component);

    
    public function string_deprecated($identifier, $component);

    
    public function get_list_of_countries($returnall = false, $lang = null);

    
    public function get_list_of_languages($lang = null, $standard = 'iso6392');

    
    public function translation_exists($lang, $includeall = true);

    
    public function get_list_of_translations($returnall = false);

    
    public function get_list_of_currencies($lang = null);

    
    public function load_component_strings($component, $lang, $disablecache=false, $disablelocal=false);

    
    public function reset_caches($phpunitreset = false);

    
    public function get_revision();
}

