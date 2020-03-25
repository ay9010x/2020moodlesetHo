<?php



defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/book/locallib.php');


function booktool_exportimscp_build_package($book, $context) {
    global $DB;

    $fs = get_file_storage();

    if ($packagefile = $fs->get_file($context->id, 'booktool_exportimscp', 'package', $book->revision, '/', 'imscp.zip')) {
        return $packagefile;
    }

        if (!book_preload_chapters($book)) {
        print_error('nochapters', 'booktool_exportimscp');
    }

        booktool_exportimscp_prepare_files($book, $context);

    $packer = get_file_packer('application/zip');
    $areafiles = $fs->get_area_files($context->id, 'booktool_exportimscp', 'temp', $book->revision, "sortorder, itemid, filepath, filename", false);
    $files = array();
    foreach ($areafiles as $file) {
        $path = $file->get_filepath().$file->get_filename();
        $path = ltrim($path, '/');
        $files[$path] = $file;
    }
    unset($areafiles);
    $packagefile = $packer->archive_to_storage($files, $context->id, 'booktool_exportimscp', 'package', $book->revision, '/', 'imscp.zip');

        $fs->delete_area_files($context->id, 'booktool_exportimscp', 'temp', $book->revision);

        $sql = "SELECT DISTINCT itemid
              FROM {files}
             WHERE contextid = :contextid AND component = 'booktool_exportimscp' AND itemid < :revision";
    $params = array('contextid'=>$context->id, 'revision'=>$book->revision);
    $revisions = $DB->get_records_sql($sql, $params);
    foreach ($revisions as $rev => $unused) {
        $fs->delete_area_files($context->id, 'booktool_exportimscp', 'temp', $rev);
        $fs->delete_area_files($context->id, 'booktool_exportimscp', 'package', $rev);
    }

    return $packagefile;
}


