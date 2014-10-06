<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
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
     * @var \Symfony\Component\HttpKernel\KernelInterface
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
        $this->getSession()->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath(
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
        $this->getSession()->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath(
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
        $this->getSession()->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath(
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
        $this->getSession()->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath(
            'select',
            ".//select[(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//select | .//div/label[contains(text(), %locator%)]/../select"
        );

        $select = $this->fixStepArgument($select);
        $option = $this->fixStepArgument($option);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($select, $option) {
            $this->getSession()->getPage()->selectFieldOption($select, $option);
        });
    }

    /**
     * Click element by id
     *
     * @param string $id
     */
    public function clickElementById($id)
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($id) {
            $element = $this->getSession()->getPage()->findById($id);

            if ($element) {
                $element->click();

                return;
            }

            throw new \Exception(sprintf('Element with ID [%s] is not found', $id));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function clickLink($link)
    {
        $link = $this->fixStepArgument($link);
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($link) {
            $this->getSession()->getPage()->clickLink($link);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertFieldContains($field, $value)
    {
        $this->getSession()->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath(
            'field',
            ".//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')] | .//label[contains(normalize-space(string(.)), %locator%)]/../div/div/*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]"
        );

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($field, $value) {
            $this->assertSession()->fieldValueEquals($field, $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertCheckboxChecked($checkbox)
    {
        // override selector to handle twitter-bootstrap style checkbox+label layout (and <span> variant)
        $this->getSession()->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath(
            'checkbox',
            ".//input[./@type = 'checkbox'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'checkbox'] | .//div/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox'] | .//span/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox']"
        );

        $checkbox = $this->fixStepArgument($checkbox);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($checkbox) {
            $this->assertSession()->checkboxChecked($checkbox);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertCheckboxNotChecked($checkbox)
    {
        // override selector to handle twitter-bootstrap style checkbox+label layout (and <span> variant)
        $this->getSession()->getSelectorsHandler()->getSelector('named_partial')->registerNamedXpath(
            'checkbox',
            ".//input[./@type = 'checkbox'][(((./@id = %locator% or ./@name = %locator%) or ./@id = //label[contains(normalize-space(string(.)), %locator%)]/@for) or ./@placeholder = %locator%)] | .//label[contains(normalize-space(string(.)), %locator%)]//.//input[./@type = 'checkbox'] | .//div/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox'] | .//span/label[contains(normalize-space(string(.)), %locator%)]/../input[./@type = 'checkbox']"
        );

        $checkbox = $this->fixStepArgument($checkbox);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($checkbox) {
            $this->assertSession()->checkboxNotChecked($checkbox);
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
     * {@inheritdoc}
     */
    public function switchToIFrame($name = null)
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($name) {
            $this->getSession()->switchToIFrame($name);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageAddress($page)
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($page) {
            $this->assertSession()->addressEquals($this->locatePath($page));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertHomepage()
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () {
            $this->assertSession()->addressEquals($this->locatePath('/'));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertUrlRegExp($pattern)
    {
        $pattern = $this->fixStepArgument($pattern);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($pattern) {
            $this->assertSession()->addressMatches($pattern);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertResponseStatus($code)
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($code) {
            $this->assertSession()->statusCodeEquals($code);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertResponseStatusIsNot($code)
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($code) {
            $this->assertSession()->statusCodeNotEquals($code);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageContainsText($text)
    {
        $text = $this->fixStepArgument($text);
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($text) {
            $this->assertSession()->pageTextContains($text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageNotContainsText($text)
    {
        $text = $this->fixStepArgument($text);
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($text) {
            $this->assertSession()->pageTextNotContains($text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageMatchesText($pattern)
    {
        $pattern = $this->fixStepArgument($pattern);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($pattern) {
            $this->assertSession()->pageTextMatches($pattern);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageNotMatchesText($pattern)
    {
        $pattern = $this->fixStepArgument($pattern);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($pattern) {
            $this->assertSession()->pageTextNotMatches($pattern);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertResponseContains($text)
    {
        $text = $this->fixStepArgument($text);
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($text) {
            $this->assertSession()->responseContains($text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertResponseNotContains($text)
    {
        $text = $this->fixStepArgument($text);
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($text) {
            $this->assertSession()->responseNotContains($text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementContainsText($element, $text)
    {
        $text = $this->fixStepArgument($text);
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $text) {
            $this->assertSession()->elementTextContains('css', $element, $text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementNotContainsText($element, $text)
    {
        $text = $this->fixStepArgument($text);
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $text) {
            $this->assertSession()->elementTextNotContains('css', $element, $text);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementContains($element, $value)
    {
        $value = $this->fixStepArgument($value);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $value) {
            $this->assertSession()->elementContains('css', $element, $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementNotContains($element, $value)
    {
        $value = $this->fixStepArgument($value);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element, $value) {
            $this->assertSession()->elementNotContains('css', $element, $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementOnPage($element)
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element) {
            $this->assertSession()->elementExists('css', $element);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertElementNotOnPage($element)
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($element) {
            $this->assertSession()->elementNotExists('css', $element);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertFieldNotContains($field, $value)
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);

        $this->getSubcontext('SpinCommandContext')->spin(function () use ($field, $value) {
            $this->assertSession()->fieldValueNotEquals($field, $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertNumElements($num, $element)
    {
        $this->getSubcontext('SpinCommandContext')->spin(function () use ($num, $element) {
            $this->assertSession()->elementsCount('css', $element, intval($num));
        });
    }

    /**
     * Assert an option has been selected
     *
     * @param string $option      Label of the option to assert
     * @param string $selectXpath XPath of the selector
     *
     * @throws \Exception
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     *
     * @Then /^(?:|I )should see "(?P<option>(?:[^"]|\\")*)" is selected as selector at XPath "(?P<selectXpath>[^"]*)"$/
     */
    public function assertSelected($option, $selectXpath)
    {
        $option = $this->fixStepArgument($option);
        $select = $this->findElementByXpath($selectXpath);

        if (null === $select) {
            $message = 'Could not find the selector by the given XPath: ' . $selectXpath;

            throw new \Exception($message);
        }

        $opt = $this->getSession()->getPage()->find('named', array(
            'option', $this->getSession()->getSelectorsHandler()->xpathLiteral($option)
                ));

        if (null === $opt) {
            throw new ElementNotFoundException(
                $this->getSession(),
                'select option',
                'value|text',
                $option
            );
        }
        $expectedValue = $opt->getValue();

        assertEquals($expectedValue, $this->getSession()->getDriver()->getValue($selectXpath));
    }

    /**
     * Assert an option has not been selected
     *
     * @param string $option      Label of the option to assert
     * @param string $selectXpath XPath of the selector
     *
     * @throws \Exception
     *
     * @Then /^(?:|I )should not see "(?P<option>(?:[^"]|\\")*)" is selected as selector at XPath "(?P<selectXpath>[^"]*)"$/
     */
    public function assertNotSelected($option, $selectXpath)
    {
        $option = $this->fixStepArgument($option);
        $select = $this->findElementByXpath($selectXpath);

        if (null === $select) {
            $message = 'Could not find the selector by the given XPath: ' . $selectXpath;

            throw new \Exception($message);
        }

        $opt = $this->getSession()->getPage()->find('named', array(
            'option', $this->getSession()->getSelectorsHandler()->xpathLiteral($option)
                ));
        assertTrue((null === $opt) or ($opt->getValue() !== $this->getSession()->getDriver()->getValue($selectXpath)));
    }

    /**
     * Finds element with specified XPath.
     *
     * @param string $xPath XPath
     *
     * @return \Behat\Mink\Element\NodeElement|null
     */
    private function findElementByXpath($xPath)
    {
        return $this->getSession()->getPage()->find('xpath', $xPath);
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    protected function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }

    /**
     * Get attribute value by Id
     *
     * @param string $id
     * @param string $selector
     *
     * @return mixed
     */
    public function getSelectorValueById($id, $selector)
    {
        return $this->getSubcontext('SpinCommandContext')->spin(function () use ($id, $selector) {
            $element = $this->getSession()->getPage()->findById($id);

            if ($element) {
                return $element->getAttribute($selector);
            }

            throw new \Exception(sprintf('Element with ID [%s] is not found', $id));
        });
    }

    /**
     * Selects option in select field with specified XPath.
     *
     * @param string $option      Label of the option to select
     * @param string $selectXpath XPath of the selector
     *
     * @throws \Exception
     *
     * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from selector at XPath "(?P<selectXpath>[^"]*)"$/
     */
    public function selectOptionFromSelectorAtXPath($option, $selectXpath)
    {
        $option = $this->fixStepArgument($option);
        $select = $this->findElementByXpath($selectXpath);

        if (null === $select) {
            $message = 'Could not find the selector by the given XPath: ' . $selectXpath;

            throw new \Exception($message);
        }

        $select->selectOption($option, false);
    }
}
