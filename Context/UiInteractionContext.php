<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\HttpKernel\KernelInterface;

//
// Require 3rd-party libraries here:
//

/**
 * Ui Interaction subcontext
 *
 * @author Yuan Xie <shayx@nationalfibre.net>
 */
class UiInteractionContext extends RawMinkContext implements KernelAwareInterface
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
     * Resolve bundle path
     *
     * @param string $path
     *
     * @return string
     */
    private function resolveBundlePath($path)
    {
        if ( ! preg_match('/^\@([^\/\\\\]+)(.*)$/', $path, $matches)) {
            return $path;
        }

        $bundle = $this->kernel
                       ->getBundle($matches[1]);

        return str_replace('@' . $bundle->getName(), $bundle->getPath(), $path);
    }

    /**
     * Attach a file to a field by a given id
     *
     * @param string $path           File path
     * @param string $fieldElementId The id for the target field
     *
     * @When /^(?:|I )attach a file at "([^"]*)" path to "([^"]*)"$/
     */
    public function attachAFileToField($path, $fieldElementId)
    {
        $this->getMainContext()->attachFileToField($fieldElementId, realpath($this->resolveBundlePath($path)));
    }

    /**
     * Attach a file to a field by a given XPath
     *
     * @param string $path         File path
     * @param string $elementXPath The XPath for the target field
     *
     * @When /^(?:|I )attach a file at "([^"]*)" path to XPath "([^"]*)"$/
     */
    public function attachAFileToFieldByXPath($path, $elementXPath)
    {
        $field = $this->findElementByXpath($elementXPath);

        if (null === $field) {
            throw new ElementNotFoundException(
                $this->getSession(),
                'form field',
                'xpath',
                $elementXPath
            );
        }
        $field->attachFile(realpath($this->resolveBundlePath($path)));
    }

    /**
     * Drop a file or a directory of files to the page
     *
     * @param string $path File or directory path
     *
     * @When /^(?:|I )drop (?:|a )file(?:|s) at "([^"]*)" path to the page$/
     */
    public function dropFilesToPage($path)
    {
        $elementFinder = "document.body";

        $this->dropFilesToElement($path, $elementFinder);
    }

    /**
     * Drop a file or a directory of files to the page
     *
     * @param string $path File or directory path
     *
     * @When /^(?:|I )drop (?:|a )file(?:|s) at "([^"]*)" path to the document$/
     */
    public function dropFilesToDocument($path)
    {

        $elementFinder = "document";

        $this->dropFilesToElement($path, $elementFinder);

    }

    /**
     * Drop a file or a directory of files to a target element by id
     *
     * @param string $path      File or directory path
     * @param string $elementId Target element id
     *
     * @When /^(?:|I )drop (?:|a )file(?:|s) at "([^"]*)" path to "([^"]*)"$/
     */
    public function dropFilesToElementById($path, $elementId)
    {
        $this->elementShouldExist($elementId);

        $elementFinder = 'document.getElementById("' . $elementId . '")';

        $this->dropFilesToElement($path, $elementFinder);
    }

    /**
     * Drop a file or a directory of files to a target element by XPath
     *
     * @param string $path         File or directory path
     * @param string $elementXPath Target element XPath
     *
     * @When /^(?:|I )drop (?:|a )file(?:|s) at "([^"]*)" path to XPath "([^"]*)"$/
     */
    public function dropFilesToElementByXPath($path, $elementXPath)
    {
        $this->elementAtXPathShouldExist($elementXPath);

        $elementFinder = $this->getRetrieveElementByXPathJavaScript($elementXPath);

        $this->dropFilesToElement($path, $elementFinder);
    }

    /**
     * Drop a file or a directory of files to a target element
     *
     * @param string $path          File or directory path
     * @param string $elementFinder The JavaScript which is used for finding the drop-target element
     */
    protected function dropFilesToElement($path, $elementFinder)
    {
        // Step 1. a) Create a variable by $fileListVariableName name
        //         b) AppendChild the variable to body
        //         c) Assert the variable exists
        $fileListVariableName = $this->insertArrayVariable();

        // Step 2. a) Collect the list of file paths from the give $path
        $filePathList = $this->collectFileList($path);

        // Step 3. a) Create a <input id="$inputHelperElementId" type="file" multiple="multiple" />
        //         b) AppendChild this <input> helper to body
        //         c) Assert the helper exists
        //         d) For each file in the $filePathList
        //            i)  Create the File object inside of the <input> helper
        //            ii) Move the helper's new File object to the FileList variable's holdings
        //         e) Delete the <input> helper from the DOM
        //         f) Assert <input> helper no longer exists
        //         g) Assert FileList variable has all the files
        $this->loadFileListToScriptVariable($filePathList, $fileListVariableName);

        // Step 4. a) Create an event
        //         b) Mocking the event.dataTransfer object
        //         c) Mocking the event.dataTransfer.files object using FileList variable
        //         d) Using this newly-created event object, trigger the "drop" event
        $this->triggerDropEvent($fileListVariableName, $elementFinder);
    }

    /**
     * Insert an array variable
     *
     * @return string $name The name of the variable
     */
    protected function insertArrayVariable()
    {
        $name = 'arrayVariable' . rand(5, 5);
        $insertArrayVariableJavaScript = <<<JS
            var variableScript   = document.createElement('script');
            var variableTextNode = document.createTextNode('var $name = [];');

            variableScript.appendChild(variableTextNode);
            document.body.appendChild(variableScript);
JS;
        $this->getSession()->executeScript($insertArrayVariableJavaScript);

        $assertVariableExistJavaScript = <<<JS
            return typeof $name != 'undefined';
JS;
        $this->assertByJavaScript(
            $assertVariableExistJavaScript,
            'The variable named "' . $name . ' was NOT successfully defined.'
        );

        return $name;
    }

    /**
     * Insert a <input type="file" multiple="multiple"> element into the page by a given element ID
     *
     * @param string $elementId The id for <input type="file" multiple="multiple">
     */
    protected function insertInputFileElement($elementId)
    {
        $inputFileInjectionJavaScript = <<<JS
            var inputElement = document.createElement('input');

            inputElement.setAttribute('id', '$elementId');
            inputElement.setAttribute('type', 'file');
            inputElement.setAttribute('multiple', 'multiple');

            document.body.appendChild(inputElement);
JS;
        $this->getSession()->executeScript($inputFileInjectionJavaScript);

        $assertInputFileExistJavaScript = <<<JS
            return !! document.getElementById('$elementId');
JS;
        $this->assertByJavaScript(
            $assertInputFileExistJavaScript,
            'The <input> element with id="' . $elementId . ' was NOT successfully added.'
        );
    }

    /**
     * Add a list of files to a JavaScript variable by a given id
     *
     * @param string $filePathList A list of absolute paths of files
     * @param string $variableName The name of the array variable which holds the loaded File objects
     */
    protected function loadFileListToScriptVariable($filePathList, $variableName)
    {
        $inputHelperElementId = 'drag_and_drop_test_input_helper';
        $this->insertInputFileElement($inputHelperElementId);

        foreach ($filePathList as $filePath) {
            $this->loadFileToScriptVariable($filePath, $inputHelperElementId, $variableName);
        }

        $this->removeElementById($inputHelperElementId);

        $fileCount = count($filePathList);
        $assertFileListAlreadyInInputHelperJavaScript = <<<JS
            return $variableName.length === $fileCount;
JS;
        $this->assertByJavaScript(
            $assertFileListAlreadyInInputHelperJavaScript,
            'The file(s) were NOT successfully set into the variable named "' . $variableName . '".'
        );

    }

    /**
     * Add a file to a field by a given id on top of its existing list
     *
     * @param string $path                 File path
     * @param string $inputHelperElementId The id of the <input> helper for creating the File object
     * @param string $variableName         The name of the variable which holds an array of File objects
     */
    protected function loadFileToScriptVariable($path, $inputHelperElementId, $variableName)
    {
        $this->attachAFileToField($path, $inputHelperElementId);

        $mergeNewFileToFileListJavaScript = <<<JS
            var helperElement = document.getElementById('$inputHelperElementId');

            $variableName [$variableName.length] = helperElement.files[0];
JS;
        $this->getSession()->executeScript($mergeNewFileToFileListJavaScript);
    }

    /**
     * Collect the real path of a file, or all files in a directory and under
     *
     * @param string $path File or directory path
     *
     * @return array An array of files that is under the $path
     */
    protected function collectFileList($path)
    {
        $realPath = realpath($this->resolveBundlePath($path));
        $filePathArray = array();

        switch (true) {
            case is_file($realPath):
                $filePathArray[] = $realPath;
                continue;
            case is_dir($realPath):
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($realPath),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($iterator as $item) {
                    if (substr_count($item->getPathname(), '/.') == 0 && ($item->isFile() || $item->isLink())) {
                        $filePathArray[] = $item->getPathname();
                    }
                }
                continue;
            default:
                $message = 'The provided path is neither a file nor a directory: ' . $realPath;
                throw new \Exception($message);
        }

        return $filePathArray;
    }

    /**
     * Trigger a "drop" Event by using the array variable which holds File objects
     *
     * @param string $variableName  The name of the variable which holds an array of File objects
     * @param string $elementFinder The JavaScript which is used for finding the target element
     */
    protected function triggerDropEvent($variableName, $elementFinder)
    {
        $triggerDropEventJavaScript = <<<JS
            var event = document.createEvent("HTMLEvents");

            event.initEvent("drop", true, true);
            event.dataTransfer = {};
            event.dataTransfer.files = $variableName;

            $elementFinder.dispatchEvent(event);
JS;
        $this->getSession()->executeScript($triggerDropEventJavaScript);
    }

    /**
     * Remove an element by its id
     *
     * @param string $elementId The id for the element to be removed
     */
    protected function removeElementById($elementId)
    {
        $removalJavaScript = <<<JS
            var targetElement = document.getElementById('$elementId');

            document.body.removeChild(targetElement);
JS;
        $this->getSession()->executeScript($removalJavaScript);

        $assertInputHelperNotExistJavaScript = <<<JS
            return ! document.getElementById('$elementId');
JS;
        $this->assertByJavaScript(
            $assertInputHelperNotExistJavaScript,
            'The target element with id="' . $elementId . '" was NOT successfully removed.'
        );
    }

    /**
     * Assert by running the JavaScript
     *
     * @param string $javaScript The JavaScript to assert
     * @param string $failReason The reason why JavaScript asserts false
     *
     * @throws \Exception
     */
    protected function assertByJavaScript($javaScript, $failReason)
    {
        if ( ! $this->getSession()->evaluateScript($javaScript)) {
            $message = 'A JavaScript assertion has yielded false: ' . $failReason;

            throw new \Exception($message);
        }
    }

    /**
     * Reveal an element
     *
     * @param string $elementId The id for the element to be revealed
     *
     * @Given /^(?:|I )reveal the "([^"]*)" element$/
     */
    public function revealElement($elementId)
    {
        $revealElementJavaScript = <<<JS
            var targetElement = document.getElementById('$elementId');

            targetElement.style.display    = 'block';
            targetElement.style.visibility = 'visible';
JS;
        $this->getSession()->executeScript($revealElementJavaScript);

        $assertElementRevealedJavaScript = <<<JS
            var targetElement = document.getElementById('$elementId');

            return ! (targetElement.style.display != 'block' || targetElement.style.visibility != 'visible');
JS;
        $this->assertByJavaScript(
            $assertElementRevealedJavaScript,
            'The target element with id="' . $elementId . '" was NOT successfully revealed.'
        );
    }

    /**
     * Reveal an element using XPath
     *
     * @param string $elementXPath The XPath for the element to be revealed
     *
     * @Given /^(?:|I )reveal the XPath "([^"]*)" element$/
     */
    public function revealElementByXPath($elementXPath)
    {
        $javascriptElement = $this->getRetrieveElementByXPathJavaScript($elementXPath);

        $revealElementJavaScript = <<<JS
            var targetElement = $javascriptElement;

            targetElement.style.display    = 'block';
            targetElement.style.visibility = 'visible';
JS;
        $this->getSession()->executeScript($revealElementJavaScript);

        $assertElementRevealedJavaScript = <<<JS
            var targetElement = $javascriptElement;

            return ! (targetElement.style.display != 'block' || targetElement.style.visibility != 'visible');
JS;
        $this->assertByJavaScript(
            $assertElementRevealedJavaScript,
            'The target element with XPath="' . $elementXPath . '" was NOT successfully revealed.'
        );
    }

    /**
     * Conceal an element
     *
     * @param string $elementId The id for the element to be concealed
     *
     * @Given /^(?:|I )conceal the "([^"]*)" element$/
     */
    public function concealElement($elementId)
    {
        $concealElementJavaScript = <<<JS
            var targetElement = document.getElementById('$elementId');

            targetElement.style.display    = 'none';
            targetElement.style.visibility = 'hidden';
JS;
        $this->getSession()->executeScript($concealElementJavaScript);

        $assertElementConcealedJavaScript = <<<JS
            var targetElement = document.getElementById('$elementId');

            return ! (targetElement.style.display != 'none' || targetElement.style.visibility != 'hidden');
JS;
        $this->assertByJavaScript(
            $assertElementConcealedJavaScript,
            'The target element with id="' . $elementId . '" was NOT successfully concealed.'
        );
    }

    /**
     * Conceal an element using XPath
     *
     * @param string $elementXPath The XPath for the element to be concealed
     *
     * @Given /^(?:|I )conceal the XPath "([^"]*)" element$/
     */
    public function concealElementXPath($elementXPath)
    {
        $javascriptElement = $this->getRetrieveElementByXPathJavaScript($elementXPath);

        $concealElementJavaScript = <<<JS
            var targetElement = $javascriptElement;

            targetElement.style.display    = 'none';
            targetElement.style.visibility = 'hidden';
JS;
        $this->getSession()->executeScript($concealElementJavaScript);

        $assertElementConcealedJavaScript = <<<JS
            var targetElement = $javascriptElement;

            return ! (targetElement.style.display != 'none' || targetElement.style.visibility != 'hidden');
JS;
        $this->assertByJavaScript(
            $assertElementConcealedJavaScript,
            'The target element with XPath="' . $elementXPath . '" was NOT successfully concealed.'
        );
    }

    /**
     * Removes an attribute from an element.
     *
     * @param string $elementId            Element's id
     * @param string $elementAttributeName Element's attribute name
     *
     * @Given /^element "([^"]*)" attribute "([^"]*)" is removed$/
     */
    public function elementAttributeRemoved($elementId, $elementAttributeName)
    {
        $this->elementShouldExist($elementId);

        $removeElementAttributeJavaScript = <<<JS
            document.getElementById('$elementId').removeAttribute('$elementAttributeName');
JS;
        $this->getSession()->executeScript($removeElementAttributeJavaScript);

        $assertElementAttributeNotExistJavaScript = <<<JS
            var targetAttribute = document.getElementById('$elementId').getAttribute('$elementAttributeName');

            return ! targetAttribute;
JS;
        $this->assertByJavaScript(
            $assertElementAttributeNotExistJavaScript,
            'The target element\'s attribute "' . $elementAttributeName . '" was not successfully removed.'
        );
    }

    /**
     * Checks whether a specified element exists.
     *
     * @param string $elementId Element's id
     */
    protected function elementShouldExist($elementId)
    {
        $that = $this;

        $this->getMainContext()->getSubContext('SpinCommandContext')->spin(function () use ($elementId, $that) {
            $assertElementExistsJavaScript = <<<JS
                var targetElement = document.getElementById('$elementId');

                return !! targetElement;
JS;
            $that->assertByJavaScript(
                $assertElementExistsJavaScript,
                'The target element with id="' . $elementId . '" does not exist.'
            );
        });
    }

    /**
     * Get a piece of JavaScript code which retrieves an element by XPath
     *
     * @param string $xPath Element's XPath
     *
     * @return string
     */
    protected function getRetrieveElementByXPathJavaScript($xPath)
    {
        return 'document.evaluate("'. $xPath . '" ,document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue';
    }

    /**
     * Checks whether a specified element at the XPath exists.
     *
     * @param string $xPath Element's xPath
     *
     * @Then /^I should see an element at XPath "([^"]*)"$/
     */
    public function elementAtXPathShouldExist($xPath)
    {
        $that = $this;

        $this->getMainContext()->getSubContext('SpinCommandContext')->spin(function () use ($xPath, $that) {
            $element = $that->findElementByXpath($xPath);

            if ( ! $element) {
                throw new ElementNotFoundException($that->getSession(), 'element', 'xpath', $xPath);
            }
        });
    }

    /**
     * Checks whether a specified element at the XPath doesn't exist.
     *
     * @param string $xPath Element's xPath
     *
     * @Then /^I should not see an element at XPath "([^"]*)"$/
     */
    public function elementAtXPathShouldNotExist($xPath)
    {
        $retrieveElementJavaScript = $this->getRetrieveElementByXPathJavaScript($xPath);

        $assertElementAtXPathExistsJavaScript = <<<JS
            var targetElement = $retrieveElementJavaScript;

            return ! targetElement;
JS;
        $this->assertByJavaScript(
            $assertElementAtXPathExistsJavaScript,
            'The target element at XPath="' . $xPath . '" exists (which should not).'
        );
    }

    /**
     * Checks whether a specified element for each XPath doesn't exist.
     *
     * @param \Behat\Gherkin\Node\TableNode $table
     *
     * @Then /^I should not see an element at XPaths:$/
     */
    public function elementAtXPathsShouldNotExist(TableNode $table)
    {
        $hash = $table->getHash();

        foreach ($hash as $row) {
            $this->elementAtXPathShouldNotExist($row['xpath']);
        }
    }

    /**
     * Assert a specified attribute of a specified element exists.
     *
     * @param string $elementId            Element's id
     * @param string $elementAttributeName Element's attribute name
     *
     * @Then /^element "([^"]*)" should have an attribute "([^"]*)"$/
     */
    public function elementAttributeShouldExist($elementId, $elementAttributeName)
    {
        $this->elementShouldExist($elementId);

        $assertElementAttributeExistsJavaScript = <<<JS
            var targetAttribute = document.getElementById('$elementId').hasAttribute('$elementAttributeName');

            return !! targetAttribute;
JS;
        $this->assertByJavaScript(
            $assertElementAttributeExistsJavaScript,
            'The target element\'s attribute "' . $elementAttributeName . '" does not exist (which should).'
        );
    }

    /**
     * Assert a specified attribute of a specified element does not exist.
     *
     * @param string $elementId            Element's id
     * @param string $elementAttributeName Element's attribute name
     *
     * @Then /^element "([^"]*)" should not have an attribute "([^"]*)"$/
     */
    public function elementAttributeShouldNotExist($elementId, $elementAttributeName)
    {
        $this->elementShouldExist($elementId);

        $assertElementAttributeExistsJavaScript = <<<JS
            var targetAttribute = document.getElementById('$elementId').hasAttribute('$elementAttributeName');

            return ! targetAttribute;
JS;
        $this->assertByJavaScript(
            $assertElementAttributeExistsJavaScript,
            'The target element\'s attribute "' . $elementAttributeName . '" exists (which should not).'
        );
    }

    /**
     * Assert a specified attribute of a specified element at XPath exists.
     *
     * @param string $xPath                Element's XPath
     * @param string $elementAttributeName Element's attribute name
     *
     * @Then /^element at XPath "([^"]*)" should have an attribute "([^"]*)"$/
     */
    public function elementAtXPathAttributeShouldExist($xPath, $elementAttributeName)
    {
        $this->elementAtXPathShouldExist($xPath);

        $retrieveElementJavaScript = $this->getRetrieveElementByXPathJavaScript($xPath);

        $assertElementAttributeExistsJavaScript = <<<JS
            var targetAttribute = $retrieveElementJavaScript.hasAttribute('$elementAttributeName');

            return !! targetAttribute;
JS;
        $this->assertByJavaScript(
            $assertElementAttributeExistsJavaScript,
            'The target element\'s attribute "' . $elementAttributeName . '" does not exist (which should).'
        );
    }

    /**
     * Assert a specified attribute of a specified element does not exist.
     *
     * @param string $xPath                Element's XPath
     * @param string $elementAttributeName Element's attribute name
     *
     * @Then /^element at XPath "([^"]*)" should not have an attribute "([^"]*)"$/
     */
    public function elementAtXPathAttributeShouldNotExist($xPath, $elementAttributeName)
    {
        $this->elementAtXPathShouldExist($xPath);

        $retrieveElementJavaScript = $this->getRetrieveElementByXPathJavaScript($xPath);

        $assertElementAttributeExistsJavaScript = <<<JS
            var targetAttribute = $retrieveElementJavaScript.hasAttribute('$elementAttributeName');

            return ! targetAttribute;
JS;
        $this->assertByJavaScript(
            $assertElementAttributeExistsJavaScript,
            'The target element\'s attribute "' . $elementAttributeName . '" exists (which should not).'
        );
    }

    /**
     * Validate an attribute's value of a specified (by id) element.
     *
     * @param string $elementId                   Element's id
     * @param string $elementAttributeName        Element's attribute name
     * @param string $elementAttributeTargetValue Element's attribute value
     *
     * @Then /^element "([^"]*)" should have an attribute "([^"]*)" that is "([^"]*)"$/
     */
    public function elementHasAttributeOfValue($elementId, $elementAttributeName, $elementAttributeTargetValue)
    {
        $this->elementAttributeShouldExist($elementId, $elementAttributeName);

        $retrieveElementAttributeValueJavaScript = <<<JS
            return document.getElementById('$elementId').getAttribute('$elementAttributeName');
JS;

        $retrievedAttribute = $this->getSession()->evaluateScript($retrieveElementAttributeValueJavaScript);

        if ($retrievedAttribute != $elementAttributeTargetValue) {
            $message = 'The target element attribute "' . $elementAttributeTargetValue . '" does not match the element attribute\'s actual value "'. $retrievedAttribute . '"';

            throw new \Exception($message);
        }
    }

    /**
     * Validate an attribute's value of a specified (by XPath) element, as checking for exact match.
     *
     * @param string $xPath                       Element's XPath
     * @param string $elementAttributeName        Element's attribute name
     * @param string $elementAttributeTargetValue Element's attribute value
     *
     * @Then /^element at XPath "([^"]*)" should have an attribute "([^"]*)" that is "([^"]*)"$/
     */
    public function elementAtXPathHasAttributeOfValue($xPath, $elementAttributeName, $elementAttributeTargetValue)
    {
        $this->elementAtXPathAttributeShouldExist($xPath, $elementAttributeName);

        $retrieveElementJavaScript = $this->getRetrieveElementByXPathJavaScript($xPath);

        $retrieveElementAttributeValueJavaScript = <<<JS
            return $retrieveElementJavaScript.getAttribute('$elementAttributeName');
JS;

        $retrievedAttribute = $this->getSession()->evaluateScript($retrieveElementAttributeValueJavaScript);

        if ($retrievedAttribute != $elementAttributeTargetValue) {
            $message = 'The target element attribute "' . $elementAttributeTargetValue . '" does not match the element attribute\'s actual value "'. $retrievedAttribute . '"';

            throw new \Exception($message);
        }
    }

    /**
     * Validate an attribute's value of a specified (by XPath) element, as checking for different match.
     *
     * @param string $xPath                       Element's XPath
     * @param string $elementAttributeName        Element's attribute name
     * @param string $elementAttributeTargetValue Element's attribute not expected value
     *
     * @Then /^element at XPath "([^"]*)" should have an attribute "([^"]*)" that is not "([^"]*)"$/
     */
    public function elementAtXPathHasAttributeOfValueDifferent($xPath, $elementAttributeName, $elementAttributeTargetValue)
    {
        $this->elementAtXPathAttributeShouldExist($xPath, $elementAttributeName);

        $retrieveElementJavaScript = $this->getRetrieveElementByXPathJavaScript($xPath);

        $retrieveElementAttributeValueJavaScript = <<<JS
            return $retrieveElementJavaScript.getAttribute('$elementAttributeName');
JS;

        $retrievedAttribute = $this->getSession()->evaluateScript($retrieveElementAttributeValueJavaScript);

        if ($retrievedAttribute == $elementAttributeTargetValue) {
            $message = 'The target element attribute "' . $elementAttributeTargetValue . '" does match the element attribute\'s actual value "'. $retrievedAttribute . '"';

            throw new \Exception($message);
        }
    }

    /**
     * Validate an attribute's value of a specified (by XPath) element, as checking for exact match.
     *
     * @param string $xPath                       Element's XPath
     * @param string $elementAttributeTargetValue Element's attribute value
     *
     * @Then /^element at XPath "([^"]*)" should have the value "([^"]*)"$/
     */
    public function elementAtXPathHasSpecifiedValue($xPath, $elementAttributeTargetValue)
    {
        $retrieveElementJavaScript = $this->getRetrieveElementByXPathJavaScript($xPath);

        $retrieveElementAttributeValueJavaScript = <<<JS
            return $retrieveElementJavaScript.value;
JS;

        $retrievedAttribute = $this->getSession()->evaluateScript($retrieveElementAttributeValueJavaScript);

        assertSame($elementAttributeTargetValue, $retrievedAttribute);
    }

    /**
     * Validate an attribute's value of a specified (by XPath) element, as checking for exact match.
     *
     * @param string $xPath                       Element's XPath
     * @param string $elementAttributeTargetValue Element's attribute value
     *
     * @Then /^element at XPath "([^"]*)" should not have the value "([^"]*)"$/
     */
    public function elementAtXPathHasNotSpecifiedValue($xPath, $elementAttributeTargetValue)
    {
        $retrieveElementJavaScript = $this->getRetrieveElementByXPathJavaScript($xPath);

        $retrieveElementAttributeValueJavaScript = <<<JS
            return $retrieveElementJavaScript.value;
JS;

        $retrievedAttribute = $this->getSession()->evaluateScript($retrieveElementAttributeValueJavaScript);

        assertNotSame($elementAttributeTargetValue, $retrievedAttribute);
    }

    /**
     * Validate an attribute's value of a specified (by XPath) element, as check if the value contains a specific string.
     *
     * @param string $xPath                           Element's XPath
     * @param string $elementAttributeName            Element's attribute name
     * @param string $elementAttributeTargetSubstring Element's attribute target substring
     *
     * @Then /^element at XPath "([^"]*)" should have an attribute "([^"]*)" that contains "([^"]*)"$/
     */
    public function elementAtXPathHasAttributeOfSubstring($xPath, $elementAttributeName, $elementAttributeTargetSubstring)
    {
        $this->elementAtXPathAttributeShouldExist($xPath, $elementAttributeName);

        $retrieveElementJavaScript = $this->getRetrieveElementByXPathJavaScript($xPath);

        $retrieveElementAttributeValueJavaScript = <<<JS
            return $retrieveElementJavaScript.getAttribute('$elementAttributeName');
JS;

        $retrievedAttribute = $this->getSession()->evaluateScript($retrieveElementAttributeValueJavaScript);

        if (false === stristr($retrievedAttribute, $elementAttributeTargetSubstring)) {
            $message = 'The target element attribute "' . $elementAttributeTargetSubstring . '" does not appear in the element attribute\'s actual value "'. $retrievedAttribute . '"';

            throw new \Exception($message);
        }
    }

    /**
     * Checks, that element with specified CSS contains specified text.
     *
     * @param string $xpath XPath of the element
     * @param string $text  Expected text
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in the element at XPath "(?P<xpath>[^"]*)"$/
     */
    public function assertElementContainsText($xpath, $text)
    {
        $that = $this;

        $this->getMainContext()->getSubContext('SpinCommandContext')->spin(function () use ($xpath, $text, $that) {
            $that->assertSession()->elementTextContains('xpath', $xpath, $that->fixStepArgument($text));
        });
    }

    /**
     * Checks, that element with specified CSS contains specified HTML.
     *
     * @param string $xpath XPath of the element
     * @param string $value Expected HTML
     *
     * @Then /^the element at XPath "(?P<xpath>[^"]*)" should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertElementContains($xpath, $value)
    {
        $this->assertSession()->elementContains('xpath', $xpath, $this->fixStepArgument($value));
    }

    /**
     * Checks, that (?P<num>\d+) XPath "(?P<element>[^"]*)" elements exists
     *
     * @param integer $number
     * @param string  $elementXPath
     *
     * @Then /^(?:|I )should see "(?P<num>\d+)" elements at XPath "(?P<element>[^"]*)"?$/
     */
    public function assertElementExistsAtXPathSpecifiedTimes($number, $elementXPath)
    {
        assertSame((integer) $number, count($this->getSession()->getPage()->findAll('xpath', $elementXPath)));
    }

    /**
     * Checks, that element begins with a certain value
     *
     * @param string $xpath XPath of the element
     * @param string $value Expected HTML
     *
     * @Then /^the element at XPath "(?P<xpath>[^"]*)" should begin with "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertElementBeginsWith($xpath, $value)
    {
        $webAssert = $this->assertSession();
        $element   = $webAssert->elementExists('xpath', $xpath);
        $actual    = $element->getHtml();

        if (strpos($actual, $value) !== 0) {
            $message = sprintf('The string "%s" was not found in the HTML of the element matching xpath "%s".', $value, $xpath);
            throw new \Exception($message);
        }
    }

    /**
     * Clicks a clickable element with specified XPath
     *
     * @param string $xPath XPath of the element to be clicked on
     *
     * @When /^(?:|I )click on the element at XPath "([^"]*)"$/
     */
    public function clickElementByXPath($xPath)
    {
        $that = $this;

        $this->getMainContext()->getSubContext('SpinCommandContext')->spin(function () use ($xPath, $that) {
            $element = $that->findElementByXpath($xPath);

            if ( ! $element) {
                $message = 'Could not find the element by the given XPath: ' . $xPath;

                throw new \Exception($message);
            }

            $element->click();
        });

    }

    /**
     * Add css class to element with specified XPath
     *
     * @param string $cssClass CSS class to be added
     * @param string $xPath    XPath of the element
     *
     * @When /^(?:|I )add the css class "([^"]*)" to the element at XPath "([^"]*)"$/
     */
    public function addCssClassToElementByXPath($cssClass, $xPath)
    {
        $element = $this->getSession()->getDriver()->getWebDriverSession()->element('xpath', $xPath);

        if ( ! $element) {
            $message = 'Could not find the element by the given XPath: ' . $xPath;

            throw new \Exception($message);
        }

        $script = <<<JS
    var node = {{ELEMENT}};

    \$(node).addClass('$cssClass');
JS;

        $elementID = $element->getID();
        $subscript = "arguments[0]";

        $script  = str_replace('{{ELEMENT}}', $subscript, $script);

        $this->getSession()->getDriver()->getWebDriverSession()->execute(array(
            'script' => $script,
            'args'   => array(array('ELEMENT' => $elementID))
        ));
    }

    /**
     * Fill element at specified XPath with given value
     *
     * @param string $xPath
     * @param string $value
     *
     * @When /^(?:|I )fill in the element at XPath "(?P<xPath>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for the element at XPath "(?P<xPath>(?:[^"]|\\")*)"$/
     */
    public function fillElementByXPath($xPath, $value)
    {
        $xPath   = $this->fixStepArgument($xPath);
        $value   = $this->fixStepArgument($value);
        $element = $this->findElementByXpath($xPath);

        if ( ! $element) {
            $message = 'Could not find the element by the given XPath: ' . $xPath;

            throw new \Exception($message);
        }

        $element->setValue($value);
    }

    /**
     * Hovers mouse over an element with specified XPath
     *
     * @param string $xPath XPath of the element to be hovered on
     *
     * @When /^(?:|I )hover mouse over element at XPath "([^"]*)"$/
     */
    public function hoverMouseOverElementByXPath($xPath)
    {
        // set up page to respond to simulated hover events by augmenting CSS and listening to mouseover events
        // NOTE: this initialization will run only once
        $setupJs = <<<JS
            (function(window, document, $) {
                var checkForHover     = /^[^{]*:hover/,
                    cssRuleStructure  = /^([^\{]*)(\{.*\}[^\}]*)$/,
                    allHoverSelectors = /:hover/g,
                    replacementClass  = '__simulatedHover__';

                // run initialization only once
                if (window.simulatedHoverCssInjected) {
                    return;
                }

                window.simulatedHoverCssInjected = true;

                $.each(document.styleSheets, function (i, stylesheet) {
                    var replacementClassSelector = '.' + replacementClass;

                    $.each(stylesheet.cssRules, function (ruleIndex, rule) {
                        // if the hover selector is present in the CSS rule, mirror it with the simulated hover class
                        if (!checkForHover.exec(rule.cssText)) {
                            return;
                        }

                        var ruleText              = rule.cssText,
                            parts                 = cssRuleStructure.exec(ruleText),
                            selectorParts         = parts[1].split(','),
                            declaration           = parts[2],
                            modifiedSelectorParts = [],
                            modifiedRuleText;

                        // process the comma-separated parts of the original selector
                        $.each(selectorParts, function (_, selectorPart) {
                            // simply replace the hover pseudo-class with the custom class
                            // NOTE: this does not handle potential occurrences inside [attr="xyz"] type selectors
                            var modifiedSelectorPart = selectorPart.replace(allHoverSelectors, replacementClassSelector);

                            // only use the parts that were affected by the replacement
                            if (modifiedSelectorPart !== selectorPart) {
                                modifiedSelectorParts.push(modifiedSelectorPart);
                            }
                        });

                        // add modified selectors as a new rule right after current one (to respect CSS cascade priority)
                        // NOTE: directly changing original rule.cssText does not work
                        modifiedRuleText = modifiedSelectorParts.join(',') + declaration;

                        stylesheet.insertRule(modifiedRuleText, ruleIndex + 1);
                    });
                });

                $(document).on('mouseover', '*', function() {
                    $(this).addClass(replacementClass);
                });

                $(document).on('mouseout', '*', function() {
                    $(this).removeClass(replacementClass);
                });
            })(window, document, jQuery)
JS;

        $this->getSession()->evaluateScript($setupJs);

        // perform the actual hover
        $element = $this->findElementByXpath($xPath);

        if ( ! $element) {
            $message = 'Could not find the element by the given XPath: ' . $xPath;

            throw new \Exception($message);
        }

        $element->mouseOver();
    }

    /**
     * Drag an element with specified XPath and drop it to another element with specified XPath
     *
     * @param string $draggableElementXPath XPath of the element to be dragged
     * @param string $droppableElementXPath XPath of the element to be dropped to
     *
     * @When /^(?:|I )drag element at XPath "([^"]*)" to element at XPath "([^"]*)"$/
     */
    public function dragAndDropElementByXPath($draggableElementXPath, $droppableElementXPath)
    {
        $draggableElement = $this->findElementByXpath($draggableElementXPath);
        $droppableElement = $this->findElementByXpath($droppableElementXPath);

        if ( ! $draggableElement) {
            $message = 'Could not find the draggable element by the given XPath: ' . $draggableElementXPath;

            throw new \Exception($message);
        }

        if ( ! $droppableElement) {
            $message = 'Could not find the droppable element by the given XPath: ' . $droppableElementXPath;

            throw new \Exception($message);
        }

        $draggableElement->dragTo($droppableElement);
    }

    /**
     * Finds element with specified XPath.
     *
     * @param string $xPath XPath
     *
     * @return NodeElement|null
     */
    public function findElementByXpath($xPath)
    {
        return $this->getSession()->getPage()->find('xpath', $xPath);
    }

    /**
     * Wait for specified number of seconds
     *
     * @param string $delay Delay in seconds
     *
     * @When /^(?:|I )wait (\d+) seconds?$/
     */
    public function iWaitSecond($delay)
    {
        sleep($delay);
    }

    /**
     * Wait for condition or timeout
     *
     * @param string       $timeout    Timeout in milliseconds
     * @param PyStringNode $javascript JavaScript
     *
     * @When /^(?:|I )wait (\d+) seconds or until:$/
     */
    public function iWaitUntil($timeout, PyStringNode $javascript)
    {
        $this->getSession()->wait($timeout * 1000, $javascript);
    }

    /**
     * Fills in form field with random text of required length
     *
     * Example:
     *   When I fill in "description" with 4096 random characters
     *
     * @param string  $field  Field id|name|label
     * @param integer $length Number of random characters
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with (?P<length>\d+) random characters?$/
     */
    public function fillFieldWithRandomText($field, $length)
    {
        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $value   = '';
        $field   = $this->fixStepArgument($field);

        for ($i = 0; $i < $length; $i++) {
            $value .= $charset[mt_rand(1, strlen($charset)) - 1];
        }

        $this->getMainContext()->fillField($field, $value);
    }

    /**
     * Adjust Jcrop selection box to certain coordinates
     *
     * @param string  $targetImageId Target Image Id
     * @param integer $positionLeft  Position of left side
     * @param integer $positionTop   Position of top side
     * @param integer $sideLength    Length of a side
     *
     * @Then /^(?:|I )adjust the selection square on image "([^"]*)" to X "(\d+)", Y "(\d+)" and side length "(\d+)"$/
     */
    public function adjustJcropSelectionBox($targetImageId, $positionLeft, $positionTop, $sideLength)
    {
        $positionRight  = $positionLeft + $sideLength;
        $positionBottom = $positionTop + $sideLength;

        $adjustJcropSelectionBoxJavaScript = <<<JS
            $('#$targetImageId').data('Jcrop').setSelect([$positionLeft, $positionTop, $positionRight, $positionBottom]);
JS;
        $this->getSession()->executeScript($adjustJcropSelectionBoxJavaScript);

        sleep(2);

        $assertJcropSelectionBoxJavaScript = <<<JS
            var selectedArea = $('#$targetImageId').data('Jcrop').tellSelect();

            return selectedArea.x  === $positionLeft &&
                   selectedArea.y  === $positionTop &&
                   selectedArea.x2 === $positionRight &&
                   selectedArea.y2 === $positionBottom &&
                   selectedArea.w  === $sideLength &&
                   selectedArea.h  === $sideLength;
JS;
        $this->assertByJavaScript(
            $assertJcropSelectionBoxJavaScript,
            'Could not adjust Jcrop selection box on the specified image with id="' . $targetImageId .
            '", and position X "' . $positionLeft .
            '", position Y "' . $positionTop .
            '" and side length of "' . $sideLength . '".'
        );
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    public function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }

    /**
     * Trigger document dragenter event
     *
     * @When /^(?:|I )drag (?:|a )file(?:|s) over the window$/
     */
    public function triggerDragEnterEvent()
    {
        $triggerDragEnterScript = <<<JS
            var mockEvent = document.createEvent('HTMLEvents');

            mockEvent.initEvent('dragenter', true, true);
            mockEvent.dataTransfer = {
                files: {},
                types: ['Files']
            };

            document.dispatchEvent(mockEvent);
JS;

        $this->getSession()->executeScript($triggerDragEnterScript);
    }

    /**
     * Trigger dragleave event for a certain element
     *
     * @param string $selector The element that will fire the dragleave event
     *
     * @When /^(?:|I )drag files off of an element at "([^"]*)" $/
     */
    public function triggerDragLeaveEvent($selector)
    {

        $triggerDragLeaveScript = <<<JS
            var mockEvent = document.createEvent('HTMLEvents');

            mockEvent.initEvent('dragleave', true, true);

            document.querySelectorAll('$selector')[0].dispatchEvent(mockEvent);
JS;

        $this->getSession()->executeScript($triggerDragLeaveScript);
    }

    /**
     * Verify if element at XPath is visible
     *
     * @param string $xPath XPath to element to attest visibility
     *
     * @Then /^the element at XPath "([^"]*)" should be visible$/
     */
    public function elementAtXPathShouldBeVisible($xPath)
    {
        $this->elementAtXPathShouldExist($xPath);

        $element = $this->getRetrieveElementByXPathJavaScript($xPath);

        // Create a function to test visibilty
        $isVisibleJavascript = <<<JS
            function isVisible(element) {
                var cs = window.getComputedStyle && window.getComputedStyle(element) ||
                         this.browserBot && this.browserbot.getCurrentWindow().getComputedStyle(element);

                return (cs.display !== "none" &&
                    cs.visibility === "visible" &&
                    parseInt(cs.height) > 0 &&
                    parseInt(cs.width) > 0 &&
                    parseFloat(cs.opacity) > 0);
            }

            var targetElement = $element;

            return isVisible(targetElement);
JS;

        // Return the result
        $this->assertByJavaScript(
            $isVisibleJavascript,
            "The target element\'s CSS renders it hidden (when should be visible)."
        );
    }

    /**
     * Verify if element at XPath is hidden
     *
     * @param string $xPath XPath to element to attest invisibility
     *
     * @Then /^the element at XPath "([^"]*)" should be hidden$/
     */
    public function elementAtXPathShouldBeHidden($xPath)
    {
        $this->elementAtXPathShouldExist($xPath);

        $element = $this->getRetrieveElementByXPathJavaScript($xPath);

        // Create a function to test visibilty
        $isHiddenJavascript = <<<JS
            function isVisible(element) {
                var cs = window.getComputedStyle && window.getComputedStyle(element) ||
                         this.browserBot && this.browserbot.getCurrentWindow().getComputedStyle(element);

                return (cs.display !== "none" &&
                    cs.visibility === "visible" &&
                    parseInt(cs.height) > 0 &&
                    parseInt(cs.width) > 0 &&
                    parseFloat(cs.opacity) > 0);
            }

            var targetElement = $element;

            return !isVisible(targetElement);
JS;

        // Return the result
        $this->assertByJavaScript(
            $isHiddenJavascript,
            "The target element's CSS renders it visible (when it should be hidden)."
        );
    }

    /**
     * Submit form at xPath
     *
     * @param string $xPath XPath to form element
     *
     * @When /^(?:|I )submit form at XPath "([^"]*)"$/
     */
    public function submitFormAtXPath($xPath)
    {
        $this->elementAtXPathShouldExist($xPath);

        $element = $this->getRetrieveElementByXPathJavaScript($xPath);

        $submitFormJavaScript = <<<JS
            $element.submit();
JS;

        $this->getSession()->executeScript($submitFormJavaScript);
    }

    /**
     * Select the radio button
     *
     * @param string $xPath XPath
     *
     * @When /^I check the radio button at XPath "([^"]*)"$/
     */
    public function checkRadioButton($xPath)
    {
        $token = explode("//", $xPath);
        $expr  = end($token);
        $expr = str_replace('@', '', $expr);

        $js = <<<JS
            var checkRadioButton = $("$expr").prop('checked', true);
JS;
        $this->getSession()->executeScript($js);
    }

    /**
     * Evaluate whether or not an element at a given XPath has content
     *
     * @param string $xPath The XPath of a specific element
     *
     * @Then /^the element at XPath "([^"]*)" should have content$/
     */
    public function checkForContent($xPath)
    {
        $domNode = $this->getRetrieveElementByXPathJavaScript($xPath);

        $js = <<<JS
            if ($({$domNode}).is(':empty')) {
                return false;
            } else {
                return true;
            }
JS;
        $this->assertByJavaScript(
            $js,
            "The element at XPath {$xPath} does not have content."
        );
    }

    /**
     * Cancel or accept a browser alert confirmation box
     *
     * @param string $action The decision to make for the alert modal.
     *
     * @Given /^I "([^"]*)" the alert modal$/
     */
    public function actionAlertBox($action)
    {
        switch($action) {
            case 'cancel':
                $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
                break;
            case 'accept':
                $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
                break;
        }
    }

    /**
     * Checks that the element specified by a given XPath does not contain specified text.
     * This check is case insensitive.
     *
     * @param string $text  Text that should not be seen
     * @param string $xPath XPath of the element
     *
     * @Given /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)" in the element at XPath "(?P<xpath>[^"]*)"$/
     */
    public function assertElementAtXpathDoesNotContainText($text, $xPath)
    {
        $that = $this;

        $this->getMainContext()->getSubContext('SpinCommandContext')->spin(function () use ($xPath, $text, $that) {
            $that->assertSession()->elementTextNotContains('xpath', $xPath, $that->fixStepArgument($text));
        });
    }

    /**
     * Does a relative date check for a given element
     *
     * @param string $xPath      The XPath for the element containing the date
     * @param string $format     The date format. See available options here: http://php.net/manual/en/function.date.php
     * @param string $stringDate A string representation of a date or date period
     *
     * @Then /^the date in "([^"]*)" in "([^"]*)" format is equal to "([^"]*)"$/
     */
    public function checkDateAtXpath($xPath, $format, $stringDate)
    {
        $expectedDate = date($format, strtotime($stringDate));

        $domNode = $this->getRetrieveElementByXPathJavaScript($xPath);

        $js = <<<JS
            var element = $({$domNode});

            if (element.val() == '{$expectedDate}') {
                return true;
            } else {
                return false;
            }
JS;
        $this->assertByJavaScript(
            $js,
            "The date in the element at XPath {$xPath} is not {$stringDate}."
        );
    }
}
