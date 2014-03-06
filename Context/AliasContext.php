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
    private $map = array();

    /**
     * Constructor
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->map = Yaml::parse(file_get_contents($filename));
    }

    /**
     * Map key to value
     *
     * @param mixed $key
     *
     * @return mixed
     *
     * @Transform /^((?:[^"]|\\")+)$/
     */
    public function mapKeyToValue($key)
    {
        if (is_scalar($key) && isset($this->map[$key])) {
            return $this->map[$key];
        }

        return $key;
    }
}
