<?php

 
 

function ouwiki_diff_internal($file1,$file2) {
		$n=count($file2);
	$m=count($file1);
    
        if($n==0) 
    {
        $result=array();
        for($i=1;$i<=$m;$i++) 
        {
            $result[$i]=0;
        }
        return $result;
    }
	
			
	$V=array(); 
	for($j=1;$j<=$n;$j++) {
		$V[$j]=new StdClass;
		$V[$j]->serial=$j;
		$V[$j]->hash=crc32($file2[$j]);
	}
    
			
	usort($V,"ouwiki_diff_sort_v");
    
        array_unshift($V,'bogus');
    unset($V[0]);
	
			
			
	$E=array();
	$E[0]=new StdClass;
	$E[0]->serial=0;
	$E[0]->last=true;
	for($j=1;$j<=$n;$j++) {
		$E[$j]=new StdClass;
		$E[$j]->serial=$V[$j]->serial;
		$E[$j]->last=$j===$n || $V[$j]->hash!==$V[$j+1]->hash;
	}

				
		    $P=array();
	for($i=1;$i<=$m;$i++) {
				$P[$i]=ouwiki_diff_find_last($V,$E,crc32($file1[$i]));
	}
	
							
			
				
	$candidates=array();
	$candidates[0]=new StdClass;
	$candidates[0]->a=0;
	$candidates[0]->b=0;
	$candidates[0]->previous=null;
	$candidates[1]=new StdClass;
	$candidates[1]->a=$m+1; 
	$candidates[1]->b=$n+1;
	$candidates[1]->previous=null;
	
	$K=array();
	$K[0]=0; 	$K[1]=1; 	$k=0;
	
			
	for($i=1;$i<=$m;$i++) {
		if($P[$i]!==0) {
			ouwiki_diff_merge($K,$k,$i,$E,$P[$i],$candidates);
		}
	}
	
			
	$J=array();
	for($i=1;$i<=$m;$i++) {
		$J[$i]=0;
	}
	
		    
	$index=$K[$k];
	while(!is_null($index)) {
                if($candidates[$index]->a!=0) {
		    $J[$candidates[$index]->a]=$candidates[$index]->b;
        }
		$index=$candidates[$index]->previous;
	}	
	
			
	for($i=1;$i<=$m;$i++) {
		if($J[$i]!=0 && $file1[$i]!=$file2[$J[$i]]) {
			$J[$i]=0;
		}
	}
	
		return $J;
}


function ouwiki_diff_merge(&$K,&$k,$i,&$E,$p,&$candidates) {
	$r=0;
	$c=$K[0];
	
	while(true) {
	    $j=$E[$p]->serial; 	    
	    	    $min=$r;
	    $max=$k+1;
	    
		while(true) {
			$try = (int)(($min+$max)/2);
			if($candidates[$K[$try]]->b >= $j) {
				$max=$try;
			} else if($candidates[$K[$try+1]]->b <= $j) {
				$min=$try+1;
			} else { 			    $s=$try;
				break;
			}
			if($max<=$min) {
			    $s=-1;
			    break;
			}
		}

		if($s>-1) {
			if($candidates[$K[$s+1]]->b > $j) {
								$index=count($candidates);
				$candidates[$index]=new StdClass;
				$candidates[$index]->a=$i;
				$candidates[$index]->b=$j;
				$candidates[$index]->previous=$K[$s];
				$K[$r]=$c;
				$r=$s+1;
				$c=$index; 			}	
		    
		    if($s===$k) {
		        $K[$k+2]=$K[$k+1];
		        $k++;
     		    break;
		    }		    
		}
		
		if($E[$p]->last) {
		    break;
		}
		
		$p++;
	}
	$K[$r]=$c;
	
}

function ouwiki_diff_sort_v($a,$b) {
    if($a->hash < $b->hash) {
    	return -1;
    } else if($a->hash > $b->hash) {
        return 1;    
    } else if($a->serial < $b->serial) {
        return -1;
    } else if($a->serial > $b->serial) {
        return 1;
    } else {
        return 0;
    }
}

function ouwiki_diff_find_last(&$V,&$E,$hash) {
        
        $min=1;
        end($V);
    $max=key($V)+1;
    while(true) {
        $try = (int)(($min+$max)/2);
        if($V[$try]->hash > $hash) {
            $max=$try;
        } else if($V[$try]->hash < $hash) {
            $min=$try+1;
        } else {             break;
        }
        if($max<=$min) {
        	            return 0;
        }
    }
    
        for($j=$try;!$E[$j-1]->last;$j--) ;
	return $j;
}




class ouwiki_line {
    
