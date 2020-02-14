<?php




use Behat\Mink\Element\NodeElement as NodeElement;

require_once(__DIR__ . '/behat_form_textarea.php');


class behat_form_editor extends behat_form_textarea {

    
    public function set_value($value) {

        $editorid = $this->field->getAttribute('id');
        if ($this->running_javascript()) {
            $value = addslashes($value);
            $js = '
var editor = Y.one(document.getElementById("'.$editorid.'editable"));
if (editor) {
    editor.setHTML("' . $value . '");
}
editor = Y.one(document.getElementById("'.$editorid.'"));
editor.set("value", "' . $value . '");
';
            $this->session->executeScript($js);
        } else {
            parent::set_value($value);
        }
    }

    
    public function select_text() {
                if (!$this->running_javascript()) {
            throw new coding_exception('Selecting text requires javascript.');
        }

        $editorid = $this->field->getAttribute('id');
        $js = ' (function() {
    var e = document.getElementById("'.$editorid.'editable"),
        r = rangy.createRange(),
        s = rangy.getSelection();

    while ((e.firstChild !== null) && (e.firstChild.nodeType != document.TEXT_NODE)) {
        e = e.firstChild;
    }
    e.focus();
    r.selectNodeContents(e);
    s.setSingleRange(r);
}()); ';
        $this->session->executeScript($js);
    }

    
    public function matches($expectedvalue) {
                return $this->text_matches($expectedvalue) || $this->text_matches('<p>' . $expectedvalue . '</p>');
    }
}

