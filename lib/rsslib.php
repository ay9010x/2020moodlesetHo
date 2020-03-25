<?php




defined('MOODLE_INTERNAL') || die();


function rss_add_http_header($context, $componentname, $componentinstance, $title) {
    global $PAGE, $USER;

    $componentid = null;
    if (is_object($componentinstance)) {
        $componentid = $componentinstance->id;
    } else {
        $componentid = $componentinstance;
    }

    $rsspath = rss_get_url($context->id, $USER->id, $componentname, $componentid);
    $PAGE->add_alternate_version($title, $rsspath, 'application/rss+xml');
 }


function rss_get_link($contextid, $userid, $componentname, $id, $tooltiptext='') {
    global $OUTPUT;

    static $rsspath = '';

    $rsspath = rss_get_url($contextid, $userid, $componentname, $id);
    $rsspix = $OUTPUT->pix_url('i/rss');

    return '<a href="'. $rsspath .'"><img src="'. $rsspix .'" title="'. strip_tags($tooltiptext) .'" alt="'.get_string('rss').'" /></a>';
}


function rss_get_url($contextid, $userid, $componentname, $additionalargs) {
    global $CFG;
    if (empty($userid)) {
        $userid = guest_user()->id;
    }
    $usertoken = rss_get_token($userid);
    $url = '/rss/file.php';
    return moodle_url::make_file_url($url, '/'.$contextid.'/'.$usertoken.'/'.$componentname.'/'.$additionalargs.'/rss.xml');
}


function rss_print_link($contextid, $userid, $componentname, $id, $tooltiptext='') {
    print rss_get_link($contextid, $userid, $componentname, $id, $tooltiptext);

}


function rss_delete_file($componentname, $instance) {
    global $CFG;

    $dirpath = "$CFG->cachedir/rss/$componentname";
    if (is_dir($dirpath)) {
        if (!$dh = opendir($dirpath)) {
            error_log("Directory permission error. RSS directory store for component '{$componentname}' exists but cannot be opened.", DEBUG_DEVELOPER);
            return;
        }
        while (false !== ($filename = readdir($dh))) {
            if ($filename!='.' && $filename!='..') {
                if (preg_match("/{$instance->id}_/", $filename)) {
                    unlink("$dirpath/$filename");
                }
            }
        }
    }
}


function rss_enabled_for_mod($modname, $instance=null, $hasrsstype=true, $hasrssarticles=true) {
    if ($hasrsstype) {
        if (empty($instance->rsstype) || $instance->rsstype==0) {
            return false;
        }
    }

        if ($hasrssarticles) {
        if (empty($instance->rssarticles) || $instance->rssarticles==0) {
            return false;
        }
    }

    if (!empty($instance) && !instance_is_visible($modname,$instance)) {
        return false;
    }

    return true;
}


function rss_save_file($componentname, $filename, $contents, $expandfilename=true) {
    global $CFG;

    $status = true;

    if (! $basedir = make_cache_directory ('rss/'. $componentname)) {
                $status = false;
    }

    if ($status) {
        $fullfilename = $filename;
        if ($expandfilename) {
            $fullfilename = rss_get_file_full_name($componentname, $filename);
        }

        $rss_file = fopen($fullfilename, "w");
        if ($rss_file) {
            $status = fwrite ($rss_file, $contents);
            fclose($rss_file);
        } else {
            $status = false;
        }
    }
    return $status;
}


function rss_get_file_full_name($componentname, $filename) {
    global $CFG;
    return "$CFG->cachedir/rss/$componentname/$filename.xml";
}


function rss_get_file_name($instance, $sql, $params = array()) {
    if ($params) {
                                        asort($params);
        $serializearray = serialize($params);
        return $instance->id.'_'.md5($sql . $serializearray);
    } else {
        return $instance->id.'_'.md5($sql);
    }
}


function rss_standard_header($title = NULL, $link = NULL, $description = NULL) {
    global $CFG, $USER, $OUTPUT;

    $status = true;
    $result = "";

    $site = get_site();

    if ($status) {

                if (empty($title)) {
            $title = format_string($site->fullname);
        }
        if (empty($link)) {
            $link = $CFG->wwwroot;
        }
        if (empty($description)) {
            $description = $site->summary;
        }

                $result .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $result .= "<rss version=\"2.0\">\n";

                $result .= rss_start_tag('channel', 1, true);

                $result .= rss_full_tag('title', 2, false, strip_tags($title));
        $result .= rss_full_tag('link', 2, false, $link);
        $result .= rss_full_tag('description', 2, false, $description);
        $result .= rss_full_tag('generator', 2, false, 'Moodle');
        if (!empty($USER->lang)) {
            $result .= rss_full_tag('language', 2, false, substr($USER->lang,0,2));
        }
        $today = getdate();
        $result .= rss_full_tag('copyright', 2, false, '(c) '. $today['year'] .' '. format_string($site->fullname));
        

                $rsspix = $OUTPUT->pix_url('i/rsssitelogo');

                $result .= rss_start_tag('image', 2, true);
        $result .= rss_full_tag('url', 3, false, $rsspix);
        $result .= rss_full_tag('title', 3, false, 'moodle');
        $result .= rss_full_tag('link', 3, false, $CFG->wwwroot);
        $result .= rss_full_tag('width', 3, false, '140');
        $result .= rss_full_tag('height', 3, false, '35');
        $result .= rss_end_tag('image', 2, true);
    }

    if (!$status) {
        return false;
    } else {
        return $result;
    }
}



