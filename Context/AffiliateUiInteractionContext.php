<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * UiInteractionContext
 *
 * @author Mohib Malgi <mohibm@nationalfibre.net>
 */
class AffiliateUiInteractionContext extends RawMinkContext
{
    /**
     * Assert that Element Exists For Selector
     *
     * @param string $value
     * @param string $selector
     *
     * @Given /^I should see "([^"]*)" for selector "([^"]*)" using jq$/
     */
    public function assertElementExistsForSelector($value, $selector)
    {
        $actualValue = $this->retrieveElementUsingJquery($selector);

        assertEquals($value, $actualValue);
    }

    /**
     * Retrieve Element Using Jquery
     *
     * @param string $selector Css Selector
     *
     * @return string
     */
    private function retrieveElementUsingJquery($selector)
    {
        $js = <<<JS
            var value = $("$selector").val();

            return value;
JS;

        $actualValue = $this->getSession()->evaluateScript($js);

        return $actualValue;
    }

    /**
     * Assert First Day Of Month
     *
     * @param string $selector
     *
     * @Given /^I should see start of the month for selector "([^"]*)" using jq$/
     */
    public function assertFirstDayOfMonth($selector)
    {
        date_default_timezone_set('UTC');

        $actualValue = $this->retrieveElementUsingJquery($selector);
        assertEquals($actualValue, date('Y-m-01'));
    }


    /**
     * Assert Current Date
     *
     * @param string $selector
     *
     * @Given /^I should see today for selector "([^"]*)" using jq$/
     */
    public function assertCurrentDate($selector)
    {
        date_default_timezone_set('UTC');

        $actualValue = $this->retrieveElementUsingJquery($selector);
        assertEquals($actualValue, date('Y-m-d'));
    }
}
