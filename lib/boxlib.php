<?php



defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/oauthlib.php');


class boxnet_client extends oauth2_client {

    
    const API = 'https://api.box.com/2.0';

    
    const UPLOAD_API = 'https://upload.box.com/api/2.0';

    
    protected function auth_url() {
        return 'https://www.box.com/api/oauth2/authorize';
    }

    
    public function create_folder($foldername, $parentid = 0) {
        $params = array('name' => $foldername, 'parent' => array('id' => (string) $parentid));
        $this->reset_state();
        $result = $this->post($this->make_url("/folders"), json_encode($params));
        $result = json_decode($result);
        return $result;
    }

    
    public function download_file($fileid, $path) {
        $this->reset_state();
        $result = $this->download_one($this->make_url("/files/$fileid/content"), array(),
            array('filepath' => $path, 'CURLOPT_FOLLOWLOCATION' => true));
        return ($result === true && $this->info['http_code'] === 200);
    }

    
    public function get_file_info($fileid) {
        $this->reset_state();
        $result = $this->request($this->make_url("/files/$fileid"));
        return json_decode($result);
    }

    
    public function get_folder_items($folderid = 0) {
        $this->reset_state();
        $result = $this->request($this->make_url("/folders/$folderid/items",
            array('fields' => 'id,name,type,modified_at,size,owned_by')));
        return json_decode($result);
    }

    
    public function log_out() {
        if ($accesstoken = $this->get_accesstoken()) {
            $params = array(
                'client_id' => $this->get_clientid(),
                'client_secret' => $this->get_clientsecret(),
                'token' => $accesstoken->token
            );
            $this->reset_state();
            $this->post($this->revoke_url(), $params);
        }
        parent::log_out();
    }

    
    protected function make_url($uri, $params = array(), $uploadapi = false) {
        $api = $uploadapi ? self::UPLOAD_API : self::API;
        $url = new moodle_url($api . '/' . ltrim($uri, '/'), $params);
        return $url->out(false);
    }

    
    public function rename_file($fileid, $newname) {
                        $data = array('name' => $newname);
        $options = array(
            'CURLOPT_CUSTOMREQUEST' => 'PUT',
            'CURLOPT_POSTFIELDS' => json_encode($data)
        );
        $url = $this->make_url("/files/$fileid");
        $this->reset_state();
        $result = $this->request($url, $options);
        $result = json_decode($result);
        return $result;
    }

    
    public function reset_state() {
        $this->cleanopt();
        $this->resetHeader();
    }

    
    protected function revoke_url() {
        return 'https://www.box.com/api/oauth2/revoke';
    }

    
    public function share_file($fileid, $businesscheck = true) {
                        $data = array('shared_link' => array('access' => 'open', 'permissions' =>
            array('can_download' => true, 'can_preview' => true)));
        $options = array(
            'CURLOPT_CUSTOMREQUEST' => 'PUT',
            'CURLOPT_POSTFIELDS' => json_encode($data)
        );
        $this->reset_state();
        $result = $this->request($this->make_url("/files/$fileid"), $options);
        $result = json_decode($result);

        if ($businesscheck) {
                        $this->reset_state();
            $this->head($result->shared_link->download_url);
            $info = $this->get_info();
            if ($info['http_code'] == 403) {
                throw new moodle_exception('No permission to share the file');
            }
        }

        return $result->shared_link;
    }

    
    public function search($query) {
        $this->reset_state();
        $result = $this->request($this->make_url('/search', array('query' => $query, 'limit' => 50, 'offset' => 0)));
        return json_decode($result);
    }

    
    protected function token_url() {
        return 'https://www.box.com/api/oauth2/token';
    }

    
    public function upload_file(stored_file $storedfile, $parentid = 0) {
        $url = $this->make_url('/files/content', array(), true);
        $options = array(
            'filename' => $storedfile,
            'parent_id' => $parentid
        );
        $this->reset_state();
        $result = $this->post($url, $options);
        $result = json_decode($result);
        return $result;
    }

}


