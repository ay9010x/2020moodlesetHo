<?php






















class fcFPP
{
    var $_hostname;             var $_port;                 var $_conn = 0;             var $_debug = FALSE;    
        public function __construct($host="localhost", $port="3333")
    {
    $this->_hostname = $host;
    $this->_port = $port;
    $this->_user = "";
    $this->_pwd = "";
    }

    function fcFPP($host="localhost", $port="3333")
    {
           debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
           self::__construct($host, $port);
    }

        function open()
    {
    if ($this->_debug) echo "Connecting to host ";
    $host = $this->_hostname;
    $port = $this->_port;

    if ($this->_debug) echo "[$host:$port]..";

        $conn = fsockopen($host, $port, $errno, $errstr, 5);
    if (!$conn)
    {
        print_error('auth_fcconnfail','auth_fc', '', array('no'=>$errno, 'str'=>$errstr));
        return false;
    }

        if ($this->_debug) echo "connected!";

        $line = fgets ($conn);            $line = fgets ($conn);        
        $this->_conn = & $conn;

    return true;
    }

        function close()
    {
        $conn = &$this->_conn;

            if ($conn)
    {
        fclose($conn);

                unset($this->_conn);
        return true;
    }
    return;
    }


        function login($userid, $passwd)
    {
            if ($this->_conn)
    {
                fputs($this->_conn,"$userid\r\n");

        $line = fgets ($this->_conn);                $line = fgets ($this->_conn);                $line = fgets ($this->_conn);        
                fputs($this->_conn,"$passwd\r\n");
        $line = fgets ($this->_conn);                $line = fgets ($this->_conn);                $line = fgets ($this->_conn);        
        if ($this->_debug) echo $line;

        if (preg_match ("/^\+0/", $line)) {                  $this->_user = $userid;
            $this->_pwd  = $passwd;
            return TRUE;
        } elseif (strpos($line, 'You are not allowed')) {                                                                      return TRUE;
        } else {                                return FALSE;
        }


    }
    return FALSE;
    }

        function getGroups($userid) {

    $groups = array();

        if ($this->_conn AND $this->_user) {
                fputs($this->_conn,"GET USER '" . $userid . "' 4 -1\r");
        $line = "";
        while (!$line) {
        $line = trim(fgets ($this->_conn));
        }
        $n = 0;
        while ($line AND !preg_match("/^\+0/", $line) AND $line != "-1003") {
        list( , , $groups[$n++]) = explode(" ",$line,3);
        $line = trim(fgets ($this->_conn));
        }
            if ($this->_debug) echo "getGroups:" . implode(",",$groups);
    }

    return $groups;
    }

            function isMemberOf($userid, $groups) {

    $usergroups = array_map("strtolower",$this->getGroups($userid));
    $groups = array_map("strtolower",$groups);

    $result = array_intersect($groups,$usergroups);

        if ($this->_debug) echo "isMemberOf:" . implode(",",$result);

    return $result;

    }

    function getUserInfo($userid, $field) {

    $userinfo = "";

    if ($this->_conn AND $this->_user) {
                fputs($this->_conn,"GET USER '" . $userid . "' " . $field . "\r");
        $line = "";
        while (!$line) {
            $line = trim(fgets ($this->_conn));
        }
        $n = 0;
        while ($line AND !preg_match("/^\+0/", $line)) {
        list( , , $userinfo) = explode(" ",$line,3);
        $line = trim(fgets ($this->_conn));
        }
        if ($this->_debug) echo "getUserInfo:" . $userinfo;
    }

    return str_replace('\r',' ',trim($userinfo,'"'));

    }

    function getResume($userid) {

    $resume = "";

    $pattern = "/\[.+:.+\..+\]/";         
    if ($this->_conn AND $this->_user) {
                fputs($this->_conn,"GET RESUME '" . $userid . "' 6\r");
        $line = "";
        while (!$line) {
               $line = trim(fgets ($this->_conn));
        }
        $n = 0;
        while ($line AND !preg_match("/^\+0/", $line)) {
            $resume .= preg_replace($pattern,"",str_replace('\r',"\n",trim($line,'6 ')));
        $line = trim(fgets ($this->_conn));
        
        }
        if ($this->_debug) echo "getResume:" . $resume;
    }

    return $resume;

    }


}


?>
