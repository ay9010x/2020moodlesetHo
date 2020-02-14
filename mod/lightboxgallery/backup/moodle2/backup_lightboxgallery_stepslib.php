<?php






class backup_lightboxgallery_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $lightboxgallery = new backup_nested_element('lightboxgallery', array('id'), array(
            'course', 'folder', 'name', 'perpage', 'comments', 'extinfo',
            'timemodified', 'ispublic', 'rss', 'autoresize', 'resize', 'perrow',
            'captionfull', 'captionpos', 'intro', 'introformat'
        ));

        $comments = new backup_nested_element('usercomments');
        $comment = new backup_nested_element('comment', array('id'), array(
            'gallery', 'userid', 'commenttext', 'timemodified'
        ));

        $imagemetas = new backup_nested_element('image_metas');
        $imagemeta = new backup_nested_element('image_meta', array('id'), array(
            'gallery', 'image', 'description', 'metatype'
        ));

        
        $lightboxgallery->add_child($comments);
        $comments->add_child($comment);
        $lightboxgallery->add_child($imagemetas);
        $imagemetas->add_child($imagemeta);

                $lightboxgallery->set_source_table('lightboxgallery', array('id' => backup::VAR_ACTIVITYID));
        $imagemeta->set_source_table('lightboxgallery_image_meta', array('gallery' => backup::VAR_PARENTID));

                if ($userinfo) {
            $comment->set_source_table('lightboxgallery_comments', array('gallery' => backup::VAR_PARENTID));
        }

                $lightboxgallery->annotate_files('mod_lightboxgallery', 'gallery_images', null);
        $lightboxgallery->annotate_files('mod_lightboxgallery', 'gallery_thumbs', null);
        $lightboxgallery->annotate_files('mod_lightboxgallery', 'gallery_index', null);
        $lightboxgallery->annotate_files('mod_lightboxgallery', 'intro', null);

        $comment->annotate_ids('user', 'userid');

                return $this->prepare_activity_structure($lightboxgallery);
    }
}
