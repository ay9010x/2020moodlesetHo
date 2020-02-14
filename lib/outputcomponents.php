<?php



defined('MOODLE_INTERNAL') || die();


interface renderable {
    }


interface templatable {

    
    public function export_for_template(renderer_base $output);
}


class file_picker implements renderable {

    
    public $options;

    
    public function __construct(stdClass $options) {
        global $CFG, $USER, $PAGE;
        require_once($CFG->dirroot. '/repository/lib.php');
        $defaults = array(
            'accepted_types'=>'*',
            'return_types'=>FILE_INTERNAL,
            'env' => 'filepicker',
            'client_id' => uniqid(),
            'itemid' => 0,
            'maxbytes'=>-1,
            'maxfiles'=>1,
            'buttonname'=>false
        );
        foreach ($defaults as $key=>$value) {
            if (empty($options->$key)) {
                $options->$key = $value;
            }
        }

        $options->currentfile = '';
        if (!empty($options->itemid)) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if (empty($options->filename)) {
                if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $options->itemid, 'id DESC', false)) {
                    $file = reset($files);
                }
            } else {
                $file = $fs->get_file($usercontext->id, 'user', 'draft', $options->itemid, $options->filepath, $options->filename);
            }
            if (!empty($file)) {
                $options->currentfile = html_writer::link(moodle_url::make_draftfile_url($file->get_itemid(), $file->get_filepath(), $file->get_filename()), $file->get_filename());
            }
        }

                $this->options = initialise_filepicker($options);

                foreach ($options as $name=>$value) {
            if (!isset($this->options->$name)) {
                $this->options->$name = $value;
            }
        }
    }
}


class user_picture implements renderable {
    
    protected static $fields = array('id', 'picture', 'firstname', 'lastname', 'firstnamephonetic', 'lastnamephonetic',
            'middlename', 'alternatename', 'imagealt', 'email');

    
    public $user;

    
    public $courseid;

    
    public $link = true;

    
    public $size = 35;

    
    public $alttext = true;

    
    public $popup = false;

    
    public $class = 'userpicture';

    
    public $visibletoscreenreaders = true;

    
    public function __construct(stdClass $user) {
        global $DB;

        if (empty($user->id)) {
            throw new coding_exception('User id is required when printing user avatar image.');
        }

                $needrec = false;
        foreach (self::$fields as $field) {
            if (!array_key_exists($field, $user)) {
                $needrec = true;
                debugging('Missing '.$field.' property in $user object, this is a performance problem that needs to be fixed by a developer. '
                          .'Please use user_picture::fields() to get the full list of required fields.', DEBUG_DEVELOPER);
                break;
            }
        }

        if ($needrec) {
            $this->user = $DB->get_record('user', array('id'=>$user->id), self::fields(), MUST_EXIST);
        } else {
            $this->user = clone($user);
        }
    }

    
    public static function fields($tableprefix = '', array $extrafields = NULL, $idalias = 'id', $fieldprefix = '') {
        if (!$tableprefix and !$extrafields and !$idalias) {
            return implode(',', self::$fields);
        }
        if ($tableprefix) {
            $tableprefix .= '.';
        }
        foreach (self::$fields as $field) {
            if ($field === 'id' and $idalias and $idalias !== 'id') {
                $fields[$field] = "$tableprefix$field AS $idalias";
            } else {
                if ($fieldprefix and $field !== 'id') {
                    $fields[$field] = "$tableprefix$field AS $fieldprefix$field";
                } else {
                    $fields[$field] = "$tableprefix$field";
                }
            }
        }
                if ($extrafields) {
            foreach ($extrafields as $e) {
                if ($e === 'id' or isset($fields[$e])) {
                    continue;
                }
                if ($fieldprefix) {
                    $fields[$e] = "$tableprefix$e AS $fieldprefix$e";
                } else {
                    $fields[$e] = "$tableprefix$e";
                }
            }
        }
        return implode(',', $fields);
    }

    
    public static function unalias(stdClass $record, array $extrafields = null, $idalias = 'id', $fieldprefix = '') {

        if (empty($idalias)) {
            $idalias = 'id';
        }

        $return = new stdClass();

        foreach (self::$fields as $field) {
            if ($field === 'id') {
                if (property_exists($record, $idalias)) {
                    $return->id = $record->{$idalias};
                }
            } else {
                if (property_exists($record, $fieldprefix.$field)) {
                    $return->{$field} = $record->{$fieldprefix.$field};
                }
            }
        }
                if ($extrafields) {
            foreach ($extrafields as $e) {
                if ($e === 'id' or property_exists($return, $e)) {
                    continue;
                }
                $return->{$e} = $record->{$fieldprefix.$e};
            }
        }

        return $return;
    }

    
    public function get_url(moodle_page $page, renderer_base $renderer = null) {
        global $CFG;

        if (is_null($renderer)) {
            $renderer = $page->get_renderer('core');
        }

                        if (empty($this->size)) {
            $filename = 'f2';
            $size = 35;
        } else if ($this->size === true or $this->size == 1) {
            $filename = 'f1';
            $size = 100;
        } else if ($this->size > 100) {
            $filename = 'f3';
            $size = (int)$this->size;
        } else if ($this->size >= 50) {
            $filename = 'f1';
            $size = (int)$this->size;
        } else {
            $filename = 'f2';
            $size = (int)$this->size;
        }

        $defaulturl = $renderer->pix_url('u/'.$filename); 
        if ((!empty($CFG->forcelogin) and !isloggedin()) ||
            (!empty($CFG->forceloginforprofileimage) && (!isloggedin() || isguestuser()))) {
                                                return $defaulturl;
        }

                if (!empty($this->user->deleted) or strpos($this->user->email, '@') === false) {
                                    return $defaulturl;
        }

                if ($this->user->picture > 0) {
            if (!empty($this->user->contextid)) {
                $contextid = $this->user->contextid;
            } else {
                $context = context_user::instance($this->user->id, IGNORE_MISSING);
                if (!$context) {
                                        return $defaulturl;
                }
                $contextid = $context->id;
            }

            $path = '/';
            if (clean_param($page->theme->name, PARAM_THEME) == $page->theme->name) {
                                                                                $path .= $page->theme->name.'/';
            }
                        $url = moodle_url::make_pluginfile_url($contextid, 'user', 'icon', NULL, $path, $filename);
            $url->param('rev', $this->user->picture);
            return $url;
        }

        if ($this->user->picture == 0 and !empty($CFG->enablegravatar)) {
                        if ($size < 1 || $size > 512) {
                $size = 35;
            }
                        $md5 = md5(strtolower(trim($this->user->email)));
            
                        if (empty($CFG->gravatardefaulturl)) {
                $absoluteimagepath = $page->theme->resolve_image_location('u/'.$filename, 'core');
                if (strpos($absoluteimagepath, $CFG->dirroot) === 0) {
                    $gravatardefault = $CFG->wwwroot . substr($absoluteimagepath, strlen($CFG->dirroot));
                } else {
                    $gravatardefault = $CFG->wwwroot . '/pix/u/' . $filename . '.png';
                }
            } else {
                $gravatardefault = $CFG->gravatardefaulturl;
            }

                                    if (is_https()) {
                $gravatardefault = str_replace($CFG->wwwroot, $CFG->httpswwwroot, $gravatardefault);                 return new moodle_url("https://secure.gravatar.com/avatar/{$md5}", array('s' => $size, 'd' => $gravatardefault));
            } else {
                return new moodle_url("http://www.gravatar.com/avatar/{$md5}", array('s' => $size, 'd' => $gravatardefault));
            }
        }

        return $defaulturl;
    }
}


