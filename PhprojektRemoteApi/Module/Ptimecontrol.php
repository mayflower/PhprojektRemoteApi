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

namespace PhprojektRemoteApi\Module;

use Symfony\Component\DomCrawler\Crawler;

class Ptimecontrol extends AbstractApi
{

    protected $projectHoursXPath = '//*/table/tbody/tr/td[2]';
    protected $workingHoursXPath = '//*/table/tbody/tr/td[3]';
    protected $overtimeXPath = '//*/table/tbody/tr/td[4]';

    public function getOvertimeOverall()
    {
        $page = $this->getPtimecontrolPage(['action' => 'full']);

        return $page->filterXPath($this->overtimeXPath)->html();
    }

    public function getOvertimeYear($year)
    {
        $page = $this->getPtimecontrolPage([
            'action' => 'year',
            'year' => $year
        ]);

        return $page->filterXPath($this->overtimeXPath)->html();
    }

    public function getOvertimeMonth($month, $year)
    {
        $page = $this->getPtimecontrolPage([
            'action' => 'month',
            'month' => $month,
            'year' => $year
        ]);

        return $page->filterXPath($this->overtimeXPath)->html();
    }

    public function getWorkedHoursOverall()
    {
        $page = $this->getPtimecontrolPage(['action' => 'full']);

        return $page->filterXPath($this->workingHoursXPath)->html();
    }

    public function getWorkedHoursYear($year)
    {
        $page = $this->getPtimecontrolPage([
            'action' => 'year',
            'year' => $year
        ]);

        return $page->filterXPath($this->workingHoursXPath)->html();
    }

    public function getWorkedHoursMonth($month, $year)
    {
        $page = $this->getPtimecontrolPage([
            'action' => 'month',
            'month' => $month,
            'year' => $year
        ]);

        return $page->filterXPath($this->workingHoursXPath)->html();
    }

    public function getProjectHoursOverall()
    {
        $page = $this->getPtimecontrolPage(['action' => 'full']);

        return $page->filterXPath($this->projectHoursXPath)->html();
    }

    public function getProjectHoursYear($year)
    {
        $page = $this->getPtimecontrolPage([
            'action' => 'year',
            'year' => $year
        ]);

        return $page->filterXPath($this->projectHoursXPath)->html();
    }

    public function getProjectHoursMonth($month, $year)
    {
        $page = $this->getPtimecontrolPage([
            'action' => 'month',
            'month' => $month,
            'year' => $year
        ]);

        return $page->filterXPath($this->projectHoursXPath)->html();
    }

    /**
     * @param $params
     *
     * @return Crawler
     */
    private function getPtimecontrolPage($params)
    {
        return $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/ptimecontrol/ptc.php?' . http_build_query($params)
        );
    }

}