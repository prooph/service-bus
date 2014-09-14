<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 18:22
 */

namespace Prooph\ServiceBus\Message;

use Codeliner\Comparison\EqualsBuilder;
use Rhumsaa\Uuid\Uuid;

/**
 * Class MessageHeader
 *
 * @package Prooph\ServiceBus\Message
 * @author Alexander Miertsch <contact@prooph.de>
 */
class MessageHeader implements MessageHeaderInterface
{
    const TYPE_COMMAND = 'command';
    const TYPE_EVENT   = 'event';
    /**
     * @var Uuid
     */
    protected $uuid;

    /**
     * @var \DateTime
     */
    protected $createdOn;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var string
     */
    protected $sender;

    /**
     * Type of the Message, can either be command or event
     *
     * @var string
     */
    protected $type;

    /**
     * @param array $aMessageHeaderArray
     * @return MessageHeaderInterface
     */
    public static function fromArray(array $aMessageHeaderArray)
    {
        \Assert\that($aMessageHeaderArray)
            ->keyExists('uuid')
            ->keyExists('createdOn')
            ->keyExists('version')
            ->keyExists('sender')
            ->keyExists('type');

        $uuid = Uuid::fromString($aMessageHeaderArray['uuid']);
        $createdOn = new \DateTime($aMessageHeaderArray['createdOn']);

        return new static(
            $uuid,
            $createdOn,
            $aMessageHeaderArray['version'],
            $aMessageHeaderArray['sender'],
            $aMessageHeaderArray['type']
        );
    }

    /**
     * @param Uuid      $aUuid
     * @param \DateTime $aCreatedOn
     * @param int       $aVersion
     * @param string    $aSender
     * @param string    $aType
     */
    public function __construct(Uuid $aUuid, \DateTime $aCreatedOn, $aVersion, $aSender, $aType)
    {
        \Assert\that($aVersion)
            ->notEmpty('MessageHeader.version must not be empty')
            ->integer('MessageHeader.version must be an integer');

        \Assert\that($aSender)
            ->notEmpty('MessageHeader.sender must not be empty')
            ->string('MessageHeader.sender must be a string');

        \Assert\that($aType)
            ->inArray(array(self::TYPE_COMMAND, self::TYPE_EVENT), 'MessageHeader.type must be command or event');

        $this->uuid      = $aUuid;
        $this->createdOn = $aCreatedOn;
        $this->version   = $aVersion;
        $this->sender    = $aSender;
        $this->type      = $aType;
    }

    /**
     * @return Uuid
     */
    public function uuid()
    {
        return $this->uuid;
    }

    /**
     * @return \DateTime
     */
    public function createdOn()
    {
        return $this->createdOn;
    }

    /**
     * @return int
     */
    public function version()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function sender()
    {
        return $this->sender;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'uuid'      => $this->uuid()->toString(),
            'createdOn' => $this->createdOn()->format(\DateTime::ISO8601),
            'version'   => $this->version(),
            'sender'    => $this->sender(),
            'type'      => $this->type()
        );
    }
}
