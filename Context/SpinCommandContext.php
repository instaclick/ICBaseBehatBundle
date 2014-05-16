<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\BehatBundle\Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Spin Context.
 *
 * @author Mark Kasaboski <markk@nationalfibre.net>
 * @author John Cartwright <johnc@nationalfibre.net>
 */
class SpinCommandContext extends RawMinkContext
{
    /**
     * @var array
     */
    private $microSecondDelayList = array(
        200000,
        300000,
        400000,
        600000,
        800000,
        1000000,
        2000000,
        4000000,
        6000000,
        8000000,
    );

    /**
     * Keep retrying assertion for a defined number of iterations.
     *
     * @param closure $lambda           Callback.
     * @param integer $attemptThreshold Number of attempts to execute the command.
     *
     * @throws \Exception If attemptThreshold is met
     *
     * @return mixed
     */
    public function spin($lambda, $attemptThreshold = 9)
    {
        for ($iteration = 0; $iteration <= $attemptThreshold; $iteration++) {
            try {
                return call_user_func($lambda);
            } catch (\Exception $exception) {
                if ($iteration < $attemptThreshold) {
                    usleep($this->microSecondDelayList[$iteration]);

                    continue;
                }

                throw $exception;
            }
        }
    }
}