function rss_add_items($items) {
    global $CFG;

    $result = '';

    if (!empty($items)) {
        foreach ($items as $item) {
            $result .= rss_start_tag('item',2,true);
                        if (isset($item->category)) {
                $result .= rss_full_tag('category',3,false,$item->category);
            }
            if (isset($item->tags)) {
                $attributes = array();
                if (isset($item->tagscheme)) {
                    $attributes['domain'] = s($item->tagscheme);
                }
                foreach ($item->tags as $tag) {
                    $result .= rss_full_tag('category', 3, false, $tag, $attributes);
                }
            }
            $result .= rss_full_tag('title',3,false,strip_tags($item->title));
            $result .= rss_full_tag('link',3,false,$item->link);
            $result .= rss_add_enclosures($item);
            $result .= rss_full_tag('pubDate',3,false,gmdate('D, d M Y H:i:s',$item->pubdate).' GMT');                          if (isset($item->author) && !empty($item->author)) {
                                                                                $item->description = get_string('byname','',$item->author).'. &nbsp;<p>'.$item->description.'</p>';
            }
            $result .= rss_full_tag('description',3,false,$item->description);
            $result .= rss_full_tag('guid',3,false,$item->link,array('isPermaLink' => 'true'));
            $result .= rss_end_tag('item',2,true);

        }
    } else {
        $result = false;
    }
    return $result;
}


function rss_standard_footer() {
    $status = true;
    $result = '';

    $result .= rss_end_tag('channel', 1, true);
    $result .= '</rss>';

    return $result;
}



function rss_geterrorxmlfile($errortype = 'rsserror') {
    global $CFG;

    $return = '';

        $return = rss_standard_header();

        if ($return) {
        $item = new stdClass();
        $item->title       = "RSS Error";
        $item->link        = $CFG->wwwroot;
        $item->pubdate     = time();
        $item->description = get_string($errortype);
        $return .= rss_add_items(array($item));
    }

        if ($return) {
        $return .= rss_standard_footer();
    }

    return $return;
}


function rss_get_userid_from_token($token) {
    global $DB;

    $sql = 'SELECT u.id FROM {user} u
            JOIN {user_private_key} k ON u.id = k.userid
            WHERE u.deleted = 0 AND u.confirmed = 1
            AND u.suspended = 0 AND k.value = ?';
    return $DB->get_field_sql($sql, array($token), IGNORE_MISSING);
}


function rss_get_token($userid) {
    return get_user_key('rss', $userid);
}


function rss_delete_token($userid) {
    delete_user_key('rss', $userid);
}


function rss_start_tag($tag,$level=0,$endline=false,$attributes=null) {
    if ($endline) {
       $endchar = "\n";
    } else {
       $endchar = "";
    }
    $attrstring = '';
    if (!empty($attributes) && is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attrstring .= " ".$key."=\"".$value."\"";
        }
    }
    return str_repeat(" ",$level*2)."<".$tag.$attrstring.">".$endchar;
}


function rss_end_tag($tag,$level=0,$endline=true) {
    if ($endline) {
       $endchar = "\n";
    } else {
       $endchar = "";
    }
    return str_repeat(" ",$level*2)."</".$tag.">".$endchar;
}


function rss_full_tag($tag,$level=0,$endline=true,$content,$attributes=null) {
    $st = rss_start_tag($tag,$level,$endline,$attributes);
    $co="";
    $co = preg_replace("/\r\n|\r/", "\n", htmlspecialchars($content));
    $et = rss_end_tag($tag,0,true);

    return $st.$co.$et;
}


function rss_add_enclosures($item){
    global $CFG;

    $returnstring = '';

        include_once($CFG->libdir.'/filelib.php');
    $mediafiletypes = get_mimetypes_array();

        if (isset($item->attachments) && is_array($item->attachments)) {
        foreach ($item->attachments as $attachment){
            $extension = strtolower(substr($attachment->url, strrpos($attachment->url, '.')+1));
            if (isset($mediafiletypes[$extension]['type'])) {
                $type = $mediafiletypes[$extension]['type'];
            } else {
                $type = 'document/unknown';
            }
            $returnstring .= "\n<enclosure url=\"$attachment->url\" length=\"$attachment->length\" type=\"$type\" />\n";
        }
    }

    return $returnstring;
}