class help_icon implements renderable {

    
    public $identifier;

    
    public $component;

    
    public $linktext = null;

    
    public function __construct($identifier, $component) {
        $this->identifier = $identifier;
        $this->component  = $component;
    }

    
    public function diag_strings() {
        $sm = get_string_manager();
        if (!$sm->string_exists($this->identifier, $this->component)) {
            debugging("Help title string does not exist: [$this->identifier, $this->component]");
        }
        if (!$sm->string_exists($this->identifier.'_help', $this->component)) {
            debugging("Help contents string does not exist: [{$this->identifier}_help, $this->component]");
        }
    }
}



class pix_icon implements renderable, templatable {

    
    var $pix;

    
    var $component;

    
    var $attributes = array();

    
    public function __construct($pix, $alt, $component='moodle', array $attributes = null) {
        $this->pix        = $pix;
        $this->component  = $component;
        $this->attributes = (array)$attributes;

        if (empty($this->attributes['class'])) {
            $this->attributes['class'] = 'smallicon';
        }

                if (!is_null($alt)) {
            $this->attributes['alt'] = $alt;

                        if (!isset($this->attributes['title'])) {
                $this->attributes['title'] = $this->attributes['alt'];
            }
        } else {
            unset($this->attributes['alt']);
        }

        if (empty($this->attributes['title'])) {
                                    unset($this->attributes['title']);
        }
    }

    
    public function export_for_template(renderer_base $output) {
        $attributes = $this->attributes;
        $attributes['src'] = $output->pix_url($this->pix, $this->component);
        $templatecontext = array();
        foreach ($attributes as $name => $value) {
            $templatecontext[] = array('name' => $name, 'value' => $value);
        }
        $data = array('attributes' => $templatecontext);

        return $data;
    }
}


class pix_emoticon extends pix_icon implements renderable {

    
    public function __construct($pix, $alt, $component = 'moodle', array $attributes = array()) {
        if (empty($attributes['class'])) {
            $attributes['class'] = 'emoticon';
        }
        parent::__construct($pix, $alt, $component, $attributes);
    }
}


class single_button implements renderable {

    
    var $url;

    
    var $label;

    
    var $method = 'post';

    
    var $class = 'singlebutton';

    
    var $disabled = false;

    
    var $tooltip = null;

    
    var $formid;

    
    var $actions = array();

    
    var $params;

    
    var $actionid;

    
    public function __construct(moodle_url $url, $label, $method='post') {
        $this->url    = clone($url);
        $this->label  = $label;
        $this->method = $method;
    }

    
    public function add_confirm_action($confirmmessage) {
        $this->add_action(new confirm_action($confirmmessage));
    }

    
    public function add_action(component_action $action) {
        $this->actions[] = $action;
    }
}



class single_select implements renderable {

    
    var $url;

    
    var $name;

    
    var $options;

    
    var $selected;

    
    var $nothing;

    
    var $attributes = array();

    
    var $label = '';

    
    var $labelattributes = array();

    
    var $method = 'get';

    
    var $class = 'singleselect';

    
    var $disabled = false;

    
    var $tooltip = null;

    
    var $formid = null;

    
    var $helpicon = null;

    
    public function __construct(moodle_url $url, $name, array $options, $selected = '', $nothing = array('' => 'choosedots'), $formid = null) {
        $this->url      = $url;
        $this->name     = $name;
        $this->options  = $options;
        $this->selected = $selected;
        $this->nothing  = $nothing;
        $this->formid   = $formid;
    }

    
    public function add_confirm_action($confirmmessage) {
        $this->add_action(new component_action('submit', 'M.util.show_confirm_dialog', array('message' => $confirmmessage)));
    }

    
    public function add_action(component_action $action) {
        $this->actions[] = $action;
    }

    
    public function set_old_help_icon($helppage, $title, $component = 'moodle') {
        throw new coding_exception('set_old_help_icon() can not be used any more, please see set_help_icon().');
    }

    
    public function set_help_icon($identifier, $component = 'moodle') {
        $this->helpicon = new help_icon($identifier, $component);
    }

    
    public function set_label($label, $attributes = array()) {
        $this->label = $label;
        $this->labelattributes = $attributes;

    }
}


class url_select implements renderable {
    
    var $urls;

    
    var $selected;

    
    var $nothing;

    
    var $attributes = array();

    
    var $label = '';

    
    var $labelattributes = array();

    
    var $class = 'urlselect';

    
    var $disabled = false;

    
    var $tooltip = null;

    
    var $formid = null;

    
    var $helpicon = null;

    
    var $showbutton = null;

    
    public function __construct(array $urls, $selected = '', $nothing = array('' => 'choosedots'), $formid = null, $showbutton = null) {
        $this->urls       = $urls;
        $this->selected   = $selected;
        $this->nothing    = $nothing;
        $this->formid     = $formid;
        $this->showbutton = $showbutton;
    }

    
    public function set_old_help_icon($helppage, $title, $component = 'moodle') {
        throw new coding_exception('set_old_help_icon() can not be used any more, please see set_help_icon().');
    }

    
    public function set_help_icon($identifier, $component = 'moodle') {
        $this->helpicon = new help_icon($identifier, $component);
    }

    
    public function set_label($label, $attributes = array()) {
        $this->label = $label;
        $this->labelattributes = $attributes;
    }
}


