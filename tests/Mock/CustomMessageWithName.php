<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2013-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Messaging\HasMessageName;

/**
 * Class CustomMessage
 * @package ProophTest\ServiceBus\Mock
 */
final class CustomMessageWithName implements HasMessageName
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    public function messageName()
    {
        return 'Prooph\Test\ServiceBus\Mock\CustomMessageWithSomeOtherName';
    }
}
