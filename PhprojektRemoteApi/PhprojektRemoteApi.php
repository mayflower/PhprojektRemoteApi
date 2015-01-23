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
use \Symfony\Component\DomCrawler\Crawler as Crawler;

/**
 * Remote API to Phprojekt
 */
class PhprojektRemoteApi
{

    /**
     * @var string
     */
    protected $phprojektUrl;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var bool
     */
    protected $loggedIn = false;

    /**
     * @var \Goutte\Client
     */
    protected $httpClient;

    /**
     * @param string $phprojektUrl
     * @param string $username
     * @param string $password
     */
    public function __construct($phprojektUrl, $username, $password)
    {
        $this->phprojektUrl  = $phprojektUrl;
        $this->username      = $username;
        $this->password      = $password;

        $this->httpClient = new Client();
        $this->httpClient->getClient()->setDefaultOption('verify', false);
    }

    /**
     * Login to PHProjekt
     *
     * @return bool True on successful login, false otherwise
     */
    public function login()
    {
        if (empty($this->username) || empty($this->password)) {
            return false;
        }

        $crawler = $this->httpClient->request('GET', $this->phprojektUrl);
        $xpath = '//*[@id="global-main"]/div[2]/form/fieldset/input[3]';
        $node = $crawler->filterXPath($xpath);
        $form = $node->form();

        $login = $this->httpClient->submit(
            $form,
            [
                'loginstring' => $this->username,
                'user_pw'     => $this->password
            ]
        );

        try {
            $message = trim($login->filter('div > fieldset')->text());
            return $message != 'Sorry you are not allowed to enter.';
        } catch (\InvalidArgumentException $e) {
            return true;
        }
    }

    /**
     * Returns bookings for a specific user and project between $start and $end
     *
     * @param string $start
     * @param string $end
     * @param int    $project
     * @param int    $user
     *
     * @return float
     */
    public function stat($start, $end, $project, $user)
    {
        $crawler = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/projects/projects.php?mode=stat'
        );

        $xpath = '//*[@id="global-content"]/form[3]/fieldset/input[7]';
        $node = $crawler->filterXPath($xpath);
        $form = $node->form();

        $form->setValues(
            array(
                'periodtype'  => 0,
                'anfang'      => $start,
                'ende'        => $end,
                'projectlist' => array($project),
                'userlist'    => array($user),
            )
        );

        $crawler = $this->httpClient->submit($form);

        $node = $crawler->filterXPath(
            '//*[@id="global-content"]/table/tfoot/tr[2]/td[2]/b'
        );
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
     * Stop the working hours timer
     *
     * @return void
     */
    public function stopWorkingtime()
    {
        $timecard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php'
        );
        $link = $timecard->selectLink('Arbeitszeit Ende')->link();
        $this->httpClient->click($link);
    }

    /**
     * Start the working hours timer
     *
     * @return void
     */
    public function startWorkingtime()
    {
        $timecard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php'
        );
        $link = $timecard->selectLink('Arbeitszeit Start')->link();
        $this->httpClient->click($link);
    }

    /**
     * Add a time booking for today
     *
     * @param string $start
     * @param string $end
     *
     * @return void
     */
    public function bookTime($start, $end)
    {
        $timecard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php'
        );

        $xpath = '//*[@name="nachtragen1"]';
        $node = $timecard->filterXPath($xpath);
        $form = $node->form();

        $form->setValues([
                'timestart' => $start,
                'timestop' => $end
            ]);

        $this->httpClient->submit($form);
    }

    /**
     * Returns the working times for today
     *
     * @return array
     */
    public function listWorkingtimeToday()
    {
        $timeCard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php'
        );

        $xpath = '//table[@summary=""]/tbody';
        $node = $timeCard->filterXPath($xpath);

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
        $node = $timeCard->filterXPath($xpath);
        $overall = $node->html();

        return [
            $out,
            $overall
        ];
    }

    /**
     * Add a project time booking
     *
     * @param int    $project Project index
     * @param int    $hours
     * @param int    $minutes
     * @param string $description
     */
    public function bookProjectTime($project, $hours, $minutes, $description)
    {
        $this->bookProject(
            $project,
            $hours,
            $minutes,
            $description,
            $this->getProjectCard()
        );
    }

    /**
     * Add a project time booking
     *
     * @param int     $project Project index
     * @param int     $hours
     * @param int     $minutes
     * @param string  $description
     * @param Crawler $projectCard
     */
    protected function bookProject($project, $hours, $minutes, $description, $projectCard)
    {
        $xpath = '//form[@name="book"]';
        $node = $projectCard->filterXPath($xpath);

        $formProject = $project - 1;
        $this->httpClient->submit(
            $node->form(),
            [
                "note[$formProject]" => $description,
                "h[$formProject]"    => $hours,
                "m[$formProject]"    => $minutes
            ]
        );
    }

    /**
     * @return Crawler
     */
    protected function getProjectCard()
    {
        $timeCard = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/timecard/timecard.php'
        );
        $link = $timeCard->selectLink('Favoriten')->link();
        return $this->httpClient->click($link);
    }

    /**
     * Returns project bookings for today
     *
     * @return array
     */
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
            $projects[$projectIndex] = [
                'name'     => $projectTextContent,
                'bookings' => []
            ];
        }

        $xpath = '//table[@summary=""]/tfoot/tr/td[2]';
        $node = $projectCard->filterXPath($xpath);
        $stillToBook = $node->html();

        $xpath = '//table[@summary=""]/tfoot/tr/td[3]';
        $node = $projectCard->filterXPath($xpath);
        $overallBookings = $node->html();

        return [
            $projects,
            $stillToBook,
            $overallBookings
        ];
    }

}