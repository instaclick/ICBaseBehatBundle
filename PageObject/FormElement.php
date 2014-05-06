<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\PageObject;

use Behat\Mink\Session;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface;

/**
 * Form Element.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class FormElement extends Element
{
    /**
     * @var array $handlerList
     */
    private $handlerList = array();

    /**
     * @var array $listenerList
     */
    private $listenerList = array();

    /**
     * {@inheritdoc}
     */
    public function __construct(Session $session, PageFactoryInterface $pageFactory, $selector)
    {
        parent::__construct($session, $pageFactory, $selector);

        foreach ($this->getHandlerList() as $property => $handler) {
            $this->addHandler($property, $handler);
        }
    }

    /**
     * Attach an event listener to a form property.
     *
     * @param string   $property
     * @param callable $callback
     */
    public function addListener($property, callable $callback)
    {
        $this->listenerList[$property] = $callback;
    }

    /**
     * Attach an property element to a form property.
     *
     * @param string                                                    $property
     * @param \IC\Bundle\Base\BehatBundle\PageObject\FormElementHandler $formElementHandler
     */
    public function addHandler($property, FormElementHandler $formElementHandler)
    {
        $formElementHandler->setContainerElement($this);

        $this->handlerList[$property] = $formElementHandler;
    }

    /**
     * Set the form data.
     *
     * @param array $data
     */
    public function setData($data)
    {
        foreach ($data as $key => $value) {
            if ( ! isset($this->handlerList[$key])) {
                continue;
            }

            $this->handlerList[$key]->setValue($value);

            if ( ! isset($this->listenerList[$key])) {
                continue;
            }

            call_user_func($this->listenerList[$key]);
        }
    }

    /**
     * Retrieve the list of handlers.
     *
     * @return array
     */
    public function getHandlerList()
    {
        return array();
    }
}
