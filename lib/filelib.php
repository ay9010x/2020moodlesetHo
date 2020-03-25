<?php



defined('MOODLE_INTERNAL') || die();


define('BYTESERVING_BOUNDARY', 's1k2o3d4a5k6s7');


define('FILE_AREA_MAX_BYTES_UNLIMITED', -1);

require_once("$CFG->libdir/filestorage/file_exceptions.php");
require_once("$CFG->libdir/filestorage/file_storage.php");
require_once("$CFG->libdir/filestorage/zip_packer.php");
require_once("$CFG->libdir/filebrowser/file_browser.php");


function file_encode_url($urlbase, $path, $forcedownload=false, $https=false) {
    global $CFG;


    if ($CFG->slasharguments) {
        $parts = explode('/', $path);
        $parts = array_map('rawurlencode', $parts);
        $path  = implode('/', $parts);
        $return = $urlbase.$path;
        if ($forcedownload) {
            $return .= '?forcedownload=1';
        }
    } else {
        $path = rawurlencode($path);
        $return = $urlbase.'?file='.$path;
        if ($forcedownload) {
            $return .= '&amp;forcedownload=1';
        }
    }

    if ($https) {
        $return = str_replace('http://', 'https://', $return);
    }

    return $return;
}


function file_area_contains_subdirs(context $context, $component, $filearea, $itemid) {
    global $DB;

    if (!isset($itemid)) {
                return false;
    }

        $select = "contextid = :contextid AND component = :component AND filearea = :filearea AND itemid = :itemid AND filepath <> '/' AND filename = '.'";
    $params = array('contextid'=>$context->id, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid);
    return $DB->record_exists_select('files', $select, $params);
}


function file_prepare_standard_editor($data, $field, array $options, $context=null, $component=null, $filearea=null, $itemid=null) {
    $options = (array)$options;
    if (!isset($options['trusttext'])) {
        $options['trusttext'] = false;
    }
    if (!isset($options['forcehttps'])) {
        $options['forcehttps'] = false;
    }
    if (!isset($options['subdirs'])) {
        $options['subdirs'] = false;
    }
    if (!isset($options['maxfiles'])) {
        $options['maxfiles'] = 0;     }
    if (!isset($options['noclean'])) {
        $options['noclean'] = false;
    }

                if (isset($context) && !isset($options['context'])) {
                debugging('Context for editor is not set in editoroptions. Hence editor will not respect editor filters', DEBUG_DEVELOPER);
    } else if (isset($options['context']) && isset($context)) {
                if ($options['context']->id != $context->id) {
            $exceptionmsg = 'Editor context ['.$options['context']->id.'] is not equal to passed context ['.$context->id.']';
            throw new coding_exception($exceptionmsg);
        }
    }

    if (is_null($itemid) or is_null($context)) {
        $contextid = null;
        $itemid = null;
        if (!isset($data)) {
            $data = new stdClass();
        }
        if (!isset($data->{$field})) {
            $data->{$field} = '';
        }
        if (!isset($data->{$field.'format'})) {
            $data->{$field.'format'} = editors_get_preferred_format();
        }
        if (!$options['noclean']) {
            $data->{$field} = clean_text($data->{$field}, $data->{$field.'format'});
        }

    } else {
        if ($options['trusttext']) {
                        if (!isset($data->{$field.'trust'})) {
                $data->{$field.'trust'} = 0;
            }
            $data = trusttext_pre_edit($data, $field, $context);
        } else {
            if (!$options['noclean']) {
                $data->{$field} = clean_text($data->{$field}, $data->{$field.'format'});
            }
        }
        $contextid = $context->id;
    }

    if ($options['maxfiles'] != 0) {
        $draftid_editor = file_get_submitted_draft_itemid($field);
        $currenttext = file_prepare_draft_area($draftid_editor, $contextid, $component, $filearea, $itemid, $options, $data->{$field});
        $data->{$field.'_editor'} = array('text'=>$currenttext, 'format'=>$data->{$field.'format'}, 'itemid'=>$draftid_editor);
    } else {
        $data->{$field.'_editor'} = array('text'=>$data->{$field}, 'format'=>$data->{$field.'format'}, 'itemid'=>0);
    }

    return $data;
}


function file_postupdate_standard_editor($data, $field, array $options, $context, $component=null, $filearea=null, $itemid=null) {
    $options = (array)$options;
    if (!isset($options['trusttext'])) {
        $options['trusttext'] = false;
    }
    if (!isset($options['forcehttps'])) {
        $options['forcehttps'] = false;
    }
    if (!isset($options['subdirs'])) {
        $options['subdirs'] = false;
    }
    if (!isset($options['maxfiles'])) {
        $options['maxfiles'] = 0;     }
    if (!isset($options['maxbytes'])) {
        $options['maxbytes'] = 0;     }

    if ($options['trusttext']) {
        $data->{$field.'trust'} = trusttext_trusted($context);
    } else {
        $data->{$field.'trust'} = 0;
    }

    $editor = $data->{$field.'_editor'};

    if ($options['maxfiles'] == 0 or is_null($filearea) or is_null($itemid) or empty($editor['itemid'])) {
        $data->{$field} = $editor['text'];
    } else {
        $data->{$field} = file_save_draft_area_files($editor['itemid'], $context->id, $component, $filearea, $itemid, $options, $editor['text'], $options['forcehttps']);
    }
    $data->{$field.'format'} = $editor['format'];

    return $data;
}


function file_prepare_standard_filemanager($data, $field, array $options, $context=null, $component=null, $filearea=null, $itemid=null) {
    $options = (array)$options;
    if (!isset($options['subdirs'])) {
        $options['subdirs'] = false;
    }
    if (is_null($itemid) or is_null($context)) {
        $itemid = null;
        $contextid = null;
    } else {
        $contextid = $context->id;
    }

    $draftid_editor = file_get_submitted_draft_itemid($field.'_filemanager');
    file_prepare_draft_area($draftid_editor, $contextid, $component, $filearea, $itemid, $options);
    $data->{$field.'_filemanager'} = $draftid_editor;

    return $data;
}


function file_postupdate_standard_filemanager($data, $field, array $options, $context, $component, $filearea, $itemid) {
    $options = (array)$options;
    if (!isset($options['subdirs'])) {
        $options['subdirs'] = false;
    }
    if (!isset($options['maxfiles'])) {
        $options['maxfiles'] = -1;     }
    if (!isset($options['maxbytes'])) {
        $options['maxbytes'] = 0;     }

    if (empty($data->{$field.'_filemanager'})) {
        $data->$field = '';

    } else {
        file_save_draft_area_files($data->{$field.'_filemanager'}, $context->id, $component, $filearea, $itemid, $options);
        $fs = get_file_storage();

        if ($fs->get_area_files($context->id, $component, $filearea, $itemid)) {
            $data->$field = '1';         } else {
            $data->$field = '';
        }
    }

    return $data;
}


function file_get_unused_draft_itemid() {
    global $DB, $USER;

    if (isguestuser() or !isloggedin()) {
                print_error('noguest');
    }

    $contextid = context_user::instance($USER->id)->id;

    $fs = get_file_storage();
    $draftitemid = rand(1, 999999999);
    while ($files = $fs->get_area_files($contextid, 'user', 'draft', $draftitemid)) {
        $draftitemid = rand(1, 999999999);
    }

    return $draftitemid;
}


function file_prepare_draft_area(&$draftitemid, $contextid, $component, $filearea, $itemid, array $options=null, $text=null) {
    global $CFG, $USER, $CFG;

    $options = (array)$options;
    if (!isset($options['subdirs'])) {
        $options['subdirs'] = false;
    }
    if (!isset($options['forcehttps'])) {
        $options['forcehttps'] = false;
    }

    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();

    if (empty($draftitemid)) {
                $draftitemid = file_get_unused_draft_itemid();
        $file_record = array('contextid'=>$usercontext->id, 'component'=>'user', 'filearea'=>'draft', 'itemid'=>$draftitemid);
        if (!is_null($itemid) and $files = $fs->get_area_files($contextid, $component, $filearea, $itemid)) {
            foreach ($files as $file) {
                if ($file->is_directory() and $file->get_filepath() === '/') {
                                                            continue;
                }
                if (!$options['subdirs'] and ($file->is_directory() or $file->get_filepath() !== '/')) {
                    continue;
                }
                $draftfile = $fs->create_file_from_storedfile($file_record, $file);
                                                                                                $sourcefield = $file->get_source();
                $newsourcefield = new stdClass;
                $newsourcefield->source = $sourcefield;
                $original = new stdClass;
                $original->contextid = $contextid;
                $original->component = $component;
                $original->filearea  = $filearea;
                $original->itemid    = $itemid;
                $original->filename  = $file->get_filename();
                $original->filepath  = $file->get_filepath();
                $newsourcefield->original = file_storage::pack_reference($original);
                $draftfile->set_source(serialize($newsourcefield));
                            }
        }
        if (!is_null($text)) {
                                                $text = str_replace("\"$CFG->httpswwwroot/draftfile.php", "\"$CFG->httpswwwroot/brokenfile.php#", $text);
        }
    } else {
            }

    if (is_null($text)) {
        return null;
    }

        return file_rewrite_pluginfile_urls($text, 'draftfile.php', $usercontext->id, 'user', 'draft', $draftitemid, $options);
}


function file_rewrite_pluginfile_urls($text, $file, $contextid, $component, $filearea, $itemid, array $options=null) {
    global $CFG;

    $options = (array)$options;
    if (!isset($options['forcehttps'])) {
        $options['forcehttps'] = false;
    }

    if (!$CFG->slasharguments) {
        $file = $file . '?file=';
    }

    $baseurl = "$CFG->wwwroot/$file/$contextid/$component/$filearea/";

    if ($itemid !== null) {
        $baseurl .= "$itemid/";
    }

    if ($options['forcehttps']) {
        $baseurl = str_replace('http://', 'https://', $baseurl);
    }

    if (!empty($options['reverse'])) {
        return str_replace($baseurl, '@@PLUGINFILE@@/', $text);
    } else {
        return str_replace('@@PLUGINFILE@@/', $baseurl, $text);
    }
}


function file_get_draft_area_info($draftitemid, $filepath = '/') {
    global $CFG, $USER;

    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();

    $results = array(
        'filecount' => 0,
        'foldercount' => 0,
        'filesize' => 0,
        'filesize_without_references' => 0
    );

    if ($filepath != '/') {
        $draftfiles = $fs->get_directory_files($usercontext->id, 'user', 'draft', $draftitemid, $filepath, true, true);
    } else {
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', true);
    }
    foreach ($draftfiles as $file) {
        if ($file->is_directory()) {
            $results['foldercount'] += 1;
        } else {
            $results['filecount'] += 1;
        }

        $filesize = $file->get_filesize();
        $results['filesize'] += $filesize;
        if (!$file->is_external_file()) {
            $results['filesize_without_references'] += $filesize;
        }
    }

    return $results;
}


function file_is_draft_area_limit_reached($draftitemid, $areamaxbytes, $newfilesize = 0, $includereferences = false) {
    if ($areamaxbytes != FILE_AREA_MAX_BYTES_UNLIMITED) {
        $draftinfo = file_get_draft_area_info($draftitemid);
        $areasize = $draftinfo['filesize_without_references'];
        if ($includereferences) {
            $areasize = $draftinfo['filesize'];
        }
        if ($areasize + $newfilesize > $areamaxbytes) {
            return true;
        }
    }
    return false;
}


function file_get_user_used_space() {
    global $DB, $USER;

    $usercontext = context_user::instance($USER->id);
    $sql = "SELECT SUM(files1.filesize) AS totalbytes FROM {files} files1
            JOIN (SELECT contenthash, filename, MAX(id) AS id
            FROM {files}
            WHERE contextid = ? AND component = ? AND filearea != ?
            GROUP BY contenthash, filename) files2 ON files1.id = files2.id";
    $params = array('contextid'=>$usercontext->id, 'component'=>'user', 'filearea'=>'draft');
    $record = $DB->get_record_sql($sql, $params);
    return (int)$record->totalbytes;
}


function file_correct_filepath($str) {     if ($str == '/' or empty($str)) {
        return '/';
    } else {
        return '/'.trim($str, '/').'/';
    }
}


