<?php




defined('MOODLE_INTERNAL') || die();


define( "STATE_NONE",1 ); define( "STATE_PARAGRAPH",2 ); define( "STATE_BLOCKQUOTE",3 ); define( "STATE_PREFORM",4 ); define( "STATE_NOTIKI",5 ); 

define( "LIST_NONE", 1 ); define( "LIST_UNORDERED", 2 ); define( "LIST_ORDERED", 3 ); define( "LIST_DEFINITION", 4 ); 


class WikiToMarkdown {

  var $block_state;
  var $list_state;
  var $list_depth;
  var $list_backtrack;
  var $output;   var $courseid;

  function close_block( $state ) {
    
        $lclose = "";
    if ($this->list_state != LIST_NONE) {
      $lclose = $this->do_list( " ",true );
    }

    $sclose = "";
    switch ($state) {
      case STATE_PARAGRAPH:
        $sclose =  "\n";
        break;
      case STATE_BLOCKQUOTE:
        $sclose =  "\n";
        break;
      case STATE_PREFORM:
        $sclose =  "</pre>\n";
        break;
      case STATE_NOTIKI:
        $sclose =  "\n";
        break;
    }

    return $lclose . $sclose;
  }

  function do_replace( $line, $mark, $tag ) {
        
            $bodge = chr(1);
    $line = preg_replace( '/([[:alnum:]])'.$mark.'([[:alnum:]])/i', '\\1'.$bodge.'\\2',$line );

    $regex = '/(^| |[(.,])'.$mark.'([^'.$mark.']*)'.$mark.'([^[:alnum:]]|$)/i';
    $replace = '\\1<'.$tag.'>\\2</'.$tag.'>\\3';
    $line = preg_replace( $regex, $replace, $line );

        $line = preg_replace( '/'.$bodge.'/i', $mark, $line );

    return $line;
  }


  function do_replace_markdown( $line, $mark, $tag ) {
            
            $bodge = chr(1);
    $line = preg_replace( '/([[:alnum:]])'.$mark.'([[:alnum:]])/i', '\\1'.$bodge.'\\2',$line );

    $regex = '/(^| |[(.,])'.$mark.'([^'.$mark.']*)'.$mark.'([^[:alnum:]]|$)/i';
    $replace = '\\1'.$tag.'\\2'.$tag.'\\3';
    $line = preg_replace( $regex, $replace, $line );

        $line = preg_replace( '/'.$bodge.'/i', $mark, $line );

    return $line;
  }


  function do_replace_sub( $line, $mark, $tag ) {
        
    $regex = '/'.$mark.'([^'.$mark.']*)'.$mark.'/i';
    $replace = '<'.$tag.'>\\1</'.$tag.'>';

    return preg_replace( $regex, $replace, $line );
  }

  function do_list( $line, $blank=false ) {
        
        if ($blank) {
      $listchar="";
      $count = 0;
    }
    else {
      $listchar = $line{0};
      $count = strspn( $line, $listchar );
      $line = preg_replace( "/^[".$listchar."]+ /i", "", $line );
    }

        $list_tag = "";
    $list_close_tag = "";
    $item_tag = "";
    $item_close_tag = "";
    $list_style = LIST_NONE;
    switch ($listchar) {
      case '*':
        $list_tag = "";
        $list_close_tag = "";
        $item_tag = "*";
        $item_close_tag = "";
        $list_style = LIST_UNORDERED;
        break;
      case '#':
        $list_tag = "";
        $list_close_tag = "";
        $item_tag = "1.";
        $item_close_tag = "";
        $list_style = LIST_ORDERED;
        break;
      case ';':
        $list_tag = "<dl>";
        $list_close_tag = "</dl>";
        $item_tag = "<dd>";
        $item_close_tag = "</dd>";
        $list_style = LIST_DEFINITION;
        break;
      case ':':
        $list_tag = "<dl>";
        $list_close_tag = "</dl>";
        $item_tag = "<dt>";
        $item_close_tag = "</dt>";
        $list_style = LIST_DEFINITION;
        break;
      }

        $tags = "";

        for ($i=$this->list_depth; $i>$count; $i-- ) {
      $close_tag = array_pop( $this->list_backtrack );
      $tags = $tags . $close_tag;
      }

        for ($i=$this->list_depth; $i<$count; $i++ ) {
      array_push( $this->list_backtrack, "$list_close_tag" );
      $tags = $tags . "$list_tag";
    }

        $this->list_state = $list_style;
    $this->list_depth = $count;

        $indent = substr( "                      ",1,$count-1 );

    if ($blank) {
      $newline = $tags;
    }
    else {
      $newline = $tags . $indent . "$item_tag " . $line . "$item_close_tag";
    }

    return $newline;
  }


