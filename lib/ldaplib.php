<?php



defined('MOODLE_INTERNAL') || die();

if (!defined('ROOTDSE')) {
    define ('ROOTDSE', '');
}

if (!defined('LDAP_DEFAULT_PAGESIZE')) {
    define('LDAP_DEFAULT_PAGESIZE', 250);
}


function ldap_supported_usertypes() {
    $types = array();
    $types['edir'] = 'Novell Edirectory';
    $types['rfc2307'] = 'posixAccount (rfc2307)';
    $types['rfc2307bis'] = 'posixAccount (rfc2307bis)';
    $types['samba'] = 'sambaSamAccount (v.3.0.7)';
    $types['ad'] = 'MS ActiveDirectory';
    $types['default'] = get_string('default');
    return $types;
}


function ldap_getdefaults() {
            $default['objectclass'] = array(
                        'edir' => 'user',
                        'rfc2307' => 'posixaccount',
                        'rfc2307bis' => 'posixaccount',
                        'samba' => 'sambasamaccount',
                        'ad' => '(samaccounttype=805306368)',
                        'default' => '*'
                        );
    $default['user_attribute'] = array(
                        'edir' => 'cn',
                        'rfc2307' => 'uid',
                        'rfc2307bis' => 'uid',
                        'samba' => 'uid',
                        'ad' => 'cn',
                        'default' => 'cn'
                        );
    $default['suspended_attribute'] = array(
                        'edir' => '',
                        'rfc2307' => '',
                        'rfc2307bis' => '',
                        'samba' => '',
                        'ad' => '',
                        'default' => ''
                        );
    $default['memberattribute'] = array(
                        'edir' => 'member',
                        'rfc2307' => 'member',
                        'rfc2307bis' => 'member',
                        'samba' => 'member',
                        'ad' => 'member',
                        'default' => 'member'
                        );
    $default['memberattribute_isdn'] = array(
                        'edir' => '1',
                        'rfc2307' => '0',
                        'rfc2307bis' => '1',
                        'samba' => '0',                         'ad' => '1',
                        'default' => '0'
                        );
    $default['expireattr'] = array (
                        'edir' => 'passwordexpirationtime',
                        'rfc2307' => 'shadowexpire',
                        'rfc2307bis' => 'shadowexpire',
                        'samba' => '',                         'ad' => 'pwdlastset',
                        'default' => ''
                        );
    return $default;
}


function ldap_isgroupmember($ldapconnection, $userid, $group_dns, $member_attrib) {
    if (empty($ldapconnection) || empty($userid) || empty($group_dns) || empty($member_attrib)) {
        return false;
    }

    $result = false;
    foreach ($group_dns as $group) {
        $group = trim($group);
        if (empty($group)) {
            continue;
        }

                                if (stripos(strrev(strtolower($userid)), strrev(strtolower($group))) === 0) {
            $result = true;
            break;
        }

        $search = ldap_read($ldapconnection, $group,
                            '('.$member_attrib.'='.ldap_filter_addslashes($userid).')',
                            array($member_attrib));

        if (!empty($search) && ldap_count_entries($ldapconnection, $search)) {
            $info = ldap_get_entries_moodle($ldapconnection, $search);
            if (count($info) > 0 ) {
                                $result = true;
                break;
            }
        }
    }

    return $result;
}


function ldap_connect_moodle($host_url, $ldap_version, $user_type, $bind_dn, $bind_pw, $opt_deref, &$debuginfo, $start_tls=false) {
    if (empty($host_url) || empty($ldap_version) || empty($user_type)) {
        $debuginfo = 'No LDAP Host URL, Version or User Type specified in your LDAP settings';
        return false;
    }

    $debuginfo = '';
    $urls = explode(';', $host_url);
    foreach ($urls as $server) {
        $server = trim($server);
        if (empty($server)) {
            continue;
        }

        $connresult = ldap_connect($server); 
        if (!empty($ldap_version)) {
            ldap_set_option($connresult, LDAP_OPT_PROTOCOL_VERSION, $ldap_version);
        }

                if ($user_type === 'ad') {
            ldap_set_option($connresult, LDAP_OPT_REFERRALS, 0);
        }

        if (!empty($opt_deref)) {
            ldap_set_option($connresult, LDAP_OPT_DEREF, $opt_deref);
        }

        if ($start_tls && (!ldap_start_tls($connresult))) {
            $debuginfo .= "Server: '$server', Connection: '$connresult', STARTTLS failed.\n";
            continue;
        }

        if (!empty($bind_dn)) {
            $bindresult = @ldap_bind($connresult, $bind_dn, $bind_pw);
        } else {
                        $bindresult = @ldap_bind($connresult);
        }

        if ($bindresult) {
            return $connresult;
        }

        $debuginfo .= "Server: '$server', Connection: '$connresult', Bind result: '$bindresult'\n";
    }

        return false;
}


