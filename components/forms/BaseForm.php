<?php

namespace Components\Forms;

use Nette\Application\UI\Form;


class BaseForm extends Form {
    
    public $modelLoader;
    
    public function __construct($parent, $name){
        parent::__construct($parent, $name);
        $this->modelLoader = $this->getPresenter()->context->modelLoader;
        
        $this->getElementPrototype()
                            ->novalidate('novalidate')
                            ->class[] = 'form-with-smooth-buttons';
        
        
    }
    
//    public function sectionFilled($item, $args) {
//        
//        dump($args);
//        
//    }
  
}

