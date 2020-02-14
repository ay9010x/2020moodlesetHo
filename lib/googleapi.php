<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/oauthlib.php');


class google_docs {
    
    const REALM            = 'https://docs.google.com/feeds/ https://spreadsheets.google.com/feeds/ https://docs.googleusercontent.com/';
    
    const DOCUMENTFEED_URL = 'https://docs.google.com/feeds/default/private/full';
    
    const UPLOAD_URL       = 'https://docs.google.com/feeds/upload/create-session/default/private/full?convert=false';

    
    private $googleoauth = null;

    
    public function __construct(google_oauth $googleoauth) {
        $this->googleoauth = $googleoauth;
        $this->reset_curl_state();
    }

    
    private function reset_curl_state() {
        $this->googleoauth->reset_state();
        $this->googleoauth->setHeader('GData-Version: 3.0');
    }

    
    public function get_file_list($search = '') {
        global $CFG, $OUTPUT;
        $url = self::DOCUMENTFEED_URL;

        if ($search) {
            $url.='?q='.urlencode($search);
        }

        $files = array();
        $content = $this->googleoauth->get($url);
        try {
            if (strpos($content, '<?xml') !== 0) {
                throw new moodle_exception('invalidxmlresponse');
            }
            $xml = new SimpleXMLElement($content);
        } catch (Exception $e) {
                                    return $files;
        }
        date_default_timezone_set(core_date::get_user_timezone());
        foreach ($xml->entry as $gdoc) {
            $docid  = (string) $gdoc->children('http://schemas.google.com/g/2005')->resourceId;
            list($type, $docid) = explode(':', $docid);

            $title  = '';
            $source = '';
                                                switch($type){
                case 'document':
                    $title = $gdoc->title.'.rtf';
                    $source = 'https://docs.google.com/feeds/download/documents/Export?id='.$docid.'&exportFormat=rtf';
                    break;
                case 'presentation':
                    $title = $gdoc->title.'.ppt';
                    $source = 'https://docs.google.com/feeds/download/presentations/Export?id='.$docid.'&exportFormat=ppt';
                    break;
                case 'spreadsheet':
                    $title = $gdoc->title.'.xls';
                    $source = 'https://spreadsheets.google.com/feeds/download/spreadsheets/Export?key='.$docid.'&exportFormat=xls';
                    break;
                case 'pdf':
                case 'file':
                    $title  = (string)$gdoc->title;
                                        if (isset($gdoc->content)) {
                        $source = (string)$gdoc->content[0]->attributes()->src;
                    }
                    break;
            }

            $files[] =  array( 'title' => $title,
                'url' => "{$gdoc->link[0]->attributes()->href}",
                'source' => $source,
                'date'   => strtotime($gdoc->updated),
                'thumbnail' => (string) $OUTPUT->pix_url(file_extension_icon($title, 32))
            );
        }
        core_date::set_default_server_timezone();

        return $files;
    }

    
    public function send_file($file) {
                $this->googleoauth->setHeader("Content-Length: 0");
        $this->googleoauth->setHeader("X-Upload-Content-Length: ". $file->get_filesize());
        $this->googleoauth->setHeader("X-Upload-Content-Type: ". $file->get_mimetype());
        $this->googleoauth->setHeader("Slug: ". $file->get_filename());
        $this->googleoauth->post(self::UPLOAD_URL);

        if ($this->googleoauth->info['http_code'] !== 200) {
            throw new moodle_exception('Cantpostupload');
        }

                $location = $this->googleoauth->response['Location'];
        if (empty($location)) {
            throw new moodle_exception('Nouploadlocation');
        }

                $this->reset_curl_state();
        $this->googleoauth->setHeader("Content-Length: ". $file->get_filesize());
        $this->googleoauth->setHeader("Content-Type: ". $file->get_mimetype());

                $tmproot = make_temp_directory('googledocsuploads');
        $tmpfilepath = $tmproot.'/'.$file->get_contenthash();
        $file->copy_content_to($tmpfilepath);

                $this->googleoauth->put($location, array('file'=>$tmpfilepath));

                unlink($tmpfilepath);

        if ($this->googleoauth->info['http_code'] === 201) {
                        $this->reset_curl_state();
            return true;
        } else {
            return false;
        }
    }

    
    public function download_file($url, $path, $timeout = 0) {
        $result = $this->googleoauth->download_one($url, null, array('filepath' => $path, 'timeout' => $timeout));
        if ($result === true) {
            $info = $this->googleoauth->get_info();
            if (isset($info['http_code']) && $info['http_code'] == 200) {
                return array('path'=>$path, 'url'=>$url);
            } else {
                throw new moodle_exception('cannotdownload', 'repository');
            }
        } else {
            throw new moodle_exception('errorwhiledownload', 'repository', '', $result);
        }
    }
}


