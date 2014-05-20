<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Gherkin;

use Behat\Gherkin\Node\TableNode;

/**
 * Table Node Formatter
 *
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class TableNodeFormatter
{
    /**
     * Convert an expanded table into a row.
     *
     * @param \Behat\Gherkin\Node\TableNode $tableNode     TableNode to convert
     * @param string                        $keyProperty   TableNode property to use as the key
     * @param string                        $valueProperty TableNode property to use as the value
     *
     * @return array
     */
    public static function toCondensed(TableNode $tableNode = null, $keyProperty = 'Property', $valueProperty = 'Value')
    {
        if ($tableNode === null) {
            return null;
        }

        $data = array();

        foreach ($tableNode->getHash() as $row) {
            $data[$row[$keyProperty]] = $row[$valueProperty];
        }

        return $data;
    }
}
