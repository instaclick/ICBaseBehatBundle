<?php
/**
 * @copyright 2014 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\Yaml\Yaml;

/**
 * Alias Context
 *
 * @author Danilo Cabello <daniloc@nationalfibre.net>
 */
class AliasContext extends RawMinkContext
{
    /**
     * @var array
     */
    private $valueList = array();

    /**
     * Map alias to value.
     *
     * @param mixed $alias
     *
     * @return mixed
     *
     * @Transform /^((?:[^"]|\\")+)$/
     */
    public function mapAliasToValue($alias)
    {
        if (is_scalar($alias) && isset($this->valueList[$alias])) {
            return $this->valueList[$alias];
        }

        return $alias;
    }

    /**
     * Parse a YAML file and merge to current list of aliases.
     *
     * @param string $filePath
     */
    public function parseYaml($filePath)
    {
        $this->valueList = array_merge($this->valueList, Yaml::parse(file_get_contents($filePath)));
    }
}
