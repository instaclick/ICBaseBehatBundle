<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Form;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Form Element Handler interface.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
interface ElementHandlerInterface
{
    /**
     * Constructor.
     *
     * @param string $locator
     */
    public function __construct($locator);

    /**
     * Define the container element.
     *
     * @param \SensioLabs\Behat\PageObjectExtension\PageObject\Element $containerElement
     */
    public function setContainerElement(Element $containerElement);

    /**
     * Retrieve the element error.
     *
     * @return string|null
     */
    public function getError();

    /**
     * Retrieve the element(s) value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Define the element(s) value.
     *
     * @param mixed $value
     */
    public function setValue($value);

    /**
     * Retrieve the first element.
     *
     * @throws \SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException
     * @return array|SensioLabs\Behat\PageObjectExtension\PageObject\Element
     */
    public function getElement();
}