function file_get_drafarea_folders($draftitemid, $filepath, &$data) {
    global $USER, $OUTPUT, $CFG;
    $data->children = array();
    $context = context_user::instance($USER->id);
    $fs = get_file_storage();
    if ($files = $fs->get_directory_files($context->id, 'user', 'draft', $draftitemid, $filepath, false)) {
        foreach ($files as $file) {
            if ($file->is_directory()) {
                $item = new stdClass();
                $item->sortorder = $file->get_sortorder();
                $item->filepath = $file->get_filepath();

                $foldername = explode('/', trim($item->filepath, '/'));
                $item->fullname = trim(array_pop($foldername), '/');

                $item->id = uniqid();
                file_get_drafarea_folders($draftitemid, $item->filepath, $item);
                $data->children[] = $item;
            } else {
                continue;
            }
        }
    }
}


function file_get_drafarea_files($draftitemid, $filepath = '/') {
    global $USER, $OUTPUT, $CFG;

    $context = context_user::instance($USER->id);
    $fs = get_file_storage();

    $data = new stdClass();
    $data->path = array();
    $data->path[] = array('name'=>get_string('files'), 'path'=>'/');

        $trail = '/';
    if ($filepath !== '/') {
        $filepath = file_correct_filepath($filepath);
        $parts = explode('/', $filepath);
        foreach ($parts as $part) {
            if ($part != '' && $part != null) {
                $trail .= ($part.'/');
                $data->path[] = array('name'=>$part, 'path'=>$trail);
            }
        }
    }

    $list = array();
    $maxlength = 12;
    if ($files = $fs->get_directory_files($context->id, 'user', 'draft', $draftitemid, $filepath, false)) {
        foreach ($files as $file) {
            $item = new stdClass();
            $item->filename = $file->get_filename();
            $item->filepath = $file->get_filepath();
            $item->fullname = trim($item->filename, '/');
            $filesize = $file->get_filesize();
            $item->size = $filesize ? $filesize : null;
            $item->filesize = $filesize ? display_size($filesize) : '';

            $item->sortorder = $file->get_sortorder();
            $item->author = $file->get_author();
            $item->license = $file->get_license();
            $item->datemodified = $file->get_timemodified();
            $item->datecreated = $file->get_timecreated();
            $item->isref = $file->is_external_file();
            if ($item->isref && $file->get_status() == 666) {
                $item->originalmissing = true;
            }
                                    $source = @unserialize($file->get_source());
            if (isset($source->original)) {
                $item->refcount = $fs->search_references_count($source->original);
            }

            if ($file->is_directory()) {
                $item->filesize = 0;
                $item->icon = $OUTPUT->pix_url(file_folder_icon(24))->out(false);
                $item->type = 'folder';
                $foldername = explode('/', trim($item->filepath, '/'));
                $item->fullname = trim(array_pop($foldername), '/');
                $item->thumbnail = $OUTPUT->pix_url(file_folder_icon(90))->out(false);
            } else {
                                $item->mimetype = get_mimetype_description($file);
                if (file_extension_in_typegroup($file->get_filename(), 'archive')) {
                    $item->type = 'zip';
                } else {
                    $item->type = 'file';
                }
                $itemurl = moodle_url::make_draftfile_url($draftitemid, $item->filepath, $item->filename);
                $item->url = $itemurl->out();
                $item->icon = $OUTPUT->pix_url(file_file_icon($file, 24))->out(false);
                $item->thumbnail = $OUTPUT->pix_url(file_file_icon($file, 90))->out(false);
                if ($imageinfo = $file->get_imageinfo()) {
                    $item->realthumbnail = $itemurl->out(false, array('preview' => 'thumb', 'oid' => $file->get_timemodified()));
                    $item->realicon = $itemurl->out(false, array('preview' => 'tinyicon', 'oid' => $file->get_timemodified()));
                    $item->image_width = $imageinfo['width'];
                    $item->image_height = $imageinfo['height'];
                }
            }
            $list[] = $item;
        }
    }
    $data->itemid = $draftitemid;
    $data->list = $list;
    return $data;
}


function file_get_submitted_draft_itemid($elname) {
        if (!isset($_REQUEST[$elname])) {
        return 0;
    }
    if (is_array($_REQUEST[$elname])) {
        $param = optional_param_array($elname, 0, PARAM_INT);
        if (!empty($param['itemid'])) {
            $param = $param['itemid'];
        } else {
            debugging('Missing itemid, maybe caused by unset maxfiles option', DEBUG_DEVELOPER);
            return false;
        }

    } else {
        $param = optional_param($elname, 0, PARAM_INT);
    }

    if ($param) {
        require_sesskey();
    }

    return $param;
}


function file_restore_source_field_from_draft_file($storedfile) {
    $source = @unserialize($storedfile->get_source());
    if (!empty($source)) {
        if (is_object($source)) {
            $restoredsource = $source->source;
            $storedfile->set_source($restoredsource);
        } else {
            throw new moodle_exception('invalidsourcefield', 'error');
        }
    }
    return $storedfile;
}