class action_link implements renderable {

    
    public $url;

    
    public $text;

    
    public $attributes;

    
    public $actions;

    
    public $icon;

    
    public function __construct(moodle_url $url,
                                $text,
                                component_action $action=null,
                                array $attributes=null,
                                pix_icon $icon=null) {
        $this->url = clone($url);
        $this->text = $text;
        $this->attributes = (array)$attributes;
        if ($action) {
            $this->add_action($action);
        }
        $this->icon = $icon;
    }

    
    public function add_action(component_action $action) {
        $this->actions[] = $action;
    }

    
    public function add_class($class) {
        if (empty($this->attributes['class'])) {
            $this->attributes['class'] = $class;
        } else {
            $this->attributes['class'] .= ' ' . $class;
        }
    }

    
    public function has_class($class) {
        return strpos(' ' . $this->attributes['class'] . ' ', ' ' . $class . ' ') !== false;
    }
}


class html_writer {

    
    public static function tag($tagname, $contents, array $attributes = null) {
        return self::start_tag($tagname, $attributes) . $contents . self::end_tag($tagname);
    }

    
    public static function start_tag($tagname, array $attributes = null) {
        return '<' . $tagname . self::attributes($attributes) . '>';
    }

    
    public static function end_tag($tagname) {
        return '</' . $tagname . '>';
    }

    
    public static function empty_tag($tagname, array $attributes = null) {
        return '<' . $tagname . self::attributes($attributes) . ' />';
    }

    
    public static function nonempty_tag($tagname, $contents, array $attributes = null) {
        if ($contents === '' || is_null($contents)) {
            return '';
        }
        return self::tag($tagname, $contents, $attributes);
    }

    
    public static function attribute($name, $value) {
        if ($value instanceof moodle_url) {
            return ' ' . $name . '="' . $value->out() . '"';
        }

                if ($value === null) {
            return '';
        }

                return ' ' . $name . '="' . s($value) . '"';
    }

    
    public static function attributes(array $attributes = null) {
        $attributes = (array)$attributes;
        $output = '';
        foreach ($attributes as $name => $value) {
            $output .= self::attribute($name, $value);
        }
        return $output;
    }

    
    public static function img($src, $alt, array $attributes = null) {
        $attributes = (array)$attributes;
        $attributes['src'] = $src;
        $attributes['alt'] = $alt;

        return self::empty_tag('img', $attributes);
    }

    
    public static function random_id($base='random') {
        static $counter = 0;
        static $uniq;

        if (!isset($uniq)) {
            $uniq = uniqid();
        }

        $counter++;
        return $base.$uniq.$counter;
    }

    
    public static function link($url, $text, array $attributes = null) {
        $attributes = (array)$attributes;
        $attributes['href']  = $url;
        return self::tag('a', $text, $attributes);
    }

    
    public static function checkbox($name, $value, $checked = true, $label = '', array $attributes = null) {
        $attributes = (array)$attributes;
        $output = '';

        if ($label !== '' and !is_null($label)) {
            if (empty($attributes['id'])) {
                $attributes['id'] = self::random_id('checkbox_');
            }
        }
        $attributes['type']    = 'checkbox';
        $attributes['value']   = $value;
        $attributes['name']    = $name;
        $attributes['checked'] = $checked ? 'checked' : null;

        $output .= self::empty_tag('input', $attributes);

        if ($label !== '' and !is_null($label)) {
            $output .= self::tag('label', $label, array('for'=>$attributes['id']));
        }

        return $output;
    }

    
    public static function select_yes_no($name, $selected=true, array $attributes = null) {
        $options = array('1'=>get_string('yes'), '0'=>get_string('no'));
        return self::select($options, $name, $selected, null, $attributes);
    }

    
    public static function select(array $options, $name, $selected = '', $nothing = array('' => 'choosedots'), array $attributes = null) {
        $attributes = (array)$attributes;
        if (is_array($nothing)) {
            foreach ($nothing as $k=>$v) {
                if ($v === 'choose' or $v === 'choosedots') {
                    $nothing[$k] = get_string('choosedots');
                }
            }
            $options = $nothing + $options; 
        } else if (is_string($nothing) and $nothing !== '') {
                        $options = array(''=>$nothing) + $options;
        }

                $selected = (array)$selected;
        foreach ($selected as $k=>$v) {
            $selected[$k] = (string)$v;
        }

        if (!isset($attributes['id'])) {
            $id = 'menu'.$name;
                        $id = str_replace('[', '', $id);
            $id = str_replace(']', '', $id);
            $attributes['id'] = $id;
        }

        if (!isset($attributes['class'])) {
            $class = 'menu'.$name;
                        $class = str_replace('[', '', $class);
            $class = str_replace(']', '', $class);
            $attributes['class'] = $class;
        }
        $attributes['class'] = 'select ' . $attributes['class']; 
        $attributes['name'] = $name;

        if (!empty($attributes['disabled'])) {
            $attributes['disabled'] = 'disabled';
        } else {
            unset($attributes['disabled']);
        }

        $output = '';
        foreach ($options as $value=>$label) {
            if (is_array($label)) {
                                $output .= self::select_optgroup(key($label), current($label), $selected);
            } else {
                $output .= self::select_option($label, $value, $selected);
            }
        }
        return self::tag('select', $output, $attributes);
    }

    
    private static function select_option($label, $value, array $selected) {
        $attributes = array();
        $value = (string)$value;
        if (in_array($value, $selected, true)) {
            $attributes['selected'] = 'selected';
        }
        $attributes['value'] = $value;
        return self::tag('option', $label, $attributes);
    }

    
    private static function select_optgroup($groupname, $options, array $selected) {
        if (empty($options)) {
            return '';
        }
        $attributes = array('label'=>$groupname);
        $output = '';
        foreach ($options as $value=>$label) {
            $output .= self::select_option($label, $value, $selected);
        }
        return self::tag('optgroup', $output, $attributes);
    }

    
    public static function select_time($type, $name, $currenttime = 0, $step = 5, array $attributes = null) {
        if (!$currenttime) {
            $currenttime = time();
        }
        $calendartype = \core_calendar\type_factory::get_calendar_instance();
        $currentdate = $calendartype->timestamp_to_date_array($currenttime);
        $userdatetype = $type;
        $timeunits = array();

        switch ($type) {
            case 'years':
                $timeunits = $calendartype->get_years();
                $userdatetype = 'year';
                break;
            case 'months':
                $timeunits = $calendartype->get_months();
                $userdatetype = 'month';
                $currentdate['month'] = (int)$currentdate['mon'];
                break;
            case 'days':
                $timeunits = $calendartype->get_days();
                $userdatetype = 'mday';
                break;
            case 'hours':
                for ($i=0; $i<=23; $i++) {
                    $timeunits[$i] = sprintf("%02d",$i);
                }
                break;
            case 'minutes':
                if ($step != 1) {
                    $currentdate['minutes'] = ceil($currentdate['minutes']/$step)*$step;
                }

                for ($i=0; $i<=59; $i+=$step) {
                    $timeunits[$i] = sprintf("%02d",$i);
                }
                break;
            default:
                throw new coding_exception("Time type $type is not supported by html_writer::select_time().");
        }

        if (empty($attributes['id'])) {
            $attributes['id'] = self::random_id('ts_');
        }
        $timerselector = self::select($timeunits, $name, $currentdate[$userdatetype], null, $attributes);
        $label = self::tag('label', get_string(substr($type, 0, -1), 'form'), array('for'=>$attributes['id'], 'class'=>'accesshide'));

        return $label.$timerselector;
    }

    
    public static function alist(array $items, array $attributes = null, $tag = 'ul') {
        $output = html_writer::start_tag($tag, $attributes)."\n";
        foreach ($items as $item) {
            $output .= html_writer::tag('li', $item)."\n";
        }
        $output .= html_writer::end_tag($tag);
        return $output;
    }

    
    public static function input_hidden_params(moodle_url $url, array $exclude = null) {
        $exclude = (array)$exclude;
        $params = $url->params();
        foreach ($exclude as $key) {
            unset($params[$key]);
        }

        $output = '';
        foreach ($params as $key => $value) {
            $attributes = array('type'=>'hidden', 'name'=>$key, 'value'=>$value);
            $output .= self::empty_tag('input', $attributes)."\n";
        }
        return $output;
    }

    
    public static function script($jscode, $url=null) {
        if ($jscode) {
            $attributes = array('type'=>'text/javascript');
            return self::tag('script', "\n//<![CDATA[\n$jscode\n//]]>\n", $attributes) . "\n";

        } else if ($url) {
            $attributes = array('type'=>'text/javascript', 'src'=>$url);
            return self::tag('script', '', $attributes) . "\n";

        } else {
            return '';
        }
    }

    
    public static function table(html_table $table) {
                if (!empty($table->align)) {
            foreach ($table->align as $key => $aa) {
                if ($aa) {
                    $table->align[$key] = 'text-align:'. fix_align_rtl($aa) .';';                  } else {
                    $table->align[$key] = null;
                }
            }
        }
        if (!empty($table->size)) {
            foreach ($table->size as $key => $ss) {
                if ($ss) {
                    $table->size[$key] = 'width:'. $ss .';';
                } else {
                    $table->size[$key] = null;
                }
            }
        }
        if (!empty($table->wrap)) {
            foreach ($table->wrap as $key => $ww) {
                if ($ww) {
                    $table->wrap[$key] = 'white-space:nowrap;';
                } else {
                    $table->wrap[$key] = '';
                }
            }
        }
        if (!empty($table->head)) {
            foreach ($table->head as $key => $val) {
                if (!isset($table->align[$key])) {
                    $table->align[$key] = null;
                }
                if (!isset($table->size[$key])) {
                    $table->size[$key] = null;
                }
                if (!isset($table->wrap[$key])) {
                    $table->wrap[$key] = null;
                }

            }
        }
        if (empty($table->attributes['class'])) {
            $table->attributes['class'] = 'generaltable';
        }
        if (!empty($table->tablealign)) {
            $table->attributes['class'] .= ' boxalign' . $table->tablealign;
        }

                $table->attributes['class'] = trim($table->attributes['class']);
        $attributes = array_merge($table->attributes, array(
                'id'            => $table->id,
                'width'         => $table->width,
                'summary'       => $table->summary,
                'cellpadding'   => $table->cellpadding,
                'cellspacing'   => $table->cellspacing,
            ));
        $output = html_writer::start_tag('table', $attributes) . "\n";

        $countcols = 0;

                if (!empty($table->caption)) {
            $captionattributes = array();
            if ($table->captionhide) {
                $captionattributes['class'] = 'accesshide';
            }
            $output .= html_writer::tag(
                'caption',
                $table->caption,
                $captionattributes
            );
        }

        if (!empty($table->head)) {
            $countcols = count($table->head);

            $output .= html_writer::start_tag('thead', array()) . "\n";
            $output .= html_writer::start_tag('tr', array()) . "\n";
            $keys = array_keys($table->head);
            $lastkey = end($keys);

            foreach ($table->head as $key => $heading) {
                                if (!($heading instanceof html_table_cell)) {
                    $headingtext = $heading;
                    $heading = new html_table_cell();
                    $heading->text = $headingtext;
                    $heading->header = true;
                }

                if ($heading->header !== false) {
                    $heading->header = true;
                }

                if ($heading->header && empty($heading->scope)) {
                    $heading->scope = 'col';
                }

                $heading->attributes['class'] .= ' header c' . $key;
                if (isset($table->headspan[$key]) && $table->headspan[$key] > 1) {
                    $heading->colspan = $table->headspan[$key];
                    $countcols += $table->headspan[$key] - 1;
                }

                if ($key == $lastkey) {
                    $heading->attributes['class'] .= ' lastcol';
                }
                if (isset($table->colclasses[$key])) {
                    $heading->attributes['class'] .= ' ' . $table->colclasses[$key];
                }
                $heading->attributes['class'] = trim($heading->attributes['class']);
                $attributes = array_merge($heading->attributes, array(
                        'style'     => $table->align[$key] . $table->size[$key] . $heading->style,
                        'scope'     => $heading->scope,
                        'colspan'   => $heading->colspan,
                    ));

                $tagtype = 'td';
                if ($heading->header === true) {
                    $tagtype = 'th';
                }
                $output .= html_writer::tag($tagtype, $heading->text, $attributes) . "\n";
            }
            $output .= html_writer::end_tag('tr') . "\n";
            $output .= html_writer::end_tag('thead') . "\n";

            if (empty($table->data)) {
                                                $output .= html_writer::start_tag('tbody', array('class' => 'empty'));
                $output .= html_writer::tag('tr', html_writer::tag('td', '', array('colspan'=>count($table->head))));
                $output .= html_writer::end_tag('tbody');
            }
        }

        if (!empty($table->data)) {
            $keys       = array_keys($table->data);
            $lastrowkey = end($keys);
            $output .= html_writer::start_tag('tbody', array());

            foreach ($table->data as $key => $row) {
                if (($row === 'hr') && ($countcols)) {
                    $output .= html_writer::tag('td', html_writer::tag('div', '', array('class' => 'tabledivider')), array('colspan' => $countcols));
                } else {
                                        if (!($row instanceof html_table_row)) {
                        $newrow = new html_table_row();

                        foreach ($row as $cell) {
                            if (!($cell instanceof html_table_cell)) {
                                $cell = new html_table_cell($cell);
                            }
                            $newrow->cells[] = $cell;
                        }
                        $row = $newrow;
                    }

                    if (isset($table->rowclasses[$key])) {
                        $row->attributes['class'] .= ' ' . $table->rowclasses[$key];
                    }

                    if ($key == $lastrowkey) {
                        $row->attributes['class'] .= ' lastrow';
                    }

                                        $row->attributes['class'] = trim($row->attributes['class']);
                    $trattributes = array_merge($row->attributes, array(
                            'id'            => $row->id,
                            'style'         => $row->style,
                        ));
                    $output .= html_writer::start_tag('tr', $trattributes) . "\n";
                    $keys2 = array_keys($row->cells);
                    $lastkey = end($keys2);

                    $gotlastkey = false;                     foreach ($row->cells as $key => $cell) {
                        if ($gotlastkey) {
                                                        mtrace("A cell with key ($key) was found after the last key ($lastkey)");
                        }

                        if (!($cell instanceof html_table_cell)) {
                            $mycell = new html_table_cell();
                            $mycell->text = $cell;
                            $cell = $mycell;
                        }

                        if (($cell->header === true) && empty($cell->scope)) {
                            $cell->scope = 'row';
                        }

                        if (isset($table->colclasses[$key])) {
                            $cell->attributes['class'] .= ' ' . $table->colclasses[$key];
                        }

                        $cell->attributes['class'] .= ' cell c' . $key;
                        if ($key == $lastkey) {
                            $cell->attributes['class'] .= ' lastcol';
                            $gotlastkey = true;
                        }
                        $tdstyle = '';
                        $tdstyle .= isset($table->align[$key]) ? $table->align[$key] : '';
                        $tdstyle .= isset($table->size[$key]) ? $table->size[$key] : '';
                        $tdstyle .= isset($table->wrap[$key]) ? $table->wrap[$key] : '';
                        $cell->attributes['class'] = trim($cell->attributes['class']);
                        $tdattributes = array_merge($cell->attributes, array(
                                'style' => $tdstyle . $cell->style,
                                'colspan' => $cell->colspan,
                                'rowspan' => $cell->rowspan,
                                'id' => $cell->id,
                                'abbr' => $cell->abbr,
                                'scope' => $cell->scope,
                            ));
                        $tagtype = 'td';
                        if ($cell->header === true) {
                            $tagtype = 'th';
                        }
                        $output .= html_writer::tag($tagtype, $cell->text, $tdattributes) . "\n";
                    }
                }
                $output .= html_writer::end_tag('tr') . "\n";
            }
            $output .= html_writer::end_tag('tbody') . "\n";
        }
        $output .= html_writer::end_tag('table') . "\n";

        return $output;
    }

    
    public static function label($text, $for, $colonize = true, array $attributes=array()) {
        if (!is_null($for)) {
            $attributes = array_merge($attributes, array('for' => $for));
        }
        $text = trim($text);
        $label = self::tag('label', $text, $attributes);

                                                                                                                
        return $label;
    }

    
    private static function add_class($class = '', array $attributes = null) {
        if ($class !== '') {
            $classattribute = array('class' => $class);
            if ($attributes) {
                if (array_key_exists('class', $attributes)) {
                    $attributes['class'] = trim($attributes['class'] . ' ' . $class);
                } else {
                    $attributes = $classattribute + $attributes;
                }
            } else {
                $attributes = $classattribute;
            }
        }
        return $attributes;
    }

    
    public static function div($content, $class = '', array $attributes = null) {
        return self::tag('div', $content, self::add_class($class, $attributes));
    }

    
    public static function start_div($class = '', array $attributes = null) {
        return self::start_tag('div', self::add_class($class, $attributes));
    }

    
    public static function end_div() {
        return self::end_tag('div');
    }

    
    public static function span($content, $class = '', array $attributes = null) {
        return self::tag('span', $content, self::add_class($class, $attributes));
    }

    
    public static function start_span($class = '', array $attributes = null) {
        return self::start_tag('span', self::add_class($class, $attributes));
    }

    
    public static function end_span() {
        return self::end_tag('span');
    }
}


