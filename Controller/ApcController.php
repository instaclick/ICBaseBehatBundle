<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * APC Cache Controller
 *
 * @author Anthon Pang <anthonp@nationalfibre.net>
 */
class ApcController extends Controller
{
    /**
     * Clear APC user cache
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function clearUserCacheAction()
    {
        switch (true) {
            case ! function_exists('apc_clear_cache'):
                break;

            case version_compare(PHP_VERSION, '5.5') >= 0:
                // APCu emits a warning as it expects no parameters
                apc_clear_cache();
                break;

            default:
                apc_clear_cache('user');
        }

        return new Response('');
    }
}
