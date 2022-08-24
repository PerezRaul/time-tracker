<?php

declare(strict_types=1);

namespace Src\TimeTracker\Application\Find;

use Src\Shared\Domain\TimeTrackers\TimeTrackerId;
use Src\TimeTracker\Application\TimeTrackerResponse;
use Src\TimeTracker\Domain\Services\TimeTrackerFinder;
use Src\Shared\Domain\Bus\Query\QueryHandler;

final class FindTimeTrackerQueryHandler implements QueryHandler
{
    public function __construct(private TimeTrackerFinder $finder)
    {
    }

    public function __invoke(FindTimeTrackerQuery $query): TimeTrackerResponse
    {
        $timeTracker = $this->finder->__invoke(new TimeTrackerId($query->id()));

        return new TimeTrackerResponse(
            $timeTracker->id(),
            $timeTracker->name(),
            $timeTracker->startsAtTime(),
            $timeTracker->endsAtTime(),
            $timeTracker->createdAt(),
            $timeTracker->updatedAt(),
        );
    }
}
