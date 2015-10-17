<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 08/02/15 - 9:55 PM
 */

namespace ProophTest\ServiceBus\Mock;

use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;
use Prooph\Common\Messaging\Query;

/**
 * Class FetchSomething
 * @package ProophTest\ServiceBus\Mock
 */
final class FetchSomething extends Query implements PayloadConstructable
{
    use PayloadTrait;
}
