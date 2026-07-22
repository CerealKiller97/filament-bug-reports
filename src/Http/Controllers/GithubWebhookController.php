<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Http\Controllers;

use CerealKiller97\FilamentBugReports\Actions\HandleGithubIssueWebhook;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The endpoint GitHub delivers repository webhooks to. The signature has
 * already been verified by the middleware on the route, so by the time we get
 * here the body is known to have come from GitHub with our secret.
 *
 * Only `issues` events in a `closed`/`reopened` state can change a report, so
 * everything else is acknowledged and dropped — GitHub retries on non-2xx, and
 * we do not want a stream of retries for events we will always ignore.
 */
final class GithubWebhookController
{
    public function __invoke(Request $request, HandleGithubIssueWebhook $handler): Response
    {
        if ($request->header('X-GitHub-Event') !== 'issues') {
            return response()->noContent();
        }

        // `closed` sets the report resolved; `reopened` clears it. The other
        // issue actions (opened, edited, labeled, deleted, …) never move that
        // needle, so there is nothing to mirror.
        if (! in_array($request->input('action'), ['closed', 'reopened'], true)) {
            return response()->noContent();
        }

        $handler->handle($request->all());

        return response()->noContent();
    }
}
