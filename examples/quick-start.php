<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 15.03.14 - 22:34
 */
namespace {
    require_once '../vendor/autoload.php';
}

namespace Codeliner\ServiceBus\Example\Command {

    use Codeliner\ServiceBus\Command\AbstractCommand;

    class EchoText extends AbstractCommand
    {
        public function __construct($textOrPayload)
        {
            if (is_string($textOrPayload)) {
                $textOrPayload = array('text' => $textOrPayload);
            }

            parent::__construct($textOrPayload);
        }

        public function getText()
        {
            return $this->payload['text'];
        }
    }
}

namespace {
    use Codeliner\ServiceBus\Example\Command\EchoText;
    use Codeliner\ServiceBus\Initializer\LocalSynchronousInitializer;
    use Codeliner\ServiceBus\Service\ServiceBusManager;

    //The ServiceBusManager is the central class, that manages the complete service bus environment
    $serviceBusManager = new ServiceBusManager();

    //We use an Initializer to configure a local in memory service bus
    $localEnvironmentInitializer = new LocalSynchronousInitializer();

    //Register a callback as CommandHandler for the EchoText command
    $localEnvironmentInitializer->setCommandHandler(
        'Codeliner\ServiceBus\Example\Command\EchoText',
        function (EchoText $aCommand) {
            echo $aCommand->getText();
        }
    );

    //Register the Initializer at the event system of the ServiceBusManager
    $serviceBusManager->events()->attachAggregate($localEnvironmentInitializer);

    //Get the default CommandBus from ServiceBusManager
    $commandBus = $serviceBusManager->getCommandBus();

    //Create a new Command
    $echoText = new EchoText('It works');

    //... and send it to a handler via CommandBus
    $commandBus->send($echoText);

    //Output should be: It works
}

