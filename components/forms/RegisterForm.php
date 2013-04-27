<?php

namespace Components\Forms;

use Nette,
    Nette\Application\UI\Form;

class RegisterForm extends BaseForm {

    public function __construct($parent, $name) {
        parent::__construct($parent, $name);        
        
        $numberOfOtherSections = 5;
        
       
        $event = $this->presenter->getEvent();
//        dump($event);
        
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
        
        
        $cronFlags = $this->presenter->umbroModel->getEventMailingFlags();
        
        //dump($cronFlags);
        // process main section
        $main = $values['main'];
        $main['event_id'] = $values['event_id'];
        $main['mailing1_sent'] = (isset($cronFlags[$values['event_id']])) ? $cronFlags[$values['event_id']]['mailing1_sent'] : 0;
        $main['mailing2_sent'] = (isset($cronFlags[$values['event_id']])) ? $cronFlags[$values['event_id']]['mailing2_sent'] : 0;
        
        $afterReg = $main['mailing1_sent'] == 1;
        $afterRegList = array();
        
        $event = $this->presenter->umbroModel->findEventByHash($this->presenter->getParam('hash'));
        
        try {
            
            $this->presenter->umbroModel->registerUser($main);
            
            // send confirm mail
            $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/confirm.latte');
            $template->registerFilter(new Nette\Latte\Engine);
            $template->registerHelperLoader('Nette\Templating\Helpers::loader');
            $attachments = array(
                            WWW_DIR . '/attachments/vip-pozvanka-termin-'.$event['event_id'].'.pdf',
                            WWW_DIR . '/attachments/pruvodni-dopis.pdf'
            );
            $template->basePath = $this->presenter->baseUri;
            $template->event = $event;
            $this->presenter->mailerService->sendMail($main['email'], $template, 'Úspěšná registrace na V.I.P. UMBRO HAPPY DAYS', NULL, $attachments);
            
            // if start of the registration is over -> send mail to factorystore@umbro.cz
            if ($afterReg) {
                $afterRegList[] = $main;
            }
        } catch (\DibiDriverException $e) {
            $errors[] = $main['email'];
            $mainError = TRUE;
        }
        
        
        if (!$mainError) {
            // process other sections
            foreach ($values['otherSections'] as $section) {
                $section['event_id'] = $values['event_id'];
                $section['mailing1_sent'] = (isset($cronFlags[$values['event_id']])) ? $cronFlags[$values['event_id']]['mailing1_sent'] : 0;
                $section['mailing2_sent'] = (isset($cronFlags[$values['event_id']])) ? $cronFlags[$values['event_id']]['mailing2_sent'] : 0;
                if (!empty($section['email'])) {
                    try {
                        $this->presenter->umbroModel->registerUser($section);
                        
                        // send confirm mail
                        $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/confirm.latte');
                        $template->registerFilter(new Nette\Latte\Engine);
                        $template->registerHelperLoader('Nette\Templating\Helpers::loader');
                        $attachments = array(
                                        WWW_DIR . '/attachments/vip-pozvanka-termin-'.$event['event_id'].'.pdf',
                                        WWW_DIR . '/attachments/pruvodni-dopis.pdf'
                        );
                        $template->basePath = $this->presenter->baseUri;
                        $template->event = $event;
                        $this->presenter->mailerService->sendMail($section['email'], $template, 'Úspěšná registrace na V.I.P. UMBRO HAPPY DAYS', NULL, $attachments);
                        
                        if ($afterReg) {
                            $afterRegList[] = $section;
                        }
                        
                    } catch (\DibiDriverException $e) {
                        $errors[] = $section['email'];
                    }
                }

            }
        
        }
        
        // process after registration
        if ($afterReg) {
            
            
            // if registration expired -> send mail to factorystore@umbro.cz
            $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/emailTemplates/after.latte');
            $template->registerFilter(new Nette\Latte\Engine);
            $template->registerHelperLoader('Nette\Templating\Helpers::loader');
            
            $template->regList = $afterRegList;
            $template->basePath = $this->presenter->baseUri;
            $template->event = $event;
            // change to factorystore@umbro.cz

            $this->presenter->mailerService->sendMail('factorystore@umbro.cz', $template, 'Pozdní registrace na V.I.P. UMBRO HAPPY DAYS');
        }
        
        
        
        $this->presenter->flashMessage('Vaše registrace byla úspěšně zpracována');
        
//        if (!empty($errors)) {
//            $this->presenter->flashMessage('Uvedené emaily už jsou zaregistrovány', 'error');
//        } else {
//        }
        $this->presenter->redirect('sent', array('eventId' => $event['event_id']));
        
    }
    
   
    
}