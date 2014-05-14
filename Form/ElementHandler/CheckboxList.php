<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Form\ElementHandler;

use IC\Bundle\Base\BehatBundle\Form\ElementListHandler as BaseHandler;

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
        $valueList        = array();
        $labelElementList = $this->getContainerList();

        foreach ($labelElementList as $elementLabel) {
            if ( ! $elementLabel['element']->isChecked()) {
                continue;
            }

            $valueList[] = $elementLabel['label'];
        }

        return $valueList;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $labelElementList = $this->getContainerList();

        foreach ($labelElementList as $elementLabel) {
            if ( ! in_array($elementLabel['label'], $value)) {
                continue;
            }

            $callback = $elementLabel['element']->isChecked() ? 'uncheck' : 'check';

            call_user_func_array(array($elementLabel['element'], $callback), array($this));
        }
    }

    /**
     * Retrieve the list of input elements with their associated labels.
     *
     * @return array
     */
    private function getContainerList()
    {
        $labelElementList = array();
        $inputElementList = $this->getElement();

        foreach ($inputElementList as $inputElement) {
            $labelElement = $this->containerElement->find('xpath', sprintf('//label[@for="%s"]', $inputElement->getAttribute('id')));

            $labelElementList[] = array(
                'label'   => $labelElement->getText(),
                'element' => $inputElement
            );
        }

        return $labelElementList;
    }
}
