<?php




defined('MOODLE_INTERNAL') || die();


class virtual_root_file {
    
    protected $contextid;
    
    protected $component;
    
    protected $filearea;
    
    protected $itemid;

    
    public function __construct($contextid, $component, $filearea, $itemid) {
        $this->contextid = $contextid;
        $this->component = $component;
        $this->filearea  = $filearea;
        $this->itemid    = $itemid;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function delete() {
        return true;
    }

    
    public function add_to_curl_request(&$curlrequest, $key) {
        return;
    }

    
    public function get_content_file_handle() {
        return null;
    }

    
    public function readfile() {
        return;
    }

    
    public function get_content() {
        return '';
    }

    
    public function copy_content_to($pathname) {
        return false;
    }

    
    public function list_files(file_packer $packer) {
        return null;
    }

    
    public function extract_to_pathname(file_packer $packer, $pathname) {
        return false;
    }

    
    public function extract_to_storage(file_packer $packer, $contextid, $component, $filearea, $itemid, $pathbase, $userid = NULL) {
        return false;
    }

    
    public function archive_file(file_archive $filearch, $archivepath) {
        return false;
    }

    
    public function get_parent_directory() {
        return null;
    }

    
    public function get_contextid() {
        return $this->contextid;
    }

    
    public function get_component() {
        return $this->component;
    }

    
    public function get_filearea() {
        return $this->filearea;
    }

    
    public function get_itemid() {
        return $this->itemid;
    }

    
    public function get_filepath() {
        return '/';
    }

    
    public function get_filename() {
        return '.';
    }

    
    public function get_userid() {
        return null;
    }

    
    public function get_filesize() {
        return 0;
    }

    
    public function get_mimetype() {
        return null;
    }

    
    public function get_timecreated() {
        return 0;
    }

    
    public function get_timemodified() {
        return 0;
    }

    
    public function get_status() {
        return 0;
    }

    
    public function get_id() {
        return 0;
    }

    
    public function get_contenthash() {
        return sha1('');
    }

    
    public function get_pathnamehash() {
        return sha1('/'.$this->get_contextid().'/'.$this->get_component().'/'.$this->get_filearea().'/'.$this->get_itemid().$this->get_filepath().$this->get_filename());
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
        return null;
    }
}
