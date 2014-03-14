<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.03.14 - 20:28
 */

namespace Codeliner\ServiceBus\Service;

/**
 * Class Definition
 *
 * @package Codeliner\ServiceBus\Service
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Definition
{
    const CONFIG_ROOT                       = "codeliner.service_bus";

    const COMMAND_BUS                       = "command_bus";

    const DEFAULT_COMMAND_BUS               = "default_command_bus";

    const COMMAND_MAP                       = "command_map";

    const COMMAND_HANDLER_INVOKE_STRATEGIES = 'command_handler_invoke_strategies';

    const COMMAND_FACTORY                   = "command_factory";

    const EVENT_BUS                         = "event_bus";

    const DEFAULT_EVENT_BUS                 = "default_event_bus";

    const EVENT_MAP                         = "event_map";

    const EVENT_HANDLER_INVOKE_STRATEGIES   = 'event_handler_invoke_strategies';

    const EVENT_FACTORY                     = "event_factory";

    const QUEUE                             = "queue";

    const MESSAGE_DISPATCHER                = "message_dispatcher";

    const IN_MEMORY_MESSAGE_DISPATCHER      = "in_memory_message_dispatcher";

    const MESSAGE_FACTORY                   = "message_factory";

    const COMMAND_BUS_MANAGER               = "command_bus_manager";
    
    const COMMAND_RECEIVER_MANAGER          = "command_receiver_manager";

    const EVENT_BUS_MANAGER                 = "event_bus_manager";

    const EVENT_RECEIVER_MANAGER            = "event_receiver_manager";

    const INVOKE_STRATEGY_MANAGER           = "invoke_strategy_manager";

    const MESSAGE_DISPATCHER_MANAGER        = "message_dispatcher_manager";

    const QUEUE_MANAGER                     = "queue_manager";

    const SERVICE_BUS_COMPONENT             = 'service_bus_component';
}
