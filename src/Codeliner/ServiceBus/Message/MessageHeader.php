<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 18:22
 */

namespace Codeliner\ServiceBus\Message;

use Codeliner\Comparison\EqualsBuilder;
use Rhumsaa\Uuid\Uuid;

/**
 * Class MessageHeader
 *
 * @package Codeliner\ServiceBus\Message
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class MessageHeader implements MessageHeaderInterface
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var \DateTime
     */
    private $createdOn;

    /**
     * @var int
     */
    private $version;

    /**
     * @var string
     */
    private $sender;

    /**
     * @param Uuid      $aUuid
     * @param \DateTime $aCreatedOn
     * @param int       $aVersion
     * @param string    $aSender
     */
    public function __construct(Uuid $aUuid, \DateTime $aCreatedOn, $aVersion, $aSender)
    {
        \Assert\that($aVersion)
            ->notEmpty('MessageHeader.version must not be empty')
            ->integer('MessageHeader.version must be an integer');

        \Assert\that($aSender)
            ->notEmpty('MessageHeader.sender must not be empty')
            ->string('MessageHeader.sender must be a string');

        $this->uuid      = $aUuid;
        $this->createdOn = $aCreatedOn;
        $this->version   = $aVersion;
        $this->sender    = $aSender;
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
     * @param MessageHeaderInterface $other
     * @return bool
     */
    public function sameHeaderAs(MessageHeaderInterface $other)
    {
        return EqualsBuilder::create()
            ->append($this->uuid()->toString(), $other->uuid()->toString())
            ->append($this->createdOn()->getTimestamp(), $other->createdOn()->getTimestamp())
            ->append($this->version(), $other->version())
            ->append($this->sender(), $other->sender())
            ->strict()
            ->equals();
    }
}