class js_writer {

    
    public static function function_call($function, array $arguments = null, $delay=0) {
        if ($arguments) {
            $arguments = array_map('json_encode', convert_to_array($arguments));
            $arguments = implode(', ', $arguments);
        } else {
            $arguments = '';
        }
        $js = "$function($arguments);";

        if ($delay) {
            $delay = $delay * 1000;             $js = "setTimeout(function() { $js }, $delay);";
        }
        return $js . "\n";
    }

    
    public static function function_call_with_Y($function, array $extraarguments = null) {
        if ($extraarguments) {
            $extraarguments = array_map('json_encode', convert_to_array($extraarguments));
            $arguments = 'Y, ' . implode(', ', $extraarguments);
        } else {
            $arguments = 'Y';
        }
        return "$function($arguments);\n";
    }

    
    public static function object_init($var, $class, array $arguments = null, array $requirements = null, $delay=0) {
        if (is_array($arguments)) {
            $arguments = array_map('json_encode', convert_to_array($arguments));
            $arguments = implode(', ', $arguments);
        }

        if ($var === null) {
            $js = "new $class(Y, $arguments);";
        } else if (strpos($var, '.')!==false) {
            $js = "$var = new $class(Y, $arguments);";
        } else {
            $js = "var $var = new $class(Y, $arguments);";
        }

        if ($delay) {
            $delay = $delay * 1000;             $js = "setTimeout(function() { $js }, $delay);";
        }

        if (count($requirements) > 0) {
            $requirements = implode("', '", $requirements);
            $js = "Y.use('$requirements', function(Y){ $js });";
        }
        return $js."\n";
    }

    
    public static function set_variable($name, $value, $usevar = true) {
        $output = '';

        if ($usevar) {
            if (strpos($name, '.')) {
                $output .= '';
            } else {
                $output .= 'var ';
            }
        }

        $output .= "$name = ".json_encode($value).";";

        return $output;
    }

    
    public static function event_handler($selector, $event, $function, array $arguments = null) {
        $selector = json_encode($selector);
        $output = "Y.on('$event', $function, $selector, null";
        if (!empty($arguments)) {
            $output .= ', ' . json_encode($arguments);
        }
        return $output . ");\n";
    }
}