function ldap_find_userdn($ldapconnection, $username, $contexts, $objectclass, $search_attrib, $search_sub) {
    if (empty($ldapconnection) || empty($username) || empty($contexts) || empty($objectclass) || empty($search_attrib)) {
        return false;
    }

        $ldap_user_dn = false;

        foreach ($contexts as $context) {
        $context = trim($context);
        if (empty($context)) {
            continue;
        }

        if ($search_sub) {
            $ldap_result = @ldap_search($ldapconnection, $context,
                                        '(&'.$objectclass.'('.$search_attrib.'='.ldap_filter_addslashes($username).'))',
                                        array($search_attrib));
        } else {
            $ldap_result = @ldap_list($ldapconnection, $context,
                                      '(&'.$objectclass.'('.$search_attrib.'='.ldap_filter_addslashes($username).'))',
                                      array($search_attrib));
        }

        if (!$ldap_result) {
            continue;         }

        $entry = ldap_first_entry($ldapconnection, $ldap_result);
        if ($entry) {
            $ldap_user_dn = ldap_get_dn($ldapconnection, $entry);
            break;
        }
    }

    return $ldap_user_dn;
}


function ldap_normalise_objectclass($objectclass, $default = '*') {
    if (empty($objectclass)) {
                $return = sprintf('(objectClass=%s)', $default);
    } else if (stripos($objectclass, 'objectClass=') === 0) {
                $return = sprintf('(%s)', $objectclass);
    } else if (stripos($objectclass, '(') !== 0) {
                        $return = sprintf('(objectClass=%s)', $objectclass);
    } else {
                                                                                $return = $objectclass;
    }

    return $return;
}


function ldap_get_entries_moodle($ldapconnection, $searchresult) {
    if (empty($ldapconnection) || empty($searchresult)) {
        return array();
    }

    $i = 0;
    $result = array();
    $entry = ldap_first_entry($ldapconnection, $searchresult);
    if (!$entry) {
        return array();
    }
    do {
        $attributes = array_change_key_case(ldap_get_attributes($ldapconnection, $entry), CASE_LOWER);
        for ($j = 0; $j < $attributes['count']; $j++) {
            $values = ldap_get_values_len($ldapconnection, $entry, $attributes[$j]);
            if (is_array($values)) {
                $result[$i][$attributes[$j]] = $values;
            } else {
                $result[$i][$attributes[$j]] = array($values);
            }
        }
        $i++;
    } while ($entry = ldap_next_entry($ldapconnection, $entry));

    return ($result);
}


function ldap_filter_addslashes($text) {
    $text = str_replace('\\', '\\5c', $text);
    $text = str_replace(array('*',    '(',    ')',    "\0"),
                        array('\\2a', '\\28', '\\29', '\\00'), $text);
    return $text;
}

if(!defined('LDAP_DN_SPECIAL_CHARS')) {
    define('LDAP_DN_SPECIAL_CHARS', 0);
}
if(!defined('LDAP_DN_SPECIAL_CHARS_QUOTED_NUM')) {
    define('LDAP_DN_SPECIAL_CHARS_QUOTED_NUM', 1);
}
if(!defined('LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA')) {
    define('LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA', 2);
}
if(!defined('LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA_REGEX')) {
    define('LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA_REGEX', 3);
}


function ldap_get_dn_special_chars() {
    static $specialchars = null;

    if ($specialchars !== null) {
        return $specialchars;
    }

    $specialchars = array (
        LDAP_DN_SPECIAL_CHARS              => array('\\',  ' ',   '"',   '#',   '+',   ',',   ';',   '<',   '=',   '>',   "\0"),
        LDAP_DN_SPECIAL_CHARS_QUOTED_NUM   => array('\\5c','\\20','\\22','\\23','\\2b','\\2c','\\3b','\\3c','\\3d','\\3e','\\00'),
        LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA => array('\\\\','\\ ', '\\"', '\\#', '\\+', '\\,', '\\;', '\\<', '\\=', '\\>', '\\00'),
        );
    $alpharegex = implode('|', array_map (function ($expr) { return preg_quote($expr); },
                                          $specialchars[LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA]));
    $specialchars[LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA_REGEX] = $alpharegex;

    return $specialchars;
}


function ldap_addslashes($text) {
    $special_dn_chars = ldap_get_dn_special_chars();

            $text = str_replace ($special_dn_chars[LDAP_DN_SPECIAL_CHARS],
                         $special_dn_chars[LDAP_DN_SPECIAL_CHARS_QUOTED_NUM],
                         $text);
    return $text;
}


function ldap_stripslashes($text) {
    $specialchars = ldap_get_dn_special_chars();

                                $quoted = '/(\\\\[0-9A-Fa-f]{2}|' . $specialchars[LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA_REGEX] . ')/';
    $text = preg_replace_callback($quoted,
                                  function ($match) use ($specialchars) {
                                      if (ctype_xdigit(ltrim($match[1], '\\'))) {
                                          return chr(hexdec($match[1]));
                                      } else {
                                          return str_replace($specialchars[LDAP_DN_SPECIAL_CHARS_QUOTED_ALPHA],
                                                             $specialchars[LDAP_DN_SPECIAL_CHARS],
                                                             $match[1]);
                                      }
                                  },
                                  $text);

    return $text;
}



function ldap_paged_results_supported($ldapversion) {
    if ((int)$ldapversion === 3) {
        return true;
    }

    return false;
}
