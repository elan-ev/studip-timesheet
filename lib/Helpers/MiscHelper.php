<?php
/**
 * Miscellaneous helper class
 *
 * @package   StudipTimesheet\Helpers
 * @since     0.0.1
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipTimesheet\Helpers;

class MiscHelper
{
    public static function datetimeStringToTimestamp(
        string $datetime,
        ?int $hour = null,
        ?int $minute = null,
        ?int $second = null
    ) {
        if (empty($datetime)) {
            return null;
        }
        $datetimeObj = new \DateTimeImmutable($datetime);
        if ($hour || $minute || $second) {
            $datetimeObj->setTime($hours ?? 0, $minutes ?? 0, $second ?? 0);
        }
        return $datetimeObj->getTimestamp();
    }
}