function file_save_draft_area_files($draftitemid, $contextid, $component, $filearea, $itemid, array $options=null, $text=null, $forcehttps=false) {
    global $USER;

    $usercontext = context_user::instance($USER->id);
    $fs = get_file_storage();

    $options = (array)$options;
    if (!isset($options['subdirs'])) {
        $options['subdirs'] = false;
    }
    if (!isset($options['maxfiles'])) {
        $options['maxfiles'] = -1;     }
    if (!isset($options['maxbytes']) || $options['maxbytes'] == USER_CAN_IGNORE_FILE_SIZE_LIMITS) {
        $options['maxbytes'] = 0;     }
    if (!isset($options['areamaxbytes'])) {
        $options['areamaxbytes'] = FILE_AREA_MAX_BYTES_UNLIMITED;     }
    $allowreferences = true;
    if (isset($options['return_types']) && !($options['return_types'] & FILE_REFERENCE)) {
                                $allowreferences = false;
    }

                if (file_is_draft_area_limit_reached($draftitemid, $options['areamaxbytes'])) {
        return null;
    }

    $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id');
    $oldfiles   = $fs->get_area_files($contextid, $component, $filearea, $itemid, 'id');

        if (count($draftfiles) > 1 || count($oldfiles) > 1) {
                
        $newhashes = array();
        $filecount = 0;
        foreach ($draftfiles as $file) {
            if (!$options['subdirs'] && $file->get_filepath() !== '/') {
                continue;
            }
            if (!$allowreferences && $file->is_external_file()) {
                continue;
            }
            if (!$file->is_directory()) {
                if ($options['maxbytes'] and $options['maxbytes'] < $file->get_filesize()) {
                                        continue;
                }
                if ($options['maxfiles'] != -1 and $options['maxfiles'] <= $filecount) {
                                        continue;
                }
                $filecount++;
            }
            $newhash = $fs->get_pathname_hash($contextid, $component, $filearea, $itemid, $file->get_filepath(), $file->get_filename());
            $newhashes[$newhash] = $file;
        }

                        foreach ($oldfiles as $oldfile) {
            $oldhash = $oldfile->get_pathnamehash();
            if (!isset($newhashes[$oldhash])) {
                                $oldfile->delete();
                continue;
            }

            $newfile = $newhashes[$oldhash];
                                    if ($newfile->is_directory()) {
                            } else if (($source = @unserialize($newfile->get_source())) && isset($source->original)) {
                                $original = file_storage::unpack_reference($source->original);
                if ($original['filename'] !== $oldfile->get_filename() || $original['filepath'] !== $oldfile->get_filepath()) {
                                        $oldfile->delete();
                    continue;
                }
            } else {
                                                $oldfile->delete();
                continue;
            }

                        if ($oldfile->get_status() != $newfile->get_status()) {
                                $oldfile->delete();
                                continue;
            }

                        if ($oldfile->get_author() != $newfile->get_author()) {
                $oldfile->set_author($newfile->get_author());
            }
                        if ($oldfile->get_license() != $newfile->get_license()) {
                $oldfile->set_license($newfile->get_license());
            }

                                                $newsource = $newfile->get_source();
            if ($source = @unserialize($newfile->get_source())) {
                $newsource = $source->source;
            }
            if ($oldfile->get_source() !== $newsource) {
                $oldfile->set_source($newsource);
            }

                        if ($oldfile->get_sortorder() != $newfile->get_sortorder()) {
                $oldfile->set_sortorder($newfile->get_sortorder());
            }

                        if ($oldfile->get_timemodified() != $newfile->get_timemodified()) {
                $oldfile->set_timemodified($newfile->get_timemodified());
            }

                        if (!$oldfile->is_directory() &&
                    ($oldfile->get_contenthash() != $newfile->get_contenthash() ||
                    $oldfile->get_filesize() != $newfile->get_filesize() ||
                    $oldfile->get_referencefileid() != $newfile->get_referencefileid() ||
                    $oldfile->get_userid() != $newfile->get_userid())) {
                $oldfile->replace_file_with($newfile);
            }

                        unset($newhashes[$oldhash]);
        }

                        foreach ($newhashes as $file) {
            $file_record = array('contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid, 'timemodified'=>time());
            if ($source = @unserialize($file->get_source())) {
                                                $file_record['source'] = $source->source;
            }

            if ($file->is_external_file()) {
                $repoid = $file->get_repository_id();
                if (!empty($repoid)) {
                    $file_record['repositoryid'] = $repoid;
                    $file_record['reference'] = $file->get_reference();
                }
            }

            $fs->create_file_from_storedfile($file_record, $file);
        }
    }

            
    if (is_null($text)) {
        return null;
    } else {
        return file_rewrite_urls_to_pluginfile($text, $draftitemid, $forcehttps);
    }
}


function file_rewrite_urls_to_pluginfile($text, $draftitemid, $forcehttps = false) {
    global $CFG, $USER;

    $usercontext = context_user::instance($USER->id);

    $wwwroot = $CFG->wwwroot;
    if ($forcehttps) {
        $wwwroot = str_replace('http://', 'https://', $wwwroot);
    }

        $text = str_ireplace("$wwwroot/draftfile.php/$usercontext->id/user/draft/$draftitemid/", '@@PLUGINFILE@@/', $text);

    if (strpos($text, 'draftfile.php?file=') !== false) {
        $matches = array();
        preg_match_all("!$wwwroot/draftfile.php\?file=%2F{$usercontext->id}%2Fuser%2Fdraft%2F{$draftitemid}%2F[^'\",&<>|`\s:\\\\]+!iu", $text, $matches);
        if ($matches) {
            foreach ($matches[0] as $match) {
                $replace = str_ireplace('%2F', '/', $match);
                $text = str_replace($match, $replace, $text);
            }
        }
        $text = str_ireplace("$wwwroot/draftfile.php?file=/$usercontext->id/user/draft/$draftitemid/", '@@PLUGINFILE@@/', $text);
    }

    return $text;
}


function file_set_sortorder($contextid, $component, $filearea, $itemid, $filepath, $filename, $sortorder) {
    global $DB;
    $conditions = array('contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid, 'filepath'=>$filepath, 'filename'=>$filename);
    if ($file_record = $DB->get_record('files', $conditions)) {
        $sortorder = (int)$sortorder;
        $file_record->sortorder = $sortorder;
        $DB->update_record('files', $file_record);
        return true;
    }
    return false;
}


function file_reset_sortorder($contextid, $component, $filearea, $itemid=false) {
    global $DB;

    $conditions = array('contextid'=>$contextid, 'component'=>$component, 'filearea'=>$filearea);
    if ($itemid !== false) {
        $conditions['itemid'] = $itemid;
    }

    $file_records = $DB->get_records('files', $conditions);
    foreach ($file_records as $file_record) {
        $file_record->sortorder = 0;
        $DB->update_record('files', $file_record);
    }
    return true;
}


function file_get_upload_error($errorcode) {

    switch ($errorcode) {
    case 0:         $errmessage = '';
        break;

    case 1:         $errmessage = get_string('uploadserverlimit');
        break;

    case 2:         $errmessage = get_string('uploadformlimit');
        break;

    case 3:         $errmessage = get_string('uploadpartialfile');
        break;

    case 4:         $errmessage = get_string('uploadnofilefound');
        break;

    
    case 6:         $errmessage = get_string('uploadnotempdir');
        break;

    case 7:         $errmessage = get_string('uploadcantwrite');
        break;

    case 8:         $errmessage = get_string('uploadextension');
        break;

    default:
        $errmessage = get_string('uploadproblem');
    }

    return $errmessage;
}


function format_array_postdata_for_curlcall($arraydata, $currentdata, &$data) {
        foreach ($arraydata as $k=>$v) {
            $newcurrentdata = $currentdata;
            if (is_array($v)) {                 $newcurrentdata = $newcurrentdata.'['.urlencode($k).']';
                format_array_postdata_for_curlcall($v, $newcurrentdata, $data);
            }  else {                 $data[] = $newcurrentdata.'['.urlencode($k).']='.urlencode($v);
            }
        }
}


function format_postdata_for_curlcall($postdata) {
        $data = array();
        foreach ($postdata as $k=>$v) {
            if (is_array($v)) {
                $currentdata = urlencode($k);
                format_array_postdata_for_curlcall($v, $currentdata, $data);
            }  else {
                $data[] = urlencode($k).'='.urlencode($v);
            }
        }
        $convertedpostdata = implode('&', $data);
        return $convertedpostdata;
}


function download_file_content($url, $headers=null, $postdata=null, $fullresponse=false, $timeout=300, $connecttimeout=20, $skipcertverify=false, $tofile=NULL, $calctimeout=false) {
    global $CFG;

        if (!preg_match('|^https?://|i', $url)) {
        if ($fullresponse) {
            $response = new stdClass();
            $response->status        = 0;
            $response->headers       = array();
            $response->response_code = 'Invalid protocol specified in url';
            $response->results       = '';
            $response->error         = 'Invalid protocol specified in url';
            return $response;
        } else {
            return false;
        }
    }

    $options = array();

    $headers2 = array();
    if (is_array($headers)) {
        foreach ($headers as $key => $value) {
            if (is_numeric($key)) {
                $headers2[] = $value;
            } else {
                $headers2[] = "$key: $value";
            }
        }
    }

    if ($skipcertverify) {
        $options['CURLOPT_SSL_VERIFYPEER'] = false;
    } else {
        $options['CURLOPT_SSL_VERIFYPEER'] = true;
    }

    $options['CURLOPT_CONNECTTIMEOUT'] = $connecttimeout;

    $options['CURLOPT_FOLLOWLOCATION'] = 1;
    $options['CURLOPT_MAXREDIRS'] = 5;

        if (is_array($postdata)) {
        $postdata = format_postdata_for_curlcall($postdata);
    } else if (empty($postdata)) {
        $postdata = null;
    }

        if (!isset($CFG->curltimeoutkbitrate)) {
                $bitrate = 56;
    } else {
        $bitrate = $CFG->curltimeoutkbitrate;
    }
    if ($calctimeout and !isset($postdata)) {
        $curl = new curl();
        $curl->setHeader($headers2);

        $curl->head($url, $postdata, $options);

        $info = $curl->get_info();
        $error_no = $curl->get_errno();
        if (!$error_no && $info['download_content_length'] > 0) {
                        $timeout = max($timeout, ceil($info['download_content_length'] * 8 / ($bitrate * 1024)));
        }
    }

    $curl = new curl();
    $curl->setHeader($headers2);

    $options['CURLOPT_RETURNTRANSFER'] = true;
    $options['CURLOPT_NOBODY'] = false;
    $options['CURLOPT_TIMEOUT'] = $timeout;

    if ($tofile) {
        $fh = fopen($tofile, 'w');
        if (!$fh) {
            if ($fullresponse) {
                $response = new stdClass();
                $response->status        = 0;
                $response->headers       = array();
                $response->response_code = 'Can not write to file';
                $response->results       = false;
                $response->error         = 'Can not write to file';
                return $response;
            } else {
                return false;
            }
        }
        $options['CURLOPT_FILE'] = $fh;
    }

    if (isset($postdata)) {
        $content = $curl->post($url, $postdata, $options);
    } else {
        $content = $curl->get($url, null, $options);
    }

    if ($tofile) {
        fclose($fh);
        @chmod($tofile, $CFG->filepermissions);
    }



    $info       = $curl->get_info();
    $error_no   = $curl->get_errno();
    $rawheaders = $curl->get_raw_response();

    if ($error_no) {
        $error = $content;
        if (!$fullresponse) {
            debugging("cURL request for \"$url\" failed with: $error ($error_no)", DEBUG_ALL);
            return false;
        }

        $response = new stdClass();
        if ($error_no == 28) {
            $response->status    = '-100';         } else {
            $response->status    = '0';
        }
        $response->headers       = array();
        $response->response_code = $error;
        $response->results       = false;
        $response->error         = $error;
        return $response;
    }

    if ($tofile) {
        $content = true;
    }

    if (empty($info['http_code'])) {
                $response = new stdClass();
        $response->status        = '0';
        $response->headers       = array();
        $response->response_code = 'Unknown cURL error';
        $response->results       = false;         $response->error         = 'Unknown cURL error';

    } else {
        $response = new stdClass();
        $response->status        = (string)$info['http_code'];
        $response->headers       = $rawheaders;
        $response->results       = $content;
        $response->error         = '';

                $firstline = true;
        foreach ($rawheaders as $line) {
            if ($firstline) {
                $response->response_code = $line;
                $firstline = false;
            }
            if (trim($line, "\r\n") === '') {
                $firstline = true;
            }
        }
    }

    if ($fullresponse) {
        return $response;
    }

    if ($info['http_code'] != 200) {
        debugging("cURL request for \"$url\" failed, HTTP response code: ".$response->response_code, DEBUG_ALL);
        return false;
    }
    return $response->results;
}


function &get_mimetypes_array() {
        return core_filetypes::get_types();
}


function get_mimetype_for_sending($filename = '') {
        $mimetype = mimeinfo('type', $filename);

        if (!$mimetype || $mimetype === 'document/unknown') {
        $mimetype = 'application/octet-stream';
    }

    return $mimetype;
}


function mimeinfo($element, $filename) {
    global $CFG;
    $mimeinfo = & get_mimetypes_array();
    static $iconpostfixes = array(256=>'-256', 128=>'-128', 96=>'-96', 80=>'-80', 72=>'-72', 64=>'-64', 48=>'-48', 32=>'-32', 24=>'-24', 16=>'');

    $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (empty($filetype)) {
        $filetype = 'xxx';     }
    if (preg_match('/^icon(\d*)$/', $element, $iconsizematch)) {
        $iconsize = max(array(16, (int)$iconsizematch[1]));
        $filenames = array($mimeinfo['xxx']['icon']);
        if ($filetype != 'xxx' && isset($mimeinfo[$filetype]['icon'])) {
            array_unshift($filenames, $mimeinfo[$filetype]['icon']);
        }
                foreach ($filenames as $filename) {
            foreach ($iconpostfixes as $size => $postfix) {
                $fullname = $CFG->dirroot.'/pix/f/'.$filename.$postfix;
                if ($iconsize >= $size && (file_exists($fullname.'.png') || file_exists($fullname.'.gif'))) {
                    return $filename.$postfix;
                }
            }
        }
    } else if (isset($mimeinfo[$filetype][$element])) {
        return $mimeinfo[$filetype][$element];
    } else if (isset($mimeinfo['xxx'][$element])) {
        return $mimeinfo['xxx'][$element];       } else {
        return null;
    }
}


function mimeinfo_from_type($element, $mimetype) {
    
    static $cached = array();
    $mimeinfo = & get_mimetypes_array();

    if (!array_key_exists($mimetype, $cached)) {
        $cached[$mimetype] = null;
        foreach($mimeinfo as $filetype => $values) {
            if ($values['type'] == $mimetype) {
                if ($cached[$mimetype] === null) {
                    $cached[$mimetype] = '.'.$filetype;
                }
                if (!empty($values['defaulticon'])) {
                    $cached[$mimetype] = '.'.$filetype;
                    break;
                }
            }
        }
        if (empty($cached[$mimetype])) {
            $cached[$mimetype] = '.xxx';
        }
    }
    if ($element === 'extension') {
        return $cached[$mimetype];
    } else {
        return mimeinfo($element, $cached[$mimetype]);
    }
}


function file_file_icon($file, $size = null) {
    if (!is_object($file)) {
        $file = (object)$file;
    }
    if (isset($file->filename)) {
        $filename = $file->filename;
    } else if (method_exists($file, 'get_filename')) {
        $filename = $file->get_filename();
    } else if (method_exists($file, 'get_visible_name')) {
        $filename = $file->get_visible_name();
    } else {
        $filename = '';
    }
    if (isset($file->mimetype)) {
        $mimetype = $file->mimetype;
    } else if (method_exists($file, 'get_mimetype')) {
        $mimetype = $file->get_mimetype();
    } else {
        $mimetype = '';
    }
    $mimetypes = &get_mimetypes_array();
    if ($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension && !empty($mimetypes[$extension])) {
                        return file_extension_icon($filename, $size);
        }
    }
    return file_mimetype_icon($mimetype, $size);
}


function file_folder_icon($iconsize = null) {
    global $CFG;
    static $iconpostfixes = array(256=>'-256', 128=>'-128', 96=>'-96', 80=>'-80', 72=>'-72', 64=>'-64', 48=>'-48', 32=>'-32', 24=>'-24', 16=>'');
    static $cached = array();
    $iconsize = max(array(16, (int)$iconsize));
    if (!array_key_exists($iconsize, $cached)) {
        foreach ($iconpostfixes as $size => $postfix) {
            $fullname = $CFG->dirroot.'/pix/f/folder'.$postfix;
            if ($iconsize >= $size && (file_exists($fullname.'.png') || file_exists($fullname.'.gif'))) {
                $cached[$iconsize] = 'f/folder'.$postfix;
                break;
            }
        }
    }
    return $cached[$iconsize];
}


function file_mimetype_icon($mimetype, $size = NULL) {
    return 'f/'.mimeinfo_from_type('icon'.$size, $mimetype);
}


function file_extension_icon($filename, $size = NULL) {
    return 'f/'.mimeinfo('icon'.$size, $filename);
}


function get_mimetype_description($obj, $capitalise=false) {
    $filename = $mimetype = '';
    if (is_object($obj) && method_exists($obj, 'get_filename') && method_exists($obj, 'get_mimetype')) {
                $mimetype = $obj->get_mimetype();
        $filename = $obj->get_filename();
    } else if (is_object($obj) && method_exists($obj, 'get_visible_name') && method_exists($obj, 'get_mimetype')) {
                $mimetype = $obj->get_mimetype();
        $filename = $obj->get_visible_name();
    } else if (is_array($obj) || is_object ($obj)) {
        $obj = (array)$obj;
        if (!empty($obj['filename'])) {
            $filename = $obj['filename'];
        }
        if (!empty($obj['mimetype'])) {
            $mimetype = $obj['mimetype'];
        }
    } else {
        $mimetype = $obj;
    }
    $mimetypefromext = mimeinfo('type', $filename);
    if (empty($mimetype) || $mimetypefromext !== 'document/unknown') {
                $mimetype = $mimetypefromext;
    }
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (empty($extension)) {
        $mimetypestr = mimeinfo_from_type('string', $mimetype);
        $extension = str_replace('.', '', mimeinfo_from_type('extension', $mimetype));
    } else {
        $mimetypestr = mimeinfo('string', $filename);
    }
    $chunks = explode('/', $mimetype, 2);
    $chunks[] = '';
    $attr = array(
        'mimetype' => $mimetype,
        'ext' => $extension,
        'mimetype1' => $chunks[0],
        'mimetype2' => $chunks[1],
    );
    $a = array();
    foreach ($attr as $key => $value) {
        $a[$key] = $value;
        $a[strtoupper($key)] = strtoupper($value);
        $a[ucfirst($key)] = ucfirst($value);
    }

        $safemimetype = str_replace('+', '_', $mimetype);
    $safemimetypestr = str_replace('+', '_', $mimetypestr);
    $customdescription = mimeinfo('customdescription', $filename);
    if ($customdescription) {
                                        $result = format_string($customdescription, true,
                array('context' => context_system::instance()));
    } else if (get_string_manager()->string_exists($safemimetype, 'mimetypes')) {
        $result = get_string($safemimetype, 'mimetypes', (object)$a);
    } else if (get_string_manager()->string_exists($safemimetypestr, 'mimetypes')) {
        $result = get_string($safemimetypestr, 'mimetypes', (object)$a);
    } else if (get_string_manager()->string_exists('default', 'mimetypes')) {
        $result = get_string('default', 'mimetypes', (object)$a);
    } else {
        $result = $mimetype;
    }
    if ($capitalise) {
        $result=ucfirst($result);
    }
    return $result;
}


function file_get_typegroup($element, $groups) {
    static $cached = array();
    if (!is_array($groups)) {
        $groups = array($groups);
    }
    if (!array_key_exists($element, $cached)) {
        $cached[$element] = array();
    }
    $result = array();
    foreach ($groups as $group) {
        if (!array_key_exists($group, $cached[$element])) {
                        $mimeinfo = & get_mimetypes_array();
            $cached[$element][$group] = array();
            foreach ($mimeinfo as $extension => $value) {
                $value['extension'] = '.'.$extension;
                if (empty($value[$element])) {
                    continue;
                }
                if (($group === '.'.$extension || $group === $value['type'] ||
                        (!empty($value['groups']) && in_array($group, $value['groups']))) &&
                        !in_array($value[$element], $cached[$element][$group])) {
                    $cached[$element][$group][] = $value[$element];
                }
            }
        }
        $result = array_merge($result, $cached[$element][$group]);
    }
    return array_values(array_unique($result));
}


function file_extension_in_typegroup($filename, $groups, $checktype = false) {
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    if (!empty($extension) && in_array('.'.strtolower($extension), file_get_typegroup('extension', $groups))) {
        return true;
    }
    return $checktype && file_mimetype_in_typegroup(mimeinfo('type', $filename), $groups);
}


function file_mimetype_in_typegroup($mimetype, $groups) {
    return !empty($mimetype) && in_array($mimetype, file_get_typegroup('type', $groups));
}


function send_file_not_found() {
    global $CFG, $COURSE;

            if (WS_SERVER) {
        header('Access-Control-Allow-Origin: *');
    }

    send_header_404();
    print_error('filenotfound', 'error', $CFG->wwwroot.'/course/view.php?id='.$COURSE->id); }

function send_header_404() {
    if (substr(php_sapi_name(), 0, 3) == 'cgi') {
        header("Status: 404 Not Found");
    } else {
        header('HTTP/1.0 404 not found');
    }
}


function readfile_allow_large($path, $filesize = -1) {
        if ($filesize === -1) {
        $filesize = filesize($path);
    }
    if ($filesize <= 2147483647) {
                return readfile($path);
    } else {
                $handle = fopen($path, 'r');
        if ($handle === false) {
            return false;
        }
        $left = $filesize;
        while ($left > 0) {
            $size = min($left, 65536);
            $buffer = fread($handle, $size);
            if ($buffer === false) {
                return false;
            }
            echo $buffer;
            $left -= $size;
        }
        return $filesize;
    }
}


function readfile_accel($file, $mimetype, $accelerate) {
    global $CFG;

    if ($mimetype === 'text/plain') {
                header('Content-Type: text/plain; charset=utf-8');
    } else {
        header('Content-Type: '.$mimetype);
    }

    $lastmodified = is_object($file) ? $file->get_timemodified() : filemtime($file);
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', $lastmodified) .' GMT');

    if (is_object($file)) {
        header('Etag: "' . $file->get_contenthash() . '"');
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) and trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $file->get_contenthash()) {
            header('HTTP/1.1 304 Not Modified');
            return;
        }
    }

        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) and (empty($_SERVER['HTTP_IF_NONE_MATCH']) or !is_object($file))) {
                $since = strtotime(preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]));
        if ($since && $since >= $lastmodified) {
            header('HTTP/1.1 304 Not Modified');
            return;
        }
    }

    if ($accelerate and !empty($CFG->xsendfile)) {
        if (empty($CFG->disablebyteserving) and $mimetype !== 'text/plain') {
            header('Accept-Ranges: bytes');
        } else {
            header('Accept-Ranges: none');
        }

        if (is_object($file)) {
            $fs = get_file_storage();
            if ($fs->xsendfile($file->get_contenthash())) {
                return;
            }

        } else {
            require_once("$CFG->libdir/xsendfilelib.php");
            if (xsendfile($file)) {
                return;
            }
        }
    }

    $filesize = is_object($file) ? $file->get_filesize() : filesize($file);

    header('Last-Modified: '. gmdate('D, d M Y H:i:s', $lastmodified) .' GMT');

    if ($accelerate and empty($CFG->disablebyteserving) and $mimetype !== 'text/plain') {
        header('Accept-Ranges: bytes');

        if (!empty($_SERVER['HTTP_RANGE']) and strpos($_SERVER['HTTP_RANGE'],'bytes=') !== FALSE) {
                                                $ranges = false;
            if (preg_match_all('/(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $ranges, PREG_SET_ORDER)) {
                foreach ($ranges as $key=>$value) {
                    if ($ranges[$key][1] == '') {
                                                $ranges[$key][1] = $filesize - $ranges[$key][2];
                        $ranges[$key][2] = $filesize - 1;
                    } else if ($ranges[$key][2] == '' || $ranges[$key][2] > $filesize - 1) {
                                                $ranges[$key][2] = $filesize - 1;
                    }
                    if ($ranges[$key][2] != '' && $ranges[$key][2] < $ranges[$key][1]) {
                                                $ranges = false;
                        break;
                    }
                                        $ranges[$key][0] =  "\r\n--".BYTESERVING_BOUNDARY."\r\nContent-Type: $mimetype\r\n";
                    $ranges[$key][0] .= "Content-Range: bytes {$ranges[$key][1]}-{$ranges[$key][2]}/$filesize\r\n\r\n";
                }
            } else {
                $ranges = false;
            }
            if ($ranges) {
                if (is_object($file)) {
                    $handle = $file->get_content_file_handle();
                } else {
                    $handle = fopen($file, 'rb');
                }
                byteserving_send_file($handle, $mimetype, $ranges, $filesize);
            }
        }
    } else {
                header('Accept-Ranges: none');
    }

    header('Content-Length: '.$filesize);

    if ($filesize > 10000000) {
                while(@ob_get_level()) {
            if (!@ob_end_flush()) {
                break;
            }
        }
    }

        if (is_object($file)) {
        $file->readfile();
    } else {
        readfile_allow_large($file, $filesize);
    }
}


