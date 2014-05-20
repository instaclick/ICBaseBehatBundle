<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Form\ElementHandler;

use IC\Bundle\Base\BehatBundle\Form\ElementHandler as BaseHandler;
use SensioLabs\Behat\PageObjectExtension\PageObject\Exception\ElementNotFoundException;

/**
 * Select form handler.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class Select extends BaseHandler
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $element       = $this->getElement();
        $optionElement = $element->find('xpath', sprintf('/option[@value="%s"]', $element->getValue()));

        if ( ! $optionElement) {
            throw new ElementNotFoundException(sprintf('Select element [%s] with option value [%s] was not found', $this->locator, $element->getValue()));
        }

        return $optionElement->getText();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->getElement()->selectOption($value, true);
    }
}
