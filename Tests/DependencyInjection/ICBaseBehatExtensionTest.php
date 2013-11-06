<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Tests\DependencyInjection;

use IC\Bundle\Base\TestBundle\Test\DependencyInjection\ExtensionTestCase;

use IC\Bundle\Base\BehatBundle\DependencyInjection\ICBaseBehatExtension;

/**
 * Test for ICBaseBehatExtension
 *
 * @group DependencyInjection
 * @group Unit
 * @group ICBaseBehatBundle
 *
 * @author Diego Asef <diegoasef@nationalfibre.net>
 */
class ICBaseBehatExtensionTest extends ExtensionTestCase
{
    /**
     * Test configuration
     */
    public function testConfiguration()
    {
        $loader = new ICBaseBehatExtension();

        $this->load($loader, array());
    }
}
