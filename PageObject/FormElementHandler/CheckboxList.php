<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\PageObject\FormElementHandler;

use IC\Bundle\Base\BehatBundle\PageObject\FormElementHandler as BaseHandler;

/**
 * Checkbox List form handler.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class CheckboxList extends BaseHandler
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $labelElementList = array();
        $inputElementList = $this->getElementList();

        foreach ($inputElementList as $inputElement) {
            $labelElement = $this->containerElement->find('xpath', sprintf('//label[@for="%s"]', $inputElement->getAttribute('id')));

            $labelElementList[] = array(
                'label'   => $labelElement->getText(),
                'element' => $inputElement
            );
        }

        foreach ($labelElementList as $elementLabel) {
            if ( ! in_array($elementLabel['label'], $value)) {
                continue;
            }

            $callback = $elementLabel['element']->isChecked() ? 'uncheck' : 'check';

            call_user_func(array($elementLabel['element'], $callback));
        }
    }
}
