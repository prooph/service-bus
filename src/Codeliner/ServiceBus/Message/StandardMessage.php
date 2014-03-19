<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 18:12
 */

namespace Codeliner\ServiceBus\Message;

/**
 * Class StandardMessage
 *
 * @package Codeliner\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class StandardMessage implements MessageInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var MessageHeaderInterface
     */
    protected $header;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @param array $aMessageArray
     * @return MessageInterface
     */
    public static function fromArray(array $aMessageArray)
    {
        \Assert\that($aMessageArray)->keyExists('name')->keyExists('header')->keyExists('payload');
        \Assert\that($aMessageArray['name'])->notEmpty()->string();
        \Assert\that($aMessageArray['header'])->isArray();
        \Assert\that($aMessageArray['payload'])->isArray();

        $header = MessageHeader::fromArray($aMessageArray['header']);

        return new static($aMessageArray['name'], $header, $aMessageArray['payload']);
    }

    /**
     * @param string                 $aName
     * @param MessageHeaderInterface $aMessageHeader
     * @param array                  $aPayload
     */
    public function __construct($aName, MessageHeaderInterface $aMessageHeader, array $aPayload)
    {
        \Assert\that($aName)->notEmpty('Message.name must not be empty')->string('Message.name must be string');

        $this->name    = $aName;
        $this->header  = $aMessageHeader;
        $this->payload = $aPayload;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return MessageHeaderInterface
     */
    public function header()
    {
        return $this->header;
    }

    /**
     * @return array
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'name'    => $this->name(),
            'header'  => $this->header()->toArray(),
            'payload' => $this->payload()
        );
    }
}
