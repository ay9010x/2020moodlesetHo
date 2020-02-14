<?php



interface iXHProfRuns {

  
  public function get_run($run_id, $type, &$run_desc);

  
  public function save_run($xhprof_data, $type, $run_id = null);
}



class XHProfRuns_Default implements iXHProfRuns {

  private $dir = '';
  private $suffix = 'xhprof';

  private function gen_run_id($type) {
    return uniqid();
  }

  private function file_name($run_id, $type) {

    $file = "$run_id.$type." . $this->suffix;

    if (!empty($this->dir)) {
      $file = $this->dir . "/" . $file;
    }
    return $file;
  }

  public function __construct($dir = null) {

                
    if (empty($dir)) {
      $dir = ini_get("xhprof.output_dir");
      if (empty($dir)) {

                $dir = "/tmp";

        xhprof_error("Warning: Must specify directory location for XHProf runs. ".
                     "Trying {$dir} as default. You can either pass the " .
                     "directory location as an argument to the constructor ".
                     "for XHProfRuns_Default() or set xhprof.output_dir ".
                     "ini param.");
      }
    }
    $this->dir = $dir;
  }

  public function get_run($run_id, $type, &$run_desc) {
    $file_name = $this->file_name($run_id, $type);

    if (!file_exists($file_name)) {
      xhprof_error("Could not find file $file_name");
      $run_desc = "Invalid Run Id = $run_id";
      return null;
    }

    $contents = file_get_contents($file_name);
    $run_desc = "XHProf Run (Namespace=$type)";
    return unserialize($contents);
  }

  public function save_run($xhprof_data, $type, $run_id = null) {

            $xhprof_data = serialize($xhprof_data);

    if ($run_id === null) {
      $run_id = $this->gen_run_id($type);
    }

    $file_name = $this->file_name($run_id, $type);
    $file = fopen($file_name, 'w');

    if ($file) {
      fwrite($file, $xhprof_data);
      fclose($file);
    } else {
      xhprof_error("Could not open $file_name\n");
    }

        return $run_id;
  }

  function list_runs() {
    if (is_dir($this->dir)) {
        echo "<hr/>Existing runs:\n<ul>\n";
        $files = glob("{$this->dir}/*.{$this->suffix}");
        usort($files, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
        foreach ($files as $file) {
            list($run,$source) = explode('.', basename($file));
            echo '<li><a href="' . htmlentities($_SERVER['SCRIPT_NAME'])
                . '?run=' . htmlentities($run) . '&source='
                . htmlentities($source) . '">'
                . htmlentities(basename($file)) . "</a><small> "
                . date("Y-m-d H:i:s", filemtime($file)) . "</small></li>\n";
        }
        echo "</ul>\n";
    }
  }
}
