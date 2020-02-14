<?php




require_once(__DIR__ . '/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Element\NodeElement as NodeElement;


class behat_files extends behat_base {

    
    protected function get_filepicker_node($filepickerelement) {

                $exception = new ExpectationException('"' . $filepickerelement . '" filepicker can not be found', $this->getSession());

                if (empty($filepickerelement)) {
            $filepickercontainer = $this->find(
                'xpath',
                "//*[@class=\"form-filemanager\"]",
                $exception
            );
        } else {
                        $filepickerelement = behat_context_helper::escape($filepickerelement);
            $filepickercontainer = $this->find(
                'xpath',
                "//input[./@id = //label[normalize-space(.)=$filepickerelement]/@for]" .
                    "//ancestor::div[contains(concat(' ', normalize-space(@class), ' '), ' ffilemanager ') or " .
                    "contains(concat(' ', normalize-space(@class), ' '), ' ffilepicker ')]",
                $exception
            );
        }

        return $filepickercontainer;
    }

    
    protected function perform_on_element($action, ExpectationException $exception) {

                $classname = 'fp-file-' . $action;
        $button = $this->find('css', '.moodle-dialogue-focused button.' . $classname, $exception);

        $this->ensure_node_is_visible($button);
        $button->click();
    }

    
    protected function open_element_contextual_menu($name, $filemanagerelement = false) {

                $containernode = false;
        $exceptionmsg = '"'.$name.'" element can not be found';
        if ($filemanagerelement) {
            $containernode = $this->get_filepicker_node($filemanagerelement);
            $exceptionmsg = 'The "'.$filemanagerelement.'" filemanager ' . $exceptionmsg;
            $locatorprefix = "//div[@class='fp-content']";
        } else {
            $locatorprefix = "//div[contains(concat(' ', normalize-space(@class), ' '), ' fp-repo-items ')]//descendant::div[@class='fp-content']";
        }

        $exception = new ExpectationException($exceptionmsg, $this->getSession());

                $name = behat_context_helper::escape($name);

                try {

                        $node = $this->find(
                'xpath',
                $locatorprefix .
                    "//descendant::*[self::div | self::a][contains(concat(' ', normalize-space(@class), ' '), ' fp-file ')]" .
                    "[contains(concat(' ', normalize-space(@class), ' '), ' fp-folder ')]" .
                    "[normalize-space(.)=$name]" .
                    "//descendant::a[contains(concat(' ', normalize-space(@class), ' '), ' fp-contextmenu ')]",
                $exception,
                $containernode
            );

        } catch (ExpectationException $e) {

                        $node = $this->find(
                'xpath',
                $locatorprefix .
                "//descendant::*[self::div | self::a][contains(concat(' ', normalize-space(@class), ' '), ' fp-file ')]" .
                "[normalize-space(.)=$name]" .
                "//descendant::div[contains(concat(' ', normalize-space(@class), ' '), ' fp-filename-field ')]",
                false,
                $containernode
            );
        }

                $this->ensure_node_is_visible($node);
        $node->click();
    }

    
    protected function open_add_file_window($filemanagernode, $repositoryname) {

        $exception = new ExpectationException('No files can be added to the specified filemanager', $this->getSession());

                        try {
                        $add = $this->find('css', 'div.fp-btn-add a', $exception, $filemanagernode);
        } catch (Exception $e) {
                        $add = $this->find('css', 'input.fp-btn-choose', $exception, $filemanagernode);
        }
        $this->ensure_node_is_visible($add);
        $add->click();

                        $this->ensure_element_exists(
                "//div[contains(concat(' ', normalize-space(@class), ' '), ' file-picker ')]" .
                "//div[contains(concat(' ', normalize-space(@class), ' '), ' fp-content ')]" .
                "[not(descendant::div[contains(concat(' ', normalize-space(@class), ' '), ' fp-content-loading ')])]",
                'xpath_element');

                $repoexception = new ExpectationException('The "' . $repositoryname . '" repository has not been found', $this->getSession());

                $repositoryname = behat_context_helper::escape($repositoryname);

                $repositorylink = $this->find(
            'xpath',
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' fp-repo-area ')]" .
                "//descendant::span[contains(concat(' ', normalize-space(@class), ' '), ' fp-repo-name ')]" .
                "[normalize-space(.)=$repositoryname]",
            $repoexception
        );

                $this->ensure_node_is_visible($repositorylink);
        if (!$repositorylink->getParent()->getParent()->hasClass('active')) {
                                    $repositorylink->click();
        }
    }

    
    protected function wait_until_return_to_form() {

        $exception = new ExpectationException('The file manager is taking too much time to finish the current action', $this->getSession());

         $this->find(
             'xpath',
             "//div[contains(concat(' ', @class, ' '), ' moodle-dialogue-lightbox ')][contains(@style, 'display: none;')]",
             $exception
         );
    }

    
    protected function wait_until_contents_are_updated($filepickernode) {

        $exception = new ExpectationException(
            'The file manager contents are requiring too much time to be updated',
            $this->getSession()
        );

                        $this->find(
            'xpath',
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' filemanager ')]" .
                "[not(contains(concat(' ', normalize-space(@class), ' '), ' fm-updating '))]" .
            "|" .
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' filemanager-loading ')]" .
                "[contains(@style, 'display: none;')]",
            $exception,
            $filepickernode
        );
    }

}