class html_table {

    
    public $id = null;

    
    public $attributes = array();

    
    public $head;

    
    public $headspan;

    
    public $align;

    
    public $size;

    
    public $wrap;

    
    public $data;

    
    public $width = null;

    
    public $tablealign = null;

    
    public $cellpadding = null;

    
    public $cellspacing = null;

    
    public $rowclasses;

    
    public $colclasses;

    
    public $summary;

    
    public $caption;

    
    public $captionhide = false;

    
    public function __construct() {
        $this->attributes['class'] = '';
    }
}


class html_table_row {

    
    public $id = null;

    
    public $cells = array();

    
    public $style = null;

    
    public $attributes = array();

    
    public function __construct(array $cells=null) {
        $this->attributes['class'] = '';
        $cells = (array)$cells;
        foreach ($cells as $cell) {
            if ($cell instanceof html_table_cell) {
                $this->cells[] = $cell;
            } else {
                $this->cells[] = new html_table_cell($cell);
            }
        }
    }
}


class html_table_cell {

    
    public $id = null;

    
    public $text;

    
    public $abbr = null;

    
    public $colspan = null;

    
    public $rowspan = null;

    
    public $scope = null;

    
    public $header = null;

    
    public $style = null;

    
    public $attributes = array();

    
    public function __construct($text = null) {
        $this->text = $text;
        $this->attributes['class'] = '';
    }
}