function readstring_accel($string, $mimetype, $accelerate) {
    global $CFG;

    if ($mimetype === 'text/plain') {
                header('Content-Type: text/plain; charset=utf-8');
    } else {
        header('Content-Type: '.$mimetype);
    }
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', time()) .' GMT');
    header('Accept-Ranges: none');

    if ($accelerate and !empty($CFG->xsendfile)) {
        $fs = get_file_storage();
        if ($fs->xsendfile(sha1($string))) {
            return;
        }
    }

    header('Content-Length: '.strlen($string));
    echo $string;
}


function send_temp_file($path, $filename, $pathisstring=false) {
    global $CFG;

        $mimetype = get_mimetype_for_sending($filename);

        \core\session\manager::write_close();

    if (!$pathisstring) {
        if (!file_exists($path)) {
            send_header_404();
            print_error('filenotfound', 'error', $CFG->wwwroot.'/');
        }
                core_shutdown_manager::register_function('send_temp_file_finished', array($path));
    }

        if (core_useragent::is_ie()) {
        $filename = urlencode($filename);
    }

    header('Content-Disposition: attachment; filename="'.$filename.'"');
    if (is_https()) {         header('Cache-Control: private, max-age=10, no-transform');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: ');
    } else {         header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0, no-transform');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: no-cache');
    }

        if ($pathisstring) {
        readstring_accel($path, $mimetype, false);
    } else {
        readfile_accel($path, $mimetype, false);
        @unlink($path);
    }

    die; }


function send_temp_file_finished($path) {
    if (file_exists($path)) {
        @unlink($path);
    }
}


function send_file($path, $filename, $lifetime = null , $filter=0, $pathisstring=false, $forcedownload=false, $mimetype='', $dontdie=false) {
    global $CFG, $COURSE;

    if ($dontdie) {
        ignore_user_abort(true);
    }

    if ($lifetime === 'default' or is_null($lifetime)) {
        $lifetime = $CFG->filelifetime;
    }

    \core\session\manager::write_close(); 
        if (!$mimetype || $mimetype === 'document/unknown') {
        $mimetype = get_mimetype_for_sending($filename);
    }

        if (core_useragent::is_ie()) {
        $filename = rawurlencode($filename);
    }

    if ($forcedownload) {
        header('Content-Disposition: attachment; filename="'.$filename.'"');
    } else if ($mimetype !== 'application/x-shockwave-flash') {
                
        header('Content-Disposition: inline; filename="'.$filename.'"');
    }

    if ($lifetime > 0) {
        $cacheability = ' public,';
        if (isloggedin() and !isguestuser()) {
                        $cacheability = ' private,';
        }
        $nobyteserving = false;
        header('Cache-Control:'.$cacheability.' max-age='.$lifetime.', no-transform');
        header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
        header('Pragma: ');

    } else {         $nobyteserving = true;
        if (is_https()) {             header('Cache-Control: private, max-age=10, no-transform');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: ');
        } else {             header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0, no-transform');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: no-cache');
        }
    }

    if (empty($filter)) {
                if ($pathisstring) {
            readstring_accel($path, $mimetype, !$dontdie);
        } else {
            readfile_accel($path, $mimetype, !$dontdie);
        }

    } else {
                if ($mimetype == 'text/html' || $mimetype == 'application/xhtml+xml') {
            $options = new stdClass();
            $options->noclean = true;
            $options->nocache = true;             $text = $pathisstring ? $path : implode('', file($path));

            $output = format_text($text, FORMAT_HTML, $options, $COURSE->id);

            readstring_accel($output, $mimetype, false);

        } else if (($mimetype == 'text/plain') and ($filter == 1)) {
                        $options = new stdClass();
            $options->newlines = false;
            $options->noclean = true;
            $text = htmlentities($pathisstring ? $path : implode('', file($path)), ENT_QUOTES, 'UTF-8');
            $output = '<pre>'. format_text($text, FORMAT_MOODLE, $options, $COURSE->id) .'</pre>';

            readstring_accel($output, $mimetype, false);

        } else {
                        if ($pathisstring) {
                readstring_accel($path, $mimetype, !$dontdie);
            } else {
                readfile_accel($path, $mimetype, !$dontdie);
            }
        }
    }
    if ($dontdie) {
        return;
    }
    die; }


function send_stored_file($stored_file, $lifetime=null, $filter=0, $forcedownload=false, array $options=array()) {
    global $CFG, $COURSE;

    if (empty($options['filename'])) {
        $filename = null;
    } else {
        $filename = $options['filename'];
    }

    if (empty($options['dontdie'])) {
        $dontdie = false;
    } else {
        $dontdie = true;
    }

    if ($lifetime === 'default' or is_null($lifetime)) {
        $lifetime = $CFG->filelifetime;
    }

    if (!empty($options['preview'])) {
                $fs = get_file_storage();
        $preview_file = $fs->get_file_preview($stored_file, $options['preview']);
        if (!$preview_file) {
                        if ($options['preview'] === 'tinyicon') {
                $size = 24;
            } else if ($options['preview'] === 'thumb') {
                $size = 90;
            } else {
                $size = 256;
            }
            $fileicon = file_file_icon($stored_file, $size);
            send_file($CFG->dirroot.'/pix/'.$fileicon.'.png', basename($fileicon).'.png');
        } else {
                                    $stored_file = $preview_file;
            $lifetime = DAYSECS;
            $filter = 0;
            $forcedownload = false;
        }
    }

        if ($stored_file && $stored_file->is_external_file() && !isset($options['sendcachedexternalfile'])) {
        $stored_file->send_file($lifetime, $filter, $forcedownload, $options);
        die;
    }

    if (!$stored_file or $stored_file->is_directory()) {
                if ($dontdie) {
            return;
        }
        die;
    }

    if ($dontdie) {
        ignore_user_abort(true);
    }

    \core\session\manager::write_close(); 
    $filename     = is_null($filename) ? $stored_file->get_filename() : $filename;

        $mimetype = $stored_file->get_mimetype();

        if (!$mimetype || $mimetype === 'document/unknown') {
        $mimetype = get_mimetype_for_sending($filename);
    }

        if (core_useragent::is_ie()) {
        $filename = rawurlencode($filename);
    }

    if ($forcedownload) {
        header('Content-Disposition: attachment; filename="'.$filename.'"');
    } else if ($mimetype !== 'application/x-shockwave-flash') {
                
        header('Content-Disposition: inline; filename="'.$filename.'"');
    }

    if ($lifetime > 0) {
        $cacheability = ' public,';
        if (!empty($options['cacheability']) && ($options['cacheability'] === 'public')) {
                        $cacheability = ' public,';
        } else if (!empty($options['cacheability']) && ($options['cacheability'] === 'private')) {
                        $cacheability = ' private,';
        } else if (isloggedin() and !isguestuser()) {
            $cacheability = ' private,';
        }
        header('Cache-Control:'.$cacheability.' max-age='.$lifetime.', no-transform');
        header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
        header('Pragma: ');

    } else {         if (is_https()) {             header('Cache-Control: private, max-age=10, no-transform');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: ');
        } else {             header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0, no-transform');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: no-cache');
        }
    }

            if (WS_SERVER) {
        header('Access-Control-Allow-Origin: *');
    }

    if (empty($filter)) {
                readfile_accel($stored_file, $mimetype, !$dontdie);

    } else {             if ($mimetype == 'text/html' || $mimetype == 'application/xhtml+xml') {
            $options = new stdClass();
            $options->noclean = true;
            $options->nocache = true;             $text = $stored_file->get_content();
            $output = format_text($text, FORMAT_HTML, $options, $COURSE->id);

            readstring_accel($output, $mimetype, false);

        } else if (($mimetype == 'text/plain') and ($filter == 1)) {
                        $options = new stdClass();
            $options->newlines = false;
            $options->noclean = true;
            $text = $stored_file->get_content();
            $output = '<pre>'. format_text($text, FORMAT_MOODLE, $options, $COURSE->id) .'</pre>';

            readstring_accel($output, $mimetype, false);

        } else {                readfile_accel($stored_file, $mimetype, !$dontdie);
        }
    }
    if ($dontdie) {
        return;
    }
    die; }


