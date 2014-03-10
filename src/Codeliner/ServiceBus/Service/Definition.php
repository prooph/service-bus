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
    //Config keys
    const CONFIG_ROOT                       = "codeliner.service_bus";

    const COMMAND_BUS                       = "command_bus";

    const COMMAND_MAP                       = "command_map";

    const COMMAND_HANDLER_INVOKE_STRATEGIES = 'command_handler_invoke_strategies';

    //ServiceBusManager aliases
    const COMMAND_FACTORY                   = "commandfactory";

    const COMMAND_RECEIVER_MANAGER          = "commandreceivermanager";

    const INVOKE_STRATEGY_MANAGER           = 'invokestrategymanager';
}
