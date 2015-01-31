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

use PhprojektRemoteApi\Tools\Convert;
use Symfony\Component\DomCrawler\Crawler;

class Projects extends AbstractApi
{

    /**
     * Returns bookings for a specific user and project between $start and $end
     *
     * @param int    $project
     * @param string $start
     * @param string $end
     * @param int    $user
     *
     * @return float
     */
    public function getProjectBookings($project, $start, $end, $user)
    {
        $page = $this->getProjectsPage(array(
            'mode' => 'stat'
        ));
        $form = $this->getFilterConfigurationForm($page);
        $form->setValues(array(
            'periodtype'  => 0,
            'anfang'      => $start,
            'ende'        => $end,
            'projectlist' => array($project),
            'userlist'    => array($user),
        ));
        return $this->getStatisticsSum($form);
    }

    /**
     * Returns own bookings for current user and project between $start and $end
     *
     * @param int    $project
     * @param string $start
     * @param string $end
     *
     * @return float
     */
    public function getMyProjectBookings($project, $start, $end)
    {
        $page = $this->getProjectsPage(array(
            'mode'  => 'stat',
            'mode2' => 'mystat'
        ));
        $form = $this->getFilterConfigurationForm($page);
        $form->setValues(array(
            'periodtype'  => 0,
            'anfang'      => $start,
            'ende'        => $end,
            'projectlist' => array($project),
        ));
        return $this->getStatisticsSum($form);
    }

    /**
     * Returns the crawler for the projects page.
     *
     * @param array $urlParams
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function getProjectsPage(array $urlParams)
    {
        $crawler = $this->httpClient->request(
            'GET',
            $this->phprojektUrl . '/projects/projects.php?' . http_build_query($urlParams)
        );
        return $crawler;
    }

    /**
     * Returns the filter configuration form node.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     *
     * @return \Symfony\Component\DomCrawler\Form
     */
    protected function getFilterConfigurationForm(Crawler $crawler) {
        $xpath = '//*[@id="global-content"]/form[3]';
        $node = $crawler->filterXPath($xpath);
        return $node->form();
    }

    /**
     * Returns the statistics sum as a result for the given form.
     *
     * @param \Symfony\Component\DomCrawler\Form $form
     *
     * @return float
     */
    protected function getStatisticsSum(\Symfony\Component\DomCrawler\Form $form)
    {
        $crawler = $this->httpClient->submit($form);

        $node = $crawler->filterXPath(
            '//*[@id="global-content"]/table/tfoot/tr[2]/td[2]/b'
        );
        return Convert::text2hours($node->text());
    }

}