<?php




class webdav_client {

    
    private $_debug = false;
    private $sock;
    private $_server;
    private $_protocol = 'HTTP/1.1';
    private $_port = 80;
    private $_socket = '';
    private $_path ='/';
    private $_auth = false;
    private $_user;
    private $_pass;

    private $_socket_timeout = 5;
    private $_errno;
    private $_errstr;
    private $_user_agent = 'Moodle WebDav Client';
    private $_crlf = "\r\n";
    private $_req;
    private $_resp_status;
    private $_parser;
    private $_parserid;
    private $_xmltree;
    private $_tree;
    private $_ls = array();
    private $_ls_ref;
    private $_ls_ref_cdata;
    private $_delete = array();
    private $_delete_ref;
    private $_delete_ref_cdata;
    private $_lock = array();
    private $_lock_ref;
    private $_lock_rec_cdata;
    private $_null = NULL;
    private $_header='';
    private $_body='';
    private $_connection_closed = false;
    private $_maxheaderlenth = 65536;
    private $_digestchallenge = null;
    private $_cnonce = '';
    private $_nc = 0;

    

    
    function __construct($server = '', $user = '', $pass = '', $auth = false, $socket = '') {
        if (!empty($server)) {
            $this->_server = $server;
        }
        if (!empty($user) && !empty($pass)) {
            $this->user = $user;
            $this->pass = $pass;
        }
        $this->_auth = $auth;
        $this->_socket = $socket;
    }
    public function __set($key, $value) {
        $property = '_' . $key;
        $this->$property = $value;
    }

    
    function set_protocol($version) {
        if ($version == 1) {
            $this->_protocol = 'HTTP/1.1';
        } else {
            $this->_protocol = 'HTTP/1.0';
        }
    }

    
    function iso8601totime($iso8601) {
        

        $regs = array();
        
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})Z$/', $iso8601, $regs)) {
            return mktime($regs[4],$regs[5], $regs[6], $regs[2], $regs[3], $regs[1]);
        }
        
        return false;
    }

    
    function open() {
                $this->_error_log('open a socket connection');
        $this->sock = fsockopen($this->_socket . $this->_server, $this->_port, $this->_errno, $this->_errstr, $this->_socket_timeout);
        core_php_time_limit::raise(30);
        if (is_resource($this->sock)) {
            socket_set_blocking($this->sock, true);
            $this->_connection_closed = false;
            $this->_error_log('socket is open: ' . $this->sock);
            return true;
        } else {
            $this->_error_log("$this->_errstr ($this->_errno)\n");
            return false;
        }
    }

    
    function close() {
        $this->_error_log('closing socket ' . $this->sock);
        $this->_connection_closed = true;
        fclose($this->sock);
    }

    
    function check_webdav() {
        $resp = $this->options();
        if (!$resp) {
            return false;
        }
        $this->_error_log($resp['header']['DAV']);
                if (preg_match('/1,2/', $resp['header']['DAV'])) {
            return true;
        }
                return false;
    }


    
    function options() {
        $this->header_unset();
        $this->create_basic_request('OPTIONS');
        $this->send_request();
        $this->get_respond();
        $response = $this->process_respond();
                        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
                return $response;
            }
        $this->_error_log('Response was not even http');
        return false;

    }

    
    function mkcol($path) {
        $this->_path = $this->translate_uri($path);
        $this->header_unset();
        $this->create_basic_request('MKCOL');
        $this->send_request();
        $this->get_respond();
        $response = $this->process_respond();
                        $http_version = $response['status']['http-version'];
        if ($http_version == 'HTTP/1.1' || $http_version == 'HTTP/1.0') {
            
            return $response['status']['status-code'];
        }

    }

    
    function get($path, &$buffer, $fp = null) {
        $this->_path = $this->translate_uri($path);
        $this->header_unset();
        $this->create_basic_request('GET');
        $this->send_request();
        $this->get_respond($fp);
        $response = $this->process_respond();

        $http_version = $response['status']['http-version'];
                        if ($http_version == 'HTTP/1.1' || $http_version == 'HTTP/1.0') {
                                                if ($response['status']['status-code'] == 200 ) {
                    if (!is_null($fp)) {
                        $stat = fstat($fp);
                        $this->_error_log('file created with ' . $stat['size'] . ' bytes.');
                    } else {
                        $this->_error_log('returning buffer with ' . strlen($response['body']) . ' bytes.');
                        $buffer = $response['body'];
                    }
                }
                return $response['status']['status-code'];
            }
                return false;
    }

    
    function put($path, $data ) {
        $this->_path = $this->translate_uri($path);
        $this->header_unset();
        $this->create_basic_request('PUT');
                $this->header_add('Content-length: ' . strlen($data));
        $this->header_add('Content-type: application/octet-stream');
                $this->send_request();
                fputs($this->sock, $data);
        $this->get_respond();
        $response = $this->process_respond();

                        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
                                                                                return $response['status']['status-code'];
            }
                return false;
    }

    
    function put_file($path, $filename) {
        

        $handle = @fopen ($filename, 'r');

        if ($handle) {
                        $this->_path = $this->translate_uri($path);
            $this->header_unset();
            $this->create_basic_request('PUT');
                        $this->header_add('Content-length: ' . filesize($filename));
            $this->header_add('Content-type: application/octet-stream');
                        $this->send_request();
            while (!feof($handle)) {
                fputs($this->sock,fgets($handle,4096));
            }
            fclose($handle);
            $this->get_respond();
            $response = $this->process_respond();

                                    if ($response['status']['http-version'] == 'HTTP/1.1' ||
                $response['status']['http-version'] == 'HTTP/1.0') {
                                                                                                    return $response['status']['status-code'];
                }
                        return false;
        } else {
            $this->_error_log('put_file: could not open ' . $filename);
            return false;
        }

    }

    
    function get_file($srcpath, $localpath) {

        $localpath = $this->utf_decode_path($localpath);

        $handle = fopen($localpath, 'wb');
        if ($handle) {
            $unused = '';
            $ret = $this->get($srcpath, $unused, $handle);
            fclose($handle);
            if ($ret) {
                return true;
            }
        }
        return false;
    }

    
    function copy_file($src_path, $dst_path, $overwrite) {
        $this->_path = $this->translate_uri($src_path);
        $this->header_unset();
        $this->create_basic_request('COPY');
        $this->header_add(sprintf('Destination: http://%s%s', $this->_server, $this->translate_uri($dst_path)));
        if ($overwrite) {
            $this->header_add('Overwrite: T');
        } else {
            $this->header_add('Overwrite: F');
        }
        $this->header_add('');
        $this->send_request();
        $this->get_respond();
        $response = $this->process_respond();
                        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
         
                return $response['status']['status-code'];
            }
        return false;
    }

    
    function copy_coll($src_path, $dst_path, $overwrite) {
        $this->_path = $this->translate_uri($src_path);
        $this->header_unset();
        $this->create_basic_request('COPY');
        $this->header_add(sprintf('Destination: http://%s%s', $this->_server, $this->translate_uri($dst_path)));
        $this->header_add('Depth: Infinity');

        $xml  = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
        $xml .= "<d:propertybehavior xmlns:d=\"DAV:\">\r\n";
        $xml .= "  <d:keepalive>*</d:keepalive>\r\n";
        $xml .= "</d:propertybehavior>\r\n";

        $this->header_add('Content-length: ' . strlen($xml));
        $this->header_add('Content-type: application/xml');
        $this->send_request();
                fputs($this->sock, $xml);
        $this->get_respond();
        $response = $this->process_respond();
                        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
         
                return $response['status']['status-code'];
            }
        return false;
    }

    
                function move($src_path,$dst_path, $overwrite) {

        $this->_path = $this->translate_uri($src_path);
        $this->header_unset();

        $this->create_basic_request('MOVE');
        $this->header_add(sprintf('Destination: http://%s%s', $this->_server, $this->translate_uri($dst_path)));
        if ($overwrite) {
            $this->header_add('Overwrite: T');
        } else {
            $this->header_add('Overwrite: F');
        }
        $this->header_add('');

        $this->send_request();
        $this->get_respond();
        $response = $this->process_respond();
                        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
            
                return $response['status']['status-code'];
            }
        return false;
    }

    
    function lock($path) {
        $this->_path = $this->translate_uri($path);
        $this->header_unset();
        $this->create_basic_request('LOCK');
        $this->header_add('Timeout: Infinite');
        $this->header_add('Content-type: text/xml');
                $xml =  "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
        $xml .= "<D:lockinfo xmlns:D='DAV:'\r\n>";
        $xml .= "  <D:lockscope><D:exclusive/></D:lockscope>\r\n";
        $xml .= "  <D:locktype><D:write/></D:locktype>\r\n";
        $xml .= "  <D:owner>\r\n";
        $xml .= "    <D:href>".($this->_user)."</D:href>\r\n";
        $xml .= "  </D:owner>\r\n";
        $xml .= "</D:lockinfo>\r\n";
        $this->header_add('Content-length: ' . strlen($xml));
        $this->send_request();
                fputs($this->sock, $xml);
        $this->get_respond();
        $response = $this->process_respond();
                        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
            

                switch($response['status']['status-code']) {
                case 200:
                                        if (strcmp($response['header']['Content-Type'], 'text/xml; charset="utf-8"') == 0) {
                                                $this->_parser = xml_parser_create_ns();
                        $this->_parserid = (int) $this->_parser;
                                                unset($this->_lock[$this->_parserid]);
                        unset($this->_xmltree[$this->_parserid]);
                        xml_parser_set_option($this->_parser,XML_OPTION_SKIP_WHITE,0);
                        xml_parser_set_option($this->_parser,XML_OPTION_CASE_FOLDING,0);
                        xml_set_object($this->_parser, $this);
                        xml_set_element_handler($this->_parser, "_lock_startElement", "_endElement");
                        xml_set_character_data_handler($this->_parser, "_lock_cdata");

                        if (!xml_parse($this->_parser, $response['body'])) {
                            die(sprintf("XML error: %s at line %d",
                                xml_error_string(xml_get_error_code($this->_parser)),
                                xml_get_current_line_number($this->_parser)));
                        }

                                                xml_parser_free($this->_parser);
                                                $this->_lock[$this->_parserid]['status'] = 200;
                        return $this->_lock[$this->_parserid];

                    } else {
                        print 'Missing Content-Type: text/xml header in response.<br>';
                    }
                    return false;

                default:
                                                            $this->_lock['status'] = $response['status']['status-code'];
                    return $this->_lock;
                }
            }


    }


    
    function unlock($path, $locktoken) {
        $this->_path = $this->translate_uri($path);
        $this->header_unset();
        $this->create_basic_request('UNLOCK');
        $this->header_add(sprintf('Lock-Token: <%s>', $locktoken));
        $this->send_request();
        $this->get_respond();
        $response = $this->process_respond();
        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
            
                return $response['status']['status-code'];
            }
        return false;
    }

    
    function delete($path) {
        $this->_path = $this->translate_uri($path);
        $this->header_unset();
        $this->create_basic_request('DELETE');
        
        $this->header_add('');
        $this->send_request();
        $this->get_respond();
        $response = $this->process_respond();

                        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
                                                
                switch ($response['status']['status-code']) {
                case 207:
                                                            if (strcmp($response['header']['Content-Type'], 'text/xml; charset="utf-8"') == 0) {
                                                $this->_parser = xml_parser_create_ns();
                        $this->_parserid = (int) $this->_parser;
                                                unset($this->_delete[$this->_parserid]);
                        unset($this->_xmltree[$this->_parserid]);
                        xml_parser_set_option($this->_parser,XML_OPTION_SKIP_WHITE,0);
                        xml_parser_set_option($this->_parser,XML_OPTION_CASE_FOLDING,0);
                        xml_set_object($this->_parser, $this);
                        xml_set_element_handler($this->_parser, "_delete_startElement", "_endElement");
                        xml_set_character_data_handler($this->_parser, "_delete_cdata");

                        if (!xml_parse($this->_parser, $response['body'])) {
                            die(sprintf("XML error: %s at line %d",
                                xml_error_string(xml_get_error_code($this->_parser)),
                                xml_get_current_line_number($this->_parser)));
                        }

                        print "<br>";

                                                xml_parser_free($this->_parser);
                        $this->_delete[$this->_parserid]['status'] = $response['status']['status-code'];
                        return $this->_delete[$this->_parserid];

                    } else {
                        print 'Missing Content-Type: text/xml header in response.<br>';
                    }
                    return false;

                default:
                                        $this->_delete['status'] = $response['status']['status-code'];
                    return $this->_delete;


                }
            }

    }

    
    function ls($path) {

        if (trim($path) == '') {
            $this->_error_log('Missing a path in method ls');
            return false;
        }
        $this->_path = $this->translate_uri($path);

        $this->header_unset();
        $this->create_basic_request('PROPFIND');
        $this->header_add('Depth: 1');
        $this->header_add('Content-type: application/xml');
                $xml  = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<propfind xmlns="DAV:"><prop>
<getcontentlength xmlns="DAV:"/>
<getlastmodified xmlns="DAV:"/>
<executable xmlns="http://apache.org/dav/props/"/>
<resourcetype xmlns="DAV:"/>
<checked-in xmlns="DAV:"/>
<checked-out xmlns="DAV:"/>
</prop></propfind>
EOD;
        $this->header_add('Content-length: ' . strlen($xml));
        $this->send_request();
        $this->_error_log($xml);
        fputs($this->sock, $xml);
        $this->get_respond();
        $response = $this->process_respond();
                        if ($response['status']['http-version'] == 'HTTP/1.1' ||
            $response['status']['http-version'] == 'HTTP/1.0') {
                                                                if (strcmp($response['status']['status-code'],'207') == 0 ) {
                                                            if (preg_match('#(application|text)/xml;\s?charset=[\'\"]?utf-8[\'\"]?#i', $response['header']['Content-Type'])) {
                                                $this->_parser = xml_parser_create_ns('UTF-8');
                        $this->_parserid = (int) $this->_parser;
                                                unset($this->_ls[$this->_parserid]);
                        unset($this->_xmltree[$this->_parserid]);
                        xml_parser_set_option($this->_parser,XML_OPTION_SKIP_WHITE,0);
                        xml_parser_set_option($this->_parser,XML_OPTION_CASE_FOLDING,0);
                                                xml_set_object($this->_parser, $this);
                        xml_set_element_handler($this->_parser, "_propfind_startElement", "_endElement");
                        xml_set_character_data_handler($this->_parser, "_propfind_cdata");


                        if (!xml_parse($this->_parser, $response['body'])) {
                            die(sprintf("XML error: %s at line %d",
                                xml_error_string(xml_get_error_code($this->_parser)),
                                xml_get_current_line_number($this->_parser)));
                        }

                                                xml_parser_free($this->_parser);
                        $arr = $this->_ls[$this->_parserid];
                        return $arr;
                    } else {
                        $this->_error_log('Missing Content-Type: text/xml header in response!!');
                        return false;
                    }
                } else {
                                        return $response['status']['status-code'];
                }
            }

                $this->_error_log('Ups in method ls: error in response from server');
        return false;
    }


    
    function gpi($path) {

                $path = rtrim($path, "/");
        $item = basename($path);
        $dir  = dirname($path);

        $list = $this->ls($dir);

                if (is_array($list)) {
            foreach($list as $e) {

                $fullpath = urldecode($e['href']);
                $filename = basename($fullpath);

                if ($filename == $item && $filename != "" and $fullpath != $dir."/") {
                    return $e;
                }
            }
        }
        return false;
    }

    
    function is_file($path) {

        $item = $this->gpi($path);

        if ($item === false) {
            return false;
        } else {
            return ($item['resourcetype'] != 'collection');
        }
    }

    
    function is_dir($path) {

                $item = $this->gpi($path);

        if ($item === false) {
            return false;
        } else {
            return ($item['resourcetype'] == 'collection');
        }
    }


    
    function mput($filelist) {

        $result = true;

        while (list($localpath, $destpath) = each($filelist)) {

            $localpath = rtrim($localpath, "/");
            $destpath  = rtrim($destpath, "/");

                        if (is_dir($localpath)) {
                $pathparts = explode("/", $destpath."/ ");             } else {
                $pathparts = explode("/", $destpath);
            }
            $checkpath = "";
            for ($i=1; $i<sizeof($pathparts)-1; $i++) {
                $checkpath .= "/" . $pathparts[$i];
                if (!($this->is_dir($checkpath))) {

                    $result &= ($this->mkcol($checkpath) == 201 );
                }
            }

            if ($result) {
                                if (is_dir($localpath)) {
                    if (!$dp = opendir($localpath)) {
                        $this->_error_log("Could not open localpath for reading");
                        return false;
                    }
                    $fl = array();
                    while($filename = readdir($dp)) {
                        if ((is_file($localpath."/".$filename) || is_dir($localpath."/".$filename)) && $filename!="." && $filename != "..") {
                            $fl[$localpath."/".$filename] = $destpath."/".$filename;
                        }
                    }
                    $result &= $this->mput($fl);
                } else {
                    $result &= ($this->put_file($destpath, $localpath) == 201);
                }
            }
        }
        return $result;
    }

    
    function mget($filelist) {

        $result = true;

        while (list($remotepath, $localpath) = each($filelist)) {

            $localpath   = rtrim($localpath, "/");
            $remotepath  = rtrim($remotepath, "/");

                        if ($this->is_dir($remotepath)) {
                $pathparts = explode("/", $localpath."/ ");             } else {
                $pathparts = explode("/", $localpath);
            }
            $checkpath = "";
            for ($i=1; $i<sizeof($pathparts)-1; $i++) {
                $checkpath .= "/" . $pathparts[$i];
                if (!is_dir($checkpath)) {

                    $result &= mkdir($checkpath);
                }
            }

            if ($result) {
                                if ($this->is_dir($remotepath)) {
                    $list = $this->ls($remotepath);

                    $fl = array();
                    foreach($list as $e) {
                        $fullpath = urldecode($e['href']);
                        $filename = basename($fullpath);
                        if ($filename != '' and $fullpath != $remotepath . '/') {
                            $fl[$remotepath."/".$filename] = $localpath."/".$filename;
                        }
                    }
                    $result &= $this->mget($fl);
                } else {
                    $result &= ($this->get_file($remotepath, $localpath));
                }
            }
        }
        return $result;
    }

            

    

    private function _endElement($parser, $name) {
                $parserid = (int) $parser;
        $this->_xmltree[$parserid] = substr($this->_xmltree[$parserid],0, strlen($this->_xmltree[$parserid]) - (strlen($name) + 1));
    }

    
    private function _propfind_startElement($parser, $name, $attrs) {
                $parserid = (int) $parser;

        $propname = strtolower($name);
        if (!empty($this->_xmltree[$parserid])) {
            $this->_xmltree[$parserid] .= $propname . '_';
        } else {
            $this->_xmltree[$parserid] = $propname . '_';
        }

                switch($this->_xmltree[$parserid]) {
        case 'dav::multistatus_dav::response_':
                        $this->_ls_ref =& $this->_ls[$parserid][];
            break;
        case 'dav::multistatus_dav::response_dav::href_':
            $this->_ls_ref_cdata = &$this->_ls_ref['href'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::creationdate_':
            $this->_ls_ref_cdata = &$this->_ls_ref['creationdate'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getlastmodified_':
            $this->_ls_ref_cdata = &$this->_ls_ref['lastmodified'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getcontenttype_':
            $this->_ls_ref_cdata = &$this->_ls_ref['getcontenttype'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::getcontentlength_':
            $this->_ls_ref_cdata = &$this->_ls_ref['getcontentlength'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::depth_':
            $this->_ls_ref_cdata = &$this->_ls_ref['activelock_depth'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_dav::href_':
            $this->_ls_ref_cdata = &$this->_ls_ref['activelock_owner'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_':
            $this->_ls_ref_cdata = &$this->_ls_ref['activelock_owner'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::timeout_':
            $this->_ls_ref_cdata = &$this->_ls_ref['activelock_timeout'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::locktoken_dav::href_':
            $this->_ls_ref_cdata = &$this->_ls_ref['activelock_token'];
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::lockdiscovery_dav::activelock_dav::locktype_dav::write_':
            $this->_ls_ref_cdata = &$this->_ls_ref['activelock_type'];
            $this->_ls_ref_cdata = 'write';
            $this->_ls_ref_cdata = &$this->_null;
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::prop_dav::resourcetype_dav::collection_':
            $this->_ls_ref_cdata = &$this->_ls_ref['resourcetype'];
            $this->_ls_ref_cdata = 'collection';
            $this->_ls_ref_cdata = &$this->_null;
            break;
        case 'dav::multistatus_dav::response_dav::propstat_dav::status_':
            $this->_ls_ref_cdata = &$this->_ls_ref['status'];
            break;

        default:
                        $this->_ls_ref_cdata = &$this->_ls_ref[$this->_xmltree[$parserid]];
        }
    }

    
    private function _propfind_cData($parser, $cdata) {
        if (trim($cdata) <> '') {
                                    $this->_ls_ref_cdata .= $cdata;
        } else {
                    }
    }

    
    private function _delete_startElement($parser, $name, $attrs) {
                $parserid = (int) $parser;
        $propname = strtolower($name);
        $this->_xmltree[$parserid] .= $propname . '_';

                switch($this->_xmltree[$parserid]) {
        case 'dav::multistatus_dav::response_':
                        $this->_delete_ref =& $this->_delete[$parserid][];
            break;
        case 'dav::multistatus_dav::response_dav::href_':
            $this->_delete_ref_cdata = &$this->_ls_ref['href'];
            break;

        default:
                        $this->_delete_cdata = &$this->_delete_ref[$this->_xmltree[$parserid]];
        }
    }


    
    private function _delete_cData($parser, $cdata) {
        if (trim($cdata) <> '') {
            $this->_delete_ref_cdata .= $cdata;
        } else {
                    }
    }


    
    private function _lock_startElement($parser, $name, $attrs) {
                $parserid = (int) $parser;
        $propname = strtolower($name);
        $this->_xmltree[$parserid] .= $propname . '_';

                
        switch($this->_xmltree[$parserid]) {
        case 'dav::prop_dav::lockdiscovery_dav::activelock_':
                        $this->_lock_ref =& $this->_lock[$parserid][];
            break;
        case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::locktype_dav::write_':
            $this->_lock_ref_cdata = &$this->_lock_ref['locktype'];
            $this->_lock_cdata = 'write';
            $this->_lock_cdata = &$this->_null;
            break;
        case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::lockscope_dav::exclusive_':
            $this->_lock_ref_cdata = &$this->_lock_ref['lockscope'];
            $this->_lock_ref_cdata = 'exclusive';
            $this->_lock_ref_cdata = &$this->_null;
            break;
        case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::depth_':
            $this->_lock_ref_cdata = &$this->_lock_ref['depth'];
            break;
        case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::owner_dav::href_':
            $this->_lock_ref_cdata = &$this->_lock_ref['owner'];
            break;
        case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::timeout_':
            $this->_lock_ref_cdata = &$this->_lock_ref['timeout'];
            break;
        case 'dav::prop_dav::lockdiscovery_dav::activelock_dav::locktoken_dav::href_':
            $this->_lock_ref_cdata = &$this->_lock_ref['locktoken'];
            break;
        default:
                        $this->_lock_cdata = &$this->_lock_ref[$this->_xmltree[$parserid]];

        }
    }

    
    private function _lock_cData($parser, $cdata) {
        $parserid = (int) $parser;
        if (trim($cdata) <> '') {
                        $this->_lock_ref_cdata .= $cdata;
        } else {
                    }
    }


    
    private function header_add($string) {
        $this->_req[] = $string;
    }

    

    private function header_unset() {
        unset($this->_req);
    }

    
    private function create_basic_request($method) {
        $this->header_add(sprintf('%s %s %s', $method, $this->_path, $this->_protocol));
        $this->header_add(sprintf('Host: %s:%s', $this->_server, $this->_port));
                $this->header_add(sprintf('User-Agent: %s', $this->_user_agent));
        $this->header_add('Connection: TE');
        $this->header_add('TE: Trailers');
        if ($this->_auth == 'basic') {
            $this->header_add(sprintf('Authorization: Basic %s', base64_encode("$this->_user:$this->_pass")));
        } else if ($this->_auth == 'digest') {
            if ($signature = $this->digest_signature($method)){
                $this->header_add($signature);
            }
        }
    }

    
    private function digest_auth() {

        $headers = array();
        $headers[] = sprintf('%s %s %s', 'HEAD', $this->_path, $this->_protocol);
        $headers[] = sprintf('Host: %s:%s', $this->_server, $this->_port);
        $headers[] = sprintf('User-Agent: %s', $this->_user_agent);
        $headers = implode("\r\n", $headers);
        $headers .= "\r\n\r\n";
        fputs($this->sock, $headers);

                $i = 0;
        $header = '';
        do {
            $header .= fread($this->sock, 1);
            $i++;
        } while (!preg_match('/\\r\\n\\r\\n$/', $header, $matches) && $i < $this->_maxheaderlenth);

                $digest = array();
        $splitheaders = explode("\r\n", $header);
        foreach ($splitheaders as $line) {
            if (!preg_match('/^WWW-Authenticate: Digest/', $line)) {
                continue;
            }
            $line = substr($line, strlen('WWW-Authenticate: Digest '));
            $params = explode(',', $line);
            foreach ($params as $param) {
                list($key, $value) = explode('=', trim($param), 2);
                $digest[$key] = trim($value, '"');
            }
            break;
        }

        $this->_digestchallenge = $digest;
    }

    
    private function digest_signature($method) {
        if (!$this->_digestchallenge) {
            $this->digest_auth();
        }

        $signature = array();
        $signature['username'] = '"' . $this->_user . '"';
        $signature['realm'] = '"' . $this->_digestchallenge['realm'] . '"';
        $signature['nonce'] = '"' . $this->_digestchallenge['nonce'] . '"';
        $signature['uri'] = '"' . $this->_path . '"';

        if (isset($this->_digestchallenge['algorithm']) && $this->_digestchallenge['algorithm'] != 'MD5') {
            $this->_error_log('Algorithm other than MD5 are not supported');
            return false;
        }

        $a1 = $this->_user . ':' . $this->_digestchallenge['realm'] . ':' . $this->_pass;
        $a2 = $method . ':' . $this->_path;

        if (!isset($this->_digestchallenge['qop'])) {
            $signature['response'] = '"' . md5(md5($a1) . ':' . $this->_digestchallenge['nonce'] . ':' . md5($a2)) . '"';
        } else {
                        if (empty($this->_cnonce)) {
                $this->_cnonce = random_string();
                $this->_nc = 0;
            }
            $this->_nc++;
            $nc = sprintf('%08d', $this->_nc);
            $signature['cnonce'] = '"' . $this->_cnonce . '"';
            $signature['nc'] = '"' . $nc . '"';
            $signature['qop'] = '"' . $this->_digestchallenge['qop'] . '"';
            $signature['response'] = '"' . md5(md5($a1) . ':' . $this->_digestchallenge['nonce'] . ':' .
                    $nc . ':' . $this->_cnonce . ':' . $this->_digestchallenge['qop'] . ':' . md5($a2)) . '"';
        }

        $response = array();
        foreach ($signature as $key => $value) {
            $response[] = "$key=$value";
        }
        return 'Authorization: Digest ' . implode(', ', $response);
    }

    
    private function send_request() {
                        if ($this->_connection_closed) {
                                    $this->close();
            $this->reopen();
        }

                $buffer = implode("\r\n", $this->_req);
        $buffer .= "\r\n\r\n";
        $this->_error_log($buffer);
        fputs($this->sock, $buffer);
    }

    
    private function get_respond($fp = null) {
        $this->_error_log('get_respond()');
                $buffer = '';
        $header = '';
                $max_chunk_size = 8192;
                if (! $this->sock) {
            $this->_error_log('socket is not open. Can not process response');
            return false;
        }

                                
                $i = 0;
        $matches = array();
        do {
            $header.=fread($this->sock, 1);
            $i++;
        } while (!preg_match('/\\r\\n\\r\\n$/',$header, $matches) && $i < $this->_maxheaderlenth);

        $this->_error_log($header);

        if (preg_match('/Connection: close\\r\\n/', $header)) {
                                    $this->_error_log('Connection: close found');
            $this->_connection_closed = true;
        } else if (preg_match('@^HTTP/1\.(1|0) 401 @', $header)) {
            $this->_error_log('The server requires an authentication');
        }

                                switch(true) {
        case (preg_match('/Transfer\\-Encoding:\\s+chunked\\r\\n/',$header)):
            $this->_error_log('Getting HTTP/1.1 chunked data...');
            do {
                $byte = '';
                $chunk_size='';
                do {
                    $chunk_size.=$byte;
                    $byte=fread($this->sock,1);
                                                            if (strlen($byte) == 0) {
                        $this->_error_log('get_respond: warning --> read zero bytes');
                    }
                } while ($byte!="\r" and strlen($byte)>0);                      fread($this->sock, 1);                                           $chunk_size=hexdec($chunk_size);                                if ($chunk_size > 0) {
                    $read = 0;
                                        while ($read < $chunk_size) {
                        $chunk = fread($this->sock, 1);
                        self::update_file_or_buffer($chunk, $fp, $buffer);
                        $read++;
                    }
                }
                fread($this->sock, 2);                                        } while ($chunk_size);                                        break;

                    case preg_match('/Content\\-Length:\\s+([0-9]*)\\r\\n/',$header,$matches):
            $this->_error_log('Getting data using Content-Length '. $matches[1]);

                        if ($matches[1] <= $max_chunk_size ) {
                                if ($matches[1] > 0 ) {
                    $chunk = fread($this->sock, $matches[1]);
                    $loadsize = strlen($chunk);
                                        if ($loadsize < $matches[1]) {
                        $max_chunk_size = $loadsize;
                        do {
                            $mod = $max_chunk_size % ($matches[1] - strlen($chunk));
                            $chunk_size = ($mod == $max_chunk_size ? $max_chunk_size : $matches[1] - strlen($chunk));
                            $chunk .= fread($this->sock, $chunk_size);
                            $this->_error_log('mod: ' . $mod . ' chunk: ' . $chunk_size . ' total: ' . strlen($chunk));
                        } while ($mod == $max_chunk_size);
                    }
                    self::update_file_or_buffer($chunk, $fp, $buffer);
                    break;
                } else {
                    $buffer = '';
                    break;
                }
            }

                                    $chunk = fread($this->sock, $max_chunk_size);
            $loadsize = strlen($chunk);
            self::update_file_or_buffer($chunk, $fp, $buffer);
            if ($loadsize < $max_chunk_size) {
                $max_chunk_size = $loadsize;
            }
            do {
                $mod = $max_chunk_size % ($matches[1] - $loadsize);
                $chunk_size = ($mod == $max_chunk_size ? $max_chunk_size : $matches[1] - $loadsize);
                $chunk = fread($this->sock, $chunk_size);
                self::update_file_or_buffer($chunk, $fp, $buffer);
                $loadsize += strlen($chunk);
                $this->_error_log('mod: ' . $mod . ' chunk: ' . $chunk_size . ' total: ' . $loadsize);
            } while ($mod == $max_chunk_size);
            if ($loadsize < $matches[1]) {
                $chunk = fread($this->sock, $matches[1] - $loadsize);
                self::update_file_or_buffer($chunk, $fp, $buffer);
            }
            break;

                                            case preg_match('/HTTP\/1\.1\ 204/',$header):
                        $this->_error_log('204 No Content found. No further data to read..');
            break;
        default:
                        $this->_error_log('reading until feof...' . $header);
            socket_set_timeout($this->sock, 0, 0);
            while (!feof($this->sock)) {
                $chunk = fread($this->sock, 4096);
                self::update_file_or_buffer($chunk, $fp, $buffer);
            }
                        socket_set_timeout($this->sock, $this->_socket_timeout, 0);
        }

        $this->_header = $header;
        $this->_body = $buffer;
                $this->_error_log($this->_header);
        $this->_error_log($this->_body);

    }

    
    static private function update_file_or_buffer($chunk, $fp, &$buffer) {
        if ($fp) {
            fwrite($fp, $chunk);
        } else {
            $buffer .= $chunk;
        }
    }

    
    private function process_respond() {
        $lines = explode("\r\n", $this->_header);
        $header_done = false;
                                list($ret_struct['status']['http-version'],
            $ret_struct['status']['status-code'],
            $ret_struct['status']['reason-phrase']) = explode(' ', $lines[0],3);

                                for($i=1; $i<count($lines); $i++) {
            if (rtrim($lines[$i]) == '' && !$header_done) {
                $header_done = true;
                
            }
            if (!$header_done ) {
                                list($fieldname, $fieldvalue) = explode(':', $lines[$i]);
                                                if (empty($ret_struct['header'])) {
                    $ret_struct['header'] = array();
                }
                if (empty($ret_struct['header'][$fieldname])) {
                    $ret_struct['header'][$fieldname] = trim($fieldvalue);
                } else {
                    $ret_struct['header'][$fieldname] .= ',' . trim($fieldvalue);
                }
            }
        }
                        $ret_struct['body'] = $this->_body;
        $this->_error_log('process_respond: ' . var_export($ret_struct,true));
        return $ret_struct;

    }

    
    private function reopen() {
                $this->_error_log('reopen a socket connection');
        return $this->open();
    }


    
    private function translate_uri($uri) {
                $native_path = html_entity_decode($uri);
        $parts = explode('/', $native_path);
        for ($i = 0; $i < count($parts); $i++) {
                        if (iconv('UTF-8', 'UTF-8', $parts[$i]) == $parts[$i]) {
                $parts[$i] = rawurlencode($parts[$i]);
            } else {
                $parts[$i] = rawurlencode(utf8_encode($parts[$i]));
            }
        }
        return implode('/', $parts);
    }

    
    private function utf_decode_path($path) {
        $fullpath = $path;
        if (iconv('UTF-8', 'UTF-8', $fullpath) == $fullpath) {
            $this->_error_log("filename is utf-8. Needs conversion...");
            $fullpath = utf8_decode($fullpath);
        }
        return $fullpath;
    }

    
    private function _error_log($err_string) {
        if ($this->_debug) {
            error_log($err_string);
        }
    }
}
