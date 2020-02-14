<?php




defined('MOODLE_INTERNAL') || die();


function editors_get_preferred_editor($format = NULL) {
    global $USER, $CFG;

    if (!empty($CFG->adminsetuppending)) {
                return get_texteditor('textarea');
    }

    $enabled = editors_get_enabled();

    $preference = get_user_preferences('htmleditor', '', $USER);

    if (isset($enabled[$preference])) {
                $editor = $enabled[$preference];
        unset($enabled[$preference]);
        array_unshift($enabled, $editor);
    }

        $editor = false;
    foreach ($enabled as $e) {
        if (!$e->supported_by_browser()) {
                        continue;
        }
        if (!$supports = $e->get_supported_formats()) {
                        continue;
        }
        if (is_null($format) || in_array($format, $supports)) {
                        $editor = $e;
            break;
        }
    }

    if (!$editor) {
        $editor = get_texteditor('textarea');     }

    return $editor;
}


function editors_get_preferred_format() {
    global $USER;

    $editor = editors_get_preferred_editor();
    return $editor->get_preferred_format();
}


function editors_get_enabled() {
    global $CFG;

    if (empty($CFG->texteditors)) {
        $CFG->texteditors = 'atto,tinymce,textarea';
    }
    $active = array();
    foreach(explode(',', $CFG->texteditors) as $e) {
        if ($editor = get_texteditor($e)) {
            $active[$e] = $editor;
        }
    }

    if (empty($active)) {
        return array('textarea'=>get_texteditor('textarea'));     }

    return $active;
}


function get_texteditor($editorname) {
    global $CFG;

    $libfile = "$CFG->libdir/editor/$editorname/lib.php";
    if (!file_exists($libfile)) {
        return false;
    }
    require_once($libfile);
    $classname = $editorname.'_texteditor';
    if (!class_exists($classname)) {
        return false;
    }
    return new $classname();
}


function editors_get_available() {
    $editors = array();
    foreach (core_component::get_plugin_list('editor') as $editorname => $dir) {
        $editors[$editorname] = get_string('pluginname', 'editor_'.$editorname);
    }
    return $editors;
}


function editors_head_setup() {
    global $CFG;

    if (empty($CFG->texteditors)) {
        $CFG->texteditors = 'atto,tinymce,textarea';
    }
    $active = explode(',', $CFG->texteditors);

    foreach ($active as $editorname) {
        if (!$editor = get_texteditor($editorname)) {
            continue;
        }
        if (!$editor->supported_by_browser()) {
                        continue;
        }
        $editor->head_setup();
    }
}


abstract class texteditor {
    
    public abstract function supported_by_browser();

    
    public abstract function get_supported_formats();

    
    public abstract function get_preferred_format();

    
    public abstract function supports_repositories();

    
    protected $text = '';

    
    public function set_text($text) {
        $this->text = $text;
    }

    
    public function get_text() {
        return $this->text;
    }

    
    public abstract function use_editor($elementid, array $options=null, $fpoptions = null);

    
    public function head_setup() {
    }
}