class google_picasa {
    
    const REALM             = 'http://picasaweb.google.com/data/';
    
    const UPLOAD_LOCATION   = 'https://picasaweb.google.com/data/feed/api/user/default/albumid/default';
    
    const ALBUM_PHOTO_LIST  = 'https://picasaweb.google.com/data/feed/api/user/default/albumid/';
    
    const PHOTO_SEARCH_URL  = 'https://picasaweb.google.com/data/feed/api/user/default?kind=photo&q=';
    
    const LIST_ALBUMS_URL   = 'https://picasaweb.google.com/data/feed/api/user/default';
    
    const MANAGE_URL        = 'http://picasaweb.google.com/';

    
    private $googleoauth = null;
    
    private $lastalbumname = null;

    
    public function __construct(google_oauth $googleoauth) {
        $this->googleoauth = $googleoauth;
        $this->googleoauth->setHeader('GData-Version: 2');
    }

    
    public function send_file($file) {
        $this->googleoauth->setHeader("Content-Length: ". $file->get_filesize());
        $this->googleoauth->setHeader("Content-Type: ". $file->get_mimetype());
        $this->googleoauth->setHeader("Slug: ". $file->get_filename());

        $this->googleoauth->post(self::UPLOAD_LOCATION, $file->get_content());

        if ($this->googleoauth->info['http_code'] === 201) {
            return true;
        } else {
            return false;
        }
    }

    
    public function get_file_list($path = '') {
        if (!$path) {
            return $this->get_albums();
        } else {
            return $this->get_album_photos($path);
        }
    }

    
    public function get_album_photos($albumid) {
        $albumcontent = $this->googleoauth->get(self::ALBUM_PHOTO_LIST.$albumid);

        return $this->get_photo_details($albumcontent);
    }

    
    public function get_last_album_name() {
        return $this->lastalbumname;
    }

    
    public function do_photo_search($query) {
        $content = $this->googleoauth->get(self::PHOTO_SEARCH_URL.htmlentities($query));

        return $this->get_photo_details($content);
    }

    
    public function get_albums() {
        $files = array();
        $content = $this->googleoauth->get(self::LIST_ALBUMS_URL);

        try {
            if (strpos($content, '<?xml') !== 0) {
                throw new moodle_exception('invalidxmlresponse');
            }
            $xml = new SimpleXMLElement($content);
        } catch (Exception $e) {
                                    return $files;
        }

        foreach ($xml->entry as $album) {
            $gphoto = $album->children('http://schemas.google.com/photos/2007');

            $mediainfo = $album->children('http://search.yahoo.com/mrss/');
                        $thumbnailinfo = $mediainfo->group->thumbnail[0]->attributes();

            $files[] = array( 'title' => (string) $album->title,
                'date'  => userdate($gphoto->timestamp),
                'size'  => (int) $gphoto->bytesUsed,
                'path'  => (string) $gphoto->id,
                'thumbnail' => (string) $thumbnailinfo['url'],
                'thumbnail_width' => 160,                  'thumbnail_height' => 160,
                'children' => array(),
            );
        }

        return $files;
    }

    
    public function get_photo_details($rawxml) {
        $files = array();

        try {
            if (strpos($rawxml, '<?xml') !== 0) {
                throw new moodle_exception('invalidxmlresponse');
            }
            $xml = new SimpleXMLElement($rawxml);
        } catch (Exception $e) {
                                    return $files;
        }
        $this->lastalbumname = (string)$xml->title;

        foreach ($xml->entry as $photo) {
            $gphoto = $photo->children('http://schemas.google.com/photos/2007');

            $mediainfo = $photo->children('http://search.yahoo.com/mrss/');
            $fullinfo = $mediainfo->group->content->attributes();
                        $thumbnailinfo = $mediainfo->group->thumbnail[0]->attributes();

                        if (!empty($mediainfo->group->description)) {
                $title = shorten_text((string)$mediainfo->group->description, 20, false, '');
                $title = clean_filename($title).'.jpg';
            } else {
                $title = (string)$mediainfo->group->title;
            }

            $files[] = array(
                'title' => $title,
                'date'  => userdate($gphoto->timestamp),
                'size' => (int) $gphoto->size,
                'path' => $gphoto->albumid.'/'.$gphoto->id,
                'thumbnail' => (string) $thumbnailinfo['url'],
                'thumbnail_width' => 72,                  'thumbnail_height' => 72,
                'source' => (string) $fullinfo['url'],
                'url' => (string) $fullinfo['url']
            );
        }

        return $files;
    }
}


class google_oauth extends oauth2_client {
    
    protected function auth_url() {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    
    protected function token_url() {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    
    public function reset_state() {
        $this->header = array();
        $this->response = array();
    }
}
