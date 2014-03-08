<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 21:03
 */

namespace Codeliner\ServiceBus\Command;

use Codeliner\ServiceBus\Exception\RuntimeException;
use Codeliner\ServiceBus\Message\MessageHeaderInterface;
use Codeliner\ServiceBus\Message\PayloadInterface;

class AbstractCommand implements CommandInterface
{
    /**
     * @var MessageHeaderInterface
     */
    protected $header;

    /**
     * @var array
     */
    protected $payload = array();

    /**
     * @param MessageHeaderInterface $aMessageHeader
     * @param null|array|PayloadInterface $aPayload
     * @throws \Codeliner\ServiceBus\Exception\RuntimeException
     */
    public function __construct(MessageHeaderInterface $aMessageHeader, $aPayload = null)
    {
        $this->header = $aMessageHeader;

        if (!is_null($aPayload)) {
            if (is_array($aPayload)) {
                $this->payload = $aPayload;
            } else if ($aPayload instanceof PayloadInterface) {
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
}
 