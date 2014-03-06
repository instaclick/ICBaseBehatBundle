<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Alias Page
 *
 * @author Anthon Pang <anthonp@nationalfibre.net>
 */
class AliasPage extends Page
{
    /**
     * @var \IC\Bundle\Base\BehatBundle\Context\AliasContext
     */
    private $aliasContext;

    /**
     * Set alias context
     *
     * @param \IC\Bundle\Base\BehatBundle\Context\AliasContext $aliasContext
     */
    public function setAliasContext($aliasContext)
    {
        $this->aliasContext = $aliasContext;
    }

    /**
     * {@inheritdoc}
     */
    public function find($alias, $locator)
    {
        if ($alias === 'xpath') {
            $locator = $this->aliasContext->mapKeyToValue($locator);
        }

        return parent::find($alias, $locator);
    }
}
