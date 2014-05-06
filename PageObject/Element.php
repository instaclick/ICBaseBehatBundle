<?php

namespace IC\Bundle\Base\BehatBundle\PageObject;

use Behat\Mink\Selector\SelectorsHandler;
use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element as BaseElement;

/**
 * Base Element.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
abstract class Element extends BaseElement
{
    /**
     * @var array|string $selector
     */
    protected $selector = array('xpath' => '//');

    /**
     * Constructor.
     *
     * @param \Behat\Mink\Session                                                $session
     * @param \SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface $pageFactory
     * @param mixed                                                              $selector
     */
    public function __construct(Session $session, PageFactoryInterface $pageFactory, $selector = null)
    {
        if ($selector) {
            $this->selector = $selector;
        }

        parent::__construct($session, $pageFactory);
    }
}
