<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\Selenium2Driver;

//
// Require 3rd-party libraries here:
//
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Main Feature Context
 *
 * This class overrides "steps" defined in MinkContext.
 * Application-specific "steps" are defined in subcontexts and loaded via the ContextInitializer extension.
 *
 * @author Yuan Xie <shayx@nationalfibre.net>
 * @author Anthon Pang <anthonp@nationalfibre.net>
 */
class FeatureContext extends MinkContext
{
    /**
     * {@inheritdoc}
     */
    public function fillField($field, $value)
    {
        $this->getSession()->getSelectorsHandler()->getSelector('named')->registerNamedXpath(
            'field',
            ".//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')] | .//label[contains(normalize-space(string(.)), %locator%)]/../div/div/*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]"
        );

        parent::fillField($field, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function checkOption($option)
    {
        // override selector to handle twitter-bootstrap style checkbox+label layout (and <span> variant)
        $this->getSession()->getSelectorsHandler()->getSelector('named')->registerNamedXpath(
            'checkbox',
            ".//input[./@type = 'checkbox'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'checkbox'] | .//div/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox'] | .//span/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox']"
        );

        parent::checkOption($option);
    }

    /**
     * {@inheritdoc}
     */
    public function uncheckOption($option)
    {
        // override selector to handle twitter-bootstrap style checkbox+label layout (and <span> variant)
        $this->getSession()->getSelectorsHandler()->getSelector('named')->registerNamedXpath(
            'checkbox',
            ".//input[./@type = 'checkbox'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'checkbox'] | .//div/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox'] | .//span/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox']"
        );

        parent::uncheckOption($option);
    }

    /**
     * {@inheritdoc}
     */
    public function selectOption($select, $option)
    {
        $this->getSession()->getSelectorsHandler()->getSelector('named')->registerNamedXpath(
            'select',
            ".//select[(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//select | .//div/label[contains(text(), %locator%)]/../select"
        );

        parent::selectOption($select, $option);
    }

    /**
     * {@inheritdoc}
     */
    public function additionallySelectOption($select, $option)
    {
        $this->selectOption($select, $option);
    }

    /**
     * {@inheritdoc}
     */
    public function assertFieldContains($field, $value)
    {
        $this->getSession()->getSelectorsHandler()->getSelector('named')->registerNamedXpath(
            'field',
            ".//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')] | .//label[contains(normalize-space(string(.)), %locator%)]/../div/div/*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]"
        );

        parent::assertFieldContains($field, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function assertCheckboxChecked($checkbox)
    {
        // override selector to handle twitter-bootstrap style checkbox+label layout (and <span> variant)
        $this->getSession()->getSelectorsHandler()->getSelector('named')->registerNamedXpath(
            'checkbox',
            ".//input[./@type = 'checkbox'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'checkbox'] | .//div/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox'] | .//span/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox']"
        );

        parent::assertCheckboxChecked($checkbox);
    }

    /**
     * {@inheritdoc}
     */
    public function assertCheckboxNotChecked($checkbox)
    {
        // override selector to handle twitter-bootstrap style checkbox+label layout (and <span> variant)
        $this->getSession()->getSelectorsHandler()->getSelector('named')->registerNamedXpath(
            'checkbox',
            ".//input[./@type = 'checkbox'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'checkbox'] | .//div/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox'] | .//span/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox']"
        );

        parent::assertCheckboxNotChecked($checkbox);
    }

    /**
     * {@inheritdoc}
     */
    public function visit($page)
    {
        // TODO: decouple
        $this->getSubcontext('CredentialContext')->visitWithPropertySubstitution($page);

        // maximize current browser window if it uses Selenium2Driver
        $driver = $this->getSession()->getDriver();

        if ($driver instanceof Selenium2Driver) {
            $this->getSession()->getDriver()->getWebDriverSession()->window('current')->postSize(array('width' => 1280, 'height' => 1024));
        }
    }

    /**
     * Checks, that page contains text matching specified pattern.
     *
     * @param string $pattern the pattern needs to be matched for the text on the page
     *
     * @Then /^(?:|I )should see the text matching (?P<pattern>"(?:[^"]|\\")*")$/
     */
    public function pageMatchesText($pattern)
    {
        $that = $this;

        $this->getMainContext()->getSubcontext('SpinCommandContext')->spin(function () use ($pattern, $that) {
            $that->assertPageMatchesText($pattern);
        });
    }
}
