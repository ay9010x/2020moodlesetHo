<?php



defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->dirroot . '/cache/lib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');


class tool_uploadcourse_helper {

    
    public static function generate_shortname($data, $templateshortname) {
        if (empty($templateshortname) && !is_numeric($templateshortname)) {
            return null;
        }
        if (strpos($templateshortname, '%') === false) {
            return $templateshortname;
        }

        $course = (object) $data;
        $fullname   = isset($course->fullname) ? $course->fullname : '';
        $idnumber   = isset($course->idnumber) ? $course->idnumber  : '';

        $callback = partial(array('tool_uploadcourse_helper', 'generate_shortname_callback'), $fullname, $idnumber);
        $result = preg_replace_callback('/(?<!%)%([+~-])?(\d)*([fi])/', $callback, $templateshortname);

        if (!is_null($result)) {
            $result = clean_param($result, PARAM_TEXT);
        }

        if (empty($result) && !is_numeric($result)) {
            $result = null;
        }

        return $result;
    }

    
    public static function generate_shortname_callback($fullname, $idnumber, $block) {
        switch ($block[3]) {
            case 'f':
                $repl = $fullname;
                break;
            case 'i':
                $repl = $idnumber;
                break;
            default:
                return $block[0];
        }

        switch ($block[1]) {
            case '+':
                $repl = core_text::strtoupper($repl);
                break;
            case '-':
                $repl = core_text::strtolower($repl);
                break;
            case '~':
                $repl = core_text::strtotitle($repl);
                break;
        }

        if (!empty($block[2])) {
            $repl = core_text::substr($repl, 0, $block[2]);
        }

        return $repl;
    }

    
    public static function get_course_formats() {
        return array_keys(core_component::get_plugin_list('format'));
    }

    
    public static function get_enrolment_data($data) {
        $enrolmethods = array();
        $enroloptions = array();
        foreach ($data as $field => $value) {

                        $matches = array();
            if (preg_match('/^enrolment_(\d+)(_(.+))?$/', $field, $matches)) {
                $key = $matches[1];
                if (!isset($enroloptions[$key])) {
                    $enroloptions[$key] = array();
                }
                if (empty($matches[3])) {
                    $enrolmethods[$key] = $value;
                } else {
                    $enroloptions[$key][$matches[3]] = $value;
                }
            }
        }

                $enrolmentdata = array();
        if (!empty($enrolmethods)) {
            $enrolmentplugins = self::get_enrolment_plugins();
            foreach ($enrolmethods as $key => $method) {
                if (!array_key_exists($method, $enrolmentplugins)) {
                                        continue;
                }
                $enrolmentdata[$enrolmethods[$key]] = $enroloptions[$key];
            }
        }
        return $enrolmentdata;
    }

    
    public static function get_enrolment_plugins() {
        $cache = cache::make('tool_uploadcourse', 'helper');
        if (($enrol = $cache->get('enrol')) === false) {
            $enrol = enrol_get_plugins(false);
            $cache->set('enrol', $enrol);
        }
        return $enrol;
    }

    
    public static function get_restore_content_dir($backupfile = null, $shortname = null, &$errors = array()) {
        global $CFG, $DB, $USER;

        $cachekey = null;
        if (!empty($backupfile)) {
            $backupfile = realpath($backupfile);
            if (empty($backupfile) || !is_readable($backupfile)) {
                $errors['cannotreadbackupfile'] = new lang_string('cannotreadbackupfile', 'tool_uploadcourse');
                return false;
            }
            $cachekey = 'backup_path:' . $backupfile;
        } else if (!empty($shortname) || is_numeric($shortname)) {
            $cachekey = 'backup_sn:' . $shortname;
        }

        if (empty($cachekey)) {
            return false;
        }

                        $usecache = !empty($CFG->keeptempdirectoriesonbackup);
        if ($usecache) {
            $cache = cache::make('tool_uploadcourse', 'helper');
        }

                if (!$usecache || (($backupid = $cache->get($cachekey)) === false || !is_dir("$CFG->tempdir/backup/$backupid"))) {

                        $backupid = null;

            if (!empty($backupfile)) {
                                $packer = get_file_packer('application/vnd.moodle.backup');
                $backupid = restore_controller::get_tempdir_name(SITEID, $USER->id);
                $path = "$CFG->tempdir/backup/$backupid/";
                $result = $packer->extract_to_pathname($backupfile, $path);
                if (!$result) {
                    $errors['invalidbackupfile'] = new lang_string('invalidbackupfile', 'tool_uploadcourse');
                }
            } else if (!empty($shortname) || is_numeric($shortname)) {
                                $courseid = $DB->get_field('course', 'id', array('shortname' => $shortname), IGNORE_MISSING);
                if (!empty($courseid)) {
                    $bc = new backup_controller(backup::TYPE_1COURSE, $courseid, backup::FORMAT_MOODLE,
                        backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id);
                    $bc->execute_plan();
                    $backupid = $bc->get_backupid();
                    $bc->destroy();
                } else {
                    $errors['coursetorestorefromdoesnotexist'] =
                        new lang_string('coursetorestorefromdoesnotexist', 'tool_uploadcourse');
                }
            }

            if ($usecache) {
                $cache->set($cachekey, $backupid);
            }
        }

        if ($backupid === null) {
            $backupid = false;
        }
        return $backupid;
    }

    
    public static function get_role_ids() {
        $cache = cache::make('tool_uploadcourse', 'helper');
        if (($roles = $cache->get('roles')) === false) {
            $roles = array();
            $rolesraw = get_all_roles();
            foreach ($rolesraw as $role) {
                $roles[$role->shortname] = $role->id;
            }
            $cache->set('roles', $roles);
        }
        return $roles;
    }

    
    public static function get_role_names($data, &$errors = array()) {
        $rolenames = array();
        $rolesids = self::get_role_ids();
        $invalidroles = array();
        foreach ($data as $field => $value) {

            $matches = array();
            if (preg_match('/^role_(.+)?$/', $field, $matches)) {
                if (!isset($rolesids[$matches[1]])) {
                    $invalidroles[] = $matches[1];
                    continue;
                }
                $rolenames['role_' . $rolesids[$matches[1]]] = $value;
            }

        }

        if (!empty($invalidroles)) {
            $errors['invalidroles'] = new lang_string('invalidroles', 'tool_uploadcourse', implode(', ', $invalidroles));
        }

                return $rolenames;
    }

    
    public static function increment_idnumber($idnumber) {
        global $DB;
        while ($DB->record_exists('course', array('idnumber' => $idnumber))) {
            $matches = array();
            if (!preg_match('/(.*?)([0-9]+)$/', $idnumber, $matches)) {
                $newidnumber = $idnumber . '_2';
            } else {
                $newidnumber = $matches[1] . ((int) $matches[2] + 1);
            }
            $idnumber = $newidnumber;
        }
        return $idnumber;
    }

    
    public static function increment_shortname($shortname) {
        global $DB;
        do {
            $matches = array();
            if (!preg_match('/(.*?)([0-9]+)$/', $shortname, $matches)) {
                $newshortname = $shortname . '_2';
            } else {
                $newshortname = $matches[1] . ($matches[2]+1);
            }
            $shortname = $newshortname;
        } while ($DB->record_exists('course', array('shortname' => $shortname)));
        return $shortname;
    }

    
    public static function resolve_category($data, &$errors = array()) {
        $catid = null;

        if (!empty($data['category'])) {
            $category = coursecat::get((int) $data['category'], IGNORE_MISSING);
            if (!empty($category) && !empty($category->id)) {
                $catid = $category->id;
            } else {
                $errors['couldnotresolvecatgorybyid'] =
                    new lang_string('couldnotresolvecatgorybyid', 'tool_uploadcourse');
            }
        }

        if (empty($catid) && !empty($data['category_idnumber'])) {
            $catid = self::resolve_category_by_idnumber($data['category_idnumber']);
            if (empty($catid)) {
                $errors['couldnotresolvecatgorybyidnumber'] =
                    new lang_string('couldnotresolvecatgorybyidnumber', 'tool_uploadcourse');
            }
        }
        if (empty($catid) && !empty($data['category_path'])) {
            $catid = self::resolve_category_by_path(explode(' / ', $data['category_path']));
            if (empty($catid)) {
                $errors['couldnotresolvecatgorybypath'] =
                    new lang_string('couldnotresolvecatgorybypath', 'tool_uploadcourse');
            }
        }

        return $catid;
    }

    
    public static function resolve_category_by_idnumber($idnumber) {
        global $DB;
        $cache = cache::make('tool_uploadcourse', 'helper');
        $cachekey = 'cat_idn_' . $idnumber;
        if (($id = $cache->get($cachekey)) === false) {
            $params = array('idnumber' => $idnumber);
            $id = $DB->get_field_select('course_categories', 'id', 'idnumber = :idnumber', $params, IGNORE_MISSING);

                        if ($id === false) {
                $id = -1;
            }

            $cache->set($cachekey, $id);
        }

                if ($id == -1) {
            $id = false;
        }

        return $id;
    }

    
    public static function resolve_category_by_path(array $path) {
        global $DB;
        $cache = cache::make('tool_uploadcourse', 'helper');
        $cachekey = 'cat_path_' . serialize($path);
        if (($id = $cache->get($cachekey)) === false) {
            $parent = 0;
            $sql = 'name = :name AND parent = :parent';
            while ($name = array_shift($path)) {
                $params = array('name' => $name, 'parent' => $parent);
                if ($records = $DB->get_records_select('course_categories', $sql, $params, null, 'id, parent')) {
                    if (count($records) > 1) {
                                                $id = -1;
                        break;
                    }
                    $record = reset($records);
                    $id = $record->id;
                    $parent = $record->id;
                } else {
                                        $id = -1;
                    break;
                }
            }
            $cache->set($cachekey, $id);
        }

                if ($id == -1) {
            $id = false;
        }
        return $id;
    }

}
