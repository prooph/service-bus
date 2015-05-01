<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 15.03.14 - 22:34
 */
namespace {
    require_once '../vendor/autoload.php';
}

namespace Prooph\ServiceBus\Example\Command {

    use Prooph\Common\Messaging\Command;

    class EchoText extends Command
    {
        /**
         * @param string $text
         * @return EchoText
         */
        public static function fromString($text)
        {
            return new self(__CLASS__, $text);
        }

        protected function convertPayload($textOrPayload)
        {
            if (is_string($textOrPayload)) {
                $textOrPayload = array('text' => $textOrPayload);
            }

            return $textOrPayload;
        }

        public function getText()
        {
            return $this->payload['text'];
        }
    }
}

namespace {
    use Prooph\ServiceBus\CommandBus;
    use Prooph\ServiceBus\Example\Command\EchoText;
    use Prooph\ServiceBus\InvokeStrategy\CallbackStrategy;
    use Prooph\ServiceBus\Router\CommandRouter;

    $commandBus = new CommandBus();

    $router = new CommandRouter();

    //Register a callback as CommandHandler for the EchoText command
    $router->route('Prooph\ServiceBus\Example\Command\EchoText')
        ->to(function (EchoText $aCommand) {
            echo $aCommand->getText();
        });

    //Expand command bus with the router plugin
    $commandBus->utilize($router);

    //Expand command bus with the callback invoke strategy
    $commandBus->utilize(new CallbackStrategy());

    //We create a new Command
    $echoText = EchoText::fromString('It works');

    //... and dispatch it
    $commandBus->dispatch($echoText);

    //Output should be: It works
}

