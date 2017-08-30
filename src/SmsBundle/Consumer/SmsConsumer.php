<?php

namespace SmsBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Twilio\Rest\Client;

class SmsConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $_message)
    {
        error_log("Getting here...");

        // Send the message via twillio.
        $message = unserialize($_message);

        // Create the client used to send the message.
        $client = new Client('AC2e25c8eccffce9f3ceca5b99a60803f7', '19862dc33edabc924124bd1930fa11e6');

        // Send the message.
        $client->messages->create(
            '07507309282', // $message['number']
            [
                'from' => '+441527962622',
                'body' => $message['message'],
                'statusCallback' => $message['callback']
            ]
        );
    }
}