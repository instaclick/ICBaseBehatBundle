<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\Behat\Event\BaseScenarioEvent;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Guzzle\Http\Client;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Hook Context.
 *
 * All general behat hooks are defined here.
 *
 * @author Anthon Pang <anthonp@nationalfibre.net>
 * @author Yuan Xie <shay@nationalfibre.net>
 */
class HookContext extends RawMinkContext implements KernelAwareInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Use BeforeScenario hook to automatically clear APC cache for increased test isolation
     *
     * @param \Behat\Behat\Event\BaseScenarioEvent $event Event (unused)
     *
     * @BeforeScenario
     */
    public function prepareApcCache(BaseScenarioEvent $event = null)
    {
        $router = $this->kernel
                       ->getContainer()
                       ->get('router');

        $url = rtrim($this->getMinkParameter('base_url'), '/')
             . $router->generate('ICBaseBehatBundle_Page_Apc_Delete');

        $client = new Client();
        $client->post($url)->send();
    }
}
