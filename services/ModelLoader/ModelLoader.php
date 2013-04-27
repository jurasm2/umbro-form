<?php

namespace Services;

use Nette;

class ModelLoader extends Nette\Object {
        private $context;
        private $connection;
        
        public function __construct($context) {
                $this->context = $context;
                $this->connection = $context->database;  
        }

        public function loadModel($modelName, $modelNamespace = 'Model') {
                $class = "$modelNamespace\\$modelName";
                if (class_exists($class)) {
                    return new $class($this->context);
                } else {
                    throw new Nette\InvalidStateException("Model class $class not found");
                }
        }
}