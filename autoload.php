<?php

spl_autoload_register(
    function($className) {
      $classPath = explode('_', $className);
      if ($classPath[0] != 'SAML2') {
        return;
      }
      // Drop 'Google', and maximum class file path depth in this project is 3.
      // $classPath = array_slice($classPath, 1, 2);

      $filePath = dirname(__FILE__) . '/lib/' . implode('/', $classPath) . '.php';
      if (file_exists($filePath)) {
        require_once($filePath);
      }
    }
);

