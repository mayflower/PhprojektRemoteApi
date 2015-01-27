<?php
/**
 * @copyright Copyright (c) 2015 Florian Eibeck
 * @license   THE BEER-WARE LICENSE (Revision 42)
 *
 * "THE BEER-WARE LICENSE" (Revision 42):
 * The author mentioned above wrote this software. As long as you retain this
 * notice you can do whatever you want with this stuff. If we meet some day,
 * and you think this stuff is worth it, you can buy us a beer in return.
 */

namespace PhprojektRemoteApi\Model;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;

class DayWorkLog implements Countable, IteratorAggregate
{

    /**
     * @var float
     */
    protected $overallTime;

    /**
     * @var WorkLog[]
     */
    protected $workLog = [];

    /**
     * @param float $time
     */
    public function setOverallTime($time)
    {
        $this->overallTime = $time;
    }

    /**
     * @return float
     */
    public function getOverallTime()
    {
        return $this->overallTime;
    }

    /**
     * @param WorkLog $log
     */
    public function addLog(WorkLog $log)
    {
        $this->workLog[] = $log;
    }

    /**
     * @return WorkLog[]
     */
    public function getLog()
    {
        return $this->workLog;
    }

    /**
     * Returns the count of ProjectLog items
     *
     * @return int
     */
    public function count()
    {
        return count($this->workLog);
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->workLog);
    }

}