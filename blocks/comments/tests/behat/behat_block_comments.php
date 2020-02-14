<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException as ExpectationException;


class behat_block_comments extends behat_base {

    
    public function i_add_comment_to_comments_block($comment) {

                $exception = new ElementNotFoundException($this->getSession(), 'Comments block ');

                if ($this->running_javascript()) {
            $commentstextarea = $this->find('css', '.comment-area textarea', $exception);
            $commentstextarea->setValue($comment);

            $this->find_link(get_string('savecomment'))->click();
                                    $this->getSession()->wait(1000, false);

        } else {

            $commentstextarea = $this->find('css', '.block_comments form textarea', $exception);
            $commentstextarea->setValue($comment);

                        $submit = $this->find('css', '.block_comments form input[type=submit]');
            $submit->press();
        }
    }

    
    public function i_delete_comment_from_comments_block($comment) {

        $exception = new ElementNotFoundException($this->getSession(), '"' . $comment . '" comment ');

                $commentliteral = behat_context_helper::escape($comment);

        $commentxpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' block_comments ')]" .
            "/descendant::div[@class='comment-message'][contains(., $commentliteral)]";
        $commentnode = $this->find('xpath', $commentxpath, $exception);

                $deleteexception = new ExpectationException('"' . $comment . '" comment can not be deleted', $this->getSession());
        $deleteicon = $this->find('css', '.comment-delete a img', $deleteexception, $commentnode);
        $deleteicon->click();

                $this->getSession()->wait(4 * 1000, false);
    }

}
