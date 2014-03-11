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

    const COMMAND_MAP                       = "command_map";

    const QUEUE                             = "queue";

    const MESSAGE_DISPATCHER                = "message_dispatcher";

    const COMMAND_HANDLER_INVOKE_STRATEGIES = 'command_handler_invoke_strategies';

    const COMMAND_FACTORY                   = "command_factory";

    const IN_MEMORY_MESSAGE_DISPATCHER      = "in_memory_message_dispatcher";

    const COMMAND_BUS_MANAGER               = "command_bus_manager";
    
    const COMMAND_RECEIVER_MANAGER          = "command_receiver_manager";

    const INVOKE_STRATEGY_MANAGER           = "invoke_strategy_manager";

    const MESSAGE_DISPATCHER_MANAGER        = "message_dispatcher_manager";

    const QUEUE_MANAGER                     = "queue_manager";
}
