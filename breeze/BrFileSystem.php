<?php

/**
 * Project:     Breeze framework
 * Author:      Jager Mesh (jagermesh@gmail.com)
 *
 * @version 1.1.0.0
 * @package Breeze Core
 */

require_once(dirname(__FILE__).'/BrSingleton.php');

class BrFileSystemObject {

  private $name;
  private $path;

  function __construct($path) {

    $info = pathinfo($path);
    $this->name = $info['basename'];
    $this->path = br()->fs()->normalizePath($info['dirname']);

  }

  function isFile() {

    return !$this->isDir();

  }

  function isDir() {

    return is_dir($this->nameWithPath());

  }

  function name() {

    return $this->name;

  }

  function path() {

    return $this->path;

  }

  function nameWithPath() {

    return br()->fs()->normalizePath($this->path) . $this->name;

  }

}

class BrFileSystem extends BrSingleton {

  public function normalizePath($path) {
   
     return rtrim($path, '/').'/';
  
  }

  public function normalizeFileName($fileName) {
   
    return preg_replace('~[^-A-Za-z0-9_.#$!()\[\]]~', '_', $fileName);
  
  }

  public function fileName($fileName, $addIndex = null) {
   
    $pathinfo = pathinfo($fileName);
    if ($addIndex) {
      return br($pathinfo, 'filename').'-'.$addIndex.'.'.br($pathinfo, 'extension');
    } else {
      return br($pathinfo, 'basename');
    }
  
  }

  public function filePath($path) {
   
    return $this->normalizePath(dirname($path));
  
  }

  public function fileExt($fileName) {
   
    $pathinfo = pathinfo($fileName);
    return br($pathinfo, 'extension');
  
  }

  public function fileExists($filePath) {
    
    return file_exists($filePath);

  }

  public function loadFromFile($fileName) {
    
    return file_get_contents($fileName);

  }

  public function saveToFile($fileName, $content) {
    
    file_put_contents($fileName, $content);

  }

  public function makeDir($path) {

    if (is_dir($path)) {
      return true;
    }

    $priorPath = dirname($path);
    if (!$this->makeDir($priorPath)) {
      return false;
    }
    
    br()->errorHandler()->disable();
    $result = @mkdir($path);
    br()->errorHandler()->enable();

    return $result;    
    
  }
  
  public function checkWriteable($path) {

    if (!is_writeable($path)) {
      throw new Exception('Can not create directory "' . $path .'"');
    }
    
  }

  public function createDir($path) {

    if (!$this->makeDir($path)) {
      throw new Exception('Can not create directory "' . $path .'"');
    }

    return $this;
    
  }

  public function iterateDir($startingDir, $mask, $callback = null) {

    if (gettype($mask) == 'string') {

    } else {
      $callback = $mask;
      $mask = null;
    }

    $startingDir = $this->normalizePath($startingDir);
    if ($dir = opendir($startingDir)) {
      while (($file = readdir($dir)) !== false) {
        $fullFileName = $startingDir.$file;
        if (($file != '..') && ($file != '.')) {
          $proceed = true;
          if ($mask) {
            $proceed = preg_match('#' . $mask . '#', $file);
          }
          if ($proceed) {
            $callback(new BrFileSystemObject($fullFileName));
          }
        }
      }
      closedir($dir);
    }

  }
  
}

