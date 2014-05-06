<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\PageObject;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element as BaseElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

/**
 * Form Element Handler base class.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
abstract class FormElementHandler
{
    /**
     * @var \SensioLabs\Behat\PageObjectExtension\PageObject\Element
     */
    protected $containerElement;

    /**
     * @var string
     */
    private $locator;

    /**
     * Constructor.
     *
     * @param string $locator
     */
    public function __construct($locator)
    {
        $this->locator = $locator;
    }

    /**
     * Define the container element.
     *
     * @param \SensioLabs\Behat\PageObjectExtension\PageObject\Element $containerElement
     */
    public function setContainerElement(BaseElement $containerElement)
    {
        $this->containerElement = $containerElement;
    }

    /**
     * Retrieve the first element.
     *
     * @throws \SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Element
     */
    protected function getElement()
    {
        $element = $this->containerElement->find('xpath', sprintf('//*[@name="%s"]', $this->locator));

        if ( ! $element) {
            throw new ElementNotFoundException(sprintf('Element [%s] was not found', $this->locator));
        }

        return $element;
    }

    /**
     * Retrieve the element list.
     *
     * @throws \SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException
     * @return array
     */
    protected function getElementList()
    {
        $elementList = $this->containerElement->findAll('xpath', sprintf('//*[@name="%s"]', $this->locator));

        if ( ! count($elementList)) {
            throw new ElementNotFoundException(sprintf('Element list [%s] was not found', $this->locator));
        }

        return $elementList;
    }

    /**
     * Retrieve the element(s) value.
     *
     * @return mixed
     */
    abstract public function getValue();

    /**
     * Define the element(s) value.
     *
     * @param mixed $value
     */
    abstract public function setValue($value);
}
