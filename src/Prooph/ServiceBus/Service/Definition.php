<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:28
 */

namespace Prooph\ServiceBus\Service;

/**
 * Class Definition
 *
 * @package Prooph\ServiceBus\Service
 * @author Alexander Miertsch <contact@prooph.de>
 */
class Definition
{
    const CONFIG_ROOT                       = "prooph.service_bus";

    const CONFIG_ROOT_ESCAPED               = "prooph\.service_bus";

    const COMMAND_BUS                       = "command_bus";

    const DEFAULT_COMMAND_BUS               = "default_command_bus";

    const COMMAND_MAP                       = "command_map";

    const COMMAND_HANDLER_INVOKE_STRATEGIES = 'command_handler_invoke_strategies';

    const COMMAND_FACTORY_LOADER            = "command_factory_loader";

    const EVENT_BUS                         = "event_bus";

    const DEFAULT_EVENT_BUS                 = "default_event_bus";

    const EVENT_MAP                         = "event_map";

    const EVENT_HANDLER_INVOKE_STRATEGIES   = 'event_handler_invoke_strategies';

    const EVENT_FACTORY                     = "event_factory";

    const EVENT_FACTORY_LOADER              = "event_factory_loader";

    const QUEUE                             = "queue";

    const MESSAGE_DISPATCHER                = "message_dispatcher";

    const IN_MEMORY_MESSAGE_DISPATCHER      = "in_memory_message_dispatcher";

    const MESSAGE_FACTORY                   = "message_factory";

    const MESSAGE_FACTORY_LOADER            = "message_factory_loader";

    const COMMAND_BUS_LOADER               = "command_bus_loader";
    
    const COMMAND_RECEIVER_LOADER          = "command_receiver_loader";

    const EVENT_BUS_LOADER                 = "event_bus_loader";

    const EVENT_RECEIVER_LOADER            = "event_receiver_loader";

    const INVOKE_STRATEGY_LOADER           = "invoke_strategy_loader";

    const MESSAGE_DISPATCHER_LOADER        = "message_dispatcher_loader";

    const QUEUE_LOADER                     = "queue_loader";

    const SERVICE_BUS_COMPONENT             = 'service_bus_component';
}