class boxclient {
    
    public $auth_token = '';
    
    private $_box_api_url = 'https://www.box.com/api/1.0/rest';
    private $_box_api_upload_url = 'http://upload.box.com/api/1.0/upload';
    private $_box_api_download_url = 'http://www.box.com/api/1.0/download';
    private $_box_api_auth_url = 'http://www.box.com/api/1.0/auth';
    private $_error_code = '';
    private $_error_msg = '';
    
    private $debug = false;

    
    public function __construct($api_key, $auth_token = '', $debug = false) {
        $this->api_key    = $api_key;
        $this->auth_token = $auth_token;
        if (!empty($debug)) {
            $this->debug = true;
        } else {
            $this->debug = false;
        }
    }
    
    function makeRequest($method, $params = array()) {
        $this->_clearErrors();
        $c = new curl(array('debug'=>$this->debug, 'cache'=>true, 'module_cache'=>'repository'));
        $c->setopt(array('CURLOPT_FOLLOWLOCATION'=>1));
        try {
            if ($method == 'upload'){
                $request = $this->_box_api_upload_url.'/'.
                    $this->auth_token.'/'.$params['folder_id'];
                $xml = $c->post($request, $params);
            }else{
                $args = array();
                $xml = $c->get($this->_box_api_url, $params);
            }
            $xml_parser = xml_parser_create();
                        xml_parse_into_struct($xml_parser, $xml, $data);
            xml_parser_free($xml_parser);
        } catch (moodle_exception $e) {
            $this->setError(0, 'connection time-out or invalid url');
            return false;
        }
        return $data;
    }
    
