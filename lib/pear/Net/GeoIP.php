<?php


require_once 'PEAR/Exception.php';


class Net_GeoIP
{
    
    const ERR_INVALID_IP =  218624992; 
    
    const ERR_DB_FORMAT = 866184008; 
    public static $COUNTRY_CODES = array(
      "", "AP", "EU", "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AN", "AO", "AQ",
      "AR", "AS", "AT", "AU", "AW", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH",
      "BI", "BJ", "BM", "BN", "BO", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA",
      "CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU",
      "CV", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG",
      "EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "FX", "GA", "GB",
      "GD", "GE", "GF", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT",
      "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IN",
      "IO", "IQ", "IR", "IS", "IT", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM",
      "KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS",
      "LT", "LU", "LV", "LY", "MA", "MC", "MD", "MG", "MH", "MK", "ML", "MM", "MN",
      "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA",
      "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA",
      "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY",
      "QA", "RE", "RO", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI",
      "SJ", "SK", "SL", "SM", "SN", "SO", "SR", "ST", "SV", "SY", "SZ", "TC", "TD",
      "TF", "TG", "TH", "TJ", "TK", "TM", "TN", "TO", "TL", "TR", "TT", "TV", "TW",
      "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN",
      "VU", "WF", "WS", "YE", "YT", "RS", "ZA", "ZM", "ME", "ZW", "A1", "A2", "O1",
      "AX", "GG", "IM", "JE", "BL", "MF"
        );

    public static $COUNTRY_CODES3 = array(
    "","AP","EU","AND","ARE","AFG","ATG","AIA","ALB","ARM","ANT","AGO","AQ","ARG",
    "ASM","AUT","AUS","ABW","AZE","BIH","BRB","BGD","BEL","BFA","BGR","BHR","BDI",
    "BEN","BMU","BRN","BOL","BRA","BHS","BTN","BV","BWA","BLR","BLZ","CAN","CC",
    "COD","CAF","COG","CHE","CIV","COK","CHL","CMR","CHN","COL","CRI","CUB","CPV",
    "CX","CYP","CZE","DEU","DJI","DNK","DMA","DOM","DZA","ECU","EST","EGY","ESH",
    "ERI","ESP","ETH","FIN","FJI","FLK","FSM","FRO","FRA","FX","GAB","GBR","GRD",
    "GEO","GUF","GHA","GIB","GRL","GMB","GIN","GLP","GNQ","GRC","GS","GTM","GUM",
    "GNB","GUY","HKG","HM","HND","HRV","HTI","HUN","IDN","IRL","ISR","IND","IO",
    "IRQ","IRN","ISL","ITA","JAM","JOR","JPN","KEN","KGZ","KHM","KIR","COM","KNA",
    "PRK","KOR","KWT","CYM","KAZ","LAO","LBN","LCA","LIE","LKA","LBR","LSO","LTU",
    "LUX","LVA","LBY","MAR","MCO","MDA","MDG","MHL","MKD","MLI","MMR","MNG","MAC",
    "MNP","MTQ","MRT","MSR","MLT","MUS","MDV","MWI","MEX","MYS","MOZ","NAM","NCL",
    "NER","NFK","NGA","NIC","NLD","NOR","NPL","NRU","NIU","NZL","OMN","PAN","PER",
    "PYF","PNG","PHL","PAK","POL","SPM","PCN","PRI","PSE","PRT","PLW","PRY","QAT",
    "REU","ROU","RUS","RWA","SAU","SLB","SYC","SDN","SWE","SGP","SHN","SVN","SJM",
    "SVK","SLE","SMR","SEN","SOM","SUR","STP","SLV","SYR","SWZ","TCA","TCD","TF",
    "TGO","THA","TJK","TKL","TLS","TKM","TUN","TON","TUR","TTO","TUV","TWN","TZA",
    "UKR","UGA","UM","USA","URY","UZB","VAT","VCT","VEN","VGB","VIR","VNM","VUT",
    "WLF","WSM","YEM","YT","SRB","ZAF","ZMB","MNE","ZWE","A1","A2","O1",
    "ALA","GGY","IMN","JEY","BLM","MAF"
        );

