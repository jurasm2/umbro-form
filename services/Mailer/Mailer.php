<?php

/**
 * @author     Marek Juras
 */

namespace Services;

use Nette;

class Mailer extends Nette\Object {
 
    
    public function sendMail($to, $template, $subject, $from = NULL, $attachments = array()) {         
        
        $mail = new \Nette\Mail\Message();
        $mail->setFrom($from ?: 'umbro.cz <noreply@umbro.cz>')
                //->addTo('jurasm2@gmail.com')
                ->setSubject($subject)
                ->setHtmlBody($template);
        
        foreach ((array) $to as $email) {
            $mail->addTo($email);
        }
        
        // add attachments
        foreach ($attachments as $a) {
            $mail->addAttachment($a);
        }
        
        $mail->send();
        
    }

  
}