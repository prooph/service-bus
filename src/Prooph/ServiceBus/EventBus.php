<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.09.14 - 21:33
 */

namespace Prooph\ServiceBus;

use Prooph\ServiceBus\Exception\EventDispatchException;
use Prooph\ServiceBus\Process\EventDispatch;
use Zend\EventManager\EventManagerInterface;

class EventBus extends MessageBus
{
    /**
     * @param mixed $event
     * @throws Exception\EventDispatchException
     */
    public function dispatch($event)
    {
        $eventDispatch = EventDispatch::initializeWith($event, $this);

        if (! is_null($this->logger)) {
            $eventDispatch->useLogger($this->logger);
        }

        try {
            $this->trigger($eventDispatch);

            if (is_null($eventDispatch->getEventName())) {
                $eventDispatch->setName(EventDispatch::DETECT_MESSAGE_NAME);

                $this->trigger($eventDispatch);
            }

            $eventDispatch->setName(EventDispatch::ROUTE);

            $this->trigger($eventDispatch);

            foreach ($eventDispatch->getEventListeners() as $eventListener) {

                $eventDispatch->setCurrentEventListener($eventListener);

                if (is_string($eventListener)) {
                    $eventDispatch->setName(EventDispatch::LOCATE_LISTENER);

                    $this->trigger($eventDispatch);
                }

                $eventDispatch->setName(EventDispatch::INVOKE_LISTENER);

                $this->trigger($eventDispatch);
            }

        } catch (\Exception $ex) {
            $failedPhase = $eventDispatch->getName();
            $eventDispatch->setException($ex);

            $this->triggerError($eventDispatch);
            $this->triggerFinalize($eventDispatch);

            //Check if a listener has removed the exception to indicate that it was able to handle it
            if ($ex = $eventDispatch->getException()) {
                $eventDispatch->setName($failedPhase);
                throw EventDispatchException::failed($eventDispatch, $ex);
            }

            return;

        }

        $this->triggerFinalize($eventDispatch);
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers(array(
            'event_bus',
            __CLASS__
        ));

        parent::setEventManager($eventManager);
    }
}
 