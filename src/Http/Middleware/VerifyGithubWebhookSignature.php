<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject any webhook request that GitHub did not sign with our shared secret.
 *
 * GitHub HMACs the raw request body with the secret and sends the result as
 * `X-Hub-Signature-256: sha256=<hex>`; we recompute it and compare. The secret
 * doubles as the on/off switch — with none configured the endpoint 404s, so a
 * disabled webhook is indistinguishable from one that was never registered.
 *
 * @see https://docs.github.com/en/webhooks/using-webhooks/validating-webhook-deliveries
 */
final class VerifyGithubWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config()->string('bug-reports.webhook.secret', '');

        // No secret means the webhook was never turned on. 404 rather than 403
        // so an unconfigured endpoint gives nothing away.
        abort_if($secret === '', 404);

        $signature = (string) $request->header('X-Hub-Signature-256', '');
        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        // hash_equals guards the comparison against timing attacks; the length
        // check keeps it from short-circuiting on a missing header.
        abort_unless($signature !== '' && hash_equals($expected, $signature), 403);

        return $next($request);
    }
}