function get_records_csv($file, $table) {
    global $CFG, $DB;

    if (!$metacolumns = $DB->get_columns($table)) {
        return false;
    }

    if(!($handle = @fopen($file, 'r'))) {
        print_error('get_records_csv failed to open '.$file);
    }

    $fieldnames = fgetcsv($handle, 4096);
    if(empty($fieldnames)) {
        fclose($handle);
        return false;
    }

    $columns = array();

    foreach($metacolumns as $metacolumn) {
        $ord = array_search($metacolumn->name, $fieldnames);
        if(is_int($ord)) {
            $columns[$metacolumn->name] = $ord;
        }
    }

    $rows = array();

    while (($data = fgetcsv($handle, 4096)) !== false) {
        $item = new stdClass;
        foreach($columns as $name => $ord) {
            $item->$name = $data[$ord];
        }
        $rows[] = $item;
    }

    fclose($handle);
    return $rows;
}


function put_records_csv($file, $records, $table = NULL) {
    global $CFG, $DB;

    if (empty($records)) {
        return true;
    }

    $metacolumns = NULL;
    if ($table !== NULL && !$metacolumns = $DB->get_columns($table)) {
        return false;
    }

    echo "x";

    if(!($fp = @fopen($CFG->tempdir.'/'.$file, 'w'))) {
        print_error('put_records_csv failed to open '.$file);
    }

    $proto = reset($records);
    if(is_object($proto)) {
        $fields_records = array_keys(get_object_vars($proto));
    }
    else if(is_array($proto)) {
        $fields_records = array_keys($proto);
    }
    else {
        return false;
    }
    echo "x";

    if(!empty($metacolumns)) {
        $fields_table = array_map(create_function('$a', 'return $a->name;'), $metacolumns);
        $fields = array_intersect($fields_records, $fields_table);
    }
    else {
        $fields = $fields_records;
    }

    fwrite($fp, implode(',', $fields));
    fwrite($fp, "\r\n");

    foreach($records as $record) {
        $array  = (array)$record;
        $values = array();
        foreach($fields as $field) {
            if(strpos($array[$field], ',')) {
                $values[] = '"'.str_replace('"', '\"', $array[$field]).'"';
            }
            else {
                $values[] = $array[$field];
            }
        }
        fwrite($fp, implode(',', $values)."\r\n");
    }

    fclose($fp);
    @chmod($CFG->tempdir.'/'.$file, $CFG->filepermissions);
    return true;
}



function fulldelete($location) {
    if (empty($location)) {
                return false;
    }
    if (is_dir($location)) {
        if (!$currdir = opendir($location)) {
            return false;
        }
        while (false !== ($file = readdir($currdir))) {
            if ($file <> ".." && $file <> ".") {
                $fullfile = $location."/".$file;
                if (is_dir($fullfile)) {
                    if (!fulldelete($fullfile)) {
                        return false;
                    }
                } else {
                    if (!unlink($fullfile)) {
                        return false;
                    }
                }
            }
        }
        closedir($currdir);
        if (! rmdir($location)) {
            return false;
        }

    } else if (file_exists($location)) {
        if (!unlink($location)) {
            return false;
        }
    }
    return true;
}


function byteserving_send_file($handle, $mimetype, $ranges, $filesize) {
        ini_set('zlib.output_compression', 'Off');

    $chunksize = 1*(1024*1024);     if ($handle === false) {
        die;
    }
    if (count($ranges) == 1) {         $length = $ranges[0][2] - $ranges[0][1] + 1;
        header('HTTP/1.1 206 Partial content');
        header('Content-Length: '.$length);
        header('Content-Range: bytes '.$ranges[0][1].'-'.$ranges[0][2].'/'.$filesize);
        header('Content-Type: '.$mimetype);

        while(@ob_get_level()) {
            if (!@ob_end_flush()) {
                break;
            }
        }

        fseek($handle, $ranges[0][1]);
        while (!feof($handle) && $length > 0) {
            core_php_time_limit::raise(60*60);             $buffer = fread($handle, ($chunksize < $length ? $chunksize : $length));
            echo $buffer;
            flush();
            $length -= strlen($buffer);
        }
        fclose($handle);
        die;
    } else {         $totallength = 0;
        foreach($ranges as $range) {
            $totallength += strlen($range[0]) + $range[2] - $range[1] + 1;
        }
        $totallength += strlen("\r\n--".BYTESERVING_BOUNDARY."--\r\n");
        header('HTTP/1.1 206 Partial content');
        header('Content-Length: '.$totallength);
        header('Content-Type: multipart/byteranges; boundary='.BYTESERVING_BOUNDARY);

        while(@ob_get_level()) {
            if (!@ob_end_flush()) {
                break;
            }
        }

        foreach($ranges as $range) {
            $length = $range[2] - $range[1] + 1;
            echo $range[0];
            fseek($handle, $range[1]);
            while (!feof($handle) && $length > 0) {
                core_php_time_limit::raise(60*60);                 $buffer = fread($handle, ($chunksize < $length ? $chunksize : $length));
                echo $buffer;
                flush();
                $length -= strlen($buffer);
            }
        }
        echo "\r\n--".BYTESERVING_BOUNDARY."--\r\n";
        fclose($handle);
        die;
    }
}


function file_is_executable($filename) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        if (is_executable($filename)) {
            return true;
        } else {
            $fileext = strrchr($filename, '.');
                        if ($fileext && file_exists($filename) && !is_dir($filename)) {
                $winpathext = strtolower(getenv('PATHEXT'));
                $winpathexts = explode(';', $winpathext);

                return in_array(strtolower($fileext), $winpathexts);
            }

            return false;
        }
    } else {
        return is_executable($filename);
    }
}


function file_overwrite_existing_draftfile(stored_file $newfile, stored_file $existingfile) {
    if ($existingfile->get_component() != 'user' or $existingfile->get_filearea() != 'draft') {
        throw new coding_exception('The file to overwrite is not in a draft area.');
    }

    $fs = get_file_storage();
        $source = @unserialize($existingfile->get_source());
        $sortorder = $existingfile->get_sortorder();
    if ($newfile->is_external_file()) {
                if (isset($source->original) && $fs->search_references_count($source->original)) {
            throw new moodle_exception('errordoublereference', 'repository');
        }
    }

        $newfilerecord = array(
        'contextid' => $existingfile->get_contextid(),
        'component' => 'user',
        'filearea' => 'draft',
        'itemid' => $existingfile->get_itemid(),
        'timemodified' => time()
    );
    $existingfile->delete();

        $newfile = $fs->create_file_from_storedfile($newfilerecord, $newfile);
        if (isset($source->original)) {
        if (!($newfilesource = @unserialize($newfile->get_source()))) {
            $newfilesource = new stdClass();
        }
        $newfilesource->original = $source->original;
        $newfile->set_source(serialize($newfilesource));
    }
    $newfile->set_sortorder($sortorder);
}


function file_merge_files_from_draft_area_into_filearea($draftitemid, $contextid, $component, $filearea, $itemid,
                                                        array $options = null) {
        $finaldraftid = 0;
    file_prepare_draft_area($finaldraftid, $contextid, $component, $filearea, $itemid, $options);
    file_merge_draft_area_into_draft_area($draftitemid, $finaldraftid);
    file_save_draft_area_files($finaldraftid, $contextid, $component, $filearea, $itemid, $options);
}


function file_merge_draft_area_into_draft_area($getfromdraftid, $mergeintodraftid) {
    global $USER;

    $fs = get_file_storage();
    $contextid = context_user::instance($USER->id)->id;

    if (!$filestomerge = $fs->get_area_files($contextid, 'user', 'draft', $getfromdraftid)) {
        throw new coding_exception('Nothing to merge or area does not belong to current user');
    }

    $currentfiles = $fs->get_area_files($contextid, 'user', 'draft', $mergeintodraftid);

        $newhashes = array();
    foreach ($filestomerge as $filetomerge) {
        $filepath = $filetomerge->get_filepath();
        $filename = $filetomerge->get_filename();

        $newhash = $fs->get_pathname_hash($contextid, 'user', 'draft', $mergeintodraftid, $filepath, $filename);
        $newhashes[$newhash] = $filetomerge;
    }

        foreach ($currentfiles as $file) {
        $filehash = $file->get_pathnamehash();
                if (isset($newhashes[$filehash])) {
            $updatedfile = $newhashes[$filehash];

                        if ($file->get_timemodified() > $updatedfile->get_timemodified()) {
                                unset($newhashes[$filehash]);
                continue;
            }
                        file_overwrite_existing_draftfile($updatedfile, $file);
            unset($newhashes[$filehash]);
        }
    }

    foreach ($newhashes as $newfile) {
        $newfilerecord = array(
            'contextid' => $contextid,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $mergeintodraftid,
            'timemodified' => time()
        );

        $fs->create_file_from_storedfile($newfilerecord, $newfile);
    }
}


class curl {
    
    public  $cache    = false;
    
    public  $proxy    = null;
    
    public  $version  = '0.4 dev';
    
    public  $response = array();
    
    public $rawresponse = array();
    
    public  $header   = array();
    
    public  $info;
    
    public  $error;
    
    public  $errno;
    
    public $emulateredirects = null;

    
    private $options;

    
    private $proxy_host = '';
    
    private $proxy_auth = '';
    
    private $proxy_type = '';
    
    private $debug    = false;
    
    private $cookie   = false;
    
    private $responsefinished = false;

    
    public function __construct($settings = array()) {
        global $CFG;
        if (!function_exists('curl_init')) {
            $this->error = 'cURL module must be enabled!';
            trigger_error($this->error, E_USER_ERROR);
            return false;
        }

                $this->resetopt();
        if (!empty($settings['debug'])) {
            $this->debug = true;
        }
        if (!empty($settings['cookie'])) {
            if($settings['cookie'] === true) {
                $this->cookie = $CFG->dataroot.'/curl_cookie.txt';
            } else {
                $this->cookie = $settings['cookie'];
            }
        }
        if (!empty($settings['cache'])) {
            if (class_exists('curl_cache')) {
                if (!empty($settings['module_cache'])) {
                    $this->cache = new curl_cache($settings['module_cache']);
                } else {
                    $this->cache = new curl_cache('misc');
                }
            }
        }
        if (!empty($CFG->proxyhost)) {
            if (empty($CFG->proxyport)) {
                $this->proxy_host = $CFG->proxyhost;
            } else {
                $this->proxy_host = $CFG->proxyhost.':'.$CFG->proxyport;
            }
            if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
                $this->proxy_auth = $CFG->proxyuser.':'.$CFG->proxypassword;
                $this->setopt(array(
                            'proxyauth'=> CURLAUTH_BASIC | CURLAUTH_NTLM,
                            'proxyuserpwd'=>$this->proxy_auth));
            }
            if (!empty($CFG->proxytype)) {
                if ($CFG->proxytype == 'SOCKS5') {
                    $this->proxy_type = CURLPROXY_SOCKS5;
                } else {
                    $this->proxy_type = CURLPROXY_HTTP;
                    $this->setopt(array('httpproxytunnel'=>false));
                }
                $this->setopt(array('proxytype'=>$this->proxy_type));
            }

            if (isset($settings['proxy'])) {
                $this->proxy = $settings['proxy'];
            }
        } else {
            $this->proxy = false;
        }

