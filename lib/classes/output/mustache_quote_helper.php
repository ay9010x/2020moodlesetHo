<?php



namespace core\output;


class mustache_quote_helper {

    
    public function quote($text, \Mustache_LambdaHelper $helper) {
                $content = trim($text);
        $content = $helper->render($content);

                $content = str_replace('"', '\\"', $content);
        $content = preg_replace('([{}]{2,3})', '{{=<% %>=}}${0}<%={{ }}=%>', $content);
        return '"' . $content . '"';
    }
}
