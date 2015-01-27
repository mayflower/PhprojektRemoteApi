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
        return Convert::text2hours($node->text());
    }

}