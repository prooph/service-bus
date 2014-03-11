<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 11.03.14 - 21:13
 */

namespace Codeliner\ServiceBus\Event;
use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Message\PayloadInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Class AbstractEvent
 *
 * @package Codeliner\ServiceBus\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class AbstractEvent implements EventInterface
{
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
    protected $createdOn;

    /**
     * @var array
     */
    protected $payload = array();

    /**
     * @param null      $aPayload
     * @param int       $aVersion
     * @param Uuid      $aUuid
     * @param \DateTime $aCreatedOn
     * @throws RuntimeException
     */
    public function __construct($aPayload = null, $aVersion = 1, Uuid $aUuid = null, \DateTime $aCreatedOn = null)
    {
        if (!is_null($aPayload)) {
            if (is_array($aPayload)) {
                $this->payload = $aPayload;
            } elseif ($aPayload instanceof PayloadInterface) {
                $this->payload = $aPayload->getArrayCopy();
            } else {
                throw new RuntimeException(
                    sprintf(
                        "Payload must be an array or instance of Codeliner\ServiceBus\Message\PayloadInterface, "
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

        if (is_null($aCreatedOn)) {
            $aCreatedOn = new \DateTime();
        }

        $this->createdOn = $aCreatedOn;
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
    public function createdOn()
    {
        return $this->createdOn;
    }
}
 