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

class ProjectLog 
{

    /**
     * @var int
     */
    protected $projectIndex;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var float
     */
    protected $hours;

    /**
     * @param int    $projectIndex
     * @param string $name
     * @param string $description
     * @param float  $hours
     */
    function __construct($projectIndex, $name, $description, $hours)
    {
        $this->projectIndex = $projectIndex;
        $this->name         = $name;
        $this->description  = $description;
        $this->hours        = $hours;
    }

    /**
     * @return int
     */
    public function getProjectIndex()
    {
        return $this->projectIndex;
    }

    /**
     * @param int $projectIndex
     */
    public function setProjectIndex($projectIndex)
    {
        $this->projectIndex = $projectIndex;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * @param float $hours
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

}