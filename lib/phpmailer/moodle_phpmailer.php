<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/phpmailer/class.phpmailer.php');
require_once($CFG->libdir.'/phpmailer/class.smtp.php');


class moodle_phpmailer extends PHPMailer {

    
    public function __construct(){
        global $CFG;
        $this->Version   = 'Moodle '.$CFG->version;                 $this->CharSet   = 'UTF-8';
                $this->SMTPAutoTLS = false;

        if (!empty($CFG->smtpauthtype)) {
            $this->AuthType = $CFG->smtpauthtype;
        }

                if (isset($CFG->mailnewline) and $CFG->mailnewline == 'CRLF') {
            $this->LE = "\r\n";
        } else {
            $this->LE = "\n";
        }
    }

    
    public function addCustomHeader($custom_header, $value = null) {
        if ($value === null and preg_match('/message-id:(.*)/i', $custom_header, $matches)) {
            $this->MessageID = $matches[1];
            return true;
        } else if ($value !== null and strcasecmp($custom_header, 'message-id') === 0) {
            $this->MessageID = $value;
            return true;
        } else {
            return parent::addCustomHeader($custom_header, $value);
        }
    }

    
    public function encodeHeader($str, $position = 'text') {
        $encoded = core_text::encode_mimeheader($str, $this->CharSet);
        if ($encoded !== false) {
            if ($position === 'phrase') {
                                $chunks = preg_split("/\\n/", $encoded);
                $chunks = array_map(function($chunk) {
                    return addcslashes($chunk, "\0..\37\177\\\"");
                }, $chunks);
                return '"' . join($this->LE, $chunks) . '"';
            }
            return str_replace("\n", $this->LE, $encoded);
        }

        return parent::encodeHeader($str, $position);
    }

    
    public static function rfcDate() {
        $tz = date('Z');
        $tzs = ($tz < 0) ? '-' : '+';
        $tz = abs($tz);
        $tz = (($tz - ($tz%3600) )/3600)*100 + ($tz%3600)/60;         $result = sprintf("%s %s%04d", date('D, j M Y H:i:s'), $tzs, $tz);

        return $result;
    }

    
    public function encodeQP($string, $line_max = 76) {
                                $filters = stream_get_filters();
        if (!in_array('convert.*', $filters)) {             return parent::encodeQP($string, $line_max);         }
        $fp = fopen('php://temp/', 'r+');
        $string = preg_replace('/\r\n?/', $this->LE, $string);         $params = array('line-length' => $line_max, 'line-break-chars' => $this->LE);
        $s = stream_filter_append($fp, 'convert.quoted-printable-encode', STREAM_FILTER_READ, $params);
        fputs($fp, $string);
        rewind($fp);
        $out = stream_get_contents($fp);
        stream_filter_remove($s);
        $out = preg_replace('/^\./m', '=2E', $out);         fclose($fp);
        return $this->fixEOL($out);
    }

    
    public function postSend() {
                if (PHPUNIT_TEST) {
            if (!phpunit_util::is_redirecting_phpmailer()) {
                debugging('Unit tests must not send real emails! Use $this->redirectEmails()');
                return true;
            }
            $mail = new stdClass();
            $mail->header = $this->MIMEHeader;
            $mail->body = $this->MIMEBody;
            $mail->subject = $this->Subject;
            $mail->from = $this->From;
            $mail->to = $this->to[0][0];
            phpunit_util::phpmailer_sent($mail);
            return true;
        } else {
            return parent::postSend();
        }
    }
}
