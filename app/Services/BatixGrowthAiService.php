<?php

namespace App\Services;

use App\Models\MarketingCampaign;
use App\Models\MarketingLead;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BatixGrowthAiService
{
    public function generateFacebookPost(string $subject, string $audience, ?string $offer): array
    {
        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'title' => ['type' => 'string'],
                'hook' => ['type' => 'string'],
                'body' => ['type' => 'string'],
                'cta' => ['type' => 'string'],
            ],
            'required' => ['title', 'hook', 'body', 'cta'],
        ];

        return $this->structuredResponse('batix_growth_facebook_post', $schema, implode("\n", [
            'Tu es BATIX Growth, responsable acquisition de BatixPro.',
            'Rédige un unique post Facebook prêt à publier pour BatixPro.',
            'BatixPro aide les commerces, quincailleries, grossistes et distributeurs à gérer ventes, stocks, clients, fournisseurs, factures et inventaires.',
            'Écris en français naturel, professionnel et direct, adapté au marché ouest-africain sans caricature.',
            'N’invente ni témoignage, ni chiffre, ni résultat.',
            'Le hook doit être court. Le body doit être clair, aéré, prêt à être publié avec quelques emojis pertinents et des hashtags sobres.',
            'Sujet : '.$subject,
            'Audience : '.$audience,
            'Offre ou appel à l’action : '.($offer ?: 'Demander une démonstration gratuite de BatixPro.'),
        ]));
    }

    public function generateCampaignContents(MarketingCampaign $campaign): array
    {
        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'contents' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'format' => ['type' => 'string', 'enum' => ['post', 'reel_script', 'ad']],
                            'title' => ['type' => 'string'],
                            'hook' => ['type' => 'string'],
                            'body' => ['type' => 'string'],
                            'cta' => ['type' => 'string'],
                        ],
                        'required' => ['format', 'title', 'hook', 'body', 'cta'],
                    ],
                ],
            ],
            'required' => ['contents'],
        ];

        $result = $this->structuredResponse('batix_growth_campaign_content', $schema, implode("\n", [
            'Tu es BATIX Growth, responsable acquisition de BatixPro.',
            'BatixPro aide les commerces, quincailleries, grossistes et distributeurs à gérer ventes, stocks, clients, fournisseurs, factures, inventaires, boutiques et utilisateurs.',
            'Objectif : générer des conversations WhatsApp qualifiées puis des démonstrations.',
            'Écris en français naturel, professionnel et direct, adapté au marché ouest-africain sans caricature.',
            'N’invente ni témoignage, ni chiffre, ni résultat.',
            'Génère exactement trois contenus complémentaires : un post, un script Reel et une publicité.',
            "Canal : {$campaign->channel}",
            "Campagne : {$campaign->name}",
            "Objectif : {$campaign->objective}",
            "Audience : {$campaign->audience}",
            'Offre : '.($campaign->offer ?: 'Démonstration gratuite de BatixPro.'),
        ]));

        $contents = $result['contents'] ?? [];
        if (count($contents) !== 3) {
            throw new RuntimeException('Le moteur IA doit produire exactement trois contenus.');
        }

        return $contents;
    }

    public function scoreLead(MarketingLead $lead): array
    {
        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'score' => ['type' => 'integer'],
                'qualification' => ['type' => 'string', 'enum' => ['cold', 'warm', 'qualified']],
                'summary' => ['type' => 'string'],
                'next_action' => ['type' => 'string'],
                'whatsapp_message' => ['type' => 'string'],
            ],
            'required' => ['score', 'qualification', 'summary', 'next_action', 'whatsapp_message'],
        ];

        $result = $this->structuredResponse('batix_growth_lead_score', $schema, implode("\n", [
            'Tu es BATIX Growth, assistant commercial de BatixPro.',
            'Évalue uniquement les informations fournies. N’invente rien et ne déduis aucune donnée sensible.',
            'Le score est compris entre 0 et 100 et mesure la pertinence d’une démonstration BatixPro maintenant.',
            'Le message WhatsApp est court, humain, non insistant et orienté découverte ou démonstration.',
            "Nom : {$lead->name}",
            'Entreprise : '.($lead->company ?: 'non renseignée'),
            'Activité : '.($lead->business_type ?: 'non renseignée'),
            "Source : {$lead->source}",
            'Notes : '.($lead->notes ?: 'aucune'),
        ]));

        $result['score'] = max(0, min(100, (int) ($result['score'] ?? 0)));

        return $result;
    }

    private function structuredResponse(string $schemaName, array $schema, string $input): array
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            throw new RuntimeException('OPENAI_API_KEY n’est pas configurée.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(60)
            ->retry(2, 500)
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.marketing_model', 'gpt-5-mini'),
                'store' => false,
                'input' => $input,
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => $schemaName,
                        'schema' => $schema,
                        'strict' => true,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI API error '.$response->status());
        }

        foreach ($response->json('output', []) as $item) {
            if (($item['type'] ?? null) !== 'message') {
                continue;
            }
            foreach ($item['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text' && isset($content['text'])) {
                    $decoded = json_decode($content['text'], true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }
            }
        }

        throw new RuntimeException('Réponse IA inexploitable.');
    }
}
