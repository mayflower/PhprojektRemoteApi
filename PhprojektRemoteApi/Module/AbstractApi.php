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

use \Goutte\Client;

abstract class AbstractApi
{

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $phprojektUrl;

    /**
     * @param Client $httpClient
     */
    public function __construct(Client $httpClient, $phprojektUrl)
    {
        $this->httpClient   = $httpClient;
        $this->phprojektUrl = $phprojektUrl;
    }

}