<?php

declare(strict_types=1);

namespace Tamash\DiscogsApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse, JsonResponse};
use Symfony\Component\Routing\Annotation\Route;
use Tamash\DiscogsApiBundle\Client\Authenticator\OAuth1Authenticator;
use League\OAuth1\Client\Credentials\TokenCredentials;
use Tamash\DiscogsApiBundle\Event\{
    OAuthRequestTokenEvent,
    OAuthAccessTokenEvent,
    OAuthCompleteEvent
};
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OAuthController extends AbstractController
{
    private OAuth1Authenticator $oauthServer;
    private EventDispatcherInterface $dispatcher;

    public function __construct(OAuth1Authenticator $oauthServer, EventDispatcherInterface $dispatcher)
    {
        $this->oauthServer = $oauthServer;
        $this->dispatcher = $dispatcher;
    }

    /**
     * GET /oauth/request-token
     * Initiates OAuth flow and redirects user to Discogs
     */
    #[Route('/oauth/request-token', name: 'discogs_api_oauth_request_token', methods: ['GET'])]
    public function requestToken(Request $request, string $callbackUrl = null): RedirectResponse
    {
        // Get temporary credentials
        $temporaryCredentials = $this->oauthServer->getTemporaryCredentials();

        // Store in session
        $request->getSession()->set('discogs_oauth_temporary', serialize($temporaryCredentials));

        // Build callback URL if not provided
        if ($callbackUrl === null) {
            $callbackUrl = $this->generateUrl('discogs_api_oauth_callback', [], 0);
        }

        // Add callback to temporary credentials
        $temporaryCredentials['oauth_callback'] = $callbackUrl;

        // Dispatch event
        $event = new OAuthRequestTokenEvent($temporaryCredentials, $callbackUrl);
        $this->dispatcher->dispatch($event, 'discogs_api.oauth.request_token');

        // Redirect to Discogs authorization page
        $authUrl = $this->oauthServer->getAuthorizationUrl($temporaryCredentials);
        return $this->redirect($authUrl);
    }

    /**
     * GET /oauth/callback
     * Handles OAuth callback from Discogs
     */
    #[Route('/oauth/callback', name: 'discogs_api_oauth_callback', methods: ['GET'])]
    public function callback(Request $request, string $token, string $verifier): Response
    {
        $session = $request->getSession();
        $temporaryCredentials = @unserialize($session->get('discogs_oauth_temporary', ''));

        if (!$temporaryCredentials) {
            throw new \RuntimeException('OAuth temporary credentials not found in session');
        }

        // Exchange request token for access token
        $tokenCredentials = $this->oauthServer->getTokenCredentials(
            $temporaryCredentials,
            $token,
            $verifier
        );

        // Store token credentials in session or DB
        $session->set('discogs_oauth_token', serialize($tokenCredentials));

        // Dispatch completion event
        $event = new OAuthCompleteEvent($tokenCredentials);
        $this->dispatcher->dispatch($event, 'discogs_api.oauth.complete');

        // Clear temporary credentials
        $session->remove('discogs_oauth_temporary');

        return $this->json([
            'status' => 'success',
            'token' => $tokenCredentials->getIdentifier(),
            'token_secret' => $tokenCredentials->getSecret(),
        ]);
    }

    /**
     * GET /oauth/token
     * Returns current access token (for debugging/session inspection)
     */
    #[Route('/oauth/token', name: 'discogs_api_oauth_token', methods: ['GET'])]
    public function getToken(Request $request): JsonResponse
    {
        $token = $request->getSession()->get('discogs_oauth_token');
        if ($token) {
            $token = unserialize($token);
            return $this->json([
                'token' => $token->getIdentifier(),
                'token_secret' => $token->getSecret(),
            ]);
        }

        return $this->json(['status' => 'no_token'], 404);
    }

    /**
     * POST /oauth/logout
     * Clears OAuth session
     */
    #[Route('/oauth/logout', name: 'discogs_api_oauth_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $request->getSession()->remove('discogs_oauth_temporary');
        $request->getSession()->remove('discogs_oauth_token');

        return $this->json(['status' => 'logged_out']);
    }
}