function booktool_exportimscp_prepare_files($book, $context) {
    global $CFG, $DB;

    $fs = get_file_storage();

    $temp_file_record = array('contextid'=>$context->id, 'component'=>'booktool_exportimscp', 'filearea'=>'temp', 'itemid'=>$book->revision);
    $chapters = $DB->get_records('book_chapters', array('bookid'=>$book->id), 'pagenum');
    $chapterresources = array();
    foreach ($chapters as $chapter) {
        $chapterresources[$chapter->id] = array();
        $files = $fs->get_area_files($context->id, 'mod_book', 'chapter', $chapter->id, "sortorder, itemid, filepath, filename", false);
        foreach ($files as $file) {
            $temp_file_record['filepath'] = '/'.$chapter->pagenum.$file->get_filepath();
            $fs->create_file_from_storedfile($temp_file_record, $file);
            $chapterresources[$chapter->id][] = $chapter->pagenum.$file->get_filepath().$file->get_filename();
        }
        if ($file = $fs->get_file($context->id, 'booktool_exportimscp', 'temp', $book->revision, "/$chapter->pagenum/", 'index.html')) {
                        $file->delete();
        }
        $content = booktool_exportimscp_chapter_content($chapter, $context);
        $index_file_record = array('contextid'=>$context->id, 'component'=>'booktool_exportimscp', 'filearea'=>'temp',
                'itemid'=>$book->revision, 'filepath'=>"/$chapter->pagenum/", 'filename'=>'index.html');
        $fs->create_file_from_string($index_file_record, $content);
    }

    $css_file_record = array('contextid'=>$context->id, 'component'=>'booktool_exportimscp', 'filearea'=>'temp',
            'itemid'=>$book->revision, 'filepath'=>"/css/", 'filename'=>'styles.css');
    $fs->create_file_from_pathname($css_file_record, dirname(__FILE__).'/imscp.css');

        $imsmanifest = '';
    $imsitems = '';
    $imsresources = '';

        $moodle_release = $CFG->release;
    $moodle_version = $CFG->version;
    $book_version   = get_config('mod_book', 'version');
    $bookname       = format_string($book->name, true, array('context'=>$context));

            $imsmanifest .= '<?xml version="1.0" encoding="UTF-8"?>
<!-- This package has been created with Moodle ' . $moodle_release . ' (' . $moodle_version . ') http://moodle.org/, Book module version ' . $book_version . ' - https://github.com/skodak/moodle-mod_book -->
<!-- One idea and implementation by Eloy Lafuente (stronk7) and Antonio Vicent (C) 2001-3001 -->
<manifest xmlns="http://www.imsglobal.org/xsd/imscp_v1p1" xmlns:imsmd="http://www.imsglobal.org/xsd/imsmd_v1p2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" identifier="MANIFEST-' . md5($CFG->wwwroot . '-' . $book->course . '-' . $book->id) . '" xsi:schemaLocation="http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd http://www.imsglobal.org/xsd/imsmd_v1p2 imsmd_v1p2p2.xsd">
  <organizations default="MOODLE-' . $book->course . '-' . $book->id . '">
    <organization identifier="MOODLE-' . $book->course . '-' . $book->id . '" structure="hierarchical">
      <title>' . htmlspecialchars($bookname) . '</title>';

        $prevlevel = null;
    $currlevel = 0;
    foreach ($chapters as $chapter) {
                $currlevel = empty($chapter->subchapter) ? 0 : 1;
                if ($prevlevel !== null) {
                        $prevspaces = substr('                ', 0, $currlevel * 2);

                        if ($prevlevel == $currlevel) {
                $imsitems .= $prevspaces . '        </item>' . "\n";
            }
                                    if ($prevlevel > $currlevel) {
                $imsitems .= '          </item>' . "\n";
                $imsitems .= '        </item>' . "\n";
            }
        }
                $prevlevel = $currlevel;

                $currspaces = substr('                ', 0, $currlevel * 2);

        $chaptertitle = format_string($chapter->title, true, array('context'=>$context));

                $imsitems .= $currspaces .'        <item identifier="ITEM-' . $book->course . '-' . $book->id . '-' . $chapter->pagenum .'" isvisible="true" identifierref="RES-' .
                $book->course . '-' . $book->id . '-' . $chapter->pagenum . "\">\n" .
                $currspaces . '         <title>' . htmlspecialchars($chaptertitle) . '</title>' . "\n";

                        $localfiles = array();
        foreach ($chapterresources[$chapter->id] as $localfile) {
            $localfiles[] = "\n" . '      <file href="' . $localfile . '" />';
        }
                $cssdependency = "\n" . '      <dependency identifierref="RES-' . $book->course . '-'  . $book->id . '-css" />';
                $imsresources .= '    <resource identifier="RES-' . $book->course . '-'  . $book->id . '-' . $chapter->pagenum . '" type="webcontent" xml:base="' .
                $chapter->pagenum . '/" href="index.html">' . "\n" .
                '      <file href="' . $chapter->pagenum . '/index.html" />' . implode($localfiles) . $cssdependency . "\n".
                '    </resource>' . "\n";
    }

            if ($currlevel == 0) {
        $imsitems .= '        </item>' . "\n";
    }
        if ($currlevel == 1) {
        $imsitems .= '          </item>' . "\n";
        $imsitems .= '        </item>' . "\n";
    }

        $cssresource = '    <resource identifier="RES-' . $book->course . '-'  . $book->id . '-css" type="webcontent" xml:base="css/" href="styles.css">
      <file href="css/styles.css" />
    </resource>' . "\n";

        $imsmanifest .= "\n" . $imsitems;
        $imsmanifest .= "    </organization>
  </organizations>";
        $imsmanifest .= "\n  <resources>\n" . $imsresources . $cssresource . "  </resources>";
        $imsmanifest .= "\n</manifest>\n";

    $manifest_file_record = array('contextid'=>$context->id, 'component'=>'booktool_exportimscp', 'filearea'=>'temp',
            'itemid'=>$book->revision, 'filepath'=>"/", 'filename'=>'imsmanifest.xml');
    $fs->create_file_from_string($manifest_file_record, $imsmanifest);
}


function booktool_exportimscp_chapter_content($chapter, $context) {

    $options = new stdClass();
    $options->noclean = true;
    $options->context = $context;

        $chaptercontent = file_rewrite_pluginfile_urls($chapter->content, 'pluginfile.php', $context->id, 'mod_book', 'chapter',
                                                    $chapter->id);
    $chaptercontent = format_text($chaptercontent, $chapter->contentformat, $options);

        $options = array('reverse' => true);
    $chaptercontent = file_rewrite_pluginfile_urls($chaptercontent, 'pluginfile.php', $context->id, 'mod_book', 'chapter',
                                                    $chapter->id, $options);
    $chaptercontent = str_replace('@@PLUGINFILE@@/', '', $chaptercontent);

    $chaptertitle = format_string($chapter->title, true, array('context'=>$context));

    $content = '';
    $content .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . "\n";
    $content .= '<html>' . "\n";
    $content .= '<head>' . "\n";
    $content .= '<meta http-equiv="content-type" content="text/html; charset=utf-8" />' . "\n";
    $content .= '<link rel="stylesheet" type="text/css" href="../css/styles.css" />' . "\n";
    $content .= '<title>' . $chaptertitle . '</title>' . "\n";
    $content .= '</head>' . "\n";
    $content .= '<body>' . "\n";
    $content .= '<h1 id="header">' . $chaptertitle . '</h1>' ."\n";
    $content .= $chaptercontent . "\n";
    $content .= '</body>' . "\n";
    $content .= '</html>' . "\n";

    return $content;
}