class paging_bar implements renderable {

    
    public $maxdisplay = 18;

    
    public $totalcount;

    
    public $page;

    
    public $perpage;

    
    public $baseurl;

    
    public $pagevar;

    
    public $previouslink = null;

    
    public $nextlink = null;

    
    public $firstlink = null;

    
    public $lastlink = null;

    
    public $pagelinks = array();

    
    public function __construct($totalcount, $page, $perpage, $baseurl, $pagevar = 'page') {
        $this->totalcount = $totalcount;
        $this->page       = $page;
        $this->perpage    = $perpage;
        $this->baseurl    = $baseurl;
        $this->pagevar    = $pagevar;
    }

    
    public function prepare(renderer_base $output, moodle_page $page, $target) {
        if (!isset($this->totalcount) || is_null($this->totalcount)) {
            throw new coding_exception('paging_bar requires a totalcount value.');
        }
        if (!isset($this->page) || is_null($this->page)) {
            throw new coding_exception('paging_bar requires a page value.');
        }
        if (empty($this->perpage)) {
            throw new coding_exception('paging_bar requires a perpage value.');
        }
        if (empty($this->baseurl)) {
            throw new coding_exception('paging_bar requires a baseurl value.');
        }

        if ($this->totalcount > $this->perpage) {
            $pagenum = $this->page - 1;

            if ($this->page > 0) {
                $this->previouslink = html_writer::link(new moodle_url($this->baseurl, array($this->pagevar=>$pagenum)), get_string('previous'), array('class'=>'previous'));
            }

            if ($this->perpage > 0) {
                $lastpage = ceil($this->totalcount / $this->perpage);
            } else {
                $lastpage = 1;
            }

            if ($this->page > round(($this->maxdisplay/3)*2)) {
                $currpage = $this->page - round($this->maxdisplay/3);

                $this->firstlink = html_writer::link(new moodle_url($this->baseurl, array($this->pagevar=>0)), '1', array('class'=>'first'));
            } else {
                $currpage = 0;
            }

            $displaycount = $displaypage = 0;

            while ($displaycount < $this->maxdisplay and $currpage < $lastpage) {
                $displaypage = $currpage + 1;

                if ($this->page == $currpage) {
                    $this->pagelinks[] = html_writer::span($displaypage, 'current-page');
                } else {
                    $pagelink = html_writer::link(new moodle_url($this->baseurl, array($this->pagevar=>$currpage)), $displaypage);
                    $this->pagelinks[] = $pagelink;
                }

                $displaycount++;
                $currpage++;
            }

            if ($currpage < $lastpage) {
                $lastpageactual = $lastpage - 1;
                $this->lastlink = html_writer::link(new moodle_url($this->baseurl, array($this->pagevar=>$lastpageactual)), $lastpage, array('class'=>'last'));
            }

            $pagenum = $this->page + 1;

            if ($pagenum != $lastpage) {
                $this->nextlink = html_writer::link(new moodle_url($this->baseurl, array($this->pagevar=>$pagenum)), get_string('next'), array('class'=>'next'));
            }
        }
    }
}


class block_contents {

    
    const NOT_HIDEABLE = 0;

    
    const VISIBLE = 1;

    
    const HIDDEN = 2;

    
    protected static $idcounter = 1;

    
    public $skipid;

    
    public $blockinstanceid = 0;

    
    public $blockpositionid = 0;

    
    public $attributes;

    
    public $title = '';

    
    public $arialabel = '';

    
    public $content = '';

    
    public $footer = '';

    
    public $annotation = '';

    
    public $collapsible = self::NOT_HIDEABLE;

    
    public $dockable = false;

    
    public $controls = array();


    
    public function __construct(array $attributes = null) {
        $this->skipid = self::$idcounter;
        self::$idcounter += 1;

        if ($attributes) {
                        $this->attributes = $attributes;
        } else {
                        $this->attributes = array('class'=>'block');
        }
    }

    
    public function add_class($class) {
        $this->attributes['class'] .= ' '.$class;
    }
}



class block_move_target {

    
    public $url;

    
    public function __construct(moodle_url $url) {
        $this->url  = $url;
    }
}


class custom_menu_item implements renderable {

    
    protected $text;

    
    protected $url;

    
    protected $title;

    
    protected $sort;

    
    protected $parent;

    
    protected $children = array();

    
    protected $lastsort = 0;

    
    public function __construct($text, moodle_url $url=null, $title=null, $sort = null, custom_menu_item $parent = null) {
        $this->text = $text;
        $this->url = $url;
        $this->title = $title;
        $this->sort = (int)$sort;
        $this->parent = $parent;
    }

    
    public function add($text, moodle_url $url = null, $title = null, $sort = null) {
        $key = count($this->children);
        if (empty($sort)) {
            $sort = $this->lastsort + 1;
        }
        $this->children[$key] = new custom_menu_item($text, $url, $title, $sort, $this);
        $this->lastsort = (int)$sort;
        return $this->children[$key];
    }

    
    public function remove_child(custom_menu_item $menuitem) {
        $removed = false;
        if (($key = array_search($menuitem, $this->children)) !== false) {
            unset($this->children[$key]);
            $this->children = array_values($this->children);
            $removed = true;
        } else {
            foreach ($this->children as $child) {
                if ($removed = $child->remove_child($menuitem)) {
                    break;
                }
            }
        }
        return $removed;
    }

    
    public function get_text() {
        return $this->text;
    }

    
    public function get_url() {
        return $this->url;
    }

    
    public function get_title() {
        return $this->title;
    }

    
    public function get_children() {
        $this->sort();
        return $this->children;
    }

    
    public function get_sort_order() {
        return $this->sort;
    }

    
    public function get_parent() {
        return $this->parent;
    }

    
    public function sort() {
        usort($this->children, array('custom_menu','sort_custom_menu_items'));
    }

    
    public function has_children() {
        return (count($this->children) > 0);
    }

    
    public function set_text($text) {
        $this->text = (string)$text;
    }

    
    public function set_title($title) {
        $this->title = (string)$title;
    }

    
    public function set_url(moodle_url $url) {
        $this->url = $url;
    }
}