    function getTicket($params = array()) {
        $params['api_key'] = $this->api_key;
        $params['action']  = 'get_ticket';
        $ret_array = array();
        $data = $this->makeRequest('action=get_ticket', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        foreach ($data as $a) {
            switch ($a['tag']) {
            case 'STATUS':
                $ret_array['status'] = $a['value'];
                break;
            case 'TICKET':
                $ret_array['ticket'] = $a['value'];
                break;
            }
        }
        return $ret_array;
    }

    
    function getAuthToken($ticket, $username, $password) {
        $c = new curl(array('debug'=>$this->debug));
        $c->setopt(array('CURLOPT_FOLLOWLOCATION'=>0));
        $param =  array(
            'login_form1'=>'',
            'login'=>$username,
            'password'=>$password,
            'dologin'=>1,
            '__login'=>1
            );
        try {
            $ret = $c->post($this->_box_api_auth_url.'/'.$ticket, $param);
        } catch (moodle_exception $e) {
            $this->setError(0, 'connection time-out or invalid url');
            return false;
        }
        $header = $c->getResponse();
        if(empty($header['location'])) {
            throw new repository_exception('invalidpassword', 'repository_boxnet');
        }
        $location = $header['location'];
        preg_match('#auth_token=(.*)$#i', $location, $matches);
        $auth_token = $matches[1];
        if(!empty($auth_token)) {
            $this->auth_token = $auth_token;
            return $auth_token;
        } else {
            throw new repository_exception('invalidtoken', 'repository_boxnet');
        }
    }
    
    function getfiletree($path, $params = array()) {
        $this->_clearErrors();
        $params['auth_token'] = $this->auth_token;
        $params['folder_id']  = 0;
        $params['api_key']    = $this->api_key;
        $params['action']     = 'get_account_tree';
        $params['onelevel']   = 1;
        $params['params[]']   = 'nozip';
        $c = new curl(array('debug'=>$this->debug));
        $c->setopt(array('CURLOPT_FOLLOWLOCATION'=>1));
        try {
            $args = array();
            $xml = $c->get($this->_box_api_url, $params);
        } catch (Exception $e){
        }
        $ret = array();
        $o = simplexml_load_string(trim($xml));
        if($o->status == 'listing_ok') {
            $tree = $o->tree->folder;
            $this->buildtree($tree, $ret);
        }
        return $ret;
    }

    
    function get_file_info($fileid, $timeout = 0) {
        $this->_clearErrors();
        $params = array();
        $params['action']     = 'get_file_info';
        $params['file_id']    = $fileid;
        $params['auth_token'] = $this->auth_token;
        $params['api_key']    = $this->api_key;
        $http = new curl(array('debug'=>$this->debug));
        $xml = $http->get($this->_box_api_url, $params, array('timeout' => $timeout));
        if (!$http->get_errno()) {
            $o = simplexml_load_string(trim($xml));
            if ($o->status == 's_get_file_info') {
                return $o->info;
            }
        }
        return null;
    }

    
    function buildtree($sax, &$tree){
        $sax = (array)$sax;
        $count = 0;
        foreach($sax as $k=>$v){
            if($k == 'folders'){
                $o = $sax[$k];
                foreach($o->folder as $z){
                    $tmp = array('title'=>(string)$z->attributes()->name,
                        'size'=>0, 'date'=>userdate(time()),
                        'thumbnail'=>'https://www.box.com/img/small_folder_icon.gif',
                        'path'=>array('name'=>(string)$z->attributes()->name, 'path'=>(int)$z->attributes()->id));
                    $tmp['children'] = array();
                    $this->buildtree($z, $tmp['children']);
                    $tree[] = $tmp;
                }
            } elseif ($k == 'files') {
                $val = $sax[$k]->file;
                foreach($val as $file){
                    $thumbnail = (string)$file->attributes()->thumbnail;
                    if (!preg_match('#^(?:http://)?([^/]+)#i', $thumbnail)) {
                        $thumbnail =  'http://www.box.com'.$thumbnail;
                    }
                    $tmp = array('title'=>(string)$file->attributes()->file_name,
                        'size'=>display_size((int)$file->attributes()->size),
                        'thumbnail'=>$thumbnail,
                        'date'=>userdate((int)$file->attributes()->updated),
                        'source'=> $this->_box_api_download_url .'/'
                            .$this->auth_token.'/'.(string)$file->attributes()->id,
                        'url'=>(string)$file->attributes()->shared_link);
                    $tree[] = $tmp;
                }
            }
            $count++;
        }
    }
    
    function getAccountTree($params = array()) {
        $params['auth_token'] = $this->auth_token;
        $params['folder_id']  = 0;
        $params['api_key']    = $this->api_key;
        $params['action']     = 'get_account_tree';
        $params['onelevel']   = 1;
        $params['params[]']   = 'nozip';
        $ret_array = array();
        $data = $this->makeRequest('action=get_account_tree', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        $tree_count=count($data);
        $entry_count = 0;
        for ($i=0; $i<$tree_count; $i++) {
            $a = $data[$i];
            switch ($a['tag'])
            {
            case 'FOLDER':
                if (@is_array($a['attributes'])) {
                    $ret_array['folder_id'][$i] = $a['attributes']['ID'];
                    $ret_array['folder_name'][$i] = $a['attributes']['NAME'];
                    $ret_array['shared'][$i] = $a['attributes']['SHARED'];
                }
                break;

            case 'FILE':
                if (@is_array($a['attributes'])) {
                    $ret_array['file_id'][$i] = $a['attributes']['ID'];
                    @$ret_array['file_name'][$i] = $a['attributes']['FILE_NAME'];
                    @$ret_array['file_keyword'][$i] = $a['attributes']['KEYWORD'];
                    @$ret_array['file_size'][$i] = display_size($a['attributes']['SIZE']);
                    @$ret_array['file_date'][$i] = userdate($a['attributes']['UPDATED']);
                    if (preg_match('#^(?:http://)?([^/]+)#i', $a['attributes']['THUMBNAIL'])) {
                        @$ret_array['thumbnail'][$i] =  $a['attributes']['THUMBNAIL'];
                    } else {
                        @$ret_array['thumbnail'][$i] =  'http://www.box.com'.$a['attributes']['THUMBNAIL'];
                    }
                    $entry_count++;
                }
                break;
            }
        }
        return $ret_array;
    }

    
    function CreateFolder($new_folder_name, $params = array()) {
        $params['auth_token'] =  $this->auth_token;
        $params['api_key']    = $this->api_key;
        $params['action']     = 'create_folder';
        $params['name']       = $new_folder_name;
        $defaults = array(
            'parent_id'  => 0,             'share'     => 1,         );
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $params)) {
                $params[$key] = $value;
            }
        }

