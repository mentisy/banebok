<?php
declare(strict_types=1);

namespace Avolle\Banebok\Api;

use DateTime;

/**
 * Maatch entity. Named "Maatch" because "Match" is reserved PHP keyword
 */
class Maatch
{
    /**
     * Constructor method.
     *
     * @param \DateTime $date Date of match
     * @param string $day Day of match
     * @param string $time Time of match
     * @param string $homeTeam Home team
     * @param string $result Result
     * @param string $awayTeam Away team
     * @param string $pitch Pitch
     * @param string $tournament Tournament
     * @param string $playType Play type
     * @param string $matchId Match id
     */
    public function __construct(
        public DateTime $date,
        public string $day,
        public string $time,
        public string $homeTeam,
        public string $result,
        public string $awayTeam,
        public string $pitch,
        public string $tournament,
        public string $playType,
        public string $matchId,
    ) {
    }
}
