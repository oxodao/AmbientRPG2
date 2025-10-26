<?php

namespace App\Tests;

use Zenstruck\Assert;
use Zenstruck\Browser\KernelBrowser;

class HydraApiBrowser extends KernelBrowser
{
    public const array DATETIME_SCHEMA = [
        'type' => 'string',
        'pattern' => '^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$',
    ];

    /**
     * Attention si vous avez des sous-objets, bien penser à définir `'additionalProperties' => false` dedans
     * afin de s'assuer qu'on n'envoie pas de data en plus que ce que l'on pensait.
     * Si on a un array dans notre schema il faut aussi préciser `'minItems' => 1` et s'assurer qu'on a au moins un
     * élément dans le test.
     *
     * Les méthodes buildSchemaArrayObject et buildSchemaObject peuvent être utilisées pour s'aider
     *
     * @param array<string, mixed> $schema
     * @param array<string>|null   $rootRequired
     * @param bool                 $customEndpoint Quand on retourne un objet qui n'est pas une Api Resource afin (ex Determination Questionnaire), le "@context" est un objet
     *
     * @example
     * ```php
     * $browser
     *     ->get(\sprintf('/api/activite/%s', $activite->getId()))
     *     ->assertStatus(200)
     *     ->assertJsonItemOk([
     *          "id" => ["type" => "number"],
     *          "libelle" => ["type" => "string"],
     *          "detail" => ["type" => "string"],
     *          "resume" => ["type" => "string"],
     *          "typeActivite" => ["type" => "string"],
     *     ], ['id', 'libelle']);
     * ```
     */
    public function assertJsonItemSchemaOk(array $schema, ?array $rootRequired = null, bool $customEndpoint = false): self
    {
        if (null === $rootRequired) {
            $rootRequired = \array_keys($schema);
        }

        $ctx = ['type' => 'string'];
        if ($customEndpoint) {
            $ctx = ['type' => 'object'];
        }

        $jsonArr = \json_encode([
            'type' => 'object',
            'properties' => [
                '@id' => ['type' => 'string'],
                '@context' => $ctx,
                '@type' => ['type' => 'string'],
                ...$schema,
            ],
            'required' => $rootRequired,
            'additionalProperties' => false,
        ]);

        if (!$jsonArr) {
            throw new \LogicException('thats bs');
        }

        $this->json()->assertMatchesSchema($jsonArr);

        return $this;
    }

    /**
     * @param array<string>|null $violationMessages
     */
    public function assertValidationError(?array $violationMessages = null, ?string $title = null, ?string $detail = null, ?string $description = null): self
    {
        $json = $this->json();

        $jsonArr = \json_encode(self::buildValidationError());
        if (!$jsonArr) {
            throw new \LogicException('thats bs');
        }

        $json->assertMatchesSchema($jsonArr);

        if (null !== $violationMessages) {
            $json->assertMatches('length("violations")', \count($violationMessages));

            foreach ($violationMessages as $idx => $message) {
                $json->assertMatches(\sprintf('violations[%d].message', $idx), $message);
            }
        }

        if (null !== $title) {
            $json->assertMatches('title', $title);
        }

        if (null !== $detail) {
            $json->assertMatches('detail', $detail);
        }

        if (null !== $description) {
            $json->assertMatches('description', $description);
        }

        return $this;
    }

    /**
     * Attention si vous avez des sous-objets, bien penser à définir `'additionalProperties' => false` dedans
     * afin de s'assuer qu'on n'envoie pas de data en plus que ce que l'on pensait
     * Si on a un array dans notre schema il faut aussi préciser `'minItems' => 1` et s'assurer qu'on a au moins un
     * élément dans le test.
     *
     * Les méthodes buildSchemaArrayObject et buildSchemaObject peuvent être utilisées pour s'aider
     *
     * @param array<string, mixed> $schema
     * @param array<string>|null   $rootRequired
     * @param array<string, mixed> $extraRoot
     *
     * @example
     * ```php
     * $browser
     *     ->get('/api/activite')
     *     ->assertStatus(200)
     *     ->use($this->assertJsonCollectionOk([
     *          "id" => ["type" => "number"],
     *          "libelle" => ["type" => "string"],
     *          "detail" => ["type" => "string"],
     *          "resume" => ["type" => "string"],
     *          "typeActivite" => ["type" => "string"],
     *     ], ['id', 'libelle']));
     * ```
     */
    public function assertJsonCollectionSchemaOk(array $schema, int $amtElements, ?array $rootRequired = null, array $extraRoot = []): self
    {
        if (null === $rootRequired) {
            $rootRequired = \array_keys($schema);
        }

        $collectionRootRequired = [
            '@id',
            '@context',
            'totalItems',
            'member',
        ];

        $jsonArr = \json_encode(
            self::buildSchemaObject([
                '@context' => ['type' => 'string'],
                'totalItems' => ['type' => 'integer'],
                'member' => self::buildSchemaArrayObject([
                    '@context' => ['type' => 'string'],
                    ...$schema,
                ], $rootRequired),
                'view' => ['type' => 'object'],
                'search' => ['type' => 'object'],
                ...$extraRoot,
            ], $collectionRootRequired),
        );

        if (!$jsonArr) {
            throw new \LogicException('thats bs');
        }

        $this->json()->assertMatchesSchema($jsonArr)->assertMatches('length("member")', $amtElements);

        return $this;
    }

