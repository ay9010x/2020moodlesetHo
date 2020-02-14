<?php



class Minify_Lines {

    
    public static function minify($content, $options = array()) 
    {
        $id = (isset($options['id']) && $options['id'])
            ? $options['id']
            : '';
        $content = str_replace("\r\n", "\n", $content);

                        $content = str_replace('"/*"', '"/"+"*"', $content);
        $content = preg_replace('@([\'"])(\\.?//?)\\*@', '$1$2$1+$1*', $content);

        $lines = explode("\n", $content);
        $numLines = count($lines);
                $padTo = strlen((string) $numLines);         $inComment = false;
        $i = 0;
        $newLines = array();
        while (null !== ($line = array_shift($lines))) {
            if (('' !== $id) && (0 == $i % 50)) {
                if ($inComment) {
                    array_push($newLines, '', "/* {$id} *|", '');
                } else {
                    array_push($newLines, '', "/* {$id} */", '');
                }
            }
            ++$i;
            $newLines[] = self::_addNote($line, $i, $inComment, $padTo);
            $inComment = self::_eolInComment($line, $inComment);
        }
        $content = implode("\n", $newLines) . "\n";
        
                if (isset($options['currentDir'])) {
            Minify_CSS_UriRewriter::$debugText = '';
            $content = Minify_CSS_UriRewriter::rewrite(
                 $content
                ,$options['currentDir']
                ,isset($options['docRoot']) ? $options['docRoot'] : $_SERVER['DOCUMENT_ROOT']
                ,isset($options['symlinks']) ? $options['symlinks'] : array()
            );
            $content = "/* Minify_CSS_UriRewriter::\$debugText\n\n" 
                     . Minify_CSS_UriRewriter::$debugText . "*/\n"
                     . $content;
        }
        
        return $content;
    }
    
    
    private static function _eolInComment($line, $inComment)
    {
                $line = preg_replace('~//.*?(\\*/|/\\*).*~', '', $line);

        while (strlen($line)) {
            $search = $inComment
                ? '*/'
                : '/*';
            $pos = strpos($line, $search);
            if (false === $pos) {
                return $inComment;
            } else {
                if ($pos == 0
                    || ($inComment
                        ? substr($line, $pos, 3)
                        : substr($line, $pos-1, 3)) != '*/*')
                {
                        $inComment = ! $inComment;
                }
                $line = substr($line, $pos + 2);
            }
        }
        return $inComment;
    }
    
    
    private static function _addNote($line, $note, $inComment, $padTo)
    {
        return $inComment
            ? '/* ' . str_pad($note, $padTo, ' ', STR_PAD_RIGHT) . ' *| ' . $line
            : '/* ' . str_pad($note, $padTo, ' ', STR_PAD_RIGHT) . ' */ ' . $line;
    }
}
