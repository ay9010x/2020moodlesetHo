<?php

class Horde_Mime_Part implements ArrayAccess, Countable, Serializable
{
    
    const VERSION = 1;

    
    const EOL = "\n";

    
    const RFC_EOL = "\r\n";

    
    const DEFAULT_ENCODING = 'binary';

    
    const ENCODE_7BIT = 1;
    const ENCODE_8BIT = 2;
    const ENCODE_BINARY = 4;

    
    const UNKNOWN = 'x-unknown';

    
    const NESTING_LIMIT = 100;

    
    static public $defaultCharset = 'us-ascii';

    
    static public $encodingTypes = array(
        '7bit', '8bit', 'base64', 'binary', 'quoted-printable',
                'uuencode', 'x-uuencode', 'x-uue'
    );

    
    static public $memoryLimit = 2097152;

    
    static public $mimeTypes = array(
        'text', 'multipart', 'message', 'application', 'audio', 'image',
        'video', 'model'
    );

    
    protected $_type = 'application';

    
    protected $_subtype = 'octet-stream';

    
    protected $_contents;

    
    protected $_transferEncoding = self::DEFAULT_ENCODING;

    
    protected $_language = array();

    
    protected $_description = '';

    
    protected $_disposition = '';

    
    protected $_dispParams = array();

    
    protected $_contentTypeParams;

    
    protected $_parts = array();

    
    protected $_mimeid = null;

    
    protected $_eol = self::EOL;

    
    protected $_temp = array();

    
    protected $_metadata = array();

    
    protected $_boundary = null;

    
    protected $_bytes;

    
    protected $_contentid = null;

    
    protected $_duration;

    
    protected $_reindex = false;

    
    protected $_basepart = false;

    
    protected $_hdrCharset = null;

    
    protected $_serializedVars = array(
        '_type',
        '_subtype',
        '_transferEncoding',
        '_language',
        '_description',
        '_disposition',
        '_dispParams',
        '_contentTypeParams',
        '_parts',
        '_mimeid',
        '_eol',
        '_metadata',
        '_boundary',
        '_bytes',
        '_contentid',
        '_duration',
        '_reindex',
        '_basepart',
        '_hdrCharset'
    );

    
    public function __construct()
    {
        $this->_init();
    }

    
    protected function _init()
    {
        $this->_contentTypeParams = new Horde_Support_CaseInsensitiveArray();
    }

    
    public function __clone()
    {
        reset($this->_parts);
        while (list($k, $v) = each($this->_parts)) {
            $this->_parts[$k] = clone $v;
        }

        $this->_contentTypeParams = clone $this->_contentTypeParams;
    }

    
    public function setDisposition($disposition = null)
    {
        if (empty($disposition)) {
            $this->_disposition = '';
        } else {
            $disposition = Horde_String::lower($disposition);
            if (in_array($disposition, array('inline', 'attachment'))) {
                $this->_disposition = $disposition;
            }
        }
    }

    
    public function getDisposition()
    {
        return $this->_disposition;
    }

    
    public function setDispositionParameter($label, $data)
    {
        $this->_dispParams[$label] = $data;

        switch ($label) {
        case 'size':
                        $this->_bytes = intval($data);
            break;
        }
    }

    
    public function getDispositionParameter($label)
    {
        return (isset($this->_dispParams[$label]))
            ? $this->_dispParams[$label]
            : null;
    }

    
    public function getAllDispositionParameters()
    {
        return $this->_dispParams;
    }

    
    public function setName($name)
    {
        $this->setDispositionParameter('filename', $name);
        $this->setContentTypeParameter('name', $name);
    }

    
    public function getName($default = false)
    {
        if (!($name = $this->getDispositionParameter('filename')) &&
            !($name = $this->getContentTypeParameter('name')) &&
            $default) {
            $name = preg_replace('|\W|', '_', $this->getDescription(false));
        }

        return $name;
    }

    
    public function setContents($contents, $options = array())
    {
        $this->clearContents();
        if (empty($options['encoding'])) {
            $options['encoding'] = $this->_transferEncoding;
        }

        $fp = (empty($options['usestream']) || !is_resource($contents))
            ? $this->_writeStream($contents)
            : $contents;

        $this->setTransferEncoding($options['encoding']);
        $this->_contents = $this->_transferDecode($fp, $options['encoding']);
    }

    
    public function appendContents($contents, $options = array())
    {
        if (empty($this->_contents)) {
            $this->setContents($contents, $options);
        } else {
            $fp = (empty($options['usestream']) || !is_resource($contents))
                ? $this->_writeStream($contents)
                : $contents;

            $this->_writeStream((empty($options['encoding']) || ($options['encoding'] == $this->_transferEncoding)) ? $fp : $this->_transferDecode($fp, $options['encoding']), array('fp' => $this->_contents));
            unset($this->_temp['sendTransferEncoding']);
        }
    }

    
    public function clearContents()
    {
        if (!empty($this->_contents)) {
            fclose($this->_contents);
            $this->_contents = null;
            unset($this->_temp['sendTransferEncoding']);
        }
    }

    
    public function getContents($options = array())
    {
        return empty($options['canonical'])
            ? (empty($options['stream']) ? $this->_readStream($this->_contents) : $this->_contents)
            : $this->replaceEOL($this->_contents, self::RFC_EOL, !empty($options['stream']));
    }

    
    protected function _transferDecode($fp, $encoding)
    {
        
        fseek($fp, 0, SEEK_END);
        if (ftell($fp)) {
            switch ($encoding) {
            case 'base64':
                try {
                    return $this->_writeStream($fp, array(
                        'error' => true,
                        'filter' => array(
                            'convert.base64-decode' => array()
                        )
                    ));
                } catch (ErrorException $e) {}

                rewind($fp);
                return $this->_writeStream(base64_decode(stream_get_contents($fp)));

            case 'quoted-printable':
                try {
                    return $this->_writeStream($fp, array(
                        'error' => true,
                        'filter' => array(
                            'convert.quoted-printable-decode' => array()
                        )
                    ));
                } catch (ErrorException $e) {}

                                rewind($fp);
                return $this->_writeStream(quoted_printable_decode(stream_get_contents($fp)));

            case 'uuencode':
            case 'x-uuencode':
            case 'x-uue':
                
                $res = Horde_Mime::uudecode($this->_readStream($fp));
                return $this->_writeStream($res[0]['data']);
            }
        }

        return $fp;
    }

    
    protected function _transferEncode($fp, $encoding)
    {
        $this->_temp['transferEncodeClose'] = true;

        switch ($encoding) {
        case 'base64':
            
            return $this->_writeStream($fp, array(
                'filter' => array(
                    'convert.base64-encode' => array(
                        'line-break-chars' => $this->getEOL(),
                        'line-length' => 76
                    )
                )
            ));

        case 'quoted-printable':
            $stream = new Horde_Stream_Existing(array(
                'stream' => $fp
            ));

            
            return $this->_writeStream($fp, array(
                'filter' => array(
                    'convert.quoted-printable-encode' => array_filter(array(
                        'line-break-chars' => $stream->getEOL(),
                        'line-length' => 76
                    ))
                )
            ));

        default:
            $this->_temp['transferEncodeClose'] = false;
            return $fp;
        }
    }

    
    public function setType($type)
    {
        
        if (($this->_transferEncoding == self::UNKNOWN) ||
            (strpos($type, '/') === false)) {
            return;
        }

        list($this->_type, $this->_subtype) = explode('/', Horde_String::lower($type));

        if (in_array($this->_type, self::$mimeTypes)) {
            
            if ($this->_type == 'multipart') {
                if (!$this->getContentTypeParameter('boundary')) {
                    $this->setContentTypeParameter('boundary', $this->_generateBoundary());
                }
            } else {
                $this->clearContentTypeParameter('boundary');
            }
        } else {
            $this->_type = self::UNKNOWN;
            $this->clearContentTypeParameter('boundary');
        }
    }

     
    public function getType($charset = false)
    {
        if (empty($this->_type) || empty($this->_subtype)) {
            return false;
        }

        $ptype = $this->getPrimaryType();
        $type = $ptype . '/' . $this->getSubType();
        if ($charset &&
            ($ptype == 'text') &&
            ($charset = $this->getCharset())) {
            $type .= '; charset=' . $charset;
        }

        return $type;
    }

    
    public function getDefaultType()
    {
        switch ($this->getPrimaryType()) {
        case 'text':
            
            return 'text/plain';

        case 'multipart':
            
            return 'multipart/mixed';

        default:
            
            return 'application/octet-stream';
        }
    }

    
    public function getPrimaryType()
    {
        return $this->_type;
    }

    
    public function getSubType()
    {
        return $this->_subtype;
    }

    
    public function setCharset($charset)
    {
        $this->setContentTypeParameter('charset', $charset);
    }

    
    public function getCharset()
    {
        $charset = $this->getContentTypeParameter('charset');
        if (is_null($charset) && $this->getPrimaryType() != 'text') {
            return null;
        }

        $charset = Horde_String::lower($charset);

        if ($this->getPrimaryType() == 'text') {
            $d_charset = Horde_String::lower(self::$defaultCharset);
            if ($d_charset != 'us-ascii' &&
                (!$charset || $charset == 'us-ascii')) {
                return $d_charset;
            }
        }

        return $charset;
    }

    
    public function setHeaderCharset($charset)
    {
        $this->_hdrCharset = $charset;
    }

    
    public function getHeaderCharset()
    {
        return is_null($this->_hdrCharset)
            ? $this->getCharset()
            : $this->_hdrCharset;
    }

    
    public function setLanguage($lang)
    {
        $this->_language = is_array($lang)
            ? $lang
            : array($lang);
    }

    
    public function getLanguage()
    {
        return $this->_language;
    }

    
    public function setDuration($duration)
    {
        if (is_null($duration)) {
            unset($this->_duration);
        } else {
            $this->_duration = intval($duration);
        }
    }

    
    public function getDuration()
    {
        return isset($this->_duration)
            ? $this->_duration
            : null;
    }

    
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    
    public function getDescription($default = false)
    {
        $desc = $this->_description;

        if ($default && empty($desc)) {
            $desc = $this->getName();
        }

        return $desc;
    }

    
    public function setTransferEncoding($encoding, $options = array())
    {
        if (empty($encoding) ||
            (empty($options['send']) && !empty($this->_contents))) {
            return;
        }

        $encoding = Horde_String::lower($encoding);

        if (in_array($encoding, self::$encodingTypes)) {
            if (empty($options['send'])) {
                $this->_transferEncoding = $encoding;
            } else {
                $this->_temp['sendEncoding'] = $encoding;
            }
        } elseif (empty($options['send'])) {
            
            $this->setType('application/octet-stream');
            $this->_transferEncoding = self::UNKNOWN;
        }
    }

    
    public function addPart($mime_part)
    {
        $this->_parts[] = $mime_part;
        $this->_reindex = true;
    }

    
    public function getParts()
    {
        return $this->_parts;
    }

    
    public function getPart($id)
    {
        return $this->_partAction($id, 'get');
    }

    
    public function removePart($id)
    {
        return $this->_partAction($id, 'remove');
    }

    
    public function alterPart($id, $mime_part)
    {
        return $this->_partAction($id, 'alter', $mime_part);
    }

    
    protected function _partAction($id, $action, $mime_part = null)
    {
        $this_id = $this->getMimeId();

        
        if (($action === 'get') && (strcmp($id, $this_id) === 0)) {
            return $this;
        }

        if ($this->_reindex) {
            $this->buildMimeIds(is_null($this_id) ? '1' : $this_id);
        }

        foreach ($this->_parts as $key => $val) {
            $partid = $val->getMimeId();

            if (($match = (strcmp($id, $partid) === 0)) ||
                (strpos($id, $partid . '.') === 0) ||
                (strrchr($partid, '.') === '.0')) {
                switch ($action) {
                case 'alter':
                    if ($match) {
                        $mime_part->setMimeId($partid);
                        $this->_parts[$key] = $mime_part;
                        return true;
                    }
                    return $val->alterPart($id, $mime_part);

                case 'get':
                    return $match
                        ? $val
                        : $val->getPart($id);

                case 'remove':
                    if ($match) {
                        unset($this->_parts[$key]);
                        $this->_reindex = true;
                        return true;
                    }
                    return $val->removePart($id);
                }
            }
        }

        return ($action === 'get') ? null : false;
    }

    
    public function setContentTypeParameter($label, $data)
    {
        $this->_contentTypeParams[$label] = $data;
    }

    
    public function clearContentTypeParameter($label)
    {
        unset($this->_contentTypeParams[$label]);
    }

    
    public function getContentTypeParameter($label)
    {
        return isset($this->_contentTypeParams[$label])
            ? $this->_contentTypeParams[$label]
            : null;
    }

    
    public function getAllContentTypeParameters()
    {
        return $this->_contentTypeParams->getArrayCopy();
    }

    
    public function setEOL($eol)
    {
        $this->_eol = $eol;
    }

    
    public function getEOL()
    {
        return $this->_eol;
    }

    
    public function addMimeHeaders($options = array())
    {
        $headers = empty($options['headers'])
            ? new Horde_Mime_Headers()
            : $options['headers'];

        
        $ptype = $this->getPrimaryType();
        $c_params = $this->getAllContentTypeParameters();
        if ($ptype != 'text') {
            unset($c_params['charset']);
        }
        $headers->replaceHeader('Content-Type', $this->getType(), array('params' => $c_params));

        
        if ($langs = $this->getLanguage()) {
            $headers->replaceHeader('Content-Language', implode(',', $langs));
        }

        
        if (($descrip = $this->getDescription())) {
            $headers->replaceHeader('Content-Description', $descrip);
        }

        
        if (($duration = $this->getDuration()) !== null) {
            $headers->replaceHeader('Content-Duration', $duration);
        }

        
        if ($this->_basepart) {
            $headers->replaceHeader('MIME-Version', '1.0');
        }

        
        if ($ptype == 'message') {
            return $headers;
        }

        
        $disposition = $this->getDisposition();
        $disp_params = $this->getAllDispositionParameters();
        $name = $this->getName();
        if ($disposition || !empty($name) || !empty($disp_params)) {
            if (!$disposition) {
                $disposition = 'attachment';
            }
            if ($name) {
                $disp_params['filename'] = $name;
            }
            $headers->replaceHeader('Content-Disposition', $disposition, array('params' => $disp_params));
        } else {
            $headers->removeHeader('Content-Disposition');
        }

        
        $encoding = $this->_getTransferEncoding(empty($options['encode']) ? null : $options['encode']);
        if ($encoding == '7bit') {
            $headers->removeHeader('Content-Transfer-Encoding');
        } else {
            $headers->replaceHeader('Content-Transfer-Encoding', $encoding);
        }

        
        if (!is_null($this->_contentid)) {
            $headers->replaceHeader('Content-ID', '<' . $this->_contentid . '>');
        }

        return $headers;
    }

    
    public function toString($options = array())
    {
        $eol = $this->getEOL();
        $isbase = true;
        $oldbaseptr = null;
        $parts = $parts_close = array();

        if (isset($options['id'])) {
            $id = $options['id'];
            if (!($part = $this->getPart($id))) {
                return $part;
            }
            unset($options['id']);
            $contents = $part->toString($options);

            $prev_id = Horde_Mime::mimeIdArithmetic($id, 'up', array('norfc822' => true));
            $prev_part = ($prev_id == $this->getMimeId())
                ? $this
                : $this->getPart($prev_id);
            if (!$prev_part) {
                return $contents;
            }

            $boundary = trim($this->getContentTypeParameter('boundary'), '"');
            $parts = array(
                $eol . '--' . $boundary . $eol,
                $contents
            );

            if (!$this->getPart(Horde_Mime::mimeIdArithmetic($id, 'next'))) {
                $parts[] = $eol . '--' . $boundary . '--' . $eol;
            }
        } else {
            if ($isbase = empty($options['_notbase'])) {
                $headers = !empty($options['headers'])
                    ? $options['headers']
                    : false;

                if (empty($options['encode'])) {
                    $options['encode'] = null;
                }
                if (empty($options['defserver'])) {
                    $options['defserver'] = null;
                }
                $options['headers'] = true;
                $options['_notbase'] = true;
            } else {
                $headers = true;
                $oldbaseptr = &$options['_baseptr'];
            }

            $this->_temp['toString'] = '';
            $options['_baseptr'] = &$this->_temp['toString'];

            
            $ptype = $this->getPrimaryType();
            if ($ptype == 'message') {
                $parts[] = $this->_contents;
            } else {
                if (!empty($this->_contents)) {
                    $encoding = $this->_getTransferEncoding($options['encode']);
                    switch ($encoding) {
                    case '8bit':
                        if (empty($options['_baseptr'])) {
                            $options['_baseptr'] = '8bit';
                        }
                        break;

                    case 'binary':
                        $options['_baseptr'] = 'binary';
                        break;
                    }

                    $parts[] = $this->_transferEncode($this->_contents, $encoding);

                    
                    if ($this->_temp['transferEncodeClose']) {
                        $parts_close[] = end($parts);
                    }
                }

                
                if ($ptype == 'multipart') {
                    if (empty($this->_contents)) {
                        $parts[] = 'This message is in MIME format.' . $eol;
                    }

                    $boundary = trim($this->getContentTypeParameter('boundary'), '"');

                    reset($this->_parts);
                    while (list(,$part) = each($this->_parts)) {
                        $parts[] = $eol . '--' . $boundary . $eol;
                        $tmp = $part->toString($options);
                        if ($part->getEOL() != $eol) {
                            $tmp = $this->replaceEOL($tmp, $eol, !empty($options['stream']));
                        }
                        if (!empty($options['stream'])) {
                            $parts_close[] = $tmp;
                        }
                        $parts[] = $tmp;
                    }
                    $parts[] = $eol . '--' . $boundary . '--' . $eol;
                }
            }

            if (is_string($headers)) {
                array_unshift($parts, $headers);
            } elseif ($headers) {
                $hdr_ob = $this->addMimeHeaders(array('encode' => $options['encode'], 'headers' => ($headers === true) ? null : $headers));
                $hdr_ob->setEOL($eol);
                if (!empty($this->_temp['toString'])) {
                    $hdr_ob->replaceHeader('Content-Transfer-Encoding', $this->_temp['toString']);
                }
                array_unshift($parts, $hdr_ob->toString(array('charset' => $this->getHeaderCharset(), 'defserver' => $options['defserver'])));
            }
        }

        $newfp = $this->_writeStream($parts);

        array_map('fclose', $parts_close);

        if (!is_null($oldbaseptr)) {
            switch ($this->_temp['toString']) {
            case '8bit':
                if (empty($oldbaseptr)) {
                    $oldbaseptr = '8bit';
                }
                break;

            case 'binary':
                $oldbaseptr = 'binary';
                break;
            }
        }

        if ($isbase && !empty($options['canonical'])) {
            return $this->replaceEOL($newfp, self::RFC_EOL, !empty($options['stream']));
        }

        return empty($options['stream'])
            ? $this->_readStream($newfp)
            : $newfp;
    }

    
    protected function _getTransferEncoding($encode = self::ENCODE_7BIT)
    {
        if (!empty($this->_temp['sendEncoding'])) {
            return $this->_temp['sendEncoding'];
        } elseif (!empty($this->_temp['sendTransferEncoding'][$encode])) {
            return $this->_temp['sendTransferEncoding'][$encode];
        }

        if (empty($this->_contents)) {
            $encoding = '7bit';
        } else {
            $nobinary = false;

            switch ($this->getPrimaryType()) {
            case 'message':
            case 'multipart':
                
                $encoding = '7bit';
                $nobinary = true;
                break;

            case 'text':
                $eol = $this->getEOL();

                if ($this->_scanStream($this->_contents, '8bit')) {
                    $encoding = ($encode & self::ENCODE_8BIT || $encode & self::ENCODE_BINARY)
                        ? '8bit'
                        : 'quoted-printable';
                } elseif ($this->_scanStream($this->_contents, 'preg', "/(?:" . $eol . "|^)[^" . $eol . "]{999,}(?:" . $eol . "|$)/")) {
                    
                    $encoding = 'quoted-printable';
                } else {
                    $encoding = '7bit';
                }
                break;

            default:
                
                if ($this->_transferEncoding != self::DEFAULT_ENCODING) {
                    $encoding = $this->_transferEncoding;
                } else {
                    $encoding = ($encode & self::ENCODE_8BIT || $encode & self::ENCODE_BINARY)
                        ? '8bit'
                        : 'base64';
                }
                break;
            }

            
            if (!$nobinary &&
                in_array($encoding, array('8bit', '7bit')) &&
                $this->_scanStream($this->_contents, 'binary')) {
                $encoding = ($encode & self::ENCODE_BINARY)
                    ? 'binary'
                    : 'base64';
            }
        }

        $this->_temp['sendTransferEncoding'][$encode] = $encoding;

        return $encoding;
    }

    
    public function replaceEOL($text, $eol = null, $stream = false)
    {
        if (is_null($eol)) {
            $eol = $this->getEOL();
        }

        stream_filter_register('horde_eol', 'Horde_Stream_Filter_Eol');
        $fp = $this->_writeStream($text, array(
            'filter' => array(
                'horde_eol' => array('eol' => $eol)
            )
        ));

        return $stream ? $fp : $this->_readStream($fp, true);
    }

    
    public function getBytes($approx = false)
    {
        if ($this->getPrimaryType() == 'multipart') {
            if (isset($this->_bytes)) {
                return $this->_bytes;
            }

            $bytes = 0;
            reset($this->_parts);
            while (list(,$part) = each($this->_parts)) {
                $bytes += $part->getBytes($approx);
            }
            return $bytes;
        }

        if ($this->_contents) {
            fseek($this->_contents, 0, SEEK_END);
            $bytes = ftell($this->_contents);
        } else {
            $bytes = $this->_bytes;
        }

        
        if ($approx && ($this->_transferEncoding == 'base64')) {
            $bytes *= 0.75;
        }

        return intval($bytes);
    }

    
    public function setBytes($bytes)
    {
        $this->setDispositionParameter('size', $bytes);
    }

    
    public function getSize($approx = false)
    {
        if (!($bytes = $this->getBytes($approx))) {
            return 0;
        }

        $localeinfo = Horde_Nls::getLocaleInfo();

                return str_replace(
            array('X', 'Y'),
            array($localeinfo['decimal_point'], $localeinfo['thousands_sep']),
            number_format(ceil($bytes / 1024), 0, 'X', 'Y')
        );
    }

    
    public function setContentId($cid = null)
    {
        if (is_null($this->_contentid)) {
            $this->_contentid = is_null($cid)
                ? (strval(new Horde_Support_Randomid()) . '@' . $_SERVER['SERVER_NAME'])
                : trim($cid, '<>');
        }

        return $this->_contentid;
    }

    
    public function getContentId()
    {
        return $this->_contentid;
    }

    
    public function setMimeId($mimeid)
    {
        $this->_mimeid = $mimeid;
    }

    
    public function getMimeId()
    {
        return $this->_mimeid;
    }

    
    public function buildMimeIds($id = null, $rfc822 = false)
    {
        if (is_null($id)) {
            $rfc822 = true;
            $id = '';
        }

        if ($rfc822) {
            if (empty($this->_parts) &&
                ($this->getPrimaryType() != 'multipart')) {
                $this->setMimeId($id . '1');
            } else {
                if (empty($id) && ($this->getType() == 'message/rfc822')) {
                    $this->setMimeId('1');
                    $id = '1.';
                } else {
                    $this->setMimeId($id . '0');
                }
                $i = 1;
                foreach (array_keys($this->_parts) as $val) {
                    $this->_parts[$val]->buildMimeIds($id . ($i++));
                }
            }
        } else {
            $this->setMimeId($id);
            $id = $id
                ? ((substr($id, -2) === '.0') ? substr($id, 0, -1) : ($id . '.'))
                : '';

            if ($this->getType() == 'message/rfc822') {
                if (count($this->_parts)) {
                    reset($this->_parts);
                    $this->_parts[key($this->_parts)]->buildMimeIds($id, true);
                }
            } elseif (!empty($this->_parts)) {
                $i = 1;
                foreach (array_keys($this->_parts) as $val) {
                    $this->_parts[$val]->buildMimeIds($id . ($i++));
                }
            }
        }

        $this->_reindex = false;
    }

    
    protected function _generateBoundary()
    {
        if (is_null($this->_boundary)) {
            $this->_boundary = '=_' . strval(new Horde_Support_Randomid());
        }
        return $this->_boundary;
    }

    
    public function contentTypeMap($sort = true)
    {
        $map = array($this->getMimeId() => $this->getType());
        foreach ($this->_parts as $val) {
            $map += $val->contentTypeMap(false);
        }

        if ($sort) {
            uksort($map, 'strnatcmp');
        }

        return $map;
    }

    
    public function isBasePart($base)
    {
        $this->_basepart = $base;
    }

    
    public function setMetadata($key, $data = null)
    {
        if (is_null($data)) {
            unset($this->_metadata[$key]);
        } else {
            $this->_metadata[$key] = $data;
        }
    }

    
    public function getMetadata($key)
    {
        return isset($this->_metadata[$key])
            ? $this->_metadata[$key]
            : null;
    }

    
    public function send($email, $headers, Horde_Mail_Transport $mailer,
                         array $opts = array())
    {
        $old_basepart = $this->_basepart;
        $this->_basepart = true;

        
        $canonical = true;
        $encode = self::ENCODE_7BIT;

        if (isset($opts['encode'])) {
            
            $encode |= $opts['encode'];
        } elseif ($mailer instanceof Horde_Mail_Transport_Smtp) {
            try {
                $smtp_ext = $mailer->getSMTPObject()->getServiceExtensions();
                if (isset($smtp_ext['8BITMIME'])) {
                    $encode |= self::ENCODE_8BIT;
                }
            } catch (Horde_Mail_Exception $e) {}
            $canonical = false;
        } elseif ($mailer instanceof Horde_Mail_Transport_Smtphorde) {
            try {
                if ($mailer->getSMTPObject()->data_8bit) {
                    $encode |= self::ENCODE_8BIT;
                }
            } catch (Horde_Mail_Exception $e) {}
            $canonical = false;
        }

        $msg = $this->toString(array(
            'canonical' => $canonical,
            'encode' => $encode,
            'headers' => false,
            'stream' => true
        ));

        
        if (!$headers->getValue('MIME-Version')) {
            $headers = $this->addMimeHeaders(array('encode' => $encode, 'headers' => $headers));
        }

        if (!empty($this->_temp['toString'])) {
            $headers->replaceHeader('Content-Transfer-Encoding', $this->_temp['toString']);
            switch ($this->_temp['toString']) {
            case '8bit':
                if ($mailer instanceof Horde_Mail_Transport_Smtp) {
                    $mailer->addServiceExtensionParameter('BODY', '8BITMIME');
                } elseif ($mailer instanceof Horde_Mail_Transport_Smtphorde) {
                    $mailer->send8bit = true;
                }
                break;
            }
        }

        $this->_basepart = $old_basepart;
        $rfc822 = new Horde_Mail_Rfc822();
        try {
            $mailer->send($rfc822->parseAddressList($email)->writeAddress(array(
                'encode' => $this->getHeaderCharset(),
                'idn' => true
            )), $headers->toArray(array(
                'canonical' => $canonical,
                'charset' => $this->getHeaderCharset()
            )), $msg);
        } catch (Horde_Mail_Exception $e) {
            throw new Horde_Mime_Exception($e);
        }
    }

    
    public function findBody($subtype = null)
    {
        $initial_id = $this->getMimeId();
        $this->buildMimeIds();

        foreach ($this->contentTypeMap() as $mime_id => $mime_type) {
            if ((strpos($mime_type, 'text/') === 0) &&
                (!$initial_id || (intval($mime_id) == 1)) &&
                (is_null($subtype) || (substr($mime_type, 5) == $subtype)) &&
                ($part = $this->getPart($mime_id)) &&
                ($part->getDisposition() != 'attachment')) {
                return $mime_id;
            }
        }

        return null;
    }

    
    protected function _writeStream($data, $options = array())
    {
        if (empty($options['fp'])) {
            $fp = fopen('php://temp/maxmemory:' . self::$memoryLimit, 'r+');
        } else {
            $fp = $options['fp'];
            fseek($fp, 0, SEEK_END);
        }

        if (!is_array($data)) {
            $data = array($data);
        }

        if (!empty($options['filter'])) {
            $append_filter = array();
            foreach ($options['filter'] as $key => $val) {
                $append_filter[] = stream_filter_append($fp, $key, STREAM_FILTER_WRITE, $val);
            }
        }

        if (!empty($options['error'])) {
            set_error_handler(array($this, '_writeStreamErrorHandler'));
            $error = null;
        }

        try {
            reset($data);
            while (list(,$d) = each($data)) {
                if (is_resource($d)) {
                    rewind($d);
                    while (!feof($d)) {
                        fwrite($fp, fread($d, 8192));
                    }
                } else {
                    $len = strlen($d);
                    $i = 0;
                    while ($i < $len) {
                        fwrite($fp, substr($d, $i, 8192));
                        $i += 8192;
                    }
                }
            }
        } catch (ErrorException $e) {
            $error = $e;
        }

        if (!empty($options['filter'])) {
            foreach ($append_filter as $val) {
                stream_filter_remove($val);
            }
        }

        if (!empty($options['error'])) {
            restore_error_handler();
            if ($error) {
                throw $error;
            }
        }

        return $fp;
    }

    
    protected function _writeStreamErrorHandler($errno, $errstr)
    {
        throw new ErrorException($errstr, $errno);
    }

    
    protected function _readStream($fp, $close = false)
    {
        $out = '';

        if (!is_resource($fp)) {
            return $out;
        }

        rewind($fp);
        while (!feof($fp)) {
            $out .= fread($fp, 8192);
        }

        if ($close) {
            fclose($fp);
        }

        return $out;
    }

    
    protected function _scanStream($fp, $type, $data = null)
    {
        rewind($fp);
        while (is_resource($fp) && !feof($fp)) {
            $line = fread($fp, 8192);
            switch ($type) {
            case '8bit':
                if (Horde_Mime::is8bit($line)) {
                    return true;
                }
                break;

            case 'binary':
                if (strpos($line, "\0") !== false) {
                    return true;
                }
                break;

            case 'preg':
                if (preg_match($data, $line)) {
                    return true;
                }
                break;
            }
        }

        return false;
    }

    
    static public function parseMessage($text, array $opts = array())
    {
        
        $part = new Horde_Mime_Part();
        $rawtext = $part->replaceEOL($text, self::EOL);

        
        $hdr_pos = self::_findHeader($rawtext, self::EOL);

        unset($opts['ctype']);
        $ob = self::_getStructure(substr($rawtext, 0, $hdr_pos), substr($rawtext, $hdr_pos + 2), $opts);
        $ob->buildMimeIds();
        return $ob;
    }

    
    static protected function _getStructure($header, $body,
                                            array $opts = array())
    {
        $opts = array_merge(array(
            'ctype' => 'application/octet-stream',
            'forcemime' => false,
            'level' => 0,
            'no_body' => false
        ), $opts);

        
        $hdrs = Horde_Mime_Headers::parseHeaders($header);

        $ob = new Horde_Mime_Part();

        
        if (!$opts['forcemime'] && !$hdrs->getValue('mime-version')) {
            $ob->setType('text/plain');

            if ($len = strlen($body)) {
                if ($opts['no_body']) {
                    $ob->setBytes($len);
                } else {
                    $ob->setContents($body);
                }
            }

            return $ob;
        }

        
        if ($tmp = $hdrs->getValue('content-type', Horde_Mime_Headers::VALUE_BASE)) {
            $ob->setType($tmp);

            $ctype_params = $hdrs->getValue('content-type', Horde_Mime_Headers::VALUE_PARAMS);
            foreach ($ctype_params as $key => $val) {
                $ob->setContentTypeParameter($key, $val);
            }
        } else {
            $ob->setType($opts['ctype']);
        }

        
        if ($tmp = $hdrs->getValue('content-transfer-encoding')) {
            $ob->setTransferEncoding($tmp);
        }

        
        if ($tmp = $hdrs->getValue('content-description')) {
            $ob->setDescription($tmp);
        }

        
        if ($tmp = $hdrs->getValue('content-disposition', Horde_Mime_Headers::VALUE_BASE)) {
            $ob->setDisposition($tmp);
            foreach ($hdrs->getValue('content-disposition', Horde_Mime_Headers::VALUE_PARAMS) as $key => $val) {
                $ob->setDispositionParameter($key, $val);
            }
        }

        
        if ($tmp = $hdrs->getValue('content-duration')) {
            $ob->setDuration($tmp);
        }

        
        if ($tmp = $hdrs->getValue('content-id')) {
            $ob->setContentId($tmp);
        }

        if (($len = strlen($body)) && ($ob->getPrimaryType() != 'multipart')) {
            if ($opts['no_body']) {
                $ob->setBytes($len);
            } else {
                $ob->setContents($body);
            }
        }

        if (++$opts['level'] >= self::NESTING_LIMIT) {
            return $ob;
        }

        
        switch ($ob->getPrimaryType()) {
        case 'message':
            if ($ob->getSubType() == 'rfc822') {
                $ob->addPart(self::parseMessage($body, array('forcemime' => true)));
            }
            break;

        case 'multipart':
            $boundary = $ob->getContentTypeParameter('boundary');
            if (!is_null($boundary)) {
                foreach (self::_findBoundary($body, 0, $boundary) as $val) {
                    if (!isset($val['length'])) {
                        break;
                    }
                    $subpart = substr($body, $val['start'], $val['length']);
                    $hdr_pos = self::_findHeader($subpart, self::EOL);
                    $ob->addPart(self::_getStructure(substr($subpart, 0, $hdr_pos), substr($subpart, $hdr_pos + 2), array(
                        'ctype' => ($ob->getSubType() == 'digest') ? 'message/rfc822' : 'text/plain',
                        'forcemime' => true,
                        'level' => $opts['level'],
                        'no_body' => $opts['no_body']
                    )));
                }
            }
            break;
        }

        return $ob;
    }

    
    static public function getRawPartText($text, $type, $id)
    {
        
        $part = new Horde_Mime_Part();
        $rawtext = $part->replaceEOL($text, self::RFC_EOL);

        
        $hdr_pos = self::_findHeader($rawtext, self::RFC_EOL);
        $curr_pos = $hdr_pos + 3;

        if ($id == 0) {
            switch ($type) {
            case 'body':
                return substr($rawtext, $curr_pos + 1);

            case 'header':
                return trim(substr($rawtext, 0, $hdr_pos));
            }
        }

        $hdr_ob = Horde_Mime_Headers::parseHeaders(trim(substr($rawtext, 0, $hdr_pos)));

        
        if ($hdr_ob->getValue('Content-Type', Horde_Mime_Headers::VALUE_BASE) == 'message/rfc822') {
            return self::getRawPartText(substr($rawtext, $curr_pos + 1), $type, $id);
        }

        $base_pos = strpos($id, '.');
        $orig_id = $id;

        if ($base_pos !== false) {
            $base_pos = substr($id, 0, $base_pos);
            $id = substr($id, $base_pos);
        } else {
            $base_pos = $id;
            $id = 0;
        }

        $params = $hdr_ob->getValue('Content-Type', Horde_Mime_Headers::VALUE_PARAMS);
        if (!isset($params['boundary'])) {
            if ($orig_id == '1') {
                return substr($rawtext, $curr_pos + 1);
            }

            throw new Horde_Mime_Exception('Could not find MIME part.');
        }

        $b_find = self::_findBoundary($rawtext, $curr_pos, $params['boundary'], $base_pos);

        if (!isset($b_find[$base_pos])) {
            throw new Horde_Mime_Exception('Could not find MIME part.');
        }

        return self::getRawPartText(substr($rawtext, $b_find[$base_pos]['start'], $b_find[$base_pos]['length'] - 1), $type, $id);
    }

    
    static protected function _findHeader($text, $eol)
    {
        $hdr_pos = strpos($text, $eol . $eol);
        return ($hdr_pos === false)
            ? strlen($text)
            : $hdr_pos;
    }

    
    static protected function _findBoundary($text, $pos, $boundary,
                                            $end = null)
    {
        $i = 0;
        $out = array();

        $search = "--" . $boundary;
        $search_len = strlen($search);

        while (($pos = strpos($text, $search, $pos)) !== false) {
            
            if (($pos != 0) && ($text[$pos - 1] != "\n")) {
                continue;
            }

            if (isset($out[$i])) {
                $out[$i]['length'] = $pos - $out[$i]['start'] - 1;
            }

            if (!is_null($end) && ($end == $i)) {
                break;
            }

            $pos += $search_len;
            if (isset($text[$pos])) {
                switch ($text[$pos]) {
                case "\r":
                    $pos += 2;
                    $out[++$i] = array('start' => $pos);
                    break;

                case "\n":
                    $out[++$i] = array('start' => ++$pos);
                    break;

                case '-':
                    return $out;
                }
            }
        }

        return $out;
    }

    

