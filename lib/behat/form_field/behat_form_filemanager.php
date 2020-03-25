<?php




require_once(__DIR__  . '/behat_form_field.php');


class behat_form_filemanager extends behat_form_field {

    
    public function get_value() {

                $this->session->wait(behat_base::TIMEOUT, behat_base::PAGE_READY_JS);

                $fieldlabel = $this->get_field_locator();

                $xpath = "//label[contains(., '" . $fieldlabel . "')]" .
            "/ancestor::div[contains(concat(' ', normalize-space(@class), ' '), ' fitemtitle ')]" .
            "/following-sibling::div[contains(concat(' ', normalize-space(@class), ' '), ' ffilemanager ')]" .
            "/descendant::div[contains(concat(' ', normalize-space(@class), ' '), ' fp-filename ')]";

                        $files = $this->session->getPage()->findAll('xpath', $xpath);

        if (!$files) {
            return '';
        }

        $filenames = array();
        foreach ($files as $filenode) {
            $filenames[] = $filenode->getText();
        }

        return implode(',', $filenames);
    }

    
    public function set_value($value) {

                $fieldlabel = $this->get_field_locator();

                        $uploadcontext = behat_context_helper::get('behat_repository_upload');
        $uploadcontext->i_upload_file_to_filemanager($value, $fieldlabel);
    }

    
    public function matches($expectedvalue) {
        return $this->text_matches($expectedvalue);
    }

}
