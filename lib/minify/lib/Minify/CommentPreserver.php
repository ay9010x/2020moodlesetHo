<?php



class Minify_CommentPreserver {
    
    
    public static $prepend = "\n";
    
    
    public static $append = "\n";
    
    
    public static function process($content, $processor, $args = array())
    {
        $ret = '';
        while (true) {
            list($beforeComment, $comment, $afterComment) = self::_nextComment($content);
            if ('' !== $beforeComment) {
                $callArgs = $args;
                array_unshift($callArgs, $beforeComment);
                $ret .= call_user_func_array($processor, $callArgs);    
            }
            if (false === $comment) {
                break;
            }
            $ret .= $comment;
            $content = $afterComment;
        }
        return $ret;
    }
    
    
    private static function _nextComment($in)
    {
        if (
            false === ($start = strpos($in, '/*!'))
            || false === ($end = strpos($in, '*/', $start + 3))
        ) {
            return array($in, false, false);
        }
        $ret = array(
            substr($in, 0, $start)
            ,self::$prepend . '/*!' . substr($in, $start + 3, $end - $start - 1) . self::$append
        );
        $endChars = (strlen($in) - $end - 2);
        $ret[] = (0 === $endChars)
            ? ''
            : substr($in, -$endChars);
        return $ret;
    }
}
