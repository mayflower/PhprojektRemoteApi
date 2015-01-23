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

namespace PhprojektRemoteApi;

use Goutte\Client;

class PhprojektRemoteApi
{

    protected $baseUrl;

    protected $username;

    protected $password;

    protected $loggedIn = false;

    /**
     * @var \Goutte\Client
     */
    protected $crawler;

    public function __construct($phprojektUrl, $username, $password)
    {
        $this->baseUrl  = $phprojektUrl;
        $this->username = $username;
        $this->password = $password;

        $this->crawler = new Client();
        $this->crawler->getClient()->setDefaultOption('verify', false);
    }

    public function login()
    {
        if (empty($this->username) || empty($this->password)) {
            return false;
        }

        $crawler = $this->crawler->request('GET', $this->baseUrl);
        $xpath = '//*[@id="global-main"]/div[2]/form/fieldset/input[3]';
        $node = $crawler->filterXPath($xpath);
        $form = $node->form();

        $login = $this->crawler->submit($form, [
                'loginstring' => $this->username,
                'user_pw' => $this->password
            ]);

        try {
            $message = trim($login->filter('div > fieldset')->text());
            return $message != 'Sorry you are not allowed to enter.';
        } catch (\InvalidArgumentException $e) {
            return true;
        }
    }

    public function stat($start, $end, $project, $user)
    {
        $crawler = $this->crawler->request('GET', $this->baseUrl . '/projects/projects.php?mode=stat');

        $xpath = '//*[@id="global-content"]/form[3]/fieldset/input[7]';
        $node = $crawler->filterXPath($xpath);
        $form = $node->form();

        $form->setValues(
            array(
                'periodtype' => 0,
                'anfang' => $start,
                'ende' => $end,
                'projectlist' => array($project),
                'userlist' => array($user),
            )
        );

        $crawler = $this->crawler->submit($form);

        $node = $crawler->filterXPath('//*[@id="global-content"]/table/tfoot/tr[2]/td[2]/b');
        return $this->text2hours($node->text());
    }

    /**
     * Converts a time text from PHProjekt to hours
     * Example text: 111 : 45
     *
     * @param string $text
     *
     * @return float
     */
    public function text2hours($text)
    {
        $matches = array();
        preg_match('/^(\d*) : (\d*)$/', $text, $matches);
        return $matches[1] + ($matches[2] / 60);
    }

    /**
     * @return void
     */
    public function stopWorkingtime()
    {
        $timecard = $this->crawler->request('GET', $this->baseUrl . '/timecard/timecard.php');
        $link = $timecard->selectLink('Arbeitszeit Ende')->link();
        $this->crawler->click($link);
    }

    /**
     * @return void
     */
    public function startWorkingtime()
    {
        $timecard = $this->crawler->request('GET', $this->baseUrl . '/timecard/timecard.php');
        $link = $timecard->selectLink('Arbeitszeit Start')->link();
        $this->crawler->click($link);
    }

    public function bookTime($start, $end)
    {
        $timecard = $this->crawler->request('GET', $this->baseUrl . '/timecard/timecard.php');

        $xpath = '//*[@name="nachtragen1"]';
        $node = $timecard->filterXPath($xpath);
        $form = $node->form();

        $form->setValues([
                'timestart' => $start,
                'timestop' => $end
            ]);

        $this->crawler->submit($form);
    }

    public function listWorkingtimeToday()
    {
        $timecard = $this->crawler->request('GET', $this->baseUrl . '/timecard/timecard.php');

        $xpath = '//table[@summary=""]/tbody';
        $node = $timecard->filterXPath($xpath);

        $document = new \DOMDocument();
        $document->loadHTML($node->html());

        $times = $document->getElementsByTagName('tr');

        $out = array();

        foreach ($times as $time) {
            $specifiedTimeRow = $time->getElementsByTagName('td');

            $newTableRow = [];
            foreach($specifiedTimeRow as $timeRow) {
                array_push($newTableRow, $timeRow->textContent);
            }
            $out[] = array_filter($newTableRow);
        }

        $xpath = '//table[@summary=""]/tfoot/tr/td[3]';
        $node = $timecard->filterXPath($xpath);
        $overall = $node->html();

        return array(
            $out,
            $overall
        );
    }

    public function bookProjectTime($project, $hours, $minutes, $description)
    {
        $projectcard = $this->getProjectCard();

        $this->bookProject($project, $hours, $minutes, $description, $projectcard);
    }

    /**
     * @param $project
     * @param $hours
     * @param $minutes
     * @param $description
     * @param $projectcard
     */
    protected function bookProject($project, $hours, $minutes, $description, $projectcard)
    {
        $xpath = '//form[@name="book"]';
        $node = $projectcard->filterXPath($xpath);

        $formProject = $project - 1;
        $this->crawler->submit($node->form(), [
                "note[$formProject]" => $description,
                "h[$formProject]" => $hours,
                "m[$formProject]" => $minutes
            ]);
    }

    /**
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getProjectCard()
    {
        $timecard = $this->crawler->request('GET', $this->baseUrl . '/timecard/timecard.php');
        $link = $timecard->selectLink('Favoriten')->link();
        $projectcard = $this->crawler->click($link);
        return $projectcard;
    }

    public function listProjects()
    {
        $projectCard = $this->getProjectCard();

        $xpath = '//form[@name="book"]/fieldset/table/tbody';
        $node = $projectCard->filterXPath($xpath);
        $dom = new \DOMDocument();
        $dom->loadHTML($node->html());
        $projectsInDom = $dom->getElementsByTagName('tr');

        $projects = [];
        $projectIndex = 0;

        foreach($projectsInDom as $index => $projectRow) {
            $projectTextContent = trim($projectRow->textContent);

            if ($projectRow->hasAttribute('class')) {
                $projects[$projectIndex]['bookings'][] = $projectTextContent;
                continue;
            }

            $projectIndex++;
            $projects[$projectIndex] = ['name' => $projectTextContent, 'bookings' => []];
        }

        $xpath = '//table[@summary=""]/tfoot/tr/td[2]';
        $node = $projectCard->filterXPath($xpath);
        $stillToBook = $node->html();

        $xpath = '//table[@summary=""]/tfoot/tr/td[3]';
        $node = $projectCard->filterXPath($xpath);
        $overallBookings = $node->html();

        return array(
            $projects, $stillToBook, $overallBookings
        );

    }

}