<?php
/**
 * @copyright Copyright (c) 2015 Steven Klar
 * @license   THE BEER-WARE LICENSE (Revision 42)
 *
 * "THE BEER-WARE LICENSE" (Revision 42):
 * The author mentioned above wrote this software. As long as you retain this
 * notice you can do whatever you want with this stuff. If we meet some day,
 * and you think this stuff is worth it, you can buy us a beer in return.
 */

use PhprojektRemoteApi\Module\AbstractApi;
use Symfony\Component\DomCrawler\Crawler;

class Vacation extends AbstractApi
{

    protected $sickDaysXPath = '//*[@id="vacation_summary"]/div[2]/table/tbody/tr[3]/td[2]';
    protected $vacationDaysXPath = '//*[@id="vacation_summary"]/div[2]/table/tbody/tr[3]/td[3]';
    protected $laidOffDaysXPath = '//*[@id="vacation_summary"]/div[2]/table/tbody/tr[3]/td[4]';
    protected $schoolDaysXPath = '//*[@id="vacation_summary"]/div[2]/table/tbody/tr[3]/td[5]';
    protected $overtimeDaysXPath = '//*[@id="vacation_summary"]/div[2]/table/tbody/tr[3]/td[6]';
    protected $specialVacationDaysXPath = '//*[@id="vacation_summary"]/div[2]/table/tbody/tr[3]/td[7]';
    protected $parentalLeaveDaysXPath = '//*[@id="vacation_summary"]/div[2]/table/tbody/tr[3]/td[8]';
    protected $remainingVacationDaysXPath = '//*[@id="vacation_summary"]/div[2]/table/tbody/tr[3]/td[9]';

    public function getSickDays()
    {
        $page = $this->getVacationPage();

        return $page->filterXPath($this->sickDaysXPath)->html();
    }

    public function getVacationDays()
    {
        $page = $this->getVacationPage();

        return $page->filterXPath($this->vacationDaysXPath)->html();
    }

    public function getLaidOffDays()
    {
        $page = $this->getVacationPage();

        return $page->filterXPath($this->laidOffDaysXPath)->html();
    }

    public function getSchoolDays()
    {
        $page = $this->getVacationPage();

        return $page->filterXPath($this->schoolDaysXPath)->html();
    }

    public function getOvertimeDays()
    {
        $page = $this->getVacationPage();

        return $page->filterXPath($this->overtimeDaysXPath)->html();
    }

    public function getSpecialVacationDays()
    {
        $page = $this->getVacationPage();

        return $page->filterXPath($this->specialVacationDaysXPath)->html();
    }

    public function getParentalLeaveDays()
    {
        $page = $this->getVacationPage();

        return $page->filterXPath($this->parentalLeaveDaysXPath)->html();
    }

    public function getRemainingVacationDays()
    {
        $page = $this->getVacationPage();

        return $page->filterXPath($this->remainingVacationDaysXPath)->html();
    }

    /**
     * @return Crawler
     */
    private function getVacationPage()
    {
        return $this->httpClient->request('GET', $this->phprojektUrl . '/vacation/index.php', ['verify' => false]);
    }

}