    /**
     * Cette méthode permet de tester rapidement une réponse API pour s'assurer que les champs donnés
     * ont la valeur attendu.
     *
     * Attention il est tout de même toujours nécessaire de faire appel aussi à la méthode
     * assertJsonItemOk pour s'assurer du schéma (pas de valeur superflues)
     *
     * @param array<mixed> $data
     */
    public function assertResponseMatches(array|callable $data, string $prefix = ''): self
    {
        if (\is_callable($data)) {
            $data = $data();
        }

        $json = $this->json();

        /*
         * Cursed mais nécessaires pour les sous objets
         * Sinon on compare l'array complète ce qui pose problème
         * puisqu'on a des @id et @type qu'on ne veux pas valider
         * pas exemple quand on teste l'endpoint POST
         */
        foreach ($data as $key => $value) {
            // Si la valeur n'est pas un tableau, on compare directement
            if (!\is_array($value)) {
                Assert::that($json->search($this->prefix($prefix, $key)))->equals($value);

                continue;
            }

            /**
             * On check si l'array est associatif (possède des clé non numériques)
             * ou est un vrai array.
             * Si c'est le cas on doit l'indexer avec [] au lieux du point.
             */
            $isList = \array_reduce(
                \array_keys($value),
                fn ($carry, $key) => $carry && \is_numeric($key),
                true,
            );

            if (!$isList) {
                // Si c'est un tableau associatif,
                // on recurse avec le prefix de l'objet
                $this->assertResponseMatches($value, $this->prefix($prefix, $key));

                continue;
            }

            // Ce n'est ni un tableau associatif, ni un scalaire
            // Donc c'est un tableau indexé normal
            // Donc on itère dessus et on compare comme précédement

            foreach ($value as $listIdx => $listVal) {
                $listKey = \sprintf('%s[%s]', $this->prefix($prefix, $key), $listIdx);

                if (\is_array($listVal)) {
                    // Si c'est une sous-array, on recurse
                    $this->assertResponseMatches($listVal, $listKey);

                    continue;
                }

                // Si la valeur est scalaire on la teste directement
                Assert::that($json->search($listKey))->equals($listVal);
            }
        }

        return $this;
    }

    /**
     * @template T of object
     *
     * @param array<T>                          $entities
     * @param \Closure(T): array<string, mixed> $dataCb
     */
    public function assertCollectionResponseMatches(array $entities, \Closure $dataCb): self
    {
        foreach ($entities as $idx => $entity) {
            $prefix = \sprintf('"member"[%s]', $idx);

            $this->assertResponseMatches($dataCb($entity), $prefix);
        }

        return $this;
    }

    /**
     * @param array<mixed>       $schema
     * @param array<string>|null $rootRequired
     * @param array<mixed>       $extraConfig
     *
     * @return array<mixed>
     */
    public static function buildSchemaObject(array $schema, ?array $rootRequired = null, array $extraConfig = []): array
    {
        if (null === $rootRequired) {
            $rootRequired = \array_keys($schema);
        }

        return [
            'type' => 'object',
            'required' => $rootRequired,
            'additionalProperties' => false,
            ...$extraConfig,
            'properties' => [
                '@id' => ['type' => 'string'],
                '@type' => ['type' => 'string'],
                ...$schema,
            ],
        ];
    }

    /**
     * @param array<mixed>       $schema
     * @param array<string>|null $rootRequired
     *
     * @return array<mixed>
     */
    public static function buildSchemaArrayObject(array $schema, ?array $rootRequired = null): array
    {
        return [
            'type' => 'array',
            'minItems' => 1,
            'items' => self::buildSchemaObject($schema, $rootRequired),
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function buildEmptyArray(): array
    {
        return ['type' => 'array'];
    }

    /**
     * @return array<mixed>
     */
    public static function buildSchemaIri(string $iriBase): array
    {
        return [
            'type' => 'string',
            'pattern' => \sprintf('^/api/%s/[^/]+$', $iriBase),
        ];
    }

    /**
     * Permet de valider un array d'iri lié à une entité spécifique.
     *
     * @return array<mixed>
     */
    public static function buildSchemaIriArray(string $iriBase): array
    {
        return [
            'type' => 'array',
            'minItems' => 1,
            'items' => self::buildSchemaIri($iriBase),
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function buildSchemaProfilePicture(): array
    {
        return [
            'oneOf' => [
                ['type' => 'null'],
                [
                    'type' => 'object',
                    'properties' => [
                        'sm' => ['type' => 'string'],
                        'md' => ['type' => 'string'],
                        'xl' => ['type' => 'string'],
                    ],
                    'required' => ['sm', 'md', 'xl'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function buildValidationError(): array
    {
        return self::buildSchemaObject([
            '@context' => ['type' => 'string'],
            'status' => ['type' => 'integer'],
            'type' => ['type' => 'string'],
            'title' => ['type' => 'string'],
            'detail' => ['type' => 'string'],
            'description' => ['type' => 'string'],
            'violations' => self::buildSchemaArrayObject([
                'propertyPath' => ['type' => 'string'],
                'message' => ['type' => 'string'],
                'code' => ['type' => ['string', 'null']],
            ]),
        ]);
    }

    private function prefix(string $prefix, string $key): string
    {
        if (0 === \strlen($prefix)) {
            return $key;
        }

        return \sprintf('%s.%s', $prefix, $key);
    }
}
