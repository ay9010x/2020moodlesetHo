<?php



defined('MOODLE_INTERNAL') || die();


abstract class portfolio_format {

    
    public static function mimetypes() {
        throw new coding_exception('mimetypes() method needs to be overridden in each subclass of portfolio_format');
    }

    
    public static function get_file_directory() {
        throw new coding_exception('get_file_directory() method needs to be overridden in each subclass of portfolio_format');
    }

    
    public static function file_output($file, $options=null) {
        throw new coding_exception('file_output() method needs to be overridden in each subclass of portfolio_format');
    }

    
    public static function make_tag($file, $path, $attributes) {
        $srcattr = 'href';
        $tag     = 'a';
        $content = $file->get_filename();
        if (in_array($file->get_mimetype(), portfolio_format_image::mimetypes())) {
            $srcattr = 'src';
            $tag     = 'img';
            $content = '';
        }

        $attributes[$srcattr] = $path;         $dom = new DomDocument();
        $elem = null;
        if ($content) {
            $elem = $dom->createElement($tag, $content);
        } else {
            $elem = $dom->createElement($tag);
        }

        foreach ($attributes as $key => $value) {
            $elem->setAttribute($key, $value);
        }
        $dom->appendChild($elem);
        return $dom->saveXML($elem);
    }

    
    public static function conflicts($format) {
        return false;
    }
}


class portfolio_format_file extends portfolio_format {

    
    public static function mimetypes() {
        return array();
    }

    
    public static function get_file_directory() {
        return false;
    }

    
    public static function file_output($file, $options=null) {
        throw new portfolio_exception('fileoutputnotsupported', 'portfolio');
    }
}


class portfolio_format_image extends portfolio_format_file {
    
    public static function mimetypes() {
        return file_get_typegroup('type', 'image');
    }

    
    public static function conflicts($format) {
        return ($format == PORTFOLIO_FORMAT_RICHHTML
            || $format == PORTFOLIO_FORMAT_PLAINHTML);
    }
}


class portfolio_format_plainhtml extends portfolio_format_file {

    
    public static function mimetypes() {
        return array('text/html');
    }

    
    public static function conflicts($format) {
        return ($format == PORTFOLIO_FORMAT_RICHHTML
            || $format == PORTFOLIO_FORMAT_FILE);
    }
}


class portfolio_format_video extends portfolio_format_file {

     
    public static function mimetypes() {
        return file_get_typegroup('type', 'video');
    }
}


class portfolio_format_text extends portfolio_format_file {

    
    public static function mimetypes() {
        return array('text/plain');
    }

    
    public static function conflicts($format ) {
        return ($format == PORTFOLIO_FORMAT_PLAINHTML
            || $format == PORTFOLIO_FORMAT_RICHHTML);
    }
}


abstract class portfolio_format_rich extends portfolio_format {

    
    public static function mimetypes() {
        return array();
    }

}


class portfolio_format_richhtml extends portfolio_format_rich {

    
    public static function get_file_directory() {
        return 'site_files/';
    }

    
    public static function file_output($file, $options=null) {
        $path = self::get_file_directory() . $file->get_filename();
        $attributes = array();
        if (!empty($options['attributes']) && is_array($options['attributes'])) {
            $attributes = $options['attributes'];
        }
        return self::make_tag($file, $path, $attributes);
    }

    
    public static function conflicts($format) {         return ($format == PORTFOLIO_FORMAT_PLAINHTML || $format == PORTFOLIO_FORMAT_FILE);
    }

}


class portfolio_format_leap2a extends portfolio_format_rich {

    
    public static function get_file_directory() {
        return 'files/';
    }

    
    public static function file_id_prefix() {
        return 'storedfile';
    }

    
    public static function file_output($file, $options=null) {
        $id = '';
        if (!is_array($options)) {
            $options = array();
        }
        if (!array_key_exists('entry', $options)) {
            $options['entry'] = true;
        }
        if (!empty($options['entry'])) {
            $path = 'portfolio:' . self::file_id_prefix() . $file->get_id();
        } else {
            $path = self::get_file_directory() . $file->get_filename();
        }
        $attributes = array();
        if (!empty($options['attributes']) && is_array($options['attributes'])) {
            $attributes = $options['attributes'];
        }
        $attributes['rel']    = 'enclosure';
        return self::make_tag($file, $path, $attributes);
    }

    
    public static function leap2a_writer(stdclass $user=null) {
        global $CFG;
        if (empty($user)) {
            global $USER;
            $user = $USER;
        }
        require_once($CFG->libdir . '/portfolio/formats/leap2a/lib.php');
        return new portfolio_format_leap2a_writer($user);
    }

    
    public static function manifest_name() {
        return 'leap2a.xml';
    }
}




class portfolio_format_pdf extends portfolio_format_file {

    
    public static function mimetypes() {
        return array('application/pdf');
    }
}


class portfolio_format_document extends portfolio_format_file {

    
    public static function mimetypes() {
        return file_get_typegroup('type', 'document');
    }
}


class portfolio_format_spreadsheet extends portfolio_format_file {

    
    public static function mimetypes() {
        return file_get_typegroup('type', 'spreadsheet');
    }
}


class portfolio_format_presentation extends portfolio_format_file {

    
    public static function mimetypes() {
        return file_get_typegroup('type', 'presentation');
    }
}
