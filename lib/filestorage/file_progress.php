<?php


interface file_progress {
    
    const INDETERMINATE = -1;

    
    public function progress($progress = self::INDETERMINATE, $max = self::INDETERMINATE);
}
