<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\PageObject;

use Behat\Mink\Session;
use IC\Bundle\Base\BehatBundle\PageObject\ComponentFactory;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page as BasePage;

/**
 * Page Object.
 *
 * @author Anthon Pang <anthonp@nationalfibre.net>
 * @author John Cartwright <johnc@nationalfibre.net>
 */
abstract class Page extends BasePage
{
    /**
     * @var \SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface
     */
    private $pageFactory;

    /**
     * Constructor.
     *
     * @param \Behat\Mink\Session                                                $session
     * @param \SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface $pageFactory
     * @param array                                                              $parameters
     */
    public function __construct(Session $session, PageFactoryInterface $pageFactory, array $parameters = array())
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->pageFactory = $pageFactory;
    }

    /**
     * Retrieve the requested element.
     *
     * @param string       $name
     * @param array|string $selector
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Element
     */
    public function getElement($name, $selector = null)
    {
        $element = $this->createElement($name, $selector);

        if ( ! $this->has('xpath', $element->getXpath())) {
            throw new ElementNotFoundException(sprintf('"%s" element is not present on the page', $name));
        }

        return $element;
    }

    /**
     * Create the instance of the requested element.
     *
     * @param string       $name
     * @param array|string $selector
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Element
     */
    protected function createElement($name, $selector = null)
    {
        return $this->pageFactory->createElement($name, $selector);
    }

    /**
     * GET request
     *
     * @param string  $url
     * @param boolean $follow
     *
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function get($url, $follow = true)
    {
        $driver = $this->getSession()->getDriver();
        $client = $driver->getClient();

        $client->followRedirects($follow);
        $driver->visit($this->getParameter('base_url') . $url);

        return $client->getResponse();
    }
}
