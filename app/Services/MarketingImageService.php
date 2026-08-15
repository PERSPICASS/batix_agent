<?php

namespace App\Services;

use App\Models\MarketingContent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MarketingImageService
{
    public function generate(MarketingContent $content): array
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            throw new RuntimeException('OPENAI_API_KEY n’est pas configurée.');
        }

        $prompt = $this->promptFor($content);
        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(120)
            ->retry(2, 1000)
            ->post('https://api.openai.com/v1/images/generations', [
                'model' => config('services.openai.image_model', 'gpt-image-2'),
                'prompt' => $prompt,
                'size' => '1024x1024',
                'quality' => config('services.openai.image_quality', 'low'),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI image API error '.$response->status());
        }

        $encoded = data_get($response->json(), 'data.0.b64_json');
        $image = is_string($encoded) ? base64_decode($encoded, true) : false;
        if ($image === false || $image === '') {
            throw new RuntimeException('L’API d’images n’a pas renvoyé de visuel exploitable.');
        }

        $path = 'marketing/facebook/'.now()->format('Y/m').'/'.$content->id.'-'.Str::uuid().'.png';
        Storage::disk('public')->put($path, $image, 'public');

        return compact('path', 'prompt');
    }

    private function promptFor(MarketingContent $content): string
    {
        return implode("\n", [
            'Créer un visuel professionnel et chaleureux pour une publication Facebook de BatixPro.',
            'BatixPro est un logiciel de gestion pour commerces, quincailleries, grossistes et distributeurs : ventes, stock, factures et clients.',
            'Style : illustration publicitaire moderne, réaliste et lumineuse, composition carrée adaptée à Facebook.',
            'Représenter une scène de commerce africain contemporain utilisant une solution de gestion numérique.',
            'Ne pas inclure de texte lisible, de logo, de filigrane, de marque concurrente, ni de capture d’écran d’interface.',
            'Sujet du post : '.($content->title ?: $content->hook ?: 'Gestion commerciale simplifiée.'),
            'Contexte : '.Str::limit($content->body, 800),
        ]);
    }
}
