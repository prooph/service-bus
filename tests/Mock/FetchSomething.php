<?php
/*
 * This file is part of the prooph/service-bus.
 * (c) 2014 - 2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 22.05.15 - 22:16
 */
namespace Prooph\ServiceBusTest\Mock;
use Prooph\Common\Messaging\Query;

/**
 * Class FetchSomething
 *
 * @package Prooph\ServiceBusTest\Mock
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class FetchSomething extends Query
{
    /**
     * @param string $data
     * @return FetchSomething
     */
    public static function fromData($data)
    {
        return new static(__CLASS__, array('data' => $data));
    }

    /**
     * @return string
     */
    public function data()
    {
        return $this->payload['data'];
    }
} 