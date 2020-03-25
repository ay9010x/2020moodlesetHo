<?php




class core_useragent {

    
    const DEVICETYPE_DEFAULT = 'default';
    
    const DEVICETYPE_LEGACY = 'legacy';
    
    const DEVICETYPE_MOBILE = 'mobile';
    
    const DEVICETYPE_TABLET = 'tablet';

    
    protected static $instance = null;

    
    public static $devicetypes = array(
        self::DEVICETYPE_DEFAULT,
        self::DEVICETYPE_LEGACY,
        self::DEVICETYPE_MOBILE,
        self::DEVICETYPE_TABLET,
    );

    
    protected $useragent = null;

    
    protected $devicetype = null;

    
    protected $devicetypecustoms = array();

    
    protected $supportssvg = null;

    
    public static function instance($reload = false, $forceuseragent = null) {
        if (!self::$instance || $reload) {
            self::$instance = new core_useragent($forceuseragent);
        }
        return self::$instance;
    }

    
    protected function __construct($forceuseragent = null) {
        global $CFG;
        if (!empty($CFG->devicedetectregex)) {
            $this->devicetypecustoms = json_decode($CFG->devicedetectregex, true);
        }
        if ($this->devicetypecustoms === null) {
                        debugging('Config devicedetectregex is not valid JSON object');
            $this->devicetypecustoms = array();
        }
        if ($forceuseragent !== null) {
            $this->useragent = $forceuseragent;
        } else if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $this->useragent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $this->useragent = false;
            $this->devicetype = self::DEVICETYPE_DEFAULT;
        }
    }

    
    public static function get_user_agent_string() {
        $instance = self::instance();
        return $instance->useragent;
    }

    
    public static function get_device_type() {
        $instance = self::instance();
        if ($instance->devicetype === null) {
            return $instance->guess_device_type();
        }
        return $instance->devicetype;
    }

    
    protected function guess_device_type() {
        global $CFG;
        if (empty($CFG->enabledevicedetection)) {
            $this->devicetype = self::DEVICETYPE_DEFAULT;
            return $this->devicetype;
        }
        foreach ($this->devicetypecustoms as $value => $regex) {
            if (preg_match($regex, $this->useragent)) {
                $this->devicetype = $value;
                return $this->devicetype;
            }
        }
        if ($this->is_useragent_mobile()) {
            $this->devicetype = self::DEVICETYPE_MOBILE;
        } else if ($this->is_useragent_tablet()) {
            $this->devicetype = self::DEVICETYPE_TABLET;
        } else if (self::check_ie_version('0') && !self::check_ie_version('7.0')) {
                        $this->devicetype = self::DEVICETYPE_LEGACY;
        } else {
            $this->devicetype = self::DEVICETYPE_DEFAULT;
        }
        return $this->devicetype;
    }

    
    protected function is_useragent_mobile() {
                $phonesregex = '/android .+ mobile|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i';
        $modelsregex = '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i';
        return (preg_match($phonesregex, $this->useragent) || preg_match($modelsregex, substr($this->useragent, 0, 4)));
    }

    
    protected function is_useragent_tablet() {
        $tabletregex = '/Tablet browser|android|iPad|iProd|GT-P1000|GT-I9000|SHW-M180S|SGH-T849|SCH-I800|Build\/ERE27|sholest/i';
        return (preg_match($tabletregex, $this->useragent));
    }

    
    protected function is_useragent_web_crawler() {
        $regex = '/Googlebot|google\.com|Yahoo! Slurp|\[ZSEBOT\]|msnbot|bingbot|BingPreview|Yandex|AltaVista|Baiduspider|Teoma/i';
        return (preg_match($regex, $this->useragent));
    }

    
    public static function get_device_type_list($includecustomtypes = true) {
        $types = self::$devicetypes;
        if ($includecustomtypes) {
            $instance = self::instance();
            $types = array_merge($types, array_keys($instance->devicetypecustoms));
        }
        return $types;
    }

    
    public static function get_device_type_theme($devicetype = null) {
        global $CFG;
        if ($devicetype === null) {
            $devicetype = self::get_device_type();
        }
        $themevarname = self::get_device_type_cfg_var_name($devicetype);
        if (empty($CFG->$themevarname)) {
            return false;
        }
        return $CFG->$themevarname;
    }

    
    public static function get_device_type_cfg_var_name($devicetype = null) {
        if ($devicetype == self::DEVICETYPE_DEFAULT || empty($devicetype)) {
            return 'theme';
        }
        return 'theme' . $devicetype;
    }

    
    public static function get_user_device_type() {
        $device = self::get_device_type();
        $switched = get_user_preferences('switchdevice'.$device, false);
        if ($switched != false) {
            return $switched;
        }
        return $device;
    }

    
    public static function set_user_device_type($newdevice) {
        $devicetype = self::get_device_type();
        if ($newdevice == $devicetype) {
            unset_user_preference('switchdevice'.$devicetype);
            return true;
        } else {
            $devicetypes = self::get_device_type_list();
            if (in_array($newdevice, $devicetypes)) {
                set_user_preference('switchdevice'.$devicetype, $newdevice);
                return true;
            }
        }
        throw new coding_exception('Invalid device type provided to set_user_device_type');
    }

    
    public static function check_browser_version($brand, $version = null) {
        switch ($brand) {

            case 'MSIE':
                                return self::check_ie_version($version);

            case 'Firefox':
                                return self::check_firefox_version($version);

            case 'Chrome':
                return self::check_chrome_version($version);

            case 'Opera':
                                return self::check_opera_version($version);

            case 'Safari':
                                return self::check_safari_version($version);

            case 'Safari iOS':
                                return self::check_safari_ios_version($version);

            case 'WebKit':
                                return self::check_webkit_version($version);

            case 'Gecko':
                                return self::check_gecko_version($version);

            case 'WebKit Android':
                                return self::check_webkit_android_version($version);

            case 'Camino':
                                return self::check_camino_version($version);
        }
                return false;
    }

    
    protected static function check_camino_version($version = null) {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'Camino') === false) {
            return false;
        }
        if (empty($version)) {
            return true;         }
        if (preg_match("/Camino\/([0-9\.]+)/i", $useragent, $match)) {
            if (version_compare($match[1], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_firefox() {
        return self::check_firefox_version();
    }

    
    public static function check_firefox_version($version = null) {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'Firefox') === false && strpos($useragent, 'Iceweasel') === false) {
            return false;
        }
        if (empty($version)) {
            return true;         }
        if (preg_match("/(Iceweasel|Firefox)\/([0-9\.]+)/i", $useragent, $match)) {
            if (version_compare($match[2], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_gecko() {
        return self::check_gecko_version();
    }

    
    public static function check_gecko_version($version = null) {
                        $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (empty($version)) {
            $version = 1;
        } else if ($version > 20000000) {
                        if (preg_match('/^201/', $version)) {
                $version = 3.6;
            } else if (preg_match('/^200[7-9]/', $version)) {
                $version = 3;
            } else if (preg_match('/^2006/', $version)) {
                $version = 2;
            } else {
                $version = 1.5;
            }
        }
        if (preg_match("/(Iceweasel|Firefox)\/([0-9\.]+)/i", $useragent, $match)) {
                        if (version_compare($match[2], $version) >= 0) {
                return true;
            }
        } else if (preg_match("/Gecko\/([0-9\.]+)/i", $useragent, $match)) {
                        $browserver = $match[1];
            if ($browserver > 20000000) {
                                if (preg_match('/^201/', $browserver)) {
                    $browserver = 3.6;
                } else if (preg_match('/^200[7-9]/', $browserver)) {
                    $browserver = 3;
                } else if (preg_match('/^2006/', $version)) {
                    $browserver = 2;
                } else {
                    $browserver = 1.5;
                }
            }
            if (version_compare($browserver, $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_edge() {
        return self::check_edge_version();
    }

    
    public static function check_edge_version($version = null) {
        $useragent = self::get_user_agent_string();

        if ($useragent === false) {
                        return false;
        }

        if (strpos($useragent, 'Edge/') === false) {
                        return false;
        }

        if (empty($version)) {
                        return true;
        }

                                preg_match('%Edge/([\d]+)\.(.*)$%', $useragent, $matches);

                        $version = round($version);

                return version_compare($matches[1], $version, '>=');
    }

    
    public static function is_ie() {
        return self::check_ie_version();
    }

    
    public static function check_ie_properties() {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'Opera') !== false) {
                        return false;
        }
                if (preg_match("/MSIE ([0-9\.]+)/", $useragent, $match)) {
            $browser = $match[1];
                } else if (preg_match("/Trident\/[0-9\.]+/", $useragent) && preg_match("/rv:([0-9\.]+)/", $useragent, $match)) {
            $browser = $match[1];
        } else {
            return false;
        }

        $compatview = false;
                        if ($browser === '7.0' and preg_match("/Trident\/([0-9\.]+)/", $useragent, $match)) {
            $compatview = true;
            $browser = $match[1] + 4;         }
        $browser = round($browser, 1);
        return array(
            'version'    => $browser,
            'compatview' => $compatview
        );
    }

    
    public static function check_ie_version($version = null) {
                $properties = self::check_ie_properties();
        if (!is_array($properties)) {
            return false;
        }
                if (is_null($version)) {
            $version = 5.5;         }
                $version = round($version, 1);
        return ($properties['version'] >= $version);
    }

    
    public static function check_ie_compatibility_view() {
                                                                                        $properties = self::check_ie_properties();
        if (!is_array($properties)) {
            return false;
        }
        return $properties['compatview'];
    }

    
    public static function is_opera() {
        return self::check_opera_version();
    }

    
    public static function check_opera_version($version = null) {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'Opera') === false) {
            return false;
        }
        if (empty($version)) {
            return true;         }
                                if (preg_match("/Version\/([0-9\.]+)/i", $useragent, $match)) {
            if (version_compare($match[1], $version) >= 0) {
                return true;
            }
        } else if (preg_match("/Opera\/([0-9\.]+)/i", $useragent, $match)) {
            if (version_compare($match[1], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_webkit() {
        return self::check_webkit_version();
    }

    
    public static function check_webkit_version($version = null) {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'AppleWebKit') === false) {
            return false;
        }
        if (empty($version)) {
            return true;         }
        if (preg_match("/AppleWebKit\/([0-9.]+)/i", $useragent, $match)) {
            if (version_compare($match[1], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_safari() {
        return self::check_safari_version();
    }

    
    public static function check_safari_version($version = null) {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'AppleWebKit') === false) {
            return false;
        }
                if (strpos($useragent, 'OmniWeb')) {
                        return false;
        }
        if (strpos($useragent, 'Shiira')) {
                        return false;
        }
        if (strpos($useragent, 'SymbianOS')) {
                        return false;
        }
        if (strpos($useragent, 'Android')) {
                        return false;
        }
        if (strpos($useragent, 'iPhone') or strpos($useragent, 'iPad') or strpos($useragent, 'iPod')) {
                        return false;
        }
        if (strpos($useragent, 'Chrome')) {
                                    return false;
        }

        if (empty($version)) {
            return true;         }
        if (preg_match("/AppleWebKit\/([0-9.]+)/i", $useragent, $match)) {
            if (version_compare($match[1], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_chrome() {
        return self::check_chrome_version();
    }

    
    public static function check_chrome_version($version = null) {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'Chrome') === false) {
            return false;
        }
        if (empty($version)) {
            return true;         }
        if (preg_match("/Chrome\/(.*)[ ]+/i", $useragent, $match)) {
            if (version_compare($match[1], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_webkit_android() {
        return self::check_webkit_android_version();
    }

    
    public static function check_webkit_android_version($version = null) {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'Android') === false) {
            return false;
        }
        if (empty($version)) {
            return true;         }
        if (preg_match("/AppleWebKit\/([0-9]+)/i", $useragent, $match)) {
            if (version_compare($match[1], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_safari_ios() {
        return self::check_safari_ios_version();
    }

    
    public static function check_safari_ios_version($version = null) {
                $useragent = self::get_user_agent_string();
        if ($useragent === false) {
            return false;
        }
        if (strpos($useragent, 'AppleWebKit') === false or strpos($useragent, 'Safari') === false) {
            return false;
        }
        if (!strpos($useragent, 'iPhone') and !strpos($useragent, 'iPad') and !strpos($useragent, 'iPod')) {
            return false;
        }
        if (empty($version)) {
            return true;         }
        if (preg_match("/AppleWebKit\/([0-9]+)/i", $useragent, $match)) {
            if (version_compare($match[1], $version) >= 0) {
                return true;
            }
        }
        return false;
    }

    
    public static function is_msword() {
        $useragent = self::get_user_agent_string();
        if (!preg_match('/(\bWord\b|ms-office|MSOffice|Microsoft Office)/i', $useragent)) {
            return false;
        } else if (strpos($useragent, 'Outlook') !== false) {
            return false;
        } else if (strpos($useragent, 'Meridio') !== false) {
            return false;
        }
                return true;
    }

    
    public static function check_browser_operating_system($brand) {
        $useragent = self::get_user_agent_string();
        return ($useragent !== false && preg_match("/$brand/i", $useragent));
    }

    
    public static function get_browser_version_classes() {
        $classes = array();
        if (self::is_ie()) {
            $classes[] = 'ie';
            for ($i = 12; $i >= 6; $i--) {
                if (self::check_ie_version($i)) {
                    $classes[] = 'ie'.$i;
                    break;
                }
            }
        } else if (self::is_firefox() || self::is_gecko() || self::check_camino_version()) {
            $classes[] = 'gecko';
            if (preg_match('/rv\:([1-2])\.([0-9])/', self::get_user_agent_string(), $matches)) {
                $classes[] = "gecko{$matches[1]}{$matches[2]}";
            }
        } else if (self::is_webkit()) {
            $classes[] = 'safari';
            if (self::is_safari_ios()) {
                $classes[] = 'ios';
            } else if (self::is_webkit_android()) {
                $classes[] = 'android';
            }
        } else if (self::is_opera()) {
            $classes[] = 'opera';
        }
        return $classes;
    }

    
    public static function supports_svg() {
                $instance = self::instance();
        if ($instance->supportssvg === null) {
            if ($instance->useragent === false) {
                                $instance->supportssvg = false;
            } else if (self::check_ie_version('0') and !self::check_ie_version('9')) {
                                $instance->supportssvg = false;
            } else if (self::is_ie() and !self::check_ie_version('10') and self::check_ie_compatibility_view()) {
                                $instance->supportssvg = false;
            } else if (preg_match('#Android +[0-2]\.#', $instance->useragent)) {
                                $instance->supportssvg = false;
            } else if (self::is_opera()) {
                                $instance->supportssvg = false;
            } else {
                                $instance->supportssvg = true;
            }
        }
        return $instance->supportssvg;
    }

    
    public static function supports_json_contenttype() {
                if (!self::check_ie_version('0')) {
            return true;
        }

                                        if (self::check_ie_version(8) && !self::check_ie_compatibility_view()) {
            return true;
        }

                return false;
    }

    
    public static function is_web_crawler() {
        $instance = self::instance();
        return (bool) $instance->is_useragent_web_crawler();
    }
}