    var $words=array();
    
    
    public function __construct($data,$linepos) {
                
                $data=preg_replace('/\s/',' ',$data);
        
                                $data=str_replace(array('&nbsp;','&#xA0;','&#160;'),'      ',$data);
        
                $data=preg_replace_callback('/<.*?'.'>/',create_function(
            '$matches','return preg_replace("/./"," ",$matches[0]);'),$data);
            
                                $pos=0;
        while(true) {
                        $strlendata = strlen($data);
            for(;$pos < $strlendata && substr($data,$pos,1)===' ';$pos++) ;
            if($pos==$strlendata) {
                                break;
            }
            
                        $space2=strpos($data,' ',$pos);
            if($space2===false) {
                                $this->words[]=new ouwiki_word(substr($data,$pos),$pos+$linepos);
                break;
            } else {
                $this->words[]=new ouwiki_word(substr($data,$pos,$space2-$pos),$pos+$linepos);
                $pos=$space2;
            }
        }
    }

    
    public function ouwiki_line($data, $linepos) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($data, $linepos);
    }

    
    function get_as_string() {
        $result='';
        foreach($this->words as $word) {
            if($result!=='') {
                $result.=' ';
            }
            $result.=$word->word;
        }
        return $result;
    }
    
    
    static function get_as_strings($lines) {
        $strings=array();
        foreach($lines as $key=>$value) {
            $strings[$key]=$value->get_as_string();        
        }
        return $strings;
    }
    
    
    
    function is_empty() {
        return count($this->words)===0;
    }
}


class ouwiki_word {
    
    var $word;
    
    var $start;
    
    public function __construct($word,$start) {
        $this->word=$word;
        $this->start=$start;
    }

    
    public function ouwiki_word($word, $start) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($word, $start);
    }
}


function ouwiki_diff_html_to_lines($content) {
                        
            $content=preg_replace_callback(
        '^(<script .*?</script>)|(<object .*?</object>)|(<style .*?</style>)^i',create_function(
            '$matches','return preg_replace("/./"," ",$matches[0]);'),$content); 
    
        $content=preg_replace('/[`]/',' ',$content);
    
        $blocktags=array('p','div','h1','h2','h3','h4','h5','h6','td','li');
    $taglist='';
    foreach($blocktags as $blocktag) {
        if($taglist!=='') {
            $taglist.='|';
        }
        $taglist.="<$blocktag>|<\\/$blocktag>";
    }
    $content=preg_replace_callback('/(('.$taglist.')\s*)+/i',create_function(
        '$matches','return "`".preg_replace("/./"," ",substr($matches[0],1));'),$content);
        
        $lines=array(); $index=1;
    $pos=0;
    while($pos<strlen($content)) {
        $nextline=strpos($content,'`',$pos);
        if($nextline===false) {
                        $nextline=strlen($content);
        }
        
        $linestr=substr($content,$pos,$nextline-$pos);
        $line=new ouwiki_line($linestr,$pos);
        if(!$line->is_empty()) {
            $lines[$index++]=$line;
        }
        $pos=$nextline+1;
    }
    return $lines; 
}

 
class ouwiki_change_range {
    var $file1start,$file1count;
    var $file2start,$file2count;
}


class ouwiki_changes {
    
    
    var $adds;
    
    
    var $deletes;
    
    
    var $changes;
    
    
    public function __construct($diff,$count2) {
                $this->deletes=self::internal_find_deletes($diff,$count2);
        
                        $this->adds=self::internal_find_deletes(
            ouwiki_diff_internal_flip($diff,$count2),count($diff));
        
                                        $this->changes=array();
        $matchbefore=0;
        $inrange=-1; $lastrange=-1;
        foreach($diff as $index1=>$index2) {
                                    if($index2===0 && !in_array($index1,$this->deletes)) {
                if($inrange===-1) {
                                        $inrange=count($this->changes);
                    $this->changes[$inrange]=new ouwiki_change_range;
                    $this->changes[$inrange]->file1start=$index1;
                    $this->changes[$inrange]->file1count=1;                    
                    $this->changes[$inrange]->file2start=$matchbefore+1;                     $this->changes[$inrange]->file2count=0;
                    $lastrange=$inrange;
                } else {
                                        $this->changes[$inrange]->file1count++;
                }
            } else {
                                $inrange=-1;
                                if($index2!==0) {
                                        $matchbefore=$index2;
                                        if($lastrange!==-1) {
                        $this->changes[$lastrange]->file2count=$index2
                            -$this->changes[$lastrange]->file2start;
                        $lastrange=-1;
                    }
                }
            }
        }
                if($lastrange!==-1) {
            $this->changes[$lastrange]->file2count=$count2
                -$this->changes[$lastrange]->file2start+1;
        }
    }

    
    public function ouwiki_changes($diff, $count2) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($diff, $count2);
    }

    
    function internal_find_deletes($diff,$count2) {
        $deletes=array();
        
                                        $squidges=array();
        $lowest=0;
        $countdiff = count($diff);
        for($index1=$countdiff;$index1>=1;$index1--) {
            $index2=$diff[$index1];
            if($index2===0) {
                $squidges[$index1]=$lowest;
            } else {
                $lowest=$index2;
            }
        }
        
                                $highest=0;
        foreach($diff as $index1=>$index2) {
            if($index2===0) {
                if($highest===$count2 || $highest+1===$squidges[$index1]) {
                                        $deletes[]=$index1;                    
                } 
            } else {
                $highest=$index2;                
            }
        }
        return $deletes;        
    }
}


