<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Commands;

use CerealKiller97\FilamentBugReports\Actions\SyncBugReportGithubIssues;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Throwable;

#[Signature('bug-reports:sync')]
#[Description('Sync bug reports with their GitHub issues (marks resolved ones)')]
class SyncBugReportsCommand extends Command
{
    /**
     * @throws RequestException
     * @throws Throwable
     * @throws ConnectionException
     */
    public function handle(SyncBugReportGithubIssues $sync): int
    {
        $changed = $sync->handle();

        $this->info(sprintf('Synced. Reports updated: %d.', $changed));

        return self::SUCCESS;
    }
}
