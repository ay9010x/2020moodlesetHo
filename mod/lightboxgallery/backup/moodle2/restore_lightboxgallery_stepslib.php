<?php






class restore_lightboxgallery_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $lightboxgallery = new restore_path_element('lightboxgallery', '/activity/lightboxgallery');
        $paths[] = $lightboxgallery;

        $meta = new restore_path_element('lightboxgallery_image_meta', '/activity/lightboxgallery/image_metas/image_meta');
        $paths[] = $meta;

        if ($userinfo) {
            $comment = new restore_path_element('lightboxgallery_comment', '/activity/lightboxgallery/usercomments/comment');
            $paths[] = $comment;
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_lightboxgallery($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
                $newitemid = $DB->insert_record('lightboxgallery', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_lightboxgallery_comment($data) {
        global $DB;

        $data = (object)$data;

        $data->gallery = $this->get_new_parentid('lightboxgallery');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        if (isset($data->comment)) {
            $data->commenttext = $data->comment;
        }
        $DB->insert_record('lightboxgallery_comments', $data);
    }

    protected function process_lightboxgallery_image_meta($data) {
        global $DB;

        $data = (object)$data;

        $data->gallery = $this->get_new_parentid('lightboxgallery');
                $DB->insert_record('lightboxgallery_image_meta', $data);
    }

    protected function after_execute() {
        $this->add_related_files('mod_lightboxgallery', 'gallery_images', null);
        $this->add_related_files('mod_lightboxgallery', 'gallery_thumbs', null);
        $this->add_related_files('mod_lightboxgallery', 'gallery_index', null);
        $this->add_related_files('mod_lightboxgallery', 'intro', null);
    }
}