        if (!isset($this->emulateredirects)) {
            $this->emulateredirects = ini_get('open_basedir');
        }
    }

    
    public function resetopt() {
        $this->options = array();
        $this->options['CURLOPT_USERAGENT']         = 'MoodleBot/1.0';
                $this->options['CURLOPT_HEADER']            = 0;
                $this->options['CURLOPT_NOBODY']            = 0;
                $this->options['CURLOPT_FOLLOWLOCATION']    = 1;
        $this->options['CURLOPT_MAXREDIRS']         = 10;
        $this->options['CURLOPT_ENCODING']          = '';
                        $this->options['CURLOPT_RETURNTRANSFER']    = 1;
        $this->options['CURLOPT_SSL_VERIFYPEER']    = 0;
        $this->options['CURLOPT_SSL_VERIFYHOST']    = 2;
        $this->options['CURLOPT_CONNECTTIMEOUT']    = 30;

        if ($cacert = self::get_cacert()) {
            $this->options['CURLOPT_CAINFO'] = $cacert;
        }
    }

    
    public static function get_cacert() {
        global $CFG;

                if (is_readable("$CFG->dataroot/moodleorgca.crt")) {
            return realpath("$CFG->dataroot/moodleorgca.crt");
        }

                $cacert = ini_get('curl.cainfo');
        if (!empty($cacert) and is_readable($cacert)) {
            return realpath($cacert);
        }

                if ($CFG->ostype === 'WINDOWS') {
            if (is_readable("$CFG->libdir/cacert.pem")) {
                return realpath("$CFG->libdir/cacert.pem");
            }
        }

                return null;
    }

    
    public function resetcookie() {
        if (!empty($this->cookie)) {
            if (is_file($this->cookie)) {
                $fp = fopen($this->cookie, 'w');
                if (!empty($fp)) {
                    fwrite($fp, '');
                    fclose($fp);
                }
            }
        }
    }

    
    public function setopt($options = array()) {
        if (is_array($options)) {
            foreach ($options as $name => $val) {
                if (!is_string($name)) {
                    throw new coding_exception('Curl options should be defined using strings, not constant values.');
                }
                if (stripos($name, 'CURLOPT_') === false) {
                    $name = strtoupper('CURLOPT_'.$name);
                } else {
                    $name = strtoupper($name);
                }
                $this->options[$name] = $val;
            }
        }
    }

    
    public function cleanopt() {
        unset($this->options['CURLOPT_HTTPGET']);
        unset($this->options['CURLOPT_POST']);
        unset($this->options['CURLOPT_POSTFIELDS']);
        unset($this->options['CURLOPT_PUT']);
        unset($this->options['CURLOPT_INFILE']);
        unset($this->options['CURLOPT_INFILESIZE']);
        unset($this->options['CURLOPT_CUSTOMREQUEST']);
        unset($this->options['CURLOPT_FILE']);
    }

    
    public function resetHeader() {
        $this->header = array();
    }

    
    public function setHeader($header) {
        if (is_array($header)) {
            foreach ($header as $v) {
                $this->setHeader($v);
            }
        } else {
                        $this->header[] = preg_replace('/[\r\n]/', '', $header);
        }
    }

    
    public function getResponse() {
        return $this->response;
    }

    
    public function get_raw_response() {
        return $this->rawresponse;
    }

    
    private function formatHeader($ch, $header) {
        $this->rawresponse[] = $header;

        if (trim($header, "\r\n") === '') {
                        $this->responsefinished = true;
        }

        if (strlen($header) > 2) {
            if ($this->responsefinished) {
                                                $this->responsefinished = false;
                $this->response = array();
            }
            $parts = explode(" ", rtrim($header, "\r\n"), 2);
            $key = rtrim($parts[0], ':');
            $value = isset($parts[1]) ? $parts[1] : null;
            if (!empty($this->response[$key])) {
                if (is_array($this->response[$key])) {
                    $this->response[$key][] = $value;
                } else {
                    $tmp = $this->response[$key];
                    $this->response[$key] = array();
                    $this->response[$key][] = $tmp;
                    $this->response[$key][] = $value;

                }
            } else {
                $this->response[$key] = $value;
            }
        }
        return strlen($header);
    }

    
    private function apply_opt($curl, $options) {
                $this->cleanopt();
                if (!empty($this->cookie) || !empty($options['cookie'])) {
            $this->setopt(array('cookiejar'=>$this->cookie,
                            'cookiefile'=>$this->cookie
                             ));
        }

                if ($this->proxy === null) {
            if (!empty($this->options['CURLOPT_URL']) and is_proxybypass($this->options['CURLOPT_URL'])) {
                $proxy = false;
            } else {
                $proxy = true;
            }
        } else {
            $proxy = (bool)$this->proxy;
        }

                if ($proxy) {
            $options['CURLOPT_PROXY'] = $this->proxy_host;
        } else {
            unset($this->options['CURLOPT_PROXY']);
        }

        $this->setopt($options);

                curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this,'formatHeader'));

                $useragent = '';

        if (!empty($options['CURLOPT_USERAGENT'])) {
            $useragent = $options['CURLOPT_USERAGENT'];
        } else if (!empty($this->options['CURLOPT_USERAGENT'])) {
            $useragent = $this->options['CURLOPT_USERAGENT'];
        } else {
            $useragent = 'MoodleBot/1.0';
        }

                if (empty($this->header)) {
            $this->setHeader(array(
                'User-Agent: ' . $useragent,
                'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'Connection: keep-alive'
                ));
        } else if (!in_array('User-Agent: ' . $useragent, $this->header)) {
                                    if ($match = preg_grep('/User-Agent.*/', $this->header)) {
                $key = array_keys($match)[0];
                unset($this->header[$key]);
            }
            $this->setHeader(array('User-Agent: ' . $useragent));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);

        if ($this->debug) {
            echo '<h1>Options</h1>';
            var_dump($this->options);
            echo '<h1>Header</h1>';
            var_dump($this->header);
        }

                if (!isset($this->options['CURLOPT_MAXREDIRS'])) {
            $this->options['CURLOPT_MAXREDIRS'] = 0;
        } else if ($this->options['CURLOPT_MAXREDIRS'] > 100) {
            $this->options['CURLOPT_MAXREDIRS'] = 100;
        } else {
            $this->options['CURLOPT_MAXREDIRS'] = (int)$this->options['CURLOPT_MAXREDIRS'];
        }

                if (!isset($this->options['CURLOPT_FOLLOWLOCATION'])) {
            $this->options['CURLOPT_FOLLOWLOCATION'] = 0;
        }

                if (defined('CURLOPT_PROTOCOLS')) {
            $this->options['CURLOPT_PROTOCOLS'] = (CURLPROTO_HTTP | CURLPROTO_HTTPS);
            $this->options['CURLOPT_REDIR_PROTOCOLS'] = (CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }

                foreach($this->options as $name => $val) {
            if ($name === 'CURLOPT_FOLLOWLOCATION' and $this->emulateredirects) {
                                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
                continue;
            }
            $name = constant($name);
            curl_setopt($curl, $name, $val);
        }

        return $curl;
    }

    
    public function download($requests, $options = array()) {
        $options['RETURNTRANSFER'] = false;
        return $this->multi($requests, $options);
    }

    
    protected function multi($requests, $options = array()) {
        $count   = count($requests);
        $handles = array();
        $results = array();
        $main    = curl_multi_init();
        for ($i = 0; $i < $count; $i++) {
            if (!empty($requests[$i]['filepath']) and empty($requests[$i]['file'])) {
                                $requests[$i]['file'] = fopen($requests[$i]['filepath'], 'w');
                $requests[$i]['auto-handle'] = true;
            }
            foreach($requests[$i] as $n=>$v) {
                $options[$n] = $v;
            }
            $handles[$i] = curl_init($requests[$i]['url']);
            $this->apply_opt($handles[$i], $options);
            curl_multi_add_handle($main, $handles[$i]);
        }
        $running = 0;
        do {
            curl_multi_exec($main, $running);
        } while($running > 0);
        for ($i = 0; $i < $count; $i++) {
            if (!empty($options['CURLOPT_RETURNTRANSFER'])) {
                $results[] = true;
            } else {
                $results[] = curl_multi_getcontent($handles[$i]);
            }
            curl_multi_remove_handle($main, $handles[$i]);
        }
        curl_multi_close($main);

        for ($i = 0; $i < $count; $i++) {
            if (!empty($requests[$i]['filepath']) and !empty($requests[$i]['auto-handle'])) {
                                fclose($requests[$i]['file']);
            }
        }
        return $results;
    }

    
    protected function request($url, $options = array()) {
                $this->setopt(array('CURLOPT_URL' => $url));

                $curl = curl_init();

                $this->info             = array();
        $this->error            = '';
        $this->errno            = 0;
        $this->response         = array();
        $this->rawresponse      = array();
        $this->responsefinished = false;

        $this->apply_opt($curl, $options);
        if ($this->cache && $ret = $this->cache->get($this->options)) {
            return $ret;
        }

        $ret = curl_exec($curl);
        $this->info  = curl_getinfo($curl);
        $this->error = curl_error($curl);
        $this->errno = curl_errno($curl);
        
        if ($this->emulateredirects and $this->options['CURLOPT_FOLLOWLOCATION'] and $this->info['http_code'] != 200) {
            $redirects = 0;

            while($redirects <= $this->options['CURLOPT_MAXREDIRS']) {

                if ($this->info['http_code'] == 301) {
                    
                } else if ($this->info['http_code'] == 302) {
                    
                } else if ($this->info['http_code'] == 303) {
                                        if (empty($this->options['CURLOPT_HTTPGET'])) {
                        break;
                    }

                } else if ($this->info['http_code'] == 307) {
                    
                } else if ($this->info['http_code'] == 308) {
                    
                } else {
                                        break;
                }

                $redirects++;

                $redirecturl = null;
                if (isset($this->info['redirect_url'])) {
                    if (preg_match('|^https?://|i', $this->info['redirect_url'])) {
                        $redirecturl = $this->info['redirect_url'];
                    }
                }
                if (!$redirecturl) {
                    foreach ($this->response as $k => $v) {
                        if (strtolower($k) === 'location') {
                            $redirecturl = $v;
                            break;
                        }
                    }
                    if (preg_match('|^https?://|i', $redirecturl)) {
                        
                    } else if ($redirecturl) {
                        $current = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
                        if (strpos($redirecturl, '/') === 0) {
                                                        $pos = strpos('/', $current, 8);
                            if ($pos === false) {
                                $redirecturl = $current.$redirecturl;
                            } else {
                                $redirecturl = substr($current, 0, $pos).$redirecturl;
                            }
                        } else {
                                                        $redirecturl = dirname($current).'/'.$redirecturl;
                        }
                    }
                }

                curl_setopt($curl, CURLOPT_URL, $redirecturl);
                $ret = curl_exec($curl);

                $this->info  = curl_getinfo($curl);
                $this->error = curl_error($curl);
                $this->errno = curl_errno($curl);

                $this->info['redirect_count'] = $redirects;

                if ($this->info['http_code'] === 200) {
                                        break;
                }
                if ($this->errno != CURLE_OK) {
                                        break;
                }
            }
            if ($redirects > $this->options['CURLOPT_MAXREDIRS']) {
                $this->errno = CURLE_TOO_MANY_REDIRECTS;
                $this->error = 'Maximum ('.$this->options['CURLOPT_MAXREDIRS'].') redirects followed';
            }
        }

        if ($this->cache) {
            $this->cache->set($this->options, $ret);
        }

        if ($this->debug) {
            echo '<h1>Return Data</h1>';
            var_dump($ret);
            echo '<h1>Info</h1>';
            var_dump($this->info);
            echo '<h1>Error</h1>';
            var_dump($this->error);
        }

        curl_close($curl);

        if (empty($this->error)) {
            return $ret;
        } else {
            return $this->error;
                                }
    }

    
    public function head($url, $options = array()) {
        $options['CURLOPT_HTTPGET'] = 0;
        $options['CURLOPT_HEADER']  = 1;
        $options['CURLOPT_NOBODY']  = 1;
        return $this->request($url, $options);
    }

    
    public function post($url, $params = '', $options = array()) {
        $options['CURLOPT_POST']       = 1;
        if (is_array($params)) {
            $this->_tmp_file_post_params = array();
            foreach ($params as $key => $value) {
                if ($value instanceof stored_file) {
                    $value->add_to_curl_request($this, $key);
                } else {
                    $this->_tmp_file_post_params[$key] = $value;
                }
            }
            $options['CURLOPT_POSTFIELDS'] = $this->_tmp_file_post_params;
            unset($this->_tmp_file_post_params);
        } else {
                        $options['CURLOPT_POSTFIELDS'] = $params;
        }
        return $this->request($url, $options);
    }

    
    public function get($url, $params = array(), $options = array()) {
        $options['CURLOPT_HTTPGET'] = 1;

        if (!empty($params)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= http_build_query($params, '', '&');
        }
        return $this->request($url, $options);
    }

    
    public function download_one($url, $params, $options = array()) {
        $options['CURLOPT_HTTPGET'] = 1;
        if (!empty($params)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= http_build_query($params, '', '&');
        }
        if (!empty($options['filepath']) && empty($options['file'])) {
                        if (!($options['file'] = fopen($options['filepath'], 'w'))) {
                $this->errno = 100;
                return get_string('cannotwritefile', 'error', $options['filepath']);
            }
            $filepath = $options['filepath'];
        }
        unset($options['filepath']);
        $result = $this->request($url, $options);
        if (isset($filepath)) {
            fclose($options['file']);
            if ($result !== true) {
                unlink($filepath);
            }
        }
        return $result;
    }

    
    public function put($url, $params = array(), $options = array()) {
        $file = $params['file'];
        if (!is_file($file)) {
            return null;
        }
        $fp   = fopen($file, 'r');
        $size = filesize($file);
        $options['CURLOPT_PUT']        = 1;
        $options['CURLOPT_INFILESIZE'] = $size;
        $options['CURLOPT_INFILE']     = $fp;
        if (!isset($this->options['CURLOPT_USERPWD'])) {
            $this->setopt(array('CURLOPT_USERPWD'=>'anonymous: noreply@moodle.org'));
        }
        $ret = $this->request($url, $options);
        fclose($fp);
        return $ret;
    }

    
    public function delete($url, $param = array(), $options = array()) {
        $options['CURLOPT_CUSTOMREQUEST'] = 'DELETE';
        if (!isset($options['CURLOPT_USERPWD'])) {
            $options['CURLOPT_USERPWD'] = 'anonymous: noreply@moodle.org';
        }
        $ret = $this->request($url, $options);
        return $ret;
    }

    
    public function trace($url, $options = array()) {
        $options['CURLOPT_CUSTOMREQUEST'] = 'TRACE';
        $ret = $this->request($url, $options);
        return $ret;
    }

    
    public function options($url, $options = array()) {
        $options['CURLOPT_CUSTOMREQUEST'] = 'OPTIONS';
        $ret = $this->request($url, $options);
        return $ret;
    }

    
    public function get_info() {
        return $this->info;
    }

    
    public function get_errno() {
        return $this->errno;
    }

    
    public static function strip_double_headers($input) {
                                                                $crlf = "\r\n";
        return preg_replace(
                                '~^HTTP/1\..*' . $crlf .
                                                '(?:[\x21-\x39\x3b-\x7e]+:[^' . $crlf . ']+' . $crlf . ')*' .
                                $crlf .
                                '(HTTP/1.[01] 200 )~', '$1', $input);
    }
}


