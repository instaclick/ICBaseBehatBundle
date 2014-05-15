<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Form;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

/**
 * Form Element List Handler.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
abstract class ElementListHandler implements ElementHandlerInterface
{
    /**
     * @var \SensioLabs\Behat\PageObjectExtension\PageObject\Element
     */
    protected $containerElement;

    /**
     * @var string
     */
    protected $locator;

    /**
     * {@inheritdoc}
     */
    public function __construct($locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainerElement(Element $containerElement)
    {
        $this->containerElement = $containerElement;
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        $errorElement = $this->containerElement->find('xpath', sprintf('//div[@class="control-group error"][div[@class="controls"]//*[@name="%s"]]//span[@class="help-block message error"]', $this->locator));

        if ( ! $errorElement) {
            return null;
        }

        return $errorElement->getText();
    }

    /**
     * {@inheritdoc}
     */
    public function getElement()
    {
        $elementList = $this->containerElement->findAll('xpath', sprintf('//*[@name="%s"]', $this->locator));

        if ( ! count($elementList)) {
            throw new ElementNotFoundException(sprintf('Element list [%s] was not found', $this->locator));
        }

        return $elementList;
    }
}
