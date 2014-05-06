<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\PageObject\FormElementHandler;

use IC\Bundle\Base\BehatBundle\PageObject\FormElementHandler as BaseHandler;

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
        $this->getElement()->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->getElement()->selectOption($value, true);
    }
}
