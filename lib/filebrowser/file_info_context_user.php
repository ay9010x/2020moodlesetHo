<?php




defined('MOODLE_INTERNAL') || die();


class file_info_context_user extends file_info {
    
    protected $user;

    
    public function __construct($browser, $context, $user) {
        parent::__construct($browser, $context);
        $this->user = $user;
    }

    
    public function get_file_info($component, $filearea, $itemid, $filepath, $filename) {
        global $USER;

        if (!isloggedin() or isguestuser()) {
            return null;
        }

        if (empty($component)) {
                        if ($this->user->id != $USER->id) {
                                return null;
            }
            return $this;
        }

        $methodname = "get_area_{$component}_{$filearea}";
        if (method_exists($this, $methodname)) {
            return $this->$methodname($itemid, $filepath, $filename);
        }

        return null;
    }

    
    protected function get_area_user_private($itemid, $filepath, $filename) {
        global $USER, $CFG;

                if ($this->user->id != $USER->id) {
            return null;
        }

        if (is_null($itemid)) {
                        return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        if (!$storedfile = $fs->get_file($this->context->id, 'user', 'private', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                                $storedfile = new virtual_root_file($this->context->id, 'user', 'private', 0);
            } else {
                                return null;
            }
        }
        $urlbase = $CFG->wwwroot.'/pluginfile.php';

        
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('areauserpersonal', 'repository'), false, true, true, false);
    }

    
    protected function get_area_user_profile($itemid, $filepath, $filename) {
        global $CFG;

        $readaccess = has_capability('moodle/user:update', $this->context);
        $writeaccess = has_capability('moodle/user:viewalldetails', $this->context);

        if (!$readaccess and !$writeaccess) {
                        return null;
        }

        if (is_null($itemid)) {
                        return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        if (!$storedfile = $fs->get_file($this->context->id, 'user', 'profile', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'user', 'profile', 0);
            } else {
                                return null;
            }
        }
        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase,
                get_string('areauserprofile', 'repository'), false, $readaccess, $writeaccess, false);
    }

    
    protected function get_area_user_draft($itemid, $filepath, $filename) {
        global $USER, $CFG;

                if ($this->user->id != $USER->id) {
            return null;
        }

        if (empty($itemid)) {
                        return null;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        if (!$storedfile = $fs->get_file($this->context->id, 'user', 'draft', $itemid, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'user', 'draft', $itemid);
            } else {
                                return null;
            }
        }
        $urlbase = $CFG->wwwroot.'/draftfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('areauserdraft', 'repository'), true, true, true, true);
    }

    
    protected function get_area_user_backup($itemid, $filepath, $filename) {
        global $USER, $CFG;

                if ($this->context->instanceid != $USER->id) {
            return null;
        }

        if (is_null($itemid)) {
                        return $this;
        }

        $fs = get_file_storage();

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        if (!$storedfile = $fs->get_file($this->context->id, 'user', 'backup', $itemid, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($this->context->id, 'user', 'backup', 0);
            } else {
                                return null;
            }
        }
        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($this->browser, $this->context, $storedfile, $urlbase, get_string('areauserbackup', 'repository'), false, true, true, false);
    }

    
    public function get_visible_name() {
        return fullname($this->user, true);
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        $children = array();

        if ($child = $this->get_area_user_private(0, '/', '.')) {
            $children[] = $child;
        }

        if ($child = $this->get_area_user_backup(0, '/', '.')) {
            $children[] = $child;
        }
        
        return $children;
    }

    
    public function get_parent() {
        return $this->browser->get_file_info();
    }
}
