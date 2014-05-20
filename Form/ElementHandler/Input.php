<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Form\ElementHandler;

use IC\Bundle\Base\BehatBundle\Form\ElementHandler as BaseHandler;

/**
 * Input form handler.
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class Input extends BaseHandler
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getElement()->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->getElement()->setValue($value);
    }
}