        $ret_array = array();
        $data = $this->makeRequest('action=create_folder', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        foreach ($data as $a) {
            if (!empty($a['value'])) {
                switch ($a['tag']) {

                case 'FOLDER_ID':
                    $ret_array['folder_id'] = $a['value'];
                    break;

                case 'FOLDER_NAME':
                    $ret_array['folder_name'] = $a['value'];
                    break;

                case 'FOLDER_TYPE_ID':
                    $ret_array['folder_type_id'] = $a['value'];
                    break;

                case 'SHARED':
                    $ret_array['shared'] = $a['value'];
                    break;

                case 'PASSWORD':
                    $ret_array['password'] = $a['value'];
                    break;
                }
            } else {
                $ret_array[strtolower($a['tag'])] = null;
            }
        }
        return $ret_array;
    }

    
    function UploadFile ($params = array()) {
        $params['auth_token'] = $this->auth_token;
                $params['new_file1']  = $params['file'];
        unset($params['file']);
        $defaults = array(
            'folder_id' => 0,             'share'     => 1,         );
        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $params)) {
                $params[$key] = $value;
            }
        }
        $ret_array = array();
        $entry_count = 0;
        $data = $this->makeRequest('upload', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        for ($i=0, $tree_count=count($data); $i<$tree_count; $i++) {
            $a = $data[$i];
            switch ($a['tag']) {
            case 'STATUS':
                $ret_array['status'] = $a['value'];
                break;

            case 'FILE':
                if (is_array($a['attributes'])) {
                    @$ret_array['file_name'][$i] = $a['attributes']['FILE_NAME'];
                    @$ret_array['id'][$i] = $a['attributes']['ID'];
                    @$ret_array['folder_name'][$i] = $a['attributes']['FOLDER_NAME'];
                    @$ret_array['error'][$i] = $a['attributes']['ERROR'];
                    @$ret_array['public_name'][$i] = $a['attributes']['PUBLIC_NAME'];
                    $entry_count++;
                }
                break;
            }
        }

        return $ret_array;
    }
    
    function RenameFile($fileid, $newname) {
        $params = array(
            'api_key'    => $this->api_key,
            'auth_token' => $this->auth_token,
            'action'     => 'rename',
            'target'     => 'file',
            'target_id'  => $fileid,
            'new_name'   => $newname,
        );
        $data = $this->makeRequest('action=rename', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        foreach ($data as $a) {
            switch ($a['tag']) {
                case 'STATUS':
                    if ($a['value'] == 's_rename_node') {
                        return true;
                    }
            }
        }
        return false;
    }

    
    function RegisterUser($params = array()) {
        $params['api_key'] = $this->api_key;
        $params['action']  = 'register_new_user';
        $params['login']   = $_REQUEST['login'];
        $params['password'] = $_REQUEST['password'];
        $ret_array = array();
        $data = $this->makeRequest('action=register_new_user', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        foreach ($data as $a) {
            switch ($a['tag']) {
            case 'STATUS':
                $ret_array['status'] = $a['value'];
                break;

            case 'AUTH_TOKEN':
                $ret_array['auth_token'] = $a['value'];
                break;

            case 'LOGIN':
                $ret_array['login'] = $a['value'];
                break;
            case 'SPACE_AMOUNT':
                $ret_array['space_amount'] = $a['value'];
                break;
            case 'SPACE_USED':
                $ret_array['space_used'] = $a['value'];
                break;
            }
        }

        return $ret_array;
    }

    
    function AddTag($tag, $id, $target_type, $params = array()) {
        $params['auth_token'] = $this->auth_token;
        $params['api_key']    = $this->api_key;
        $params['action']     = 'add_to_tag';
        $params['target']     = $target_type;         $params['target_id']  = $id;         $params['tags[]']     = $tag;
        $ret_array = array();
        $data = $this->makeRequest('action=add_to_tag', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        foreach ($data as $a) {
            switch ($a['tag']) {
            case 'STATUS':
                $ret_array['status'] = $a['value'];

                break;
            }
        }
        return $ret_array;
    }

    
    function PublicShare($message, $emails, $id, $target_type, $password, $params = array()) {
        $params['auth_token'] = $this->auth_token;
        $params['api_key']    = $this->api_key;
        $params['action']     = 'public_share';
        $params['target']     = $target_type;
        $params['target_id']  = $id;
        $params['password']   =  $password;
        $params['message']    = $message;
        $params['emails']     = $emails;
        $ret_array = array();
        $data = $this->makeRequest('action=public_share', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        foreach ($data as $a) {
            switch ($a['tag']) {
            case 'STATUS':
                $ret_array['status'] = $a['value'];
                break;
            case 'PUBLIC_NAME':
                $ret_array['public_name'] = $a['value'];
                break;
            }
        }

        return $ret_array;
    }
    
    function GetFriends ($params = array()) {
        $params['auth_token'] = $this->auth_token;
        $params['action']     = 'get_friends';
        $params['api_key']    = $this->api_key;
        $params['params[]']   = 'nozip';
        $ret_array = array();
        $data = $this->makeRequest('action=get_friends', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        foreach ($data as $a) {
            switch ($a['tag']) {
            case 'NAME':
                $ret_array['name'] = $a['value'];
                break;
            case 'EMAIL':
                $ret_array['email'] = $a['value'];
                break;
            case 'ACCEPTED':
                $ret_array['accepted'] = $a['value'];
                break;
            case 'AVATAR_URL':
                $ret_array['avatar_url'] = $a['value'];
                break;
            case 'ID':
                $ret_array['id'] = $a['value'];
                break;
            case 'URL':
                $ret_array['url'] = $a['value'];
                break;
            case 'STATUS':
                $ret_array['status'] = $a['value'];
                break;
            }
        }
        return $ret_array;
    }

    
    function Logout($params = array()) {
        $params['auth_token'] = $this->auth_token;
        $params['api_key']    = $this->api_key;
        $params['action']     = 'logout';
        $ret_array = array();
        $data = $this->makeRequest('action=logout', $params);
        if ($this->_checkForError($data)) {
            return false;
        }
        foreach ($data as $a) {
            switch ($a['tag']) {
            case 'ACTION':
                $ret_array['logout'] = $a['value'];

                break;
            }
            return $ret_array;
        }
    }
    
    function _checkForError($data) {
        if ($this->_error_msg != '') {
            return true;
        }
        if (@$data[0]['attributes']['STAT'] == 'fail') {
            $this->_error_code = $data[1]['attributes']['CODE'];
            $this->_error_msg = $data[1]['attributes']['MSG'];
            return true;
        }
        return false;
    }

    
    public function isError() {
        if  ($this->_error_msg != '') {
            return true;
        }
        return false;
    }
    
    public function setError($code = 0, $msg){
        $this->_error_code = $code;
        $this->_error_msg  = $msg;
    }
    
    function getErrorMsg() {
        return '<p>Error: (' . $this->_error_code . ') ' . $this->_error_msg . '</p>';
    }
    
    function getErrorCode() {
        return $this->_error_code;
    }
    
    function _clearErrors() {
        $this->_error_code = '';
        $this->_error_msg = '';
    }

}