class curl_cache {
    
    public $dir = '';

    
    public function __construct($module = 'repository') {
        global $CFG;
        if (!empty($module)) {
            $this->dir = $CFG->cachedir.'/'.$module.'/';
        } else {
            $this->dir = $CFG->cachedir.'/misc/';
        }
        if (!file_exists($this->dir)) {
            mkdir($this->dir, $CFG->directorypermissions, true);
        }
        if ($module == 'repository') {
            if (empty($CFG->repositorycacheexpire)) {
                $CFG->repositorycacheexpire = 120;
            }
            $this->ttl = $CFG->repositorycacheexpire;
        } else {
            if (empty($CFG->curlcache)) {
                $CFG->curlcache = 120;
            }
            $this->ttl = $CFG->curlcache;
        }
    }

    
    public function get($param) {
        global $CFG, $USER;
        $this->cleanup($this->ttl);
        $filename = 'u'.$USER->id.'_'.md5(serialize($param));
        if(file_exists($this->dir.$filename)) {
            $lasttime = filemtime($this->dir.$filename);
            if (time()-$lasttime > $this->ttl) {
                return false;
            } else {
                $fp = fopen($this->dir.$filename, 'r');
                $size = filesize($this->dir.$filename);
                $content = fread($fp, $size);
                return unserialize($content);
            }
        }
        return false;
    }

    
    public function set($param, $val) {
        global $CFG, $USER;
        $filename = 'u'.$USER->id.'_'.md5(serialize($param));
        $fp = fopen($this->dir.$filename, 'w');
        fwrite($fp, serialize($val));
        fclose($fp);
        @chmod($this->dir.$filename, $CFG->filepermissions);
    }

    
    public function cleanup($expire) {
        if ($dir = opendir($this->dir)) {
            while (false !== ($file = readdir($dir))) {
                if(!is_dir($file) && $file != '.' && $file != '..') {
                    $lasttime = @filemtime($this->dir.$file);
                    if (time() - $lasttime > $expire) {
                        @unlink($this->dir.$file);
                    }
                }
            }
            closedir($dir);
        }
    }
    
    public function refresh() {
        global $CFG, $USER;
        if ($dir = opendir($this->dir)) {
            while (false !== ($file = readdir($dir))) {
                if (!is_dir($file) && $file != '.' && $file != '..') {
                    if (strpos($file, 'u'.$USER->id.'_') !== false) {
                        @unlink($this->dir.$file);
                    }
                }
            }
        }
    }
}


