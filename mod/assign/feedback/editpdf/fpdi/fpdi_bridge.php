<?php




if (!class_exists('TCPDF', false)) {
    
    class fpdi_bridge extends FPDF
    {
            }

} else {

    
    class fpdi_bridge extends pdf
    {
        
        protected $_tpls = array();

        
        public $tplPrefix = "/TPL";

        
        protected $_currentObjId;

        
        protected function _getxobjectdict()
        {
            $out = parent::_getxobjectdict();
            foreach ($this->_tpls as $tplIdx => $tpl) {
                $out .= sprintf('%s%d %d 0 R', $this->tplPrefix, $tplIdx, $tpl['n']);
            }

            return $out;
        }

        
        protected function _prepareValue(&$value)
        {
            switch ($value[0]) {
                case pdf_parser::TYPE_STRING:
                    if ($this->encrypted) {
                        $value[1] = $this->_unescape($value[1]);
                        $value[1] = $this->_encrypt_data($this->_currentObjId, $value[1]);
                        $value[1] = TCPDF_STATIC::_escape($value[1]);
                    }
                    break;

                case pdf_parser::TYPE_STREAM:
                    if ($this->encrypted) {
                        $value[2][1] = $this->_encrypt_data($this->_currentObjId, $value[2][1]);
                        $value[1][1]['/Length'] = array(
                            pdf_parser::TYPE_NUMERIC,
                            strlen($value[2][1])
                        );
                    }
                    break;

                case pdf_parser::TYPE_HEX:
                    if ($this->encrypted) {
                        $value[1] = $this->hex2str($value[1]);
                        $value[1] = $this->_encrypt_data($this->_currentObjId, $value[1]);

                                                $value[1] = $this->str2hex($value[1]);
                    }
                    break;
            }
        }

        
        protected function _unescape($s)
        {
            $out = '';
            for ($count = 0, $n = strlen($s); $count < $n; $count++) {
                if ($s[$count] != '\\' || $count == $n-1) {
                    $out .= $s[$count];
                } else {
                    switch ($s[++$count]) {
                        case ')':
                        case '(':
                        case '\\':
                            $out .= $s[$count];
                            break;
                        case 'f':
                            $out .= chr(0x0C);
                            break;
                        case 'b':
                            $out .= chr(0x08);
                            break;
                        case 't':
                            $out .= chr(0x09);
                            break;
                        case 'r':
                            $out .= chr(0x0D);
                            break;
                        case 'n':
                            $out .= chr(0x0A);
                            break;
                        case "\r":
                            if ($count != $n-1 && $s[$count+1] == "\n")
                                $count++;
                            break;
                        case "\n":
                            break;
                        default:
                                                        if (ord($s[$count]) >= ord('0') &&
                                ord($s[$count]) <= ord('9')) {
                                $oct = ''. $s[$count];

                                if (ord($s[$count+1]) >= ord('0') &&
                                    ord($s[$count+1]) <= ord('9')) {
                                    $oct .= $s[++$count];

                                    if (ord($s[$count+1]) >= ord('0') &&
                                        ord($s[$count+1]) <= ord('9')) {
                                        $oct .= $s[++$count];
                                    }
                                }

                                $out .= chr(octdec($oct));
                            } else {
                                $out .= $s[$count];
                            }
                    }
                }
            }
            return $out;
        }

        
        public function hex2str($data)
        {
            $data = preg_replace('/[^0-9A-Fa-f]/', '', rtrim($data, '>'));
            if ((strlen($data) % 2) == 1) {
                $data .= '0';
            }

            return pack('H*', $data);
        }

        
        public function str2hex($str)
        {
            return current(unpack('H*', $str));
        }
    }
}