    public static $COUNTRY_NAMES = array(
        "", "Asia/Pacific Region", "Europe", "Andorra", "United Arab Emirates",
        "Afghanistan", "Antigua and Barbuda", "Anguilla", "Albania", "Armenia",
        "Netherlands Antilles", "Angola", "Antarctica", "Argentina", "American Samoa",
        "Austria", "Australia", "Aruba", "Azerbaijan", "Bosnia and Herzegovina",
        "Barbados", "Bangladesh", "Belgium", "Burkina Faso", "Bulgaria", "Bahrain",
        "Burundi", "Benin", "Bermuda", "Brunei Darussalam", "Bolivia", "Brazil",
        "Bahamas", "Bhutan", "Bouvet Island", "Botswana", "Belarus", "Belize",
        "Canada", "Cocos (Keeling) Islands", "Congo, The Democratic Republic of the",
        "Central African Republic", "Congo", "Switzerland", "Cote D'Ivoire", "Cook Islands",
        "Chile", "Cameroon", "China", "Colombia", "Costa Rica", "Cuba", "Cape Verde",
        "Christmas Island", "Cyprus", "Czech Republic", "Germany", "Djibouti",
        "Denmark", "Dominica", "Dominican Republic", "Algeria", "Ecuador", "Estonia",
        "Egypt", "Western Sahara", "Eritrea", "Spain", "Ethiopia", "Finland", "Fiji",
        "Falkland Islands (Malvinas)", "Micronesia, Federated States of", "Faroe Islands",
        "France", "France, Metropolitan", "Gabon", "United Kingdom",
        "Grenada", "Georgia", "French Guiana", "Ghana", "Gibraltar", "Greenland",
        "Gambia", "Guinea", "Guadeloupe", "Equatorial Guinea", "Greece", "South Georgia and the South Sandwich Islands",
        "Guatemala", "Guam", "Guinea-Bissau",
        "Guyana", "Hong Kong", "Heard Island and McDonald Islands", "Honduras",
        "Croatia", "Haiti", "Hungary", "Indonesia", "Ireland", "Israel", "India",
        "British Indian Ocean Territory", "Iraq", "Iran, Islamic Republic of",
        "Iceland", "Italy", "Jamaica", "Jordan", "Japan", "Kenya", "Kyrgyzstan",
        "Cambodia", "Kiribati", "Comoros", "Saint Kitts and Nevis", "Korea, Democratic People's Republic of",
        "Korea, Republic of", "Kuwait", "Cayman Islands",
        "Kazakstan", "Lao People's Democratic Republic", "Lebanon", "Saint Lucia",
        "Liechtenstein", "Sri Lanka", "Liberia", "Lesotho", "Lithuania", "Luxembourg",
        "Latvia", "Libyan Arab Jamahiriya", "Morocco", "Monaco", "Moldova, Republic of",
        "Madagascar", "Marshall Islands", "Macedonia",
        "Mali", "Myanmar", "Mongolia", "Macau", "Northern Mariana Islands",
        "Martinique", "Mauritania", "Montserrat", "Malta", "Mauritius", "Maldives",
        "Malawi", "Mexico", "Malaysia", "Mozambique", "Namibia", "New Caledonia",
        "Niger", "Norfolk Island", "Nigeria", "Nicaragua", "Netherlands", "Norway",
        "Nepal", "Nauru", "Niue", "New Zealand", "Oman", "Panama", "Peru", "French Polynesia",
        "Papua New Guinea", "Philippines", "Pakistan", "Poland", "Saint Pierre and Miquelon",
        "Pitcairn Islands", "Puerto Rico", "Palestinian Territory",
        "Portugal", "Palau", "Paraguay", "Qatar", "Reunion", "Romania",
        "Russian Federation", "Rwanda", "Saudi Arabia", "Solomon Islands",
        "Seychelles", "Sudan", "Sweden", "Singapore", "Saint Helena", "Slovenia",
        "Svalbard and Jan Mayen", "Slovakia", "Sierra Leone", "San Marino", "Senegal",
        "Somalia", "Suriname", "Sao Tome and Principe", "El Salvador", "Syrian Arab Republic",
        "Swaziland", "Turks and Caicos Islands", "Chad", "French Southern Territories",
        "Togo", "Thailand", "Tajikistan", "Tokelau", "Turkmenistan",
        "Tunisia", "Tonga", "Timor-Leste", "Turkey", "Trinidad and Tobago", "Tuvalu",
        "Taiwan", "Tanzania, United Republic of", "Ukraine",
        "Uganda", "United States Minor Outlying Islands", "United States", "Uruguay",
        "Uzbekistan", "Holy See (Vatican City State)", "Saint Vincent and the Grenadines",
        "Venezuela", "Virgin Islands, British", "Virgin Islands, U.S.",
        "Vietnam", "Vanuatu", "Wallis and Futuna", "Samoa", "Yemen", "Mayotte",
        "Serbia", "South Africa", "Zambia", "Montenegro", "Zimbabwe",
        "Anonymous Proxy","Satellite Provider","Other",
        "Aland Islands","Guernsey","Isle of Man","Jersey","Saint Barthelemy","Saint Martin"
        );

