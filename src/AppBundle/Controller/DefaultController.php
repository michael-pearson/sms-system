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
     * Renders the index page, with all of the users messages
     * and all of the messages sent by the system if they have
     * the admin role.
     *
     * @param Request $_request
     * @return Response
     * 
     * @Route
     * (
     *      "/", 
     *      name="index"
     * )
     * @Method({"GET"})
     * 
     */
    public function indexAction(Request $_request):Response
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

        // Return the view with the form.
        return $this->render('default/index.html.twig', [
            'form' => $form->createView(),
            'messages' => $this->getAllMessages(),
            'myMessages' => $this->getMyMessages()
        ]);
    }

    /**
     * Sends a message with the message and number provided in the
     * request body.
     *
     * @param Request $_request
     * @return Response
     * 
     * @Route
     * (
     *      "/", 
     *      name="sendMessage"
     * )
     * @Method({"POST"})
     */
    public function sendMessage(Request $_request):Response
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
        $client = new RedisClient([
            'scheme' => getenv('REDIS_SCHEME') ? getenv('REDIS_SCHEME') : 'tcp',
            'host' => getenv('REDIS_HOST') ? getenv('REDIS_HOST') : 'localhost',
            'port' => getenv('REDIS_POST') ? getenv('REDIS_POST') : '6379',
        ]);

        // Get the existence of the users key.
        $hasKey = $client->exists("sms-sent." . $user->getId());

        // If there is a sms sent key for the current user.
        if($hasKey)
        {
            // TODO - need a function that rounds the TTL up to stop 0 showing.
            $form->addError(new FormError('You cannot send another SMS for ' . ($client->ttl('sms-sent.' . $user->getId()) == 0 ? 1 : $client->ttl('sms-sent.' . $user->getId())) . ' seconds.'));

            // Return with the form and errors.
            return $this->render('default/index.html.twig', [
                'form' => $form->createView(),
                'messages' => $this->getAllMessages(),
                'myMessages' => $this->getMyMessages()
            ]);
        }

        // If the form has been submitted, and the form is valid.
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

            // Queue the entity for insert.
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
     * The callback route for twillio requests. This function
     * will change the status of the corresponsing SMS to match
     * the status sent by the twillio request.
     *
     * @param int $_id
     * @param Request $_request
     * @return Response
     * 
     * @Route
     * (
     *      "/messages/{_id}/callback", 
     *      name="callback",
     *      requirements=
     *      {
     *          "id": "\d+"
     *      }
     * )
     * @Method({"POST"})
     */
    public function callback(int $_id, Request $_request):Response
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
        $sms = $this->getSmsById($_id);

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
     * Returns an array containing all of the messages 
     * stored in the database.
     *
     * @return array
     */
    private function getAllMessages():?array
    {
        // If we have a user.
        if(!$user = $this->getUser())
        {
            return null;
        }

        // If the user has the admin role.
        if($user->hasRole('ROLE_ADMIN'))
        {
            // Fetch all of the messages.
            return $this->getDoctrine()
                ->getRepository(Sms::class)
                ->findAllOrderedByDateDesc();
        }

        // User doesn't have the admin role return null.
        return null;
    }

    /**
     * Returns an array of the users messages 
     * stored in the database.
     *
     * @return array
     */
    private function getMyMessages():?array
    {
        // If we have a user.
        if(!$user = $this->getUser())
        {
            return null;
        }

        // If the user has the user role.
        if($user->hasRole('ROLE_USER'))
        {
            // Fetch all of the users messages.
            return $this->getDoctrine()
                ->getRepository(Sms::class)
                ->findByUserOrderedByDateDesc($this->getUser());
        }

        // User doesn't have the user role return null.
        return null;
    }

    /**
     * Returns a Status matching the passed shortname.
     *
     * @param string $_shortname
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
     * @param int $_id
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