function file_pluginfile($relativepath, $forcedownload, $preview = null) {
    global $DB, $CFG, $USER;
        if (!$relativepath) {
        print_error('invalidargorconf');
    } else if ($relativepath[0] != '/') {
        print_error('pathdoesnotstartslash');
    }

        $args = explode('/', ltrim($relativepath, '/'));

    if (count($args) < 3) {         print_error('invalidarguments');
    }

    $contextid = (int)array_shift($args);
    $component = clean_param(array_shift($args), PARAM_COMPONENT);
    $filearea  = clean_param(array_shift($args), PARAM_AREA);

    list($context, $course, $cm) = get_context_info_array($contextid);

    $fs = get_file_storage();

        if ($component === 'blog') {
                if ($context->contextlevel != CONTEXT_SYSTEM) {
            send_file_not_found();
        }
        if ($filearea !== 'attachment' and $filearea !== 'post') {
            send_file_not_found();
        }

        if (empty($CFG->enableblogs)) {
            print_error('siteblogdisable', 'blog');
        }

        $entryid = (int)array_shift($args);
        if (!$entry = $DB->get_record('post', array('module'=>'blog', 'id'=>$entryid))) {
            send_file_not_found();
        }
        if ($CFG->bloglevel < BLOG_GLOBAL_LEVEL) {
            require_login();
            if (isguestuser()) {
                print_error('noguest');
            }
            if ($CFG->bloglevel == BLOG_USER_LEVEL) {
                if ($USER->id != $entry->userid) {
                    send_file_not_found();
                }
            }
        }

        if ($entry->publishstate === 'public') {
            if ($CFG->forcelogin) {
                require_login();
            }

        } else if ($entry->publishstate === 'site') {
            require_login();
                    } else if ($entry->publishstate === 'draft') {
            require_login();
            if ($USER->id != $entry->userid) {
                send_file_not_found();
            }
        }

        $filename = array_pop($args);
        $filepath = $args ? '/'.implode('/', $args).'/' : '/';

        if (!$file = $fs->get_file($context->id, $component, $filearea, $entryid, $filepath, $filename) or $file->is_directory()) {
            send_file_not_found();
        }

        send_stored_file($file, 10*60, 0, true, array('preview' => $preview)); 
        } else if ($component === 'grade') {
        if (($filearea === 'outcome' or $filearea === 'scale') and $context->contextlevel == CONTEXT_SYSTEM) {
                        if ($CFG->forcelogin) {
                require_login();
            }

            $fullpath = "/$context->id/$component/$filearea/".implode('/', $args);

            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else if ($filearea === 'feedback' and $context->contextlevel == CONTEXT_COURSE) {
                        send_file_not_found();

            if ($CFG->forcelogin || $course->id != SITEID) {
                require_login($course);
            }

            $fullpath = "/$context->id/$component/$filearea/".implode('/', $args);

            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));
        } else {
            send_file_not_found();
        }

        } else if ($component === 'tag') {
        if ($filearea === 'description' and $context->contextlevel == CONTEXT_SYSTEM) {

                        if ($CFG->forcelogin) {
                require_login();
            }

            $fullpath = "/$context->id/tag/description/".implode('/', $args);

            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, true, array('preview' => $preview));

        } else {
            send_file_not_found();
        }
        } else if ($component === 'badges') {
        require_once($CFG->libdir . '/badgeslib.php');

        $badgeid = (int)array_shift($args);
        $badge = new badge($badgeid);
        $filename = array_pop($args);

        if ($filearea === 'badgeimage') {
            if ($filename !== 'f1' && $filename !== 'f2') {
                send_file_not_found();
            }
            if (!$file = $fs->get_file($context->id, 'badges', 'badgeimage', $badge->id, '/', $filename.'.png')) {
                send_file_not_found();
            }

            \core\session\manager::write_close();
            send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));
        } else if ($filearea === 'userbadge'  and $context->contextlevel == CONTEXT_USER) {
            if (!$file = $fs->get_file($context->id, 'badges', 'userbadge', $badge->id, '/', $filename.'.png')) {
                send_file_not_found();
            }

            \core\session\manager::write_close();
            send_stored_file($file, 60*60, 0, true, array('preview' => $preview));
        }
        } else if ($component === 'calendar') {
        if ($filearea === 'event_description'  and $context->contextlevel == CONTEXT_SYSTEM) {

                        if ($CFG->forcelogin) {
                require_login();
            }

                        $eventid = array_shift($args);

                        if (!$event = $DB->get_record('event', array('id'=>(int)$eventid, 'eventtype'=>'site'))) {
                send_file_not_found();
            }

                        $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, $component, $filearea, $eventid, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else if ($filearea === 'event_description' and $context->contextlevel == CONTEXT_USER) {

                        require_login();

                        if (isguestuser()) {
                send_file_not_found();
            }

                        $eventid = array_shift($args);

                        if (!$event = $DB->get_record('event', array('id'=>(int)$eventid, 'userid'=>$USER->id, 'eventtype'=>'user'))) {
                send_file_not_found();
            }

                        $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, $component, $filearea, $eventid, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 0, 0, true, array('preview' => $preview));

        } else if ($filearea === 'event_description' and $context->contextlevel == CONTEXT_COURSE) {

                                    if ($CFG->forcelogin || $course->id != SITEID) {
                require_login($course);
            }

                        if ($course->id != SITEID && (!is_enrolled($context)) && (!is_viewing($context))) {
                                send_file_not_found();
            }

                        $eventid = array_shift($args);

                                                            if (!$event = $DB->get_record('event', array('id'=>(int)$eventid, 'courseid'=>$course->id))) {
                send_file_not_found();
            }

                        if ($event->eventtype === 'group') {
                if (!has_capability('moodle/site:accessallgroups', $context) && !groups_is_member($event->groupid, $USER->id)) {
                    send_file_not_found();
                }
            } else if ($event->eventtype === 'course' || $event->eventtype === 'site') {
                            } else {
                                send_file_not_found();
            }

                        $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, $component, $filearea, $eventid, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else {
            send_file_not_found();
        }

        } else if ($component === 'user') {
        if ($filearea === 'icon' and $context->contextlevel == CONTEXT_USER) {
            if (count($args) == 1) {
                $themename = theme_config::DEFAULT_THEME;
                $filename = array_shift($args);
            } else {
                $themename = array_shift($args);
                $filename = array_shift($args);
            }

                        if ($filename !== 'f1' and $filename !== 'f2' and $filename !== 'f3') {
                $filename = 'f1';
            }

            if ((!empty($CFG->forcelogin) and !isloggedin()) ||
                    (!empty($CFG->forceloginforprofileimage) && (!isloggedin() || isguestuser()))) {
                                                                $theme = theme_config::load($themename);
                redirect($theme->pix_url('u/'.$filename, 'moodle'));             }

            if (!$file = $fs->get_file($context->id, 'user', 'icon', 0, '/', $filename.'.png')) {
                if (!$file = $fs->get_file($context->id, 'user', 'icon', 0, '/', $filename.'.jpg')) {
                    if ($filename === 'f3') {
                                                if (!$file = $fs->get_file($context->id, 'user', 'icon', 0, '/', 'f1.png')) {
                            $file = $fs->get_file($context->id, 'user', 'icon', 0, '/', 'f1.jpg');
                        }
                    }
                }
            }
            if (!$file) {
                                if ($user = $DB->get_record('user', array('id'=>$context->instanceid), 'id, picture')) {
                    if ($user->picture > 0) {
                        $DB->set_field('user', 'picture', 0, array('id'=>$user->id));
                    }
                }
                                $theme = theme_config::load($themename);
                $imagefile = $theme->resolve_image_location('u/'.$filename, 'moodle', null);
                send_file($imagefile, basename($imagefile), 60*60*24*14);
            }

            $options = array('preview' => $preview);
            if (empty($CFG->forcelogin) && empty($CFG->forceloginforprofileimage)) {
                                                $options['cacheability'] = 'public';
            }
            send_stored_file($file, 60*60*24*365, 0, false, $options); 
        } else if ($filearea === 'private' and $context->contextlevel == CONTEXT_USER) {
            require_login();

            if (isguestuser()) {
                send_file_not_found();
            }

            if ($USER->id !== $context->instanceid) {
                send_file_not_found();
            }

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, $component, $filearea, 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 0, 0, true, array('preview' => $preview)); 
        } else if ($filearea === 'profile' and $context->contextlevel == CONTEXT_USER) {

            if ($CFG->forcelogin) {
                require_login();
            }

            $userid = $context->instanceid;

            if ($USER->id == $userid) {
                
            } else if (!empty($CFG->forceloginforprofiles)) {
                require_login();

                if (isguestuser()) {
                    send_file_not_found();
                }

                                if (!has_coursecontact_role($userid) && !has_capability('moodle/user:viewdetails', $context)) {
                    send_file_not_found();
                }

                $canview = false;
                if (has_capability('moodle/user:viewdetails', $context)) {
                    $canview = true;
                } else {
                    $courses = enrol_get_my_courses();
                }

                while (!$canview && count($courses) > 0) {
                    $course = array_shift($courses);
                    if (has_capability('moodle/user:viewdetails', context_course::instance($course->id))) {
                        $canview = true;
                    }
                }
            }

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, $component, $filearea, 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 0, 0, true, array('preview' => $preview)); 
        } else if ($filearea === 'profile' and $context->contextlevel == CONTEXT_COURSE) {
            $userid = (int)array_shift($args);
            $usercontext = context_user::instance($userid);

            if ($CFG->forcelogin) {
                require_login();
            }

            if (!empty($CFG->forceloginforprofiles)) {
                require_login();
                if (isguestuser()) {
                    print_error('noguest');
                }

                                if (!has_coursecontact_role($userid) and !has_capability('moodle/user:viewdetails', $usercontext)) {
                    print_error('usernotavailable');
                }
                if (!has_capability('moodle/user:viewdetails', $context) && !has_capability('moodle/user:viewdetails', $usercontext)) {
                    print_error('cannotviewprofile');
                }
                if (!is_enrolled($context, $userid)) {
                    print_error('notenrolledprofile');
                }
                if (groups_get_course_groupmode($course) == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                    print_error('groupnotamember');
                }
            }

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($usercontext->id, 'user', 'profile', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 0, 0, true, array('preview' => $preview)); 
        } else if ($filearea === 'backup' and $context->contextlevel == CONTEXT_USER) {
            require_login();

            if (isguestuser()) {
                send_file_not_found();
            }
            $userid = $context->instanceid;

            if ($USER->id != $userid) {
                send_file_not_found();
            }

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'user', 'backup', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 0, 0, true, array('preview' => $preview)); 
        } else {
            send_file_not_found();
        }

        } else if ($component === 'coursecat') {
        if ($context->contextlevel != CONTEXT_COURSECAT) {
            send_file_not_found();
        }

        if ($filearea === 'description') {
            if ($CFG->forcelogin) {
                                require_login();
            }

                        if (!has_capability('moodle/category:viewhiddencategories', $context)) {
                $coursecatvisible = $DB->get_field('course_categories', 'visible', array('id' => $context->instanceid));
                if (!$coursecatvisible) {
                    send_file_not_found();
                }
            }

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'coursecat', 'description', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));
        } else {
            send_file_not_found();
        }

        } else if ($component === 'course') {
        if ($context->contextlevel != CONTEXT_COURSE) {
            send_file_not_found();
        }

        if ($filearea === 'summary' || $filearea === 'overviewfiles'
            || $filearea === 'outline' || $filearea === 'point' || $filearea === 'officehour' 
            
            ) {
            if ($CFG->forcelogin) {
                require_login();
            }

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'course', $filearea, 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else if ($filearea === 'section') {
            if ($CFG->forcelogin) {
                require_login($course);
            } else if ($course->id != SITEID) {
                require_login($course);
            }

            $sectionid = (int)array_shift($args);

            if (!$section = $DB->get_record('course_sections', array('id'=>$sectionid, 'course'=>$course->id))) {
                send_file_not_found();
            }

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'course', 'section', $sectionid, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else {
            send_file_not_found();
        }

    } else if ($component === 'cohort') {

        $cohortid = (int)array_shift($args);
        $cohort = $DB->get_record('cohort', array('id' => $cohortid), '*', MUST_EXIST);
        $cohortcontext = context::instance_by_id($cohort->contextid);

                if ($context->id != $cohort->contextid &&
            ($context->contextlevel != CONTEXT_COURSE || !in_array($cohort->contextid, $context->get_parent_context_ids()))) {
            send_file_not_found();
        }

                        $canview = has_capability('moodle/cohort:view', $cohortcontext) ||
                ($cohort->visible && has_capability('moodle/cohort:view', $context));

        if ($filearea === 'description' && $canview) {
            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (($file = $fs->get_file($cohortcontext->id, 'cohort', 'description', $cohort->id, $filepath, $filename))
                    && !$file->is_directory()) {
                \core\session\manager::write_close();                 send_stored_file($file, 60 * 60, 0, $forcedownload, array('preview' => $preview));
            }
        }

        send_file_not_found();

    } else if ($component === 'group') {
        if ($context->contextlevel != CONTEXT_COURSE) {
            send_file_not_found();
        }

        require_course_login($course, true, null, false);

        $groupid = (int)array_shift($args);

        $group = $DB->get_record('groups', array('id'=>$groupid, 'courseid'=>$course->id), '*', MUST_EXIST);
        if (($course->groupmodeforce and $course->groupmode == SEPARATEGROUPS) and !has_capability('moodle/site:accessallgroups', $context) and !groups_is_member($group->id, $USER->id)) {
                        send_file_not_found();
        }

        if ($filearea === 'description') {

            require_login($course);

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'group', 'description', $group->id, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else if ($filearea === 'icon') {
            $filename = array_pop($args);

            if ($filename !== 'f1' and $filename !== 'f2') {
                send_file_not_found();
            }
            if (!$file = $fs->get_file($context->id, 'group', 'icon', $group->id, '/', $filename.'.png')) {
                if (!$file = $fs->get_file($context->id, 'group', 'icon', $group->id, '/', $filename.'.jpg')) {
                    send_file_not_found();
                }
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, false, array('preview' => $preview));

        } else {
            send_file_not_found();
        }

    } else if ($component === 'grouping') {
        if ($context->contextlevel != CONTEXT_COURSE) {
            send_file_not_found();
        }

        require_login($course);

        $groupingid = (int)array_shift($args);

                if ($filearea === 'description') {

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'grouping', 'description', $groupingid, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else {
            send_file_not_found();
        }

        } else if ($component === 'backup') {
        if ($filearea === 'course' and $context->contextlevel == CONTEXT_COURSE) {
            require_login($course);
            require_capability('moodle/backup:downloadfile', $context);

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'backup', 'course', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 0, 0, $forcedownload, array('preview' => $preview));

        } else if ($filearea === 'section' and $context->contextlevel == CONTEXT_COURSE) {
            require_login($course);
            require_capability('moodle/backup:downloadfile', $context);

            $sectionid = (int)array_shift($args);

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'backup', 'section', $sectionid, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();
            send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else if ($filearea === 'activity' and $context->contextlevel == CONTEXT_MODULE) {
            require_login($course, false, $cm);
            require_capability('moodle/backup:downloadfile', $context);

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'backup', 'activity', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();
            send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));

        } else if ($filearea === 'automated' and $context->contextlevel == CONTEXT_COURSE) {
            
            require_login($course);
            require_capability('moodle/site:config', $context);

            $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'backup', 'automated', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 0, 0, $forcedownload, array('preview' => $preview));

        } else {
            send_file_not_found();
        }

        } else if ($component === 'question') {
        require_once($CFG->libdir . '/questionlib.php');
        question_pluginfile($course, $context, 'question', $filearea, $args, $forcedownload);
        send_file_not_found();

        } else if ($component === 'grading') {
        if ($filearea === 'description') {
            
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                require_login();

            } else if ($context->contextlevel >= CONTEXT_COURSE) {
                require_login($course, false, $cm);

            } else {
                send_file_not_found();
            }

            $formid = (int)array_shift($args);

            $sql = "SELECT ga.id
                FROM {grading_areas} ga
                JOIN {grading_definitions} gd ON (gd.areaid = ga.id)
                WHERE gd.id = ? AND ga.contextid = ?";
            $areaid = $DB->get_field_sql($sql, array($formid, $context->id), IGNORE_MISSING);

            if (!$areaid) {
                send_file_not_found();
            }

            $fullpath = "/$context->id/$component/$filearea/$formid/".implode('/', $args);

            if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
                send_file_not_found();
            }

            \core\session\manager::write_close();             send_stored_file($file, 60*60, 0, $forcedownload, array('preview' => $preview));
        }

            } else if (strpos($component, 'mod_') === 0) {
        $modname = substr($component, 4);
        if (!file_exists("$CFG->dirroot/mod/$modname/lib.php")) {
            send_file_not_found();
        }
        require_once("$CFG->dirroot/mod/$modname/lib.php");

        if ($context->contextlevel == CONTEXT_MODULE) {
            if ($cm->modname !== $modname) {
                                send_file_not_found();
            }
        }

        if ($filearea === 'intro') {
            if (!plugin_supports('mod', $modname, FEATURE_MOD_INTRO, true)) {
                send_file_not_found();
            }
            require_course_login($course, true, $cm);

                        $filename = array_pop($args);
            $filepath = $args ? '/'.implode('/', $args).'/' : '/';
            if (!$file = $fs->get_file($context->id, 'mod_'.$modname, 'intro', 0, $filepath, $filename) or $file->is_directory()) {
                send_file_not_found();
            }

                        send_stored_file($file, null, 0, false, array('preview' => $preview));
        }

        $filefunction = $component.'_pluginfile';
        $filefunctionold = $modname.'_pluginfile';
        if (function_exists($filefunction)) {
                        $filefunction($course, $cm, $context, $filearea, $args, $forcedownload, array('preview' => $preview));
        } else if (function_exists($filefunctionold)) {
                        $filefunctionold($course, $cm, $context, $filearea, $args, $forcedownload, array('preview' => $preview));
        }

        send_file_not_found();

        } else if (strpos($component, 'block_') === 0) {
        $blockname = substr($component, 6);
                if (!file_exists("$CFG->dirroot/blocks/$blockname/lib.php")) {
            send_file_not_found();
        }
        require_once("$CFG->dirroot/blocks/$blockname/lib.php");

        if ($context->contextlevel == CONTEXT_BLOCK) {
            $birecord = $DB->get_record('block_instances', array('id'=>$context->instanceid), '*',MUST_EXIST);
            if ($birecord->blockname !== $blockname) {
                                send_file_not_found();
            }

            if ($context->get_course_context(false)) {
                                require_course_login($course);
            } else if ($CFG->forcelogin) {
                                require_login();
            }

            $bprecord = $DB->get_record('block_positions', array('contextid' => $context->id, 'blockinstanceid' => $context->instanceid));
                        if (($bprecord && !$bprecord->visible) || !has_capability('moodle/block:view', $context)) {
                 send_file_not_found();
            }
        } else {
            $birecord = null;
        }

        $filefunction = $component.'_pluginfile';
        if (function_exists($filefunction)) {
                        $filefunction($course, $birecord, $context, $filearea, $args, $forcedownload, array('preview' => $preview));
        }

        send_file_not_found();

        } else if (strpos($component, '_') === false) {
                send_file_not_found();

    } else {
                $dir = core_component::get_component_directory($component);
        if (!file_exists("$dir/lib.php")) {
            send_file_not_found();
        }
        include_once("$dir/lib.php");

        $filefunction = $component.'_pluginfile';
        if (function_exists($filefunction)) {
                        $filefunction($course, $cm, $context, $filearea, $args, $forcedownload, array('preview' => $preview));
        }

        send_file_not_found();
    }

}
