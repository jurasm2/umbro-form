<?php

namespace Components\Forms;

use Nette,
    Nette\Application\UI\Form;

class SpringTimeRegisterForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
        $numberOfOtherSections = 5;
        
       
        $event = $this->presenter->getEvent();
        
        $this->addHidden('event_id', $event['event_id']);
        
        $this->addCheckbox('agree', 'Souhlasím se zpracováním osobních údajů pro marketingové účely firmy UNISPORT TRADE s.r.o.')
                                ->setRequired('Musíte souhlasit s podmínkami');
        
        $mainSection = $this->addContainer('main');
        
        $mainSection->addText('name', 'Jméno:')
                            ->addRule(Form::MIN_LENGTH, 'Minimální délka jména je %d znaků', 3)
                            ->addRule(Form::MAX_LENGTH, 'Maximální délka jména je %d znaků', 20);
        $mainSection->addText('surname', 'Příjmení:')
                            ->addRule(Form::MIN_LENGTH, 'Minimální délka příjmení je %d znaků', 3)
                            ->addRule(Form::MAX_LENGTH, 'Maximální délka příjmení je %d znaků', 20);
        $mainSection->addText('email', 'E-Mail:')
                            ->addRule(Form::EMAIL, 'Email není ve správném formátu');
                            
        
        
        $otherSections = $this->addContainer('otherSections');
        
        $sections = array();
        for ($i = 1; $i <= $numberOfOtherSections; $i++) {    
            $sections[$i] = $otherSections->addContainer('section'.$i);
            $sections[$i]->addText('name', 'Jméno:');
//                                    ->addRule(Form::MIN_LENGTH, 'Minimální délka jména je %d znaků', 3)
//                                    ->addRule(Form::MAX_LENGTH, 'Maximální délka jména je %d znaků', 20);
            $sections[$i]->addText('surname', 'Příjmení:');
//                                    ->addCondition(Form::FILLED)
//                                    ->addRule(Form::MIN_LENGTH, 'Minimální délka příjmení je %d znaků', 3)
//                                    ->addRule(Form::MAX_LENGTH, 'Maximální délka příjmení je %d znaků', 20);
            $sections[$i]->addText('email', 'E-Mail:')
                                    //->addRule(array($this, 'sectionFilled'), 'Bla bla', $otherSections[$i]);
                                    //->addConditionOn($sections[$i]['name'],Form::FILLED)
//                                    ->addConditionOn($otherSections[$i]['surname'],Form::FILLED)
                                    ->addCondition(Form::FILLED)
                                    ->addRule(Form::EMAIL, 'Email není ve správném formátu');
                                    
                    
            $sections[$i]['name']->getControlPrototype()->rel = 'control_first';
            $sections[$i]['name']->getControlPrototype()->class = 'control_'.$i;
            $sections[$i]['surname']->getControlPrototype()->class = 'control_'.$i;
            $sections[$i]['email']->getControlPrototype()->class = 'control_'.$i;
            
        }
        
        
        $this->addSubmit('send', 'Odeslat');
        $this->onSuccess[] = array($this, 'formSubmited');  
        
    }
    
    
    public function formSubmited($form) {
        $values = $form->getValues();
       
        $errors = array();
        
        $mainError = FALSE;
        
        //dump($cronFlags);
        // process main section
        $main = $values['main'];
        $main['event_id'] = $values['event_id'];
        $main['signoff_hash'] = sha1(serialize($main));
        $main['is_active'] = 1;
        
        try {
            $this->presenter->umbroModel->registerUser($main);   
            
            // send confirm mail
            $this->presenter->sendConfirmMail($main['email']);
        } catch (\DibiDriverException $e) {
            $errors[] = $main['email'];
            $mainError = TRUE;
        }
        
        
        if (!$mainError) {
            // process other sections
            foreach ($values['otherSections'] as $section) {
                $section['event_id'] = $values['event_id'];
                $section['signoff_hash'] = sha1(serialize($section));
                $section['is_active'] = 1;
                if (!empty($section['email'])) {
                    try {
                        $this->presenter->umbroModel->registerUser($section);
                        
                        // send confirm mail
                        $this->presenter->sendConfirmMail($section['email']);
                    } catch (\DibiDriverException $e) {
                        $errors[] = $section['email'];
                    }
                }

            }
        
        }
       
        
        
        if (!empty($errors)) {
            $this->presenter->flashMessage('Uvedené emaily už jsou zaregistrovány', 'error');
        } else {
            $this->presenter->flashMessage('Vaše registrace byla úspěšně zpracována');
            $this->presenter->redirect('sent', array('eventId' => 6));
        }
        
    }
    
   
    
}