class custom_menu extends custom_menu_item {

    
    protected $currentlanguage = null;

    
    public function __construct($definition = '', $currentlanguage = null) {
        $this->currentlanguage = $currentlanguage;
        parent::__construct('root');         if (!empty($definition)) {
            $this->override_children(self::convert_text_to_menu_nodes($definition, $currentlanguage));
        }
    }

    
    public function override_children(array $children) {
        $this->children = array();
        foreach ($children as $child) {
            if ($child instanceof custom_menu_item) {
                $this->children[] = $child;
            }
        }
    }

    
    public static function convert_text_to_menu_nodes($text, $language = null) {
        $root = new custom_menu();
        $lastitem = $root;
        $lastdepth = 0;
        $hiddenitems = array();
        $lines = explode("\n", $text);
        foreach ($lines as $linenumber => $line) {
            $line = trim($line);
            if (strlen($line) == 0) {
                continue;
            }
                        $itemtext = null;
            $itemurl = null;
            $itemtitle = null;
            $itemvisible = true;
            $settings = explode('|', $line);
            foreach ($settings as $i => $setting) {
                $setting = trim($setting);
                if (!empty($setting)) {
                    switch ($i) {
                        case 0:
                            $itemtext = ltrim($setting, '-');
                            $itemtitle = $itemtext;
                            break;
                        case 1:
                            try {
                                $itemurl = new moodle_url($setting);
                            } catch (moodle_exception $exception) {
                                                                                                $itemurl = null;
                            }
                            break;
                        case 2:
                            $itemtitle = $setting;
                            break;
                        case 3:
                            if (!empty($language)) {
                                $itemlanguages = array_map('trim', explode(',', $setting));
                                $itemvisible &= in_array($language, $itemlanguages);
                            }
                            break;
                    }
                }
            }
                        preg_match('/^(\-*)/', $line, $match);
            $itemdepth = strlen($match[1]) + 1;
                        while (($lastdepth - $itemdepth) >= 0) {
                $lastitem = $lastitem->get_parent();
                $lastdepth--;
            }
            $lastitem = $lastitem->add($itemtext, $itemurl, $itemtitle, $linenumber + 1);
            $lastdepth++;
            if (!$itemvisible) {
                $hiddenitems[] = $lastitem;
            }
        }
        foreach ($hiddenitems as $item) {
            $item->parent->remove_child($item);
        }
        return $root->get_children();
    }

    
    public static function sort_custom_menu_items(custom_menu_item $itema, custom_menu_item $itemb) {
        $itema = $itema->get_sort_order();
        $itemb = $itemb->get_sort_order();
        if ($itema == $itemb) {
            return 0;
        }
        return ($itema > $itemb) ? +1 : -1;
    }
}


class tabobject implements renderable {
    
    var $id;
    
    var $link;
    
    var $text;
    
    var $title;
    
    var $linkedwhenselected = false;
    
    var $inactive = false;
    
    var $activated = false;
    
    var $selected = false;
    
    var $subtree = array();
    
    var $level = 1;

    
    public function __construct($id, $link = null, $text = '', $title = '', $linkedwhenselected = false) {
        $this->id = $id;
        $this->link = $link;
        $this->text = $text;
        $this->title = $title ? $title : $text;
        $this->linkedwhenselected = $linkedwhenselected;
    }

    
    protected function set_selected($selected) {
        if ((string)$selected === (string)$this->id) {
            $this->selected = true;
                        return true;
        }
        foreach ($this->subtree as $subitem) {
            if ($subitem->set_selected($selected)) {
                                $this->activated = true;
                return true;
            }
        }
        return false;
    }

    
    public function find($id) {
        if ((string)$this->id === (string)$id) {
            return $this;
        }
        foreach ($this->subtree as $tab) {
            if ($obj = $tab->find($id)) {
                return $obj;
            }
        }
        return null;
    }

    
    protected function set_level($level) {
        $this->level = $level;
        foreach ($this->subtree as $tab) {
            $tab->set_level($level + 1);
        }
    }
}


class context_header implements renderable {

    
    public $heading;
    
    public $headinglevel;
    
    public $imagedata;
    
    public $additionalbuttons;

    
    public function __construct($heading = null, $headinglevel = 1, $imagedata = null, $additionalbuttons = null) {

        $this->heading = $heading;
        $this->headinglevel = $headinglevel;
        $this->imagedata = $imagedata;
        $this->additionalbuttons = $additionalbuttons;
                if (isset($this->additionalbuttons)) {
            $this->format_button_images();
        }
    }

    
    protected function format_button_images() {

        foreach ($this->additionalbuttons as $buttontype => $button) {
            $page = $button['page'];
                        if (!isset($button['image'])) {
                $this->additionalbuttons[$buttontype]['formattedimage'] = $button['title'];
            } else {
                                $internalimage = $page->theme->resolve_image_location('t/' . $button['image'], 'moodle');
                if ($internalimage) {
                    $this->additionalbuttons[$buttontype]['formattedimage'] = 't/' . $button['image'];
                } else {
                                        $this->additionalbuttons[$buttontype]['formattedimage'] = $button['image'];
                }
            }
                        $this->additionalbuttons[$buttontype]['linkattributes'] = array_merge($button['linkattributes'],
                    array('class' => 'btn'));
        }
    }
}


class tabtree extends tabobject {
    
    public function __construct($tabs, $selected = null, $inactive = null) {
        $this->subtree = $tabs;
        if ($selected !== null) {
            $this->set_selected($selected);
        }
        if ($inactive !== null) {
            if (is_array($inactive)) {
                foreach ($inactive as $id) {
                    if ($tab = $this->find($id)) {
                        $tab->inactive = true;
                    }
                }
            } else if ($tab = $this->find($inactive)) {
                $tab->inactive = true;
            }
        }
        $this->set_level(0);
    }
}


class action_menu implements renderable {

    
    const TL = 1;

    
    const TR = 2;

    
    const BL = 3;

    
    const BR = 4;

    
    protected $instance = 0;

    
    protected $primaryactions = array();

    
    protected $secondaryactions = array();

    
    public $attributes = array();
    
    public $attributesprimary = array();
    
