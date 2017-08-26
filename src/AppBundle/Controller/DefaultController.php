<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SmsBundle\Entity\Sms;
use SmsBundle\Form\SmsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Renders the index page.
     *
     * @param Request $_request
     * 
     * @Route("/", name="index")
     */
    public function indexAction(Request $_request)
    {
        // Init an empty SMS entity.
        $sms = new Sms();

        // Create the SMS form.
        $form = $this->createForm(SmsType::class, $sms, ['method' => 'POST', 'action' => '/sms/send']);

        // Return the view with the form.
        return $this->render('default/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Attempts to send an SMS and stores it in the DB.
     *
     * @param Request $_request
     * 
     * @Route("/sms/send", name="sms.send")
     */
    public function sendSms(Request $_request)
    {
        // Init an empty SMS.
        $sms = new Sms();

        // Create the form.
        $form = $this->createForm(SmsType::class, $sms, [
            'method' => 'POST', 
            'action' => '/sms/send'
        ]);
        
        // Handle the form with the request parameters.
        $form->handleRequest($_request);

        // If the form has been submitted, and the form is valid.
        if($form->isSubmitted() && $form->isValid())
        {
            // Get the data from the form.
            $data = $form->getData();

            // Get the entity manager.
            $em = $this->getDoctrine()->getManager();

            // Queue the entity for INSERT.
            $em->persist($sms);

            // Push any queued changes.
            $em->flush();

            // Return a success message.
            return new Response('Saved SMS with id');
        }
    }
}
