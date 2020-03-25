<?php




defined('MOODLE_INTERNAL') || die();


abstract class file_info {

    
    protected $context;

    
    protected $browser;

    
    public function __construct($browser, $context) {
        $this->browser = $browser;
        $this->context = $context;
    }

    
    public function get_params() {
        return array('contextid' => $this->context->id,
                     'component' => null,
                     'filearea'  => null,
                     'itemid'    => null,
                     'filepath'  => null,
                     'filename'  => null);
    }

    
    public abstract function get_visible_name();

    
    public abstract function is_directory();

    
    public abstract function get_children();

    
    protected function build_search_files_sql($extensions, $prefix = null) {
        global $DB;
        if (strlen($prefix)) {
            $prefix = $prefix.'.';
        } else {
            $prefix = '';
        }
        $sql = '';
        $params = array();
        if (is_array($extensions) && !in_array('*', $extensions)) {
            $likes = array();
            $cnt = 0;
            foreach ($extensions as $ext) {
                $cnt++;
                $likes[] = $DB->sql_like($prefix.'filename', ':filename'.$cnt, false);
                $params['filename'.$cnt] = '%'.$ext;
            }
            $sql .= ' AND (' . join(' OR ', $likes) . ')';
        }
        return array($sql, $params);
     }

    
    public function get_non_empty_children($extensions = '*') {
        $list = $this->get_children();
        $nonemptylist = array();
        foreach ($list as $fileinfo) {
            if ($fileinfo->is_directory()) {
                if ($fileinfo->count_non_empty_children($extensions)) {
                    $nonemptylist[] = $fileinfo;
                }
            } else if ($extensions === '*') {
                $nonemptylist[] = $fileinfo;
            } else {
                $filename = $fileinfo->get_visible_name();
                $extension = core_text::strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (!empty($extension) && in_array('.' . $extension, $extensions)) {
                    $nonemptylist[] = $fileinfo;
                }
            }
        }
        return $nonemptylist;
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        $list = $this->get_children();
        $cnt = 0;
                foreach ($list as $fileinfo) {
            if (!$fileinfo->is_directory()) {
                if ($extensions !== '*') {
                    $filename = $fileinfo->get_visible_name();
                    $extension = core_text::strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (empty($extension) || !in_array('.' . $extension, $extensions)) {
                        continue;
                    }
                }
                if ((++$cnt) >= $limit) {
                    return $cnt;
                }
            }
        }
                foreach ($list as $fileinfo) {
            if ($fileinfo->is_directory() && $fileinfo->count_non_empty_children($extensions)) {
                if ((++$cnt) >= $limit) {
                    return $cnt;
                }
            }
        }
        return $cnt;
    }

    
    public abstract function get_parent();

    
    public function get_params_rawencoded() {
        $params = $this->get_params();
        $encoded = array();
        $encoded[] = 'contextid=' . $params['contextid'];
        $encoded[] = 'component=' . $params['component'];
        $encoded[] = 'filearea=' . $params['filearea'];
        $encoded[] = 'itemid=' . (is_null($params['itemid']) ? -1 : $params['itemid']);
        $encoded[] = 'filepath=' . (is_null($params['filepath']) ? '' : rawurlencode($params['filepath']));
        $encoded[] = 'filename=' . ((is_null($params['filename']) or $params['filename'] === '.') ? '' : rawurlencode($params['filename']));

        return $encoded;
    }

    
    public function get_url($forcedownload=false, $https=false) {
        return null;
    }

    
    public function is_readable() {
        return true;
    }

    
    public function is_writable() {
        return true;
    }

    
    public function is_empty_area() {
        return false;
    }

    
    public function get_filesize() {
        return null;
    }

    
    public function get_mimetype() {
        return null;
    }

    
    public function get_timecreated() {
        return null;
    }

    
    public function get_timemodified() {
        return null;
    }

    
    public function get_license() {
        return null;
    }

    
    public function get_author() {
        return null;
    }

    
    public function get_source() {
        return null;
    }

    
    public function get_sortorder() {
        return 0;
    }

    
    public function is_external_file() {
        return false;
    }

    
    public function get_status() {
        return 0;
    }

    
    public function get_readable_fullname() {
        return null;
    }

    
    public function create_directory($newdirname, $userid = NULL) {
        return null;
    }

    
    public function create_file_from_string($newfilename, $content, $userid = NULL) {
        return null;
    }

    
    public function create_file_from_pathname($newfilename, $pathname, $userid = NULL) {
        return null;
    }

    
    public function create_file_from_storedfile($newfilename, $fid, $userid = NULL) {
        return null;
    }

    
    public function delete() {
        return false;
    }

    
    public function copy_to_storage($filerecord) {
        return false;
    }

    
    public function copy_to_pathname($pathname) {
        return false;
    }


                }
