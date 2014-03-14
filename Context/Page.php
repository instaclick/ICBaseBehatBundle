<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page as BasePage;

/**
 * Page Object
 *
 * @author Anthon Pang <anthonp@nationalfibre.net>
 */
class Page extends BasePage
{
    /**
     * @var \IC\Bundle\Base\BehatBundle\Context\AliasContext
     */
    private $aliasContext;

    /**
     * Set alias context
     *
     * @param \IC\Bundle\Base\BehatBundle\Context\AliasContext $aliasContext
     */
    public function setAliasContext($aliasContext)
    {
        $this->aliasContext = $aliasContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        return parent::getParameter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return parent::getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function find($alias, $locator)
    {
        if ($alias === 'xpath') {
            $locator = $this->aliasContext->mapKeyToValue($locator);
        }

        return parent::find($alias, $locator);
    }

    /**
     * GET request
     *
     * @param string  $url
     * @param boolean $follow
     */
    public function getRequest($url, $follow = false)
    {
        $driver = $this->getSession()->getDriver();

        $client = $driver->getClient();
        $client->followRedirects($follow);

        $driver->visit($this->getParameter('base_url') . $url);
    }

    /**
     * POST request
     *
     * @param string $url
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     * @param string $content
     */
    public function postRequest($url, array $parameters = array(), array $files = array(), array $server = array(), $content = null)
    {
        $driver = $this->getSession()->getDriver();

        $client = $driver->getClient();
        $client->request(
            'POST',
            $this->getParameter('base_url') . $url,
            $parameters,
            $files,
            $server,
            $content
        );
    }

    /**
     * Return response
     *
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function getResponse()
    {
        return $this->getSession()
                    ->getDriver()
                    ->getClient()
                    ->getResponse();
    }
}