function ouwiki_diff_internal_flip($diff,$count2) {
    $flip=array();
    for($i=1;$i<=$count2;$i++) {
        $flip[$i]=0;
    }
    foreach($diff as $index1=>$index2) {
        if($index2!==0) {
            $flip[$index2]=$index1;
        }
    }
    return $flip;
}


function ouwiki_diff_words($lines1,$lines2) {
        $deleted=array();
    $added=array();
        $linediff=ouwiki_diff(
        ouwiki_line::get_as_strings($lines1),
        ouwiki_line::get_as_strings($lines2));
        
        foreach($linediff->deletes as $deletedline) {
        $deleted = array_merge($deleted, $lines1[$deletedline]->words);
    }
        foreach($linediff->adds as $addedline) {
        $added = array_merge($added, $lines2[$addedline]->words);
    }
    
        foreach($linediff->changes as $changerange) {
                $file1words=array();
        for($index=$changerange->file1start;
            $index<$changerange->file1start+$changerange->file1count;$index++) {
            foreach($lines1[$index]->words as $word) {
                $file1words[]=$word;
            }
        }
        $file2words=array();
        for($index=$changerange->file2start;
            $index<$changerange->file2start+$changerange->file2count;$index++) {
            foreach($lines2[$index]->words as $word) {
                $file2words[]=$word;
            }
        }
                
                array_unshift($file1words,'dummy');
        unset($file1words[0]);
        array_unshift($file2words,'dummy');
        unset($file2words[0]);
        
                $file1strings=array();
        foreach($file1words as $index=>$word) {
            $file1strings[$index]=$word->word;
        }
        $file2strings=array();
        foreach($file2words as $index=>$word) {
            $file2strings[$index]=$word->word;
        }
        
                $worddiff=ouwiki_diff($file1strings,$file2strings);
        foreach($worddiff->adds as $index) {
            $added[]=$file2words[$index];
        }
        foreach($worddiff->deletes as $index) {
            $deleted[]=$file1words[$index];
        }
        foreach($worddiff->changes as $changerange) {
            for($index=$changerange->file1start;
                $index<$changerange->file1start+$changerange->file1count;$index++) {
                $deleted[]=$file1words[$index];
            }
            for($index=$changerange->file2start;
                $index<$changerange->file2start+$changerange->file2count;$index++) {
                $added[]=$file2words[$index];
            }
        }
    }
    
    return array($deleted,$added);
}


function ouwiki_diff($file1,$file2) {
    return new ouwiki_changes(ouwiki_diff_internal($file1,$file2),count($file2));
}


function ouwiki_diff_add_markers($html,$words,$markerclass,$beforetext,$aftertext) {
        usort($words, create_function('$a,$b','return $a->start-$b->start;'));
    
            $spanstart="<ouwiki_diff_add_markers>";
    $pos=0;
    $result='';
    foreach($words as $word) {
                $result.=substr($html,$pos,$word->start-$pos);
                $result.=$spanstart.$word->word.'</ouwiki_diff_add_markers>';
                $pos=$word->start+strlen($word->word);
    }

        $result.=substr($html,$pos);
    
            $result=preg_replace('^</ouwiki_diff_add_markers>(\s*)<ouwiki_diff_add_markers>^','$1',$result);
    
        $result=preg_replace('^<ouwiki_diff_add_markers>^',$beforetext.'<span class="'.$markerclass.'">',$result);
    $result=preg_replace('^</ouwiki_diff_add_markers>^','</span>'.$aftertext,$result);
    
    return $result;
}


function ouwiki_diff_html($html1,$html2) {
    $lines1=ouwiki_diff_html_to_lines($html1);
    $lines2=ouwiki_diff_html_to_lines($html2);
    list($deleted,$added)=ouwiki_diff_words($lines1,$lines2);
    $result1=ouwiki_diff_add_markers($html1,$deleted,'ouw_deleted',
        '<strong class="accesshide">'.get_string('deletedbegins','wiki').'</strong>',
        '<strong class="accesshide">'.get_string('deletedends','wiki').'</strong>');
    $result2=ouwiki_diff_add_markers($html2,$added,'ouw_added',
        '<strong class="accesshide">'.get_string('addedbegins','wiki').'</strong>',
        '<strong class="accesshide">'.get_string('addedends','wiki').'</strong>');
    return array($result1,$result2);    
}

