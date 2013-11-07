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
     * Keep retrying assertion for a defined number of iterations.
     *
     * @param closure $lambda           Callback.
     * @param integer $attemptThreshold Number of attempts to execute the command.
     * @param integer $interval         Interval in milliseconds to wait after an attempt.
     */
    public function spin($lambda, $attemptThreshold = 5, $interval = 1)
    {
        for ($iterations = 1; $iterations <= $attemptThreshold; $iterations++) {
            try {
                call_user_func($lambda);

                return;
            } catch (\Exception $exception) {
                if ($iterations < $attemptThreshold) {
                    usleep($interval * 1000);

                    continue;
                }

                throw $exception;
            }
        }
    }
}
