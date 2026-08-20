<?php

namespace App\Http\Controllers;

use App\Models\FacebookConnection;
use App\Services\FacebookOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class FacebookConnectionController
{
    public function redirect(Request $request, FacebookOAuthService $oauth): SymfonyRedirectResponse
    {
        if (! $oauth->configured()) {
            return redirect()->route('contents.index')->with('error', 'Ajoutez d’abord l’App ID et l’App Secret Meta.');
        }

        $state = Str::random(64);
        $request->session()->put('facebook_oauth_state', $state);

        return redirect()->away($oauth->authorizationUrl($state));
    }

    public function callback(Request $request, FacebookOAuthService $oauth): Response|RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('facebook_oauth_state');
        $receivedState = $request->string('state')->toString();

        if ($request->filled('error')) {
            return redirect()->route('contents.index')->with('error', 'Connexion Facebook annulée.');
        }

        if ($expectedState === '' || $receivedState === '' || ! hash_equals($expectedState, $receivedState)) {
            return redirect()->route('contents.index')->with('error', 'La connexion Facebook a expiré. Recommencez.');
        }

        $code = $request->string('code')->toString();
        if ($code === '') {
            return redirect()->route('contents.index')->with('error', 'Meta n’a pas renvoyé de code d’autorisation.');
        }

        try {
            $pages = $oauth->pagesForCode($code);

            if ($pages === []) {
                return redirect()->route('contents.index')->with('error', 'Aucune page Facebook administrée n’a été trouvée.');
            }

            if (count($pages) === 1) {
                $this->saveConnection($request, $pages[0]);

                return redirect()->route('contents.index')->with('success', "Page Facebook « {$pages[0]['name']} » connectée.");
            }

            $request->session()->put('facebook_oauth_pages', collect($pages)->map(fn (array $page) => [
                ...$page,
                'access_token' => Crypt::encryptString($page['access_token']),
            ])->all());

            return Inertia::render('Facebook/SelectPage', [
                'pages' => collect($pages)->map->only(['id', 'name'])->values(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Facebook OAuth connection failed.', ['exception' => $exception]);

            return redirect()->route('contents.index')->with('error', 'La connexion Facebook a échoué. Vérifiez la configuration Meta.');
        }
    }

    public function select(Request $request): RedirectResponse
    {
        $data = $request->validate(['page_id' => 'required|string|max:255']);
        $pages = collect($request->session()->get('facebook_oauth_pages', []));
        $page = $pages->firstWhere('id', $data['page_id']);

        if (! $page) {
            return redirect()->route('contents.index')->with('error', 'La sélection de page a expiré. Reconnectez Facebook.');
        }

        $page['access_token'] = Crypt::decryptString($page['access_token']);
        $this->saveConnection($request, $page);
        $request->session()->forget('facebook_oauth_pages');

        return redirect()->route('contents.index')->with('success', "Page Facebook « {$page['name']} » connectée.");
    }

    public function destroy(): RedirectResponse
    {
        FacebookConnection::query()->delete();

        return redirect()->route('contents.index')->with('success', 'Page Facebook déconnectée.');
    }

    private function saveConnection(Request $request, array $page): void
    {
        FacebookConnection::query()->delete();
        FacebookConnection::create([
            'page_id' => $page['id'],
            'page_name' => $page['name'],
            'access_token' => $page['access_token'],
            'connected_by' => $request->session()->get('growth_admin_id'),
        ]);
    }
}