    public $attributessecondary = array();

    
    public $actiontext = null;

    
    public $actionicon;

    
    public $menutrigger = '';

    
    public $prioritise = false;

    
    public function __construct(array $actions = array()) {
        static $initialised = 0;
        $this->instance = $initialised;
        $initialised++;

        $this->attributes = array(
            'id' => 'action-menu-'.$this->instance,
            'class' => 'moodle-actionmenu',
            'data-enhance' => 'moodle-core-actionmenu'
        );
        $this->attributesprimary = array(
            'id' => 'action-menu-'.$this->instance.'-menubar',
            'class' => 'menubar',
            'role' => 'menubar'
        );
        $this->attributessecondary = array(
            'id' => 'action-menu-'.$this->instance.'-menu',
            'class' => 'menu',
            'data-rel' => 'menu-content',
            'aria-labelledby' => 'action-menu-toggle-'.$this->instance,
            'role' => 'menu'
        );
        $this->set_alignment(self::TR, self::BR);
        foreach ($actions as $action) {
            $this->add($action);
        }
    }

    public function set_menu_trigger($trigger) {
        $this->menutrigger = $trigger;
    }

    
    public function initialise_js(moodle_page $page) {
        static $initialised = false;
        if (!$initialised) {
            $page->requires->yui_module('moodle-core-actionmenu', 'M.core.actionmenu.init');
            $initialised = true;
        }
    }

    
    public function add($action) {
        if ($action instanceof action_link) {
            if ($action->primary) {
                $this->add_primary_action($action);
            } else {
                $this->add_secondary_action($action);
            }
        } else if ($action instanceof pix_icon) {
            $this->add_primary_action($action);
        } else {
            $this->add_secondary_action($action);
        }
    }

    
    public function add_primary_action($action) {
        if ($action instanceof action_link || $action instanceof pix_icon) {
            $action->attributes['role'] = 'menuitem';
            if ($action instanceof action_menu_link) {
                $action->actionmenu = $this;
            }
        }
        $this->primaryactions[] = $action;
    }

    
    public function add_secondary_action($action) {
        if ($action instanceof action_link || $action instanceof pix_icon) {
            $action->attributes['role'] = 'menuitem';
            if ($action instanceof action_menu_link) {
                $action->actionmenu = $this;
            }
        }
        $this->secondaryactions[] = $action;
    }

    
    public function get_primary_actions(core_renderer $output = null) {
        global $OUTPUT;
        if ($output === null) {
            $output = $OUTPUT;
        }
        $pixicon = $this->actionicon;
        $linkclasses = array('toggle-display');

        $title = '';
        if (!empty($this->menutrigger)) {
            $pixicon = '<b class="caret"></b>';
            $linkclasses[] = 'textmenu';
        } else {
            $title = new lang_string('actions', 'moodle');
            $this->actionicon = new pix_icon(
                't/edit_menu',
                '',
                'moodle',
                array('class' => 'iconsmall actionmenu', 'title' => '')
            );
            $pixicon = $this->actionicon;
        }
        if ($pixicon instanceof renderable) {
            $pixicon = $output->render($pixicon);
            if ($pixicon instanceof pix_icon && isset($pixicon->attributes['alt'])) {
                $title = $pixicon->attributes['alt'];
            }
        }
        $string = '';
        if ($this->actiontext) {
            $string = $this->actiontext;
        }
        $actions = $this->primaryactions;
        $attributes = array(
            'class' => implode(' ', $linkclasses),
            'title' => $title,
            'id' => 'action-menu-toggle-'.$this->instance,
            'role' => 'menuitem'
        );
        $link = html_writer::link('#', $string . $this->menutrigger . $pixicon, $attributes);
        if ($this->prioritise) {
            array_unshift($actions, $link);
        } else {
            $actions[] = $link;
        }
        return $actions;
    }

    
    public function get_secondary_actions() {
        return $this->secondaryactions;
    }

    
    public function set_owner_selector($selector) {
        $this->attributes['data-owner'] = $selector;
    }

    
    public function set_alignment($dialogue, $button) {
        if (isset($this->attributessecondary['data-align'])) {
                        $class = $this->attributessecondary['class'];
            $search = 'align-'.$this->attributessecondary['data-align'];
            $this->attributessecondary['class'] = str_replace($search, '', $class);
        }
        $align = $this->get_align_string($dialogue) . '-' . $this->get_align_string($button);
        $this->attributessecondary['data-align'] = $align;
        $this->attributessecondary['class'] .= ' align-'.$align;
    }

    
    protected function get_align_string($align) {
        switch ($align) {
            case self::TL :
                return 'tl';
            case self::TR :
                return 'tr';
            case self::BL :
                return 'bl';
            case self::BR :
                return 'br';
            default :
                return 'tl';
        }
    }

    
    public function set_constraint($ancestorselector) {
        $this->attributessecondary['data-constraint'] = $ancestorselector;
    }

    
    public function do_not_enhance() {
        unset($this->attributes['data-enhance']);
    }

    
    public function will_be_enhanced() {
        return isset($this->attributes['data-enhance']);
    }

    
    public function set_nowrap_on_items($value = true) {
        $class = 'nowrap-items';
        if (!empty($this->attributes['class'])) {
            $pos = strpos($this->attributes['class'], $class);
            if ($value === true && $pos === false) {
                                $this->attributes['class'] .= ' '.$class;
            } else if ($value === false && $pos !== false) {
                                $this->attributes['class'] = substr($this->attributes['class'], $pos, strlen($class));
            }
        } else if ($value) {
                        $this->attributes['class'] = $class;
        }
    }
}


class action_menu_filler extends action_link implements renderable {

    
    public $primary = true;

    
    public function __construct() {
    }
}


class action_menu_link extends action_link implements renderable {

    
    public $primary = true;

    
    public $actionmenu = null;

    
    public function __construct(moodle_url $url, pix_icon $icon = null, $text, $primary = true, array $attributes = array()) {
        parent::__construct($url, $text, null, $attributes, $icon);
        $this->primary = (bool)$primary;
        $this->add_class('menu-action');
        $this->attributes['role'] = 'menuitem';
    }
}


class action_menu_link_primary extends action_menu_link {
    
    public function __construct(moodle_url $url, pix_icon $icon = null, $text, array $attributes = array()) {
        parent::__construct($url, $icon, $text, true, $attributes);
    }
}


class action_menu_link_secondary extends action_menu_link {
    
    public function __construct(moodle_url $url, pix_icon $icon = null, $text, array $attributes = array()) {
        parent::__construct($url, $icon, $text, false, $attributes);
    }
}


class preferences_groups implements renderable {

    
    public $groups;

    
    public function __construct($groups) {
        $this->groups = $groups;
    }

}


class preferences_group implements renderable {

    
    public $title;

    
    public $nodes;

    
    public function __construct($title, $nodes) {
        $this->title = $title;
        $this->nodes = $nodes;
    }
}
