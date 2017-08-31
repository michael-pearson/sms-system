<?php

namespace SmsBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Twilio\Rest\Client;

class SmsConsumer implements ConsumerInterface
{
    /**
     * Runs when the consumer consumes a message sent by the producer.
     *
     * @param AMQPMessage $_message
     * @return void
     */
    public function execute(AMQPMessage $_message):void
    {
        // Fetch the message body containing the details we need.
        $message = unserialize($_message->body);

        // Create the client used to send the message.
        $client = new Client(getenv('TWILLIO_SID'), getenv('TWILLIO_AUTH_TOKEN'));

        // Send the message.
        // TODO - change to actual number
        $client->messages->create(
            '07507309282', // $message['number']
            [
                'from' => getenv('TWILLIO_FROM_NUMBER'),
                'body' => $message['message'],
                'statusCallback' => $message['callback']
            ]
        );
    }
}