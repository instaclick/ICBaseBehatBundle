<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Cookie Context
 *
 * @author Anthon Pang <anthonp@nationalfibre.net>
 * @author Oleksii Strutsynskyi <oleksiis@nationalfibre.net>
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class CookieContext extends RawMinkContext
{
    /**
     * @var array
     */
    private $cookies = array();

    /**
     * Save the named cookie
     *
     * @param string $name Cookie name
     *
     * @When /^(?:|I )save my "([^"]*)" cookie$/
     */
    public function saveCookie($name)
    {
        $value = $this->getSession()->getCookie($name);

        $this->cookies[$name] = $value;
    }

    /**
     * Load page (if necessary) and set cookie
     *
     * @param string $name  Cookie name
     * @param string $value Cookie value
     *
     * @internal
     */
    private function sendCookie($name, $value)
    {
        $currentUrl = $this->getSession()->getCurrentUrl();

        if ($currentUrl === 'about:blank') {
            $this->getMainContext()->visit('/');
        }

        $this->getSession()->setCookie($name, $value);
    }

    /**
     * Restore the named cookie
     *
     * @param string $name Cookie name
     *
     * @When /^(?:|I )restore my "([^"]*)" cookie$/
     */
    public function restoreCookie($name)
    {
        $this->sendCookie($name, $this->cookies[$name]);
    }

    /**
     * Set cookie to specified value
     *
     * @param string $name  Cookie name
     * @param string $value Cookie value
     *
     * @When /^(?:|I )set my "([^"]*)" cookie to "([^"]*)"$/
     */
    public function setCookie($name, $value)
    {
        $this->sendCookie($name, $value);
    }

    /**
     * Checks if cookie already exists
     *
     * @param string $name Cookie name
     *
     * @When /^(?:|I )don't have "([^"]*)" cookie$/
     */
    public function dontHaveCookie($name)
    {
        if ($cookie = $this->getSession()->getDriver()->getClient()->getCookieJar()->get($name)) {
            throw new \Exception(sprintf('Shouldn\'t have "%s" cookie', $name));
        }
    }

    /**
     * Save the named cookie
     *
     * @param string $name Cookie name
     *
     * @When /^(?:|I )have "([^"]*)" cookie$/
     */
    public function haveCookie($name)
    {
        if ( ! $cookie = $this->getSession()->getDriver()->getClient()->getCookieJar()->get($name)) {
            throw new \Exception(sprintf('Couldn\'t get "%s" cookie', $name));
        }

        $this->cookies[$name] = $cookie;
    }

    /**
     * Checks if cookie valid for N days. There is added 5 secs delay for checking cookie for validation
     *
     * @param string  $name Cookie name
     * @param integer $days number of days for validation
     *
     * @When /^(?:|I )check if "([^"]*)" cookie valid for "([^"]*)" day(?:|s)$/
     */
    public function checkCookieExpiresNDays($name, $days)
    {
        if ( ! $this->cookies[$name]) {
            throw new \Exception(sprintf('Couldn\'t get "%s" cookie', $name));
        }

        $expireDate     = $this->cookies[$name]->getExpiresTime();
        $testExpireDate = time()+$days*60*60*24;

        if ( ! (($expireDate > $testExpireDate - 5) && ($expireDate <= $testExpireDate))) {
            throw new \Exception(sprintf('Cookie "%s" is not valid for "%s" days', $name, $days));
        }
    }

    /**
     * Checks if cookie expires when the browser is closed.
     *
     * @param string $name Cookie name
     *
     * @When /^(?:|I )check if "([^"]*)" cookie is valid for the browser session$/
     */
    public function checkCookieExpiresSession($name)
    {
        if ( ! $this->cookies[$name]) {
            throw new \Exception(sprintf('Couldn\'t get "%s" cookie', $name));
        }

        $expireDate = $this->cookies[$name]->getExpiresTime();

        if ($expireDate !== null) {
            throw new \Exception(sprintf('Cookie "%s" is not valid for the browser session', $name));
        }
    }
}