  function line_replace( $line ) {
        
    global $CFG;

        
        if (preg_match( "/^([*]+|[#]+|[;]+|[:]+) /i", $line )) {
      $line = $this->do_list( $line );
    }

                  $line = str_replace( "...", " &#8230; ", $line );
    $line = str_replace( "(R)", "&#174;", $line );
    $line = str_replace( "(r)", "&#174;", $line );
    $line = str_replace( "(TM)", "&#8482;", $line );
    $line = str_replace( "(tm)", "&#8482;", $line );
    $line = str_replace( "(C)", "&#169;", $line );
    $line = str_replace( "1/4", "&#188;", $line );
    $line = str_replace( "1/2", "&#189;", $line );
    $line = str_replace( "3/4", "&#190;", $line );
    $line = preg_replace( "/([[:digit:]]+[[:space:]]*)x([[:space:]]*[[:digit:]]+)/i", "\\1&#215;\\2", $line );                     $line = $this->do_replace_markdown( $line, "\*", "**" );
    $line = $this->do_replace_markdown( $line, "/", "*" );
    $line = $this->do_replace( $line, "\+", "ins" );
        $line = $this->do_replace_sub( $line, "~", "sub" );
    $line = $this->do_replace_sub( $line, "\^", "sup" );
    $line = $this->do_replace( $line, "%", "code" );
    $line = $this->do_replace( $line, "@", "cite" );

            $line = preg_replace("/([[:space:]]|^)([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])\(([^)]+)\)/i",
      "\\1[\\5](\\2://\\3\\4)", $line);
    $line = preg_replace("/([[:space:]])www\.([^[:space:]]*)([[:alnum:]#?/&=])\(([^)]+)\)/i",
      "\\1[\\5](http://www.\\2\\3)", $line);

        $line = preg_replace("/([[:space:]]|^)([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])/i",
      "\\1<\\2://\\3\\4>", $line);
    $line = preg_replace("/([[:space:]])www\.([^[:space:]]*)([[:alnum:]#?/&=])/i",
      "\\1<http://www.\\2\\3\>", $line);

            $line = preg_replace("/([[:space:]]|^)([[:alnum:]._-]+@[[:alnum:]._-]+)\(([^)]+)\)/i",
       "\\1<a href=\"mailto:\\2\">\\3</a>", $line);

            if (preg_match( "/^!([1-6]) (.*)$/i", $line, $regs )) {
      $depth = substr( $line, 1, 1 );
      $out = substr( '##########', 0, $depth);
      $line = preg_replace( "/^!([1-6]) (.*)$/i", "$out \\2", $line );
    }

            $line = preg_replace( "/([A-Z]+)\(([^)]+)\)/", "<acronym title=\"\\2\">\\1</acronym>", $line );

            $line = preg_replace("/ ([a-zA-Z]+):([0-9]+)\(([^)]+)\)/i",
       " [\\3](".$CFG->wwwroot."/mod/\\1/view.php?id=\\2) ", $line );

    $coursefileurl = array(moodle_url::make_legacyfile_url($this->courseid, null));

        $line = preg_replace("#/([a-zA-Z0-9./_-]+)(png|gif|jpg)\(([^)]+)\)#i",
            "![\\3](".$coursefileurl."/\\1\\2)", $line );

        $line = preg_replace("#file:/([[:alnum:]/._-]+)\(([^)]+)\)#i",
            "[\\2](".$coursefileurl."/\\1)", $line );

    return $line;
  }

  function convert( $content,$courseid ) {

            
        $this->output = "";
    $this->block_state = STATE_NONE;
    $this->list_state = LIST_NONE;
    $this->list_depth = 0;
    $this->list_backtrack = array();
    $this->spelling_on = false;
    $this->courseid = $courseid;

        $lines = explode( "\n",$content );
    $buffer = "";

        foreach( $lines as $line ) {
            $blank_line = preg_match( "/^[[:blank:]\r]*$/i", $line );
      if ($blank_line) {
                $buffer = $buffer . $this->close_block( $this->block_state );
        $this->block_state = STATE_NONE;
        continue;
      }

            if ($this->block_state == STATE_NONE) {
                if (preg_match( "/^> /i",$line )) {
                    $buffer = $buffer . $this->line_replace( $line ). "\n";
          $this->block_state = STATE_BLOCKQUOTE;
        }
        else
        if (preg_match( "/^  /i",$line) ) {
                              $buffer = $buffer . "<pre>\n";
          $buffer = $buffer . $this->line_replace($line) . "\n";
          $this->block_state = STATE_PREFORM;
        }
        else
        if (preg_match("/^\% /i",$line) ) {
                                                $buffer = $buffer . "    " . preg_replace( "/^\%/i","",$line) . "\n";
                $this->block_state = STATE_NOTIKI;
        }
        else {
                    $buffer = $buffer . $this->line_replace($line) . "\n";
          $this->block_state = STATE_PARAGRAPH;
        }
        continue;
      }

      if (($this->block_state == STATE_PARAGRAPH) |
          ($this->block_state == STATE_BLOCKQUOTE) |
          ($this->block_state == STATE_PREFORM) ) {
        $buffer = $buffer . $this->line_replace($line) . "\n";
        continue;
      }
      elseif ($this->block_state == STATE_NOTIKI) {
        $buffer = $buffer . "    " .$line . "\n";
      }
    }

        $buffer = $buffer . $this->close_block( $this->block_state );

        return $buffer;
  }
}
