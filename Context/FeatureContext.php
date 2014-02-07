<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\Behat\Event\BaseScenarioEvent;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Guzzle\Http\Client;
use Symfony\Component\HttpKernel\KernelInterface;

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
 * @author Mark Kasaboski <markk@nationalfibre.net>
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    /**
     * @var KernelInterface Kernel
     */
    private $kernel;

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

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

        $select = $this->fixStepArgument($select);
        $option = $this->fixStepArgument($option);
        $that   = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($select, $option, $that) {
            $that->getSession()->getPage()->selectFieldOption($select, $option);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function clickLink($link)
    {
        $link = $this->fixStepArgument($link);
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($link, $that) {
            $that->getSession()->getPage()->clickLink($link);
        });
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

        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($field, $value, $that) {
            $that->assertSession()->fieldValueEquals($field, $value);
        });
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

        $checkbox = $this->fixStepArgument($checkbox);
        $that     = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($checkbox, $that) {
            $that->assertSession()->checkboxChecked($checkbox);
        });
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

        $checkbox = $this->fixStepArgument($checkbox);
        $that     = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($checkbox, $that) {
            $that->assertSession()->checkboxNotChecked($checkbox);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function visit($page)
    {
        parent::visit($page);

        // maximize current browser window if it uses Selenium2Driver
        $driver = $this->getSession()->getDriver();

        if ($driver instanceof Selenium2Driver) {
            $this->getSession()->getDriver()->getWebDriverSession()->window('current')->postSize(array('width' => 1280, 'height' => 1024));
        }
    }

    /**
     * Use BeforeScenario hook to automatically clear APC cache for increased test isolation
     *
     * @param \Behat\Behat\Event\BaseScenarioEvent $event Event (unused)
     *
     * @BeforeScenario
     */
    public function prepareApcCache(BaseScenarioEvent $event = null)
    {
        $router = $this->kernel->getContainer()->get('router');

        $url = rtrim($this->getMinkParameter('base_url'), '/')
             . $router->generate('ICBaseBehatBundle_Page_Apc_Delete');

        $client = new Client;
        $client->post($url)->send();
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageAddress($page)
    {
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($page, $that) {
            $that->assertSession()->addressEquals($that->locatePath($page));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertHomepage()
    {
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($that) {
            $that->assertSession()->addressEquals($that->locatePath('/'));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertUrlRegExp($pattern)
    {
        $pattern = $this->fixStepArgument($pattern);
        $that    = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($pattern, $that) {
            $that->assertSession()->addressMatches($pattern);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertResponseStatus($code)
    {
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($code, $that) {
            $that->assertSession()->statusCodeEquals($code);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertResponseStatusIsNot($code)
    {
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($code, $that) {
            $that->assertSession()->statusCodeNotEquals($code);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageContainsText($text)
    {
        $text = $this->fixStepArgument($text);
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($text, $that) {
            $that->assertSession()->pageTextContains($text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageNotContainsText($text)
    {
        $text = $this->fixStepArgument($text);
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($text, $that) {
            $that->assertSession()->pageTextNotContains($text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageMatchesText($pattern)
    {
        $pattern = $this->fixStepArgument($pattern);
        $that    = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($pattern, $that) {
            $that->assertSession()->pageTextMatches($pattern);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageNotMatchesText($pattern)
    {
        $pattern = $this->fixStepArgument($pattern);
        $that    = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($pattern, $that) {
            $that->assertSession()->pageTextNotMatches($pattern);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertResponseContains($text)
    {
        $text = $this->fixStepArgument($text);
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($text, $that) {
            $that->assertSession()->responseContains($text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertResponseNotContains($text)
    {
        $text = $this->fixStepArgument($text);
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($text, $that) {
            $that->assertSession()->responseNotContains($text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementContainsText($element, $text)
    {
        $text = $this->fixStepArgument($text);
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $text, $that) {
            $that->assertSession()->elementTextContains('css', $element, $text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementNotContainsText($element, $text)
    {
        $text = $this->fixStepArgument($text);
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $text, $that) {
            $that->assertSession()->elementTextNotContains('css', $element, $text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementContains($element, $value)
    {
        $value = $this->fixStepArgument($value);
        $that  = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $value, $that) {
            $that->assertSession()->elementContains('css', $element, $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementNotContains($element, $value)
    {
        $value = $this->fixStepArgument($value);
        $that  = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $value, $that) {
            $that->assertSession()->elementNotContains('css', $element, $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementOnPage($element)
    {
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $that) {
            $that->assertSession()->elementExists('css', $element);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementNotOnPage($element)
    {
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $that) {
            $that->assertSession()->elementNotExists('css', $element);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertFieldNotContains($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        $that  = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($field, $value, $that) {
            $that->assertSession()->fieldValueNotEquals($field, $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertNumElements($num, $element)
    {
        $that = $this;

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($num, $element, $that) {
            $that->assertSession()->elementsCount('css', $element, intval($num));
        });
    }
}
