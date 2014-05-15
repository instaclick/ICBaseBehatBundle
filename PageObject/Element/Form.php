<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\PageObject\Element;

use Behat\Mink\Session;
use IC\Bundle\Base\BehatBundle\Form\ElementHandlerInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactoryInterface;

/**
 * Form Element.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class Form extends Element
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
     *
     * @return \IC\Bundle\Base\BehatBundle\PageObject\Element\Form
     */
    public function addListener($property, callable $callback)
    {
        $this->listenerList[$property] = $callback;

        return $this;
    }

    /**
     * Attach an property element to a form property.
     *
     * @param string                                                       $property
     * @param \IC\Bundle\Base\BehatBundle\Form\FormElementHandlerInterface $formElementHandler
     *
     * @return \IC\Bundle\Base\BehatBundle\PageObject\Element\Form
     */
    public function addHandler($property, ElementHandlerInterface $formElementHandler)
    {
        $formElementHandler->setContainerElement($this);

        $this->handlerList[$property] = $formElementHandler;

        return $this;
    }

    /**
     * Set the form data.
     *
     * @param array $data
     *
     * @return \IC\Bundle\Base\BehatBundle\PageObject\Element\Form
     */
    public function setData($data)
    {
        foreach ($data as $key => $value) {
            if ( ! isset($this->handlerList[$key])) {
                throw new \InvalidArgumentException(sprintf('Cannot set value for unknown property handler [%s]', $key));
            }

            $this->handlerList[$key]->setValue($value);

            if ( ! isset($this->listenerList[$key])) {
                continue;
            }

            call_user_func_array($this->listenerList[$key], array($this));
        }

        return $this;
    }

    /**
     * Retrieve the form data.
     *
     * @return array
     */
    public function getData()
    {
        $formData = array();

        foreach ($this->handlerList as $key => $handler) {
            $formData[$key] = $handler->getValue();
        }

        return $formData;
    }

    /**
     * Retrieve the form error list.
     *
     * @param array $keyList Fields to restrict lookups to
     *
     * @return array
     */
    public function getErrorList($keyList = null)
    {
        $errorList = array();

        foreach ($this->handlerList as $key => $handler) {
            if ($keyList && ! in_array($key, $keyList)) {
                continue;
            }

            $error = $handler->getError();

            if ($error === null) {
                continue;
            }

            $errorList[$key] = $error;
        }

        return $errorList;
    }

    /**
     * Retrieve the list of handlers.
     *
     * @return array
     */
    protected function getHandlerList()
    {
        return array();
    }
}
