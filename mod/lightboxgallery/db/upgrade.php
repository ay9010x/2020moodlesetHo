<?php




defined('MOODLE_INTERNAL') || die();



function xmldb_lightboxgallery_upgrade($oldversion=0) {

    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2007111400) {
        $table = new xmdbl_table('lightboxgallery');

                $field = new xmldb_field('perpage', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'description');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $field = new xmldb_field('comments', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'perpage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $table = new xmldb_table('lightboxgallery_comments');

        if (!$dbman->table_exists($table)) {
            $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->addField($field);

            $field = new xmldb_field('gallery', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->addField($field);

            $field = new xmldb_field('user', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->addField($field);

            $field = new xmldb_field('comment', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
            $table->addField($field);

            $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->addField($field);

            $key = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $table->add_index('gallery', XMLDB_INDEX_NOTUNIQUE, array('gallery'));

            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2007121700) {
        $table = new xmldb_table('lightboxgallery');

                $field = new xmldb_field('extinfo', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'comments');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $table = new xmldb_table('lightboxgallery_captions');

        if (!$dbman->table_exists($table)) {
            $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->addField($field);

            $field = new xmldb_field('gallery', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
            $table->addField($field);

            $field = new xmldb_field('image', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->addField($field);

            $field = new xmldb_field('caption', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
            $table->addField($field);

            $key = new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($key);

            $table->add_index('gallery', XMLDB_INDEX_NOTUNIQUE, array('gallery'));

            $dbman->create_table($table);
        }

    }

    if ($oldversion < 2008110600) {
        $table = new xmldb_table('lightboxgallery');

                $newfields = array('public', 'rss', 'autoresize', 'resize');
        $previousfield = 'comments';
        foreach ($newfields as $newfield) {
            $field = new xmldb_field($newfield, XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', $previousfield);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
                $previousfield = $newfield;
            }
        }

        $table = new xmldb_table('lightboxgallery_comments');

                $field = new xmldb_field('user', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'userid');
        }

        $table = new xmldb_table('lightboxgallery_captions');

                $field = new xmldb_field('caption', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'image');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'description');
        }

        $field = new xmldb_field('metatype',
                                 XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, array('caption', 'tag'), 'caption', 'image');
        if ($dbman->table_exists($table) && !$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                $dbman->rename_table($table, 'lightboxgallery_image_meta');
    }

    if ($oldversion < 2009051200) {
        $table = new xmldb_table('lightboxgallery');

                $field = new xmldb_field('public', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'comments');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'ispublic');
        }
    }

    if ($oldversion < 2011040800) {
        $table = new xmldb_table('lightboxgallery');

                $field = new xmldb_field('perrow', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '4', 'perpage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

                        if ($galleries = $DB->get_records('lightboxgallery')) {
            foreach ($galleries as $gallery) {
                if (!$cm = get_coursemodule_from_instance('lightboxgallery', $gallery->id, $gallery->course, false)) {
                    continue;
                }
                $context = context_module::instance($cm->id);
                $coursecontext = context_course::instance($gallery->course);

                                $fs = get_file_storage();
                if ($storedfiles = $fs->get_area_files($coursecontext->id, 'course', 'legacy')) {
                    foreach ($storedfiles as $file) {
                        $path = '/'.$gallery->folder;
                        if ($gallery->folder != '') {
                            $path .= '/';
                        }
                        if (substr($file->get_mimetype(), 0, 6) != 'image/' ||
                            substr($file->get_filepath(), -8, 8) == '/_thumb/' ||
                            $file->get_filepath() != $path) {
                            continue;
                        }
                                                $settings = new stdClass();
                        $settings->contextid = $context->id;
                        $settings->component = 'mod_lightboxgallery';
                        $settings->filearea = 'gallery_images';
                        $settings->filepath = '/';
                        $fs->create_file_from_storedfile($settings, $file);
                    }
                }
            }
        }
        upgrade_mod_savepoint(true, 2011040800, 'lightboxgallery');
    }

    if ($oldversion < 2011071100) {
                $table = new xmldb_table('lightboxgallery');

        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'extinfo');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'intro');
        }

        $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'intro');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2011071100, 'lightboxgallery');
    }

    if ($oldversion < 2011111600) {
        $table = new xmldb_table('lightboxgallery');

        $field = new xmldb_field('captionfull', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'extinfo');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field, 'extinfo');
        }

        $field = new xmldb_field('captionpos', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'captionfull');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field, 'captionfull');
        }

        upgrade_mod_savepoint(true, 2011111600, 'lightboxgallery');
    }

    if ($oldversion < 2013051300) {
        $table = new xmldb_table('lightboxgallery_comments');

        $field = new xmldb_field('comment', XMLDB_TYPE_TEXT, null, null, null, null, null, 'userid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'commenttext');
        }

        upgrade_mod_savepoint(true, 2013051300, 'lightboxgallery');
    }

    return true;
}