    public function offsetExists($offset)
    {
        return ($this->getPart($offset) !== null);
    }

    public function offsetGet($offset)
    {
        return $this->getPart($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->alterPart($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->removePart($offset);
    }

    

    
    public function count()
    {
        return count($this->_parts);
    }

    

    
    public function serialize()
    {
        $data = array(
                        self::VERSION
        );

        foreach ($this->_serializedVars as $val) {
            switch ($val) {
            case '_contentTypeParams':
                $data[] = $this->$val->getArrayCopy();
                break;

            default:
                $data[] = $this->$val;
                break;
            }
        }

        if (!empty($this->_contents)) {
            $data[] = $this->_readStream($this->_contents);
        }

        return serialize($data);
    }

    
    public function unserialize($data)
    {
        $data = @unserialize($data);
        if (!is_array($data) ||
            !isset($data[0]) ||
            (array_shift($data) != self::VERSION)) {
            throw new Exception('Cache version change');
        }

        $this->_init();

        foreach ($this->_serializedVars as $key => $val) {
            switch ($val) {
            case '_contentTypeParams':
                $this->$val = new Horde_Support_CaseInsensitiveArray($data[$key]);
                break;

            default:
                $this->$val = $data[$key];
                break;
            }
        }

                if (isset($data[++$key])) {
            $this->setContents($data[$key]);
        }
    }

}
