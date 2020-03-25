<?php



class block_community_renderer extends plugin_renderer_base {

    public function restore_confirmation_box($filename, $context) {
        $restoreurl = new moodle_url('/backup/restore.php',
                        array('filename' => $filename . ".mbz", 'contextid' => $context->id));
        $searchurl = new moodle_url('/blocks/community/communitycourse.php',
                        array('add' => 1, 'courseid' => $context->instanceid,
                            'cancelrestore' => 1, 'sesskey' => sesskey(),
                            'filename' => $filename));
        $formrestore = new single_button($restoreurl,
                        get_string('dorestore', 'block_community'));
        $formsearch = new single_button($searchurl,
                        get_string('donotrestore', 'block_community'));
        return $this->output->confirm(get_string('restorecourseinfo', 'block_community'),
                $formrestore, $formsearch);
    }

    
    public function remove_success(moodle_url $url) {
        $html = $this->output->notification(get_string('communityremoved', 'hub'),
                    'notifysuccess');
        $continuebutton = new single_button($url,
                        get_string('continue', 'block_community'));
        $html .= html_writer::tag('div', $this->output->render($continuebutton),
                array('class' => 'continuebutton'));
        return $html;
    }

    
    public function save_link_success(moodle_url $url) {
        $html = $this->output->notification(get_string('addedtoblock', 'block_community'),
                    'notifysuccess');
        $continuebutton = new single_button($url,
                        get_string('continue', 'block_community'));
        $html .= html_writer::tag('div', $this->output->render($continuebutton),
                array('class' => 'continuebutton'));
        return $html;
    }

    
    public function next_button($data) {
        $nextlink = html_writer::tag('a', get_string('next', 'block_community'),
                array('href' => new moodle_url('', $data)));
        return html_writer::tag('div', $nextlink, array( 'class' => 'nextlink'));
    }

    
    public function course_list($courses, $huburl, $contextcourseid) {
        global $CFG;

        $renderedhtml = '';

        if (empty($courses)) {
            if (isset($courses)) {
                $renderedhtml .= get_string('nocourse', 'block_community');
            }
        } else {
            $courseiteration = 0;
            foreach ($courses as $course) {
                $course = (object) $course;
                $courseiteration = $courseiteration + 1;

                                if (!empty($course->courseurl)) {
                    $courseurl = new moodle_url($course->courseurl);
                    $linktext = get_string('visitsite', 'block_community');
                } else {
                    $courseurl = new moodle_url($course->demourl);
                    $linktext = get_string('visitdemo', 'block_community');
                }

                $visitlinkhtml = html_writer::tag('a', $linktext,
                                array('href' => $courseurl, 'class' => 'hubcoursedownload',
                                    'onclick' => 'this.target="_blank"'));

                                $coursename = html_writer::tag('h3', $course->fullname,
                                array('class' => 'hubcoursetitle'));
                $coursenamehtml = html_writer::tag('div', $coursename,
                        array('class' => 'hubcoursetitlepanel'));

                                $screenshothtml = '';
                if (!empty($course->screenshots)) {
                    $baseurl = new moodle_url($huburl . '/local/hub/webservice/download.php',
                                    array('courseid' => $course->id,
                                        'filetype' => HUB_SCREENSHOT_FILE_TYPE));
                    $screenshothtml = html_writer::empty_tag('img',
                        array('src' => $baseurl, 'alt' => $course->fullname));
                }
                $coursescreenshot = html_writer::tag('div', $screenshothtml,
                                array('class' => 'coursescreenshot',
                                    'id' => 'image-' . $course->id));

                                $deschtml = html_writer::tag('div', $course->description,
                                array('class' => 'hubcoursedescription'));

                                $courseuserinfo = get_string('userinfo', 'block_community', $course);
                if ($course->contributornames) {
                    $courseuserinfo .= ' - ' . get_string('contributors', 'block_community',
                                    $course->contributornames);
                }
                $courseuserinfohtml = html_writer::tag('div', $courseuserinfo,
                                array('class' => 'hubcourseuserinfo'));

                                $course->subject = get_string($course->subject, 'edufields');
                $course->audience = get_string('audience' . $course->audience, 'hub');
                $course->educationallevel = get_string('edulevel' . $course->educationallevel, 'hub');
                $coursecontentinfo = '';
                if (empty($course->coverage)) {
                    $course->coverage = '';
                } else {
                    $coursecontentinfo .= get_string('coverage', 'block_community', $course->coverage);
                    $coursecontentinfo .= ' - ';
                }
                $coursecontentinfo .= get_string('contentinfo', 'block_community', $course);
                $coursecontentinfohtml = html_writer::tag('div', $coursecontentinfo,
                                array('class' => 'hubcoursecontentinfo'));

                                                if (!empty($course->language)) {
                    $languages = get_string_manager()->get_list_of_languages();
                    $course->lang = $languages[$course->language];
                } else {
                    $course->lang = '';
                }
                                require_once($CFG->libdir . "/licenselib.php");
                $licensemanager = new license_manager();
                $licenses = $licensemanager->get_licenses();
                foreach ($licenses as $license) {
                    if ($license->shortname == $course->licenceshortname) {
                        $course->license = $license->fullname;
                    }
                }
                $course->timeupdated = userdate($course->timemodified);
                $coursefileinfo = get_string('fileinfo', 'block_community', $course);
                $coursefileinfohtml = html_writer::tag('div', $coursefileinfo,
                                array('class' => 'hubcoursefileinfo'));



                                $blocks = core_component::get_plugin_list('block');
                $activities = core_component::get_plugin_list('mod');
                if (!empty($course->contents)) {
                    $activitieshtml = '';
                    $blockhtml = '';
                    foreach ($course->contents as $content) {
                        $content = (object) $content;
                        if ($content->moduletype == 'block') {
                            if (!empty($blockhtml)) {
                                $blockhtml .= ' - ';
                            }
                            if (array_key_exists($content->modulename, $blocks)) {
                                $blockname = get_string('pluginname', 'block_' . $content->modulename);
                            } else {
                                $blockname = $content->modulename;
                            }
                            $blockhtml .= $blockname . " (" . $content->contentcount . ")";
                        } else {
                            if (!empty($activitieshtml)) {
                                $activitieshtml .= ' - ';
                            }
                            if (array_key_exists($content->modulename, $activities)) {
                                $activityname = get_string('modulename', $content->modulename);
                            } else {
                                $activityname = $content->modulename;
                            }
                            $activitieshtml .= $activityname . " (" . $content->contentcount . ")";
                        }
                    }

                    $blocksandactivities = html_writer::tag('div',
                                    get_string('activities', 'block_community') . " : " . $activitieshtml);

                                    }

                                $outcomes= '';
                if (!empty($course->outcomes)) {
                    foreach ($course->outcomes as $outcome) {
                        if (!empty($outcomes)) {
                            $outcomes .= ', ';
                        }
                        $outcomes .= $outcome['fullname'];
                    }
                    $outcomes = get_string('outcomes', 'block_community',
                            $outcomes);
                }
                $outcomeshtml = html_writer::tag('div', $outcomes, array('class' => 'hubcourseoutcomes'));

                                $additionaldesc = $courseuserinfohtml . $coursecontentinfohtml
                        . $coursefileinfohtml . $blocksandactivities . $outcomeshtml;
                $additionaldeschtml = html_writer::tag('div', $additionaldesc,
                                array('class' => 'additionaldesc'));

                                $addbuttonhtml = "";
                if ($course->enrollable) {
                    $params = array('sesskey' => sesskey(), 'add' => 1, 'confirmed' => 1,
                        'coursefullname' => $course->fullname, 'courseurl' => $courseurl,
                        'coursedescription' => $course->description,
                        'courseid' => $contextcourseid);
                    $addurl = new moodle_url("/blocks/community/communitycourse.php", $params);
                    $addbuttonhtml = html_writer::tag('a',
                                    get_string('addtocommunityblock', 'block_community'),
                                    array('href' => $addurl, 'class' => 'centeredbutton, hubcoursedownload'));
                }

                                $downloadbuttonhtml = "";
                if (!$course->enrollable) {
                    $params = array('sesskey' => sesskey(), 'download' => 1, 'confirmed' => 1,
                        'remotemoodleurl' => $CFG->wwwroot, 'courseid' => $contextcourseid,
                        'downloadcourseid' => $course->id, 'huburl' => $huburl,
                        'coursefullname' => $course->fullname, 'backupsize' => $course->backupsize);
                    $downloadurl = new moodle_url("/blocks/community/communitycourse.php", $params);
                    $downloadbuttonhtml = html_writer::tag('a', get_string('install', 'block_community'),
                                    array('href' => $downloadurl, 'class' => 'centeredbutton, hubcoursedownload'));
                }

                                $rating = html_writer::tag('div', get_string('noratings', 'block_community'),
                                array('class' => 'norating'));
                if (!empty($course->rating)) {
                    $course->rating = (object) $course->rating;
                    if ($course->rating->count > 0) {

                                                $starimagesize = 20;                         $numberofstars = 5;
                        $size = ($course->rating->aggregate / $course->rating->scaleid)
                                * $numberofstars * $starimagesize;
                        $rating = html_writer::tag('li', '',
                                        array('class' => 'current-rating',
                                            'style' => 'width:' . $size . 'px;'));

                        $rating = html_writer::tag('ul', $rating,
                                        array('class' => 'star-rating clearfix'));
                        $rating .= html_writer::tag('div', ' (' . $course->rating->count . ')',
                                        array('class' => 'ratingcount clearfix'));
                    }
                }


                                $coursecomments = html_writer::tag('div', get_string('nocomments', 'block_community'),
                                array('class' => 'nocomments'));
                $commentcount = 0;
                if (!empty($course->comments)) {
                                        $commentcount = count($course->comments);
                    $coursecomments = html_writer::tag('div',
                                    get_string('comments', 'block_community', $commentcount),
                                    array('class' => 'commenttitle'));

                    foreach ($course->comments as $comment) {
                        $commentator = html_writer::tag('div',
                                        $comment['commentator'],
                                        array('class' => 'hubcommentator'));
                        $commentdate = html_writer::tag('div',
                                        ' - ' . userdate($comment['date'], '%e/%m/%y'),
                                        array('class' => 'hubcommentdate clearfix'));

                        $commenttext = html_writer::tag('div',
                                        $comment['comment'],
                                        array('class' => 'hubcommenttext'));

                        $coursecomments .= html_writer::tag('div',
                                        $commentator . $commentdate . $commenttext,
                                        array('class' => 'hubcomment'));
                    }
                    $coursecommenticon = html_writer::tag('div',
                                    get_string('comments', 'block_community', $commentcount),
                                    array('class' => 'hubcoursecomments',
                                        'id' => 'comments-' . $course->id));
                    $coursecomments = $coursecommenticon . html_writer::tag('div',
                                    $coursecomments,
                                    array('class' => 'yui3-overlay-loading',
                                        'id' => 'commentoverlay-' . $course->id));
                }

                                $rateandcomment = html_writer::tag('div',
                                html_writer::tag('a', get_string('rateandcomment', 'block_community'),
                                        array('href' => new moodle_url($huburl,
                                                    array('courseid' => $course->id, 'mustbelogged' => true)),
                                            'onclick' => 'this.target="_blank"')),
                                array('class' => 'hubrateandcomment'));

                                $buttonsdiv = html_writer::tag('div',
                                $addbuttonhtml . $downloadbuttonhtml . $visitlinkhtml,
                                array('class' => 'courseoperations'));
                $screenshotbuttonsdiv = html_writer::tag('div',
                                $coursescreenshot . $buttonsdiv,
                                array('class' => 'courselinks'));

                $coursedescdiv = html_writer::tag('div',
                                $deschtml . $additionaldeschtml
                                . $rating . $coursecomments . $rateandcomment,
                                array('class' => 'coursedescription'));
                $coursehtml =
                        $coursenamehtml . html_writer::tag('div',
                                $coursedescdiv . $screenshotbuttonsdiv,
                                array('class' => 'hubcourseinfo clearfix'));

                $renderedhtml .=html_writer::tag('div', $coursehtml,
                                array('class' => 'fullhubcourse clearfix'));
            }

            $renderedhtml = html_writer::tag('div', $renderedhtml,
                            array('class' => 'hubcourseresult'));
        }

        return $renderedhtml;
    }

}
