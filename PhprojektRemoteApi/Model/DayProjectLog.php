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

/**
 * List of project bookings on a single day
 */
class DayProjectLog implements Countable, IteratorAggregate
{

    /**
     * @var float
     */
    protected $bookedHours;

    /**
     * @var float
     */
    protected $remainingWorkLog;

    /**
     * @var ProjectLog[]
     */
    protected $log = [];

    /**
     * @return float
     */
    public function getBookedHours()
    {
        return $this->bookedHours;
    }

    /**
     * @param float $bookedHours
     */
    public function setBookedHours($bookedHours)
    {
        $this->bookedHours = $bookedHours;
    }

    /**
     * @return float
     */
    public function getRemainingWorkLog()
    {
        return $this->remainingWorkLog;
    }

    /**
     * @param float $remainingWorkLog
     */
    public function setRemainingWorkLog($remainingWorkLog)
    {
        $this->remainingWorkLog = $remainingWorkLog;
    }

    /**
     * @param ProjectLog $log
     */
    public function addProjectBooking(ProjectLog $log)
    {
        $this->log[] = $log;
    }

    /**
     * @return ProjectLog[]
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Returns the count of ProjectLog items
     *
     * @return int
     */
    public function count()
    {
        return count($this->log);
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->log);
    }

}