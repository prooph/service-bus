<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:13
 */

namespace Prooph\ServiceBus;

use Prooph\ServiceBus\Exception\RuntimeException;
use Prooph\ServiceBus\Message\MessageNameProvider;
use Prooph\ServiceBus\Message\PayloadInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Class Event
 *
 * The class can be used as base class for events, but it is no requirement.
 * You can dispatch all kinds of messages as long as you register plugins that are able to handle your messages.
 *
 * @deprecated This class will be removed in v4.0, use the one provided by prooph/common instead
 * @package Prooph\ServiceBus\Event
 * @author Alexander Miertsch <contact@prooph.de>
 */
class Event implements MessageNameProvider
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Uuid
     */
    protected $uuid;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var \DateTime
     */
    protected $occurredOn;

    /**
     * @var array
     */
    protected $payload = array();

    /**
     * @return Event
     */
    public static function getNew()
    {
        return new static(get_called_class());
    }

    /**
     * @param mixed $aPayload
     * @return Event
     */
    public static function fromPayload($aPayload)
    {
        return new static(get_called_class(), $aPayload);
    }

    /**
     * @param string $aMessageName
     * @param null $aPayload
     * @param int $aVersion
     * @param Uuid $aUuid
     * @param \DateTime $aOccurredOn
     * @throws \Prooph\ServiceBus\Exception\RuntimeException
     */
    public function __construct($aMessageName, $aPayload = null, $aVersion = 1, Uuid $aUuid = null, \DateTime $aOccurredOn = null)
    {
        $this->name = $aMessageName;

        if (!is_null($aPayload)) {

            if (! is_array($aPayload)) {
                $aPayload = $this->convertPayload($aPayload);
            }

            if (is_array($aPayload)) {
                $this->payload = $aPayload;
            } elseif ($aPayload instanceof PayloadInterface) {
                $this->payload = $aPayload->getArrayCopy();
            } else {
                throw new RuntimeException(
                    sprintf(
                        "Payload must be an array or instance of Prooph\ServiceBus\Message\PayloadInterface, "
                        . "instance of %s given.",
                        ((is_object($aPayload)? get_class($aPayload) : gettype($aPayload)))
                    )
                );
            }
        }

        $this->version = $aVersion;

        if (is_null($aUuid)) {
            $aUuid = Uuid::uuid4();
        }

        $this->uuid = $aUuid;

        if (is_null($aOccurredOn)) {
            $aOccurredOn = new \DateTime();
        }

        $this->occurredOn = $aOccurredOn;
    }

    /**
     * @return array
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * @return Uuid
     */
    public function uuid()
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }

    /**
     * Hook point for extending classes, override this method to convert payload to array
     *
     * @param mixed $aPayload
     * @return mixed
     */
    protected function convertPayload($aPayload)
    {
        return $aPayload;
    }

    /**
     * @return string Name of the message
     */
    public function getMessageName()
    {
        return $this->name;
    }
}
 