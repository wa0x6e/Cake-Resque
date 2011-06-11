<?php

class ResqueShell {
  protected function loadModel($modelName) {
    if (App::import('Model', $modelName)) {
      $this->$modelName = new $modelName;
      return true;
    }
    return false;
  }

  protected function out($s, $line_break = true) {
    echo $s . ($line_break? "\n" : '');
  }
}
