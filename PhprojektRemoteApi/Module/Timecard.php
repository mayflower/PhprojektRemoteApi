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

use DateTime;
use PhprojektRemoteApi\Crawler;
use PhprojektRemoteApi\Model\DayProjectLog;
use PhprojektRemoteApi\Model\ProjectLog;
use PhprojektRemoteApi\Model\DayWorkLog;
use PhprojektRemoteApi\Model\WorkLog;
use PhprojektRemoteApi\Tools\Convert;

/**
 * Remote API to the timecard features of PHProjekt
 *
 * @todo Methods to read work and project log of a full month
 */
class Timecard extends AbstractApi
{

    /**
     * Start the working hours timer
     *
     * @return void
     */
    public function workStart()
    {
        $timecard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php',
            ['verify' => false]
        );
        $link = $timecard->selectLink('Working times start')->link();
        $this->httpClient->click($link);
    }

    /**
     * Stop the working hours timer
     *
     * @return void
     */
    public function workEnd()
    {
        $timecard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php',
            ['verify' => false]
        );
        $link = $timecard->selectLink('Working times stop')->link();
        $this->httpClient->click($link);
    }

    /**
     * Log working hours for a specific date
     *
     * @param DateTime $date
     * @param string    $start
     * @param string    $end
     */
    public function logWorkingHours(DateTime $date, $start, $end)
    {
        $timecard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php',
            ['verify' => false]
        );

        $xpath = '//*[@name="nachtragen1"]';
        $node = $timecard->filterXPath($xpath);
        $form = $node->form();

        $form->setValues([
            'date'      => $date->format('d.m.Y'),
            'timestart' => $start,
            'timestop'  => $end
        ]);

        $this->httpClient->submit($form);
    }

    /**
     * Log start of working time for a specific date
     *
     * @param DateTime $date
     * @param string    $start
     * @param string    $end
     */
    public function logStartWorkingTime(DateTime $date, $start)
    {
        $timecard = $this->httpClient->request(
            'POST',
            $this->phprojektUrl . '/timecard/timecard.php',
            ['verify' => false, 'date' => $date->format('Y-m-d')]
        );

        $xpath = '//*[@name="nachtragen1"]';
        $node = $timecard->filterXPath($xpath);
        $form = $node->form();

        $form->setValues([
            'date'      => $date->format('d.m.Y'),
            'timestart' => $start,
            'timestop'  => null
        ]);

        $this->httpClient->submit($form);
    }

    /**
     * Log end of working time for a specific date
     *
     * @param DateTime $date
     * @param string    $start
     * @param string    $end
     */
    public function logEndWorkingTime(DateTime $date, $end)
    {
        $timecard = $this->httpClient->request(
            'POST',
            $this->phprojektUrl . '/timecard/timecard.php',
            ['verify' => false, 'date' => $date->format('Y-m-d')]
        );

        $xpath = '//*[@name="nachtragen"]';
        $node = $timecard->filterXPath($xpath);
        $form = $node->form();
        $endKey = array_values($form->get('ende'))[0]->getName();

        $form->setValues([
            'date'  => $date->format('d.m.Y'),
            $endKey => $end
        ]);

        $this->httpClient->submit($form);
    }

    /**
     * @param DateTime $date
     * @param string    $start
     * @param string    $end
     */
    public function deleteWorkingHours(DateTime $date, $start, $end)
    {
        throw new \RuntimeException("Not yet implemented");
    }

    /**
     * Add a project time booking
     *
     * @param DateTime $date
     * @param int       $project Project index
     * @param float     $hours
     * @param string    $description
     */
    public function logProjectHours(DateTime $date, $project, $hours, $description)
    {
        $projectCard = $this->loadFavoritesPage($date);

        $xpath = '//form[@name="book"]';
        $node = $projectCard->filterXPath($xpath);

        $formProject = $project - 1;
        $this->httpClient->submit(
            $node->form(),
            [
                "note[$formProject]" => $description,
                "h[$formProject]"    => floor($hours),
                "m[$formProject]"    => (fmod($hours, 1)) * 60
            ]
        );
    }

    /**
     * @param DateTime $date
     * @param           $project
     * @param float     $time
     */
    public function deleteProjectHours(DateTime $date, $project, $time)
    {
        throw new \RuntimeException("Not yet implemented");
    }

    /**
     * Add a work log and log project time
     *
     * @param DateTime $date
     * @param string    $start
     * @param string    $end
     * @param           $project
     * @param string    $description
     */
    public function log(DateTime $date, $start, $end, $project, $description = ".")
    {
        throw new \RuntimeException("Not yet implemented");
    }

    /**
     * Returns the working times for today
     *
     * @param DateTime $date
     *
     * @return DayWorkLog
     */
    public function getWorkingHours(DateTime $date)
    {
        $timeCard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php',
            ['verify' => false]
        );

        $xpath = '//*[@name="pickdate"]';
        $node = $timeCard->filterXPath($xpath);
        $form = $node->form();

        $form->setValues([
            'date' => $date->format('d.m.Y'),
        ]);

        $timeCard = $this->httpClient->submit($form);

        $xpath = '//table[@summary=""]/tbody';
        $node = $timeCard->filterXPath($xpath);

        $document = new \DOMDocument();
        $document->loadHTML($node->html());

        $times = $document->getElementsByTagName('tr');

        $worklog = new DayWorkLog();

        foreach ($times as $time) {
            $specifiedTimeRow = $time->getElementsByTagName('td');

            $row = [];
            foreach($specifiedTimeRow as $timeRow) {
                $row[] = $timeRow->textContent;
            }
            if ($row[0] != "") {
                $worklog->addLog(
                    new WorkLog(
                        DateTime::createFromFormat("Hi", $row[0])
                    )
                );
                
                if ($row[1] != "") {
                    $worklog->setEnd(DateTime::createFromFormat("Hi", $row[1]));
                }
            }
        }

        $xpath = '//table[@summary=""]/tfoot/tr/td[3]';
        $node = $timeCard->filterXPath($xpath);
        $worklog->setOverallTime(Convert::text2hours($node->html()));

        return $worklog;
    }

    /**
     * Returns project bookings for a day
     *
     * @param DateTime $date
     *
     * @return DayProjectLog
     */
    public function getProjectBookings(DateTime $date)
    {
        $projectCard = $this->loadFavoritesPage($date);

        $xpath = '//form[@name="book"]/fieldset/table/tbody';
        $node = $projectCard->filterXPath($xpath);
        $dom = new \DOMDocument();
        $dom->loadHTML($node->html());
        $projectsInDom = $dom->getElementsByTagName('tr');

        $projectLog = new DayProjectLog();

        $projectIndex = 0;
        $name = "";

        foreach($projectsInDom as $projectRow) {

            if (!$projectRow->hasAttribute('class')) {

                $name = trim($projectRow->textContent);
                $projectIndex++;

            } else {

                $cells = $projectRow->getElementsByTagName('td');

                $description = $cells[1]->textContent;
                $hours = Convert::text2hours($cells[2]->textContent);

                $projectLog->addProjectBooking(
                    new ProjectLog(
                        $projectIndex,
                        $name,
                        $description,
                        $hours
                    )
                );

            }

        }

        $xpath = '//table[@summary=""]/tfoot/tr/td[2]';
        $node = $projectCard->filterXPath($xpath);
        $time = str_replace('Noch zu vergeben:', '', $node->html());
        $time = trim(strip_tags($time));
        $projectLog->setRemainingWorkLog(Convert::text2hours($time));

        $xpath = '//table[@summary=""]/tfoot/tr/td[3]';
        $node = $projectCard->filterXPath($xpath);
        $projectLog->setBookedHours(Convert::text2hours($node->html()));

        return $projectLog;
    }

    /**
     * Returns list of projects
     */
    public function getProjectList()
    {
        $projectCard = $this->loadFavoritesPage(new DateTime());

        $xpath = '//form[@name="book"]/fieldset/table/tbody';
        $node = $projectCard->filterXPath($xpath);
        $dom = new \DOMDocument();
        $dom->loadHTML($node->html());
        $projectsInDom = $dom->getElementsByTagName('tr');

        $index = 1;
        $projects = [];

        foreach($projectsInDom as $projectRow) {

            if (!$projectRow->hasAttribute('class')) {

                $projects[$index++] = trim($projectRow->textContent);

            }

        }

        return $projects;
    }

    /**
     * @param DateTime $date
     *
     * @return Crawler
     */
    protected function loadFavoritesPage(DateTime $date)
    {
        $timeCard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php',
            ['verify' => false]
        );
        $link = $timeCard->selectLink('Favoriten')->link();
        $timeCard = $this->httpClient->click($link);

        $xpath = '//*[@name="pickdate"]';
        $node = $timeCard->filterXPath($xpath);
        $form = $node->form();

        $form->setValues([
            'date' => $date->format('d.m.Y'),
        ]);

        $timeCard = $this->httpClient->submit($form);
        return $timeCard;
    }

}
