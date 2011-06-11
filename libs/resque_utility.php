<?php

class ResqueUtility {
  function getJobs() {
    App::import('Core', 'Folder');
    $Folder = new Folder();
    $jobs = array();
    if ($Folder->cd(APP .'vendors'. DS .'shells'. DS .'jobs'. DS)) {
      $x = $Folder->read(true, true, true);
      $jobs = array_merge($jobs, $x[1]);
    }
    if ($Folder->cd(VENDORS . DS .'shells'. DS .'jobs'. DS)) {
      $x = $Folder->read(true, true, true);
      $jobs = array_merge($jobs, $x[1]);
    }

    return $jobs;
  }
}
