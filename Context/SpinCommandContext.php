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
     * The divisor for the attempt threshold.
     */
    const DIVISOR = 1000000;

    /**
     * Keep retrying assertion for a defined number of iterations.
     *
     * @param closure $lambda           Callback.
     * @param integer $attemptThreshold Number of attempts to execute the command.
     */
    public function spin($lambda, $attemptThreshold = 15)
    {
        for ($iteration = 1; $iteration <= $attemptThreshold; $iteration++) {
            try {
                call_user_func($lambda);

                return;
            } catch (\Exception $exception) {
                if ($iteration < $attemptThreshold) {
                    usleep($this->getNextDelay($iteration, $attemptThreshold));

                    continue;
                }

                throw $exception;
            }
        }
    }

    /**
     * Get the next delay value.
     *
     *    With an $attemptThreshold of 15 and a divisor of 1,000,000 the
     *    total amount of delay time will be 16.6 seconds (not counting
     *    $lambda execution time) and there will be 12 delays that are
     *    less than 1 second.
     *
     * @param integer $iteration
     * @param integer $attemptThreshold
     *
     * @return integer
     */
    private function getNextDelay($iteration, $attemptThreshold)
    {
        return ($attemptThreshold / self::DIVISOR) * pow($iteration, 10);
    }
}
