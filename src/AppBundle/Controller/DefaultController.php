<?php

namespace AppBundle\Controller;

use Predis\Client as RedisClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SmsBundle\Entity\Sms;
use SmsBundle\Entity\Status;
use SmsBundle\Form\SmsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\Rest\Client;

class DefaultController extends Controller
{
    /**
     * Renders the index page.
     *
     * @param Request $_request
     * 
     * @Route("/", name="index")
     * @Method({"GET"})
     * 
     */
    public function indexAction(Request $_request)
    {
        // Get the current user.
        $user = $this->getUser();

        // Init an empty SMS entity.
        $sms = new Sms();

        // Create the SMS form.
        $form = $this->createForm(SmsType::class, $sms, 
        [
            'method' => 'POST', 
            'action' => '/'
        ]);

        // TODO - move repeated code into function.
        $messages = null;
        $myMessages = null;

        // If the user is logged in.
        if($user)
        {
            $messages = $this->getAllMessages();
            $myMessages = $this->getMyMessages();
        }

        // Return the view with the form.
        return $this->render('default/index.html.twig', [
            'form' => $form->createView(),
            'messages' => $messages,
            'myMessages' => $myMessages
        ]);
    }

    /**
     * Sends a message.
     *
     * @param Request $_request
     * 
     * @Route("/", name="sendMessage")
     * @Method({"POST"})
     */
    public function sendMessage(Request $_request)
    {
        // Fetch the current user.
        $user = $this->getUser();

        // Init an empty SMS entity.
        $sms = new Sms();

        // Create the SMS form.
        $form = $this->createForm(SmsType::class, $sms, 
        [
            'method' => 'POST', 
            'action' => '/'
        ]);

        // Handle the form with the request parameters.
        $form->handleRequest($_request);

        // Create a new redis connection.
        // TODO - put in env
        $client = new RedisClient([
            'scheme' => 'tcp',
            'host' => 'localhost',
            'port' => '6379'
        ]);

        $messages = null;
        $myMessages = null;

        // If the user is logged in.
        // TODO - move repeated code to function.
        if($user)
        {
            $messages = $this->getAllMessages();
            $myMessages = $this->getMyMessages();
        }

        // Get the existence of the users key.
        $hasKey = $client->exists("sms-sent." . $user->getId());

        // Check to see if there is a key for the current user.
        if($hasKey)
        {
            // TODO - need a function that rounds the TTL up to stop 0 showing.
            $form->addError(new FormError('You cannot send another SMS for ' . ($client->ttl('sms-sent.' . $user->getId()) == 0 ? 1 : $client->ttl('sms-sent.' . $user->getId())) . ' seconds.'));

            // Return with the form and errors.
            return $this->render('default/index.html.twig', [
                'form' => $form->createView(),
                'messages' => $messages,
                'myMessages' => $myMessages
            ]);
        }

        // If the form has been submitted, the form is valid and it is not too soon.
        // TODO add too soon check to function.
        if($form->isSubmitted() && $form->isValid())
        {
            // Set the SMS user.
            $sms->setUser($user);

            // Fetch the queued status.
            $status = $this->getStatusByShortname('QUEUED');

            // Set the SMS status.
            $sms->setStatus($status);

            // Get the entity manager.
            $em = $this->getDoctrine()->getManager();

            // Queue the entity for INSERT.
            $em->persist($sms);

            // Push any queued changes.
            $em->flush();

            // Send the message.
            $message = [
                'number' => $sms->getNumber(),
                'message' => $sms->getMessage(),
                'callback' => "http://190ba4a9.ngrok.io/messages/" . $sms->getId() . "/callback"//$this->generateUrl('callback', ['id' => $sms->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ];

            // Add the sms to queue.
            $this->get('old_sound_rabbit_mq.send_sms_producer')->publish(serialize($message));

            // Create the client used to send the message.
            // TODO - move to env
            $twillioClient = new Client('AC2e25c8eccffce9f3ceca5b99a60803f7', '19862dc33edabc924124bd1930fa11e6');

            // Send the message.
            // TODO - move to queue.
            // $twillioClient->messages->create(
            //     '07507309282', // $message['number']
            //     [
            //         'from' => '+441527962622',
            //         'body' => $message['message'],
            //         'statusCallback' => $message['callback']
            //     ]
            // );

            // Add a flash message.
            $this->addFlash('send.success', "SMS queued successfully.");

            // Stop the user sending SMS for 15 seconds.
            $client->set("sms-sent." . $user->getId(), 1);
            $client->expire("sms-sent." . $user->getId(), 15);

            // Redirect to the index.
            return $this->redirectToRoute('index');
        }

        // If the form wasn't submitted, or it is invalid, send back with the form.
        return $this->render('default/index.html.twig', [
            'form' => $form->createView(),
            'messages' => $messages,
            'myMessages' => $myMessages
        ]);
    }

    /**
     * The callback route for twillio requests.
     *
     * @param Request $_request
     * 
     * @Route("/messages/{id}/callback", name="callback")
     * @Method({"POST"})
     */
    public function callback(Request $_request)
    {
        // Fetch the sms status from the db.
        $status = $this->getStatusByShortname(strtoupper($_request->get('SmsStatus')));

        // If we couldn't find the status.
        if(is_null($status))
        {
            // We couldn't find the status returned by twillio, do nothing.
            // In a real project we would handle this by adding a new status to represent this,
            // Or we would put the SMS into an error state.
            // ...
        }

        // Fetch the sms from the db.
        $sms = $this->getSmsById((int)$_request->get('id'));

        // Set the sms status.
        $sms->setStatus($status);

        // Get the entity manager.
        $em = $this->getDoctrine()->getManager();

        // Queue the entity for INSERT.
        $em->persist($sms);

        // Push any queued changes.
        $em->flush();

        // Return a 200 OK response.
        return new Response();
    }

    /**
     * Returns a list of the messages stored in the database.
     *
     * @return array
     */
    private function getAllMessages()
    {
        // Init the messages.
        $messages = null;

        // If the user has the admin role.
        if($this->getUser()->hasRole('ROLE_ADMIN'))
        {
            // Fetch all of the messages.
            $messages = $this->getDoctrine()
                ->getRepository(Sms::class)
                ->findAllOrderedByDateDesc();
        }

        // Return the messages.
        return $messages;
    }

    /**
     * Returns a list of the messages stored in the database.
     *
     * @return array
     */
    private function getMyMessages()
    {
        // Init the messages.
        $messages = null;

        // If the user has the user role.
        if($this->getUser()->hasRole('ROLE_USER'))
        {
            // Fetch all of the users messages.
            $messages = $this->getDoctrine()
                ->getRepository(Sms::class)
                ->findByUserOrderedByDateDesc($this->getUser());
        }

        // Return the messages.
        return $messages;
    }

    /**
     * Returns a status matching the passed shortname.
     *
     * @return Status
     */
    private function getStatusByShortname(string $_shortname):?Status
    {
        // Init the status.
        $status = null;

        // Fetch the status.
        $status = $this->getDoctrine()
            ->getRepository(Status::class)
            ->findByShortname($_shortname);

        // Return the status.
        return $status;
    }

    /**
     * Returns an SMS matching the passed id.
     *
     * @return SMS
     */
    private function getSmsById(int $_id):?Sms
    {
        // Init the sms.
        $sms = null;

        // Fetch the sms.
        $sms = $this->getDoctrine()
            ->getRepository(Sms::class)
            ->findSingleById($_id);

        // Return the sms.
        return $sms;
    }
}
