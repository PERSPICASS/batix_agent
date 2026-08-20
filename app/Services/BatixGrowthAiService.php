<?php

namespace App\Services;

use App\Models\MarketingCampaign;
use App\Models\MarketingLead;
use RuntimeException;

class BatixGrowthAiService
{
    private const DEFAULT_POST_HASHTAGS = ['#BatixPro', '#GestionCommerciale', '#GestionDeStock'];

    public function __construct(private readonly ClaudeService $claude) {}

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
                'hashtags' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'topic' => ['type' => 'string'],
                        'audience' => ['type' => 'string'],
                        'benefit' => ['type' => 'string'],
                        'brand' => ['type' => 'string'],
                    ],
                    'required' => ['topic', 'audience', 'benefit', 'brand'],
                ],
            ],
            'required' => ['title', 'hook', 'body', 'cta', 'hashtags'],
        ];

        $post = $this->structuredResponse($schema, implode("\n", [
            'Tu es BATIX Growth, responsable acquisition de BatixPro.',
            'Rédige un unique post Facebook prêt à publier pour BatixPro.',
            'BatixPro aide les commerces, quincailleries, grossistes et distributeurs à gérer ventes, stocks, clients, fournisseurs, factures et inventaires.',
            'Écris en français naturel, professionnel et direct, adapté au marché ouest-africain sans caricature.',
            'N’invente ni témoignage, ni chiffre, ni résultat.',
            'Le hook doit être court. Le body doit être clair, aéré et prêt à être publié avec quelques emojis pertinents.',
            'Propose dans le champ hashtags 3 à 5 hashtags réellement liés au sujet, au secteur et à l’audience, dont #BatixPro.',
            'N’écris aucun hashtag dans le body : ils seront ajoutés automatiquement à la fin du post.',
            'Sujet : '.$subject,
            'Audience : '.$audience,
            'Offre ou appel à l’action : '.($offer ?: 'Demander une démonstration gratuite de BatixPro.'),
        ]));

        return $this->ensurePostHashtags($post);
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
                            'hashtags' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'topic' => ['type' => 'string'],
                                    'audience' => ['type' => 'string'],
                                    'benefit' => ['type' => 'string'],
                                    'brand' => ['type' => 'string'],
                                ],
                                'required' => ['topic', 'audience', 'benefit', 'brand'],
                            ],
                        ],
                        'required' => ['format', 'title', 'hook', 'body', 'cta', 'hashtags'],
                    ],
                ],
            ],
            'required' => ['contents'],
        ];

        $result = $this->structuredResponse($schema, implode("\n", [
            'Tu es BATIX Growth, responsable acquisition de BatixPro.',
            'BatixPro aide les commerces, quincailleries, grossistes et distributeurs à gérer ventes, stocks, clients, fournisseurs, factures, inventaires, boutiques et utilisateurs.',
            'Objectif : générer des conversations WhatsApp qualifiées puis des démonstrations.',
            'Écris en français naturel, professionnel et direct, adapté au marché ouest-africain sans caricature.',
            'N’invente ni témoignage, ni chiffre, ni résultat.',
            'Génère exactement trois contenus complémentaires : un post, un script Reel et une publicité.',
            'Pour chaque contenu, propose dans le champ hashtags 3 à 5 hashtags spécifiques à son sujet, au secteur et à l’audience, dont #BatixPro.',
            'N’écris aucun hashtag dans le body : ils seront ajoutés automatiquement au contenu au format post.',
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

        return array_map(
            fn (array $content): array => ($content['format'] ?? null) === 'post'
                ? $this->ensurePostHashtags($content)
                : $content,
            $contents,
        );
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

        $result = $this->structuredResponse($schema, implode("\n", [
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

    private function structuredResponse(array $schema, string $input): array
    {
        return $this->claude->structured($schema, $input);
    }

    private function ensurePostHashtags(array $content): array
    {
        $hashtags = [];
        foreach ($content['hashtags'] ?? [] as $hashtag) {
            $normalized = preg_replace('/[^\p{L}\p{N}_]+/u', '', ltrim(trim((string) $hashtag), '#'));
            if ($normalized === '') {
                continue;
            }

            $normalized = '#'.$normalized;
            if (! in_array(mb_strtolower($normalized), array_map('mb_strtolower', $hashtags), true)) {
                $hashtags[] = $normalized;
            }
        }

        $hasBrandHashtag = in_array(mb_strtolower('#BatixPro'), array_map('mb_strtolower', $hashtags), true);
        if (! $hasBrandHashtag) {
            if (count($hashtags) >= 5) {
                array_pop($hashtags);
            }
            $hashtags[] = '#BatixPro';
        }

        foreach (self::DEFAULT_POST_HASHTAGS as $fallback) {
            if (count($hashtags) >= 3) {
                break;
            }
            if (! in_array(mb_strtolower($fallback), array_map('mb_strtolower', $hashtags), true)) {
                $hashtags[] = $fallback;
            }
        }

        $body = rtrim((string) ($content['body'] ?? ''));
        $body = preg_replace('/\s*(?:#[\p{L}\p{N}_]+\s*)+$/u', '', $body) ?? $body;
        $content['body'] = ($body === '' ? '' : $body."\n\n").implode(' ', array_slice($hashtags, 0, 5));
        unset($content['hashtags']);

        return $content;
    }
}