        const STANDARD = 0;
    const MEMORY_CACHE = 1;
    const SHARED_MEMORY = 2;

        const COUNTRY_BEGIN = 16776960;
    const STATE_BEGIN_REV0 = 16700000;
    const STATE_BEGIN_REV1 = 16000000;

    const STRUCTURE_INFO_MAX_SIZE = 20;
    const DATABASE_INFO_MAX_SIZE = 100;
    const COUNTRY_EDITION = 106;
    const REGION_EDITION_REV0 = 112;
    const REGION_EDITION_REV1 = 3;
    const CITY_EDITION_REV0 = 111;
    const CITY_EDITION_REV1 = 2;
    const ORG_EDITION = 110;
    const SEGMENT_RECORD_LENGTH = 3;
    const STANDARD_RECORD_LENGTH = 3;
    const ORG_RECORD_LENGTH = 4;
    const MAX_RECORD_LENGTH = 4;
    const MAX_ORG_RECORD_LENGTH = 300;
    const FULL_RECORD_LENGTH = 50;

    const US_OFFSET = 1;
    const CANADA_OFFSET = 677;
    const WORLD_OFFSET = 1353;
    const FIPS_RANGE = 360;

        const SHM_KEY = 0x4f415401;

    
    private $flags = 0;

    
    private $filehandle;

    
    private $memoryBuffer;

    
    private $databaseType;

    
    private $databaseSegments;

    
    private $recordLength;

    
    private $shmid;

    
    private static $instances = array();

    
    public function __construct($filename = null, $flags = null)
    {
        if ($filename !== null) {
            $this->open($filename, $flags);
        }
                        self::$instances[$filename] = $this;
    }

    

    
    public static function getInstance($filename = null, $flags = null)
    {
        if (!isset(self::$instances[$filename])) {
            self::$instances[$filename] = new Net_GeoIP($filename, $flags);
        }
        return self::$instances[$filename];
    }

    
    public function open($filename, $flags = null)
    {
        if ($flags !== null) {
            $this->flags = $flags;
        }
        if ($this->flags & self::SHARED_MEMORY) {
            $this->shmid = @shmop_open(self::SHM_KEY, "a", 0, 0);
            if ($this->shmid === false) {
                $this->loadSharedMemory($filename);
                $this->shmid = @shmop_open(self::SHM_KEY, "a", 0, 0);
                if ($this->shmid === false) {                     throw new PEAR_Exception("Unable to open shared memory at key: " . dechex(self::SHM_KEY));
                }
            }
        } else {
            $this->filehandle = fopen($filename, "rb");
            if (!$this->filehandle) {
                throw new PEAR_Exception("Unable to open file: $filename");
            }
            if ($this->flags & self::MEMORY_CACHE) {
                $s_array = fstat($this->filehandle);
                $this->memoryBuffer = fread($this->filehandle, $s_array['size']);
            }
        }
        $this->setupSegments();
    }

    
    protected function loadSharedMemory($filename)
    {
        $fp = fopen($filename, "rb");
        if (!$fp) {
            throw new PEAR_Exception("Unable to open file: $filename");
        }
        $s_array = fstat($fp);
        $size = $s_array['size'];

        if ($shmid = @shmop_open(self::SHM_KEY, "w", 0, 0)) {
            shmop_delete($shmid);
            shmop_close($shmid);
        }

        if ($shmid = @shmop_open(self::SHM_KEY, "c", 0644, $size)) {
            $offset = 0;
            while ($offset < $size) {
                $buf = fread($fp, 524288);
                shmop_write($shmid, $buf, $offset);
                $offset += 524288;
            }
            shmop_close($shmid);
        }

        fclose($fp);
    }

    
    protected function setupSegments()
    {

        $this->databaseType = self::COUNTRY_EDITION;
        $this->recordLength = self::STANDARD_RECORD_LENGTH;

        if ($this->flags & self::SHARED_MEMORY) {

            $offset = shmop_size($this->shmid) - 3;
            for ($i = 0; $i < self::STRUCTURE_INFO_MAX_SIZE; $i++) {
                $delim = shmop_read($this->shmid, $offset, 3);
                $offset += 3;
                if ($delim == (chr(255).chr(255).chr(255))) {
                    $this->databaseType = ord(shmop_read($this->shmid, $offset, 1));
                    $offset++;
                    if ($this->databaseType === self::REGION_EDITION_REV0) {
                        $this->databaseSegments = self::STATE_BEGIN_REV0;
                    } elseif ($this->databaseType === self::REGION_EDITION_REV1) {
                        $this->databaseSegments = self::STATE_BEGIN_REV1;
                    } elseif (($this->databaseType === self::CITY_EDITION_REV0)
                                || ($this->databaseType === self::CITY_EDITION_REV1)
                                || ($this->databaseType === self::ORG_EDITION)) {
                        $this->databaseSegments = 0;
                        $buf = shmop_read($this->shmid, $offset, self::SEGMENT_RECORD_LENGTH);
                        for ($j = 0; $j < self::SEGMENT_RECORD_LENGTH; $j++) {
                            $this->databaseSegments += (ord($buf[$j]) << ($j * 8));
                        }
                        if ($this->databaseType === self::ORG_EDITION) {
                            $this->recordLength = self::ORG_RECORD_LENGTH;
                        }
                    }
                    break;
                } else {
                    $offset -= 4;
                }
            }
            if ($this->databaseType == self::COUNTRY_EDITION) {
                $this->databaseSegments = self::COUNTRY_BEGIN;
            }

        } else {

            $filepos = ftell($this->filehandle);
            fseek($this->filehandle, -3, SEEK_END);
            for ($i = 0; $i < self::STRUCTURE_INFO_MAX_SIZE; $i++) {
                $delim = fread($this->filehandle, 3);
                if ($delim == (chr(255).chr(255).chr(255))) {
                    $this->databaseType = ord(fread($this->filehandle, 1));
                    if ($this->databaseType === self::REGION_EDITION_REV0) {
                        $this->databaseSegments = self::STATE_BEGIN_REV0;
                    } elseif ($this->databaseType === self::REGION_EDITION_REV1) {
                        $this->databaseSegments = self::STATE_BEGIN_REV1;
                    } elseif ($this->databaseType === self::CITY_EDITION_REV0
                                || $this->databaseType === self::CITY_EDITION_REV1
                                || $this->databaseType === self::ORG_EDITION) {
                        $this->databaseSegments = 0;
                        $buf = fread($this->filehandle, self::SEGMENT_RECORD_LENGTH);
                        for ($j = 0; $j < self::SEGMENT_RECORD_LENGTH; $j++) {
                            $this->databaseSegments += (ord($buf[$j]) << ($j * 8));
                        }
                        if ($this->databaseType === self::ORG_EDITION) {
                            $this->recordLength = self::ORG_RECORD_LENGTH;
                        }
                    }
                    break;
                } else {
                    fseek($this->filehandle, -4, SEEK_CUR);
                }
            }
            if ($this->databaseType === self::COUNTRY_EDITION) {
                $this->databaseSegments = self::COUNTRY_BEGIN;
            }
            fseek($this->filehandle, $filepos, SEEK_SET);

        }
    }

    
    public function close()
    {
        if ($this->flags & self::SHARED_MEMORY) {
            return shmop_close($this->shmid);
        } else {
                                    return fclose($this->filehandle);
        }
    }

    
    protected function lookupCountryId($addr)
    {
        $ipnum = ip2long($addr);
        if ($ipnum === false) {
            throw new PEAR_Exception("Invalid IP address: " . var_export($addr, true), self::ERR_INVALID_IP);
        }
        if ($this->databaseType !== self::COUNTRY_EDITION) {
            throw new PEAR_Exception("Invalid database type; lookupCountry*() methods expect Country database.");
        }
        return $this->seekCountry($ipnum) - self::COUNTRY_BEGIN;
    }

    
    public function lookupCountryCode($addr)
    {
        return self::$COUNTRY_CODES[$this->lookupCountryId($addr)];
    }

    
    public function lookupCountryName($addr)
    {
        return self::$COUNTRY_NAMES[$this->lookupCountryId($addr)];
    }

    
    protected function seekCountry($ipnum)
    {
        $offset = 0;
        for ($depth = 31; $depth >= 0; --$depth) {
            if ($this->flags & self::MEMORY_CACHE) {
                  $buf = substr($this->memoryBuffer, 2 * $this->recordLength * $offset, 2 * $this->recordLength);
            } elseif ($this->flags & self::SHARED_MEMORY) {
                $buf = shmop_read($this->shmid, 2 * $this->recordLength * $offset, 2 * $this->recordLength);
            } else {
                if (fseek($this->filehandle, 2 * $this->recordLength * $offset, SEEK_SET) !== 0) {
                    throw new PEAR_Exception("fseek failed");
                }
                $buf = fread($this->filehandle, 2 * $this->recordLength);
            }
            $x = array(0,0);
            for ($i = 0; $i < 2; ++$i) {
                for ($j = 0; $j < $this->recordLength; ++$j) {
                    $x[$i] += ord($buf[$this->recordLength * $i + $j]) << ($j * 8);
                }
            }
            if ($ipnum & (1 << $depth)) {
                if ($x[1] >= $this->databaseSegments) {
                    return $x[1];
                }
                $offset = $x[1];
            } else {
                if ($x[0] >= $this->databaseSegments) {
                    return $x[0];
                }
                $offset = $x[0];
            }
        }
        throw new PEAR_Exception("Error traversing database - perhaps it is corrupt?");
    }

    
    public function lookupOrg($addr)
    {
        $ipnum = ip2long($addr);
        if ($ipnum === false) {
            throw new PEAR_Exception("Invalid IP address: " . var_export($addr, true), self::ERR_INVALID_IP);
        }
        if ($this->databaseType !== self::ORG_EDITION) {
            throw new PEAR_Exception("Invalid database type; lookupOrg() method expects Org/ISP database.", self::ERR_DB_FORMAT);
        }
        return $this->getOrg($ipnum);
    }

    
    public function lookupRegion($addr)
    {
        $ipnum = ip2long($addr);
        if ($ipnum === false) {
            throw new PEAR_Exception("Invalid IP address: " . var_export($addr, true), self::ERR_INVALID_IP);
        }
        if ($this->databaseType !== self::REGION_EDITION_REV0 && $this->databaseType !== self::REGION_EDITION_REV1) {
            throw new PEAR_Exception("Invalid database type; lookupRegion() method expects Region database.", self::ERR_DB_FORMAT);
        }
        return $this->getRegion($ipnum);
    }

    
    public function lookupLocation($addr)
    {
        include_once 'Net/GeoIP/Location.php';
        $ipnum = ip2long($addr);
        if ($ipnum === false) {
            throw new PEAR_Exception("Invalid IP address: " . var_export($addr, true), self::ERR_INVALID_IP);
        }
        if ($this->databaseType !== self::CITY_EDITION_REV0 && $this->databaseType !== self::CITY_EDITION_REV1) {
            throw new PEAR_Exception("Invalid database type; lookupLocation() method expects City database.");
        }
        return $this->getRecord($ipnum);
    }

    
    protected function getOrg($ipnum)
    {
        $seek_org = $this->seekCountry($ipnum);
        if ($seek_org == $this->databaseSegments) {
            return null;
        }
        $record_pointer = $seek_org + (2 * $this->recordLength - 1) * $this->databaseSegments;
        if ($this->flags & self::SHARED_MEMORY) {
            $org_buf = shmop_read($this->shmid, $record_pointer, self::MAX_ORG_RECORD_LENGTH);
        } else {
            fseek($this->filehandle, $record_pointer, SEEK_SET);
            $org_buf = fread($this->filehandle, self::MAX_ORG_RECORD_LENGTH);
        }
        $org_buf = substr($org_buf, 0, strpos($org_buf, 0));
        return $org_buf;
    }

    
    protected function getRegion($ipnum)
    {
        if ($this->databaseType == self::REGION_EDITION_REV0) {
            $seek_region = $this->seekCountry($ipnum) - self::STATE_BEGIN_REV0;
            if ($seek_region >= 1000) {
                $country_code = "US";
                $region = chr(($seek_region - 1000)/26 + 65) . chr(($seek_region - 1000)%26 + 65);
            } else {
                $country_code = self::$COUNTRY_CODES[$seek_region];
                $region = "";
            }
            return array($country_code, $region);
        } elseif ($this->databaseType == self::REGION_EDITION_REV1) {
            $seek_region = $this->seekCountry($ipnum) - self::STATE_BEGIN_REV1;
                        if ($seek_region < self::US_OFFSET) {
                $country_code = "";
                $region = "";
            } elseif ($seek_region < self::CANADA_OFFSET) {
                $country_code = "US";
                $region = chr(($seek_region - self::US_OFFSET)/26 + 65) . chr(($seek_region - self::US_OFFSET)%26 + 65);
            } elseif ($seek_region < self::WORLD_OFFSET) {
                $country_code = "CA";
                $region = chr(($seek_region - self::CANADA_OFFSET)/26 + 65) . chr(($seek_region - self::CANADA_OFFSET)%26 + 65);
            } else {
                $country_code = self::$COUNTRY_CODES[($seek_region - self::WORLD_OFFSET) / self::FIPS_RANGE];
                $region = "";
            }
            return array ($country_code,$region);
        }
    }

    
    protected function getRecord($ipnum)
    {
        $seek_country = $this->seekCountry($ipnum);
        if ($seek_country == $this->databaseSegments) {
            return null;
        }

        $record_pointer = $seek_country + (2 * $this->recordLength - 1) * $this->databaseSegments;

        if ($this->flags & self::SHARED_MEMORY) {
            $record_buf = shmop_read($this->shmid, $record_pointer, self::FULL_RECORD_LENGTH);
        } else {
            fseek($this->filehandle, $record_pointer, SEEK_SET);
            $record_buf = fread($this->filehandle, self::FULL_RECORD_LENGTH);
        }

        $record = new Net_GeoIP_Location();

        $record_buf_pos = 0;
        $char = ord(substr($record_buf, $record_buf_pos, 1));

        $record->countryCode  = self::$COUNTRY_CODES[$char];
        $record->countryCode3 = self::$COUNTRY_CODES3[$char];
        $record->countryName  = self::$COUNTRY_NAMES[$char];
        $record_buf_pos++;
        $str_length = 0;

                $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        while ($char != 0) {
            $str_length++;
            $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        }
        if ($str_length > 0) {
            $record->region = substr($record_buf, $record_buf_pos, $str_length);
        }
        $record_buf_pos += $str_length + 1;
        $str_length = 0;

                $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        while ($char != 0) {
            $str_length++;
            $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        }
        if ($str_length > 0) {
            $record->city = substr($record_buf, $record_buf_pos, $str_length);
        }
        $record_buf_pos += $str_length + 1;
        $str_length = 0;

                $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        while ($char != 0) {
            $str_length++;
            $char = ord(substr($record_buf, $record_buf_pos+$str_length, 1));
        }
        if ($str_length > 0) {
            $record->postalCode = substr($record_buf, $record_buf_pos, $str_length);
        }
        $record_buf_pos += $str_length + 1;
        $str_length = 0;
        $latitude   = 0;
        $longitude  = 0;
        for ($j = 0;$j < 3; ++$j) {
            $char = ord(substr($record_buf, $record_buf_pos++, 1));
            $latitude += ($char << ($j * 8));
        }
        $record->latitude = ($latitude/10000) - 180;

        for ($j = 0;$j < 3; ++$j) {
            $char = ord(substr($record_buf, $record_buf_pos++, 1));
            $longitude += ($char << ($j * 8));
        }
        $record->longitude = ($longitude/10000) - 180;

        if ($this->databaseType === self::CITY_EDITION_REV1) {
            $dmaarea_combo = 0;
            if ($record->countryCode == "US") {
                for ($j = 0;$j < 3;++$j) {
                    $char = ord(substr($record_buf, $record_buf_pos++, 1));
                    $dmaarea_combo += ($char << ($j * 8));
                }
                $record->dmaCode = floor($dmaarea_combo/1000);
                $record->areaCode = $dmaarea_combo%1000;
            }
        }

        return $record;
    }

}

