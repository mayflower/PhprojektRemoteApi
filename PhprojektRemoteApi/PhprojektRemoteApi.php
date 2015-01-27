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
use PhprojektRemoteApi\Module\Projects;
use PhprojektRemoteApi\Module\Ptimecontrol;
use PhprojektRemoteApi\Module\Timecard;

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
            $success = ($message != 'Sorry you are not allowed to enter.');
            if ($success) {
                $this->loggedIn = true;
            }
            return $success;

        } catch (\InvalidArgumentException $e) {
            $this->loggedIn = true;
            return true;
        }
    }

    /**
     * Returns API to PHProjekt's projects module
     *
     * @return Projects
     */
    public function getProjectsApi()
    {
        if (!$this->loggedIn) {
            $this->login();
        }
        return new Projects($this->httpClient, $this->phprojektUrl);
    }

    /**
     * Returns API to PHProjekt's ptimecontrol module
     *
     * @return Ptimecontrol
     */
    public function getPtimecontrolApi()
    {
        if (!$this->loggedIn) {
            $this->login();
        }
        return new Ptimecontrol($this->httpClient, $this->phprojektUrl);
    }

    /**
     * Returns API to PHProjekt's timecard module
     *
     * @return Timecard
     */
    public function getTimecardApi()
    {
        if (!$this->loggedIn) {
            $this->login();
        }
        return new Timecard($this->httpClient, $this->phprojektUrl);
    }

}