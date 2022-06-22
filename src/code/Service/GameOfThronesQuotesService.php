<?php

namespace TickX\Challenge\Service;

/**
 * Service to access gameofthronesquotes.xyz API
 */
class GameOfThronesQuotesService
{
    private const API_URL = 'https://api.gameofthronesquotes.xyz/v1/';

    /**
     * Retrieve data from gameofthronesquotes.xyz API
     *
     * @param string $action
     * @return array|object
     */
    private function execute(string $action): array|object
    {
        $context = stream_context_create([
            'http' => ['ignore_errors' => true],
        ]);

        $data = file_get_contents(self::API_URL . $action, false, $context);
        return json_decode($data);
    }

    /**
     * Get several random quotes
     *
     * @param integer|null $qtty
     * @return array
     */
    public function random(int $qtty = null): array
    {
        $action = $qtty
            ? "random/{$qtty}"
            : 'random';

        $sentences = $this->execute($action);

        return is_array($sentences) ? $sentences : [$sentences];
    }

    /**
     * Get quotes from a character
     *
     * @param string $slug
     * @param integer|null $qtty
     * @return object|false
     */
    public function author(string $slug, int $qtty = null): array
    {
        $action = $qtty
            ? "author/{$slug}/{$qtty}"
            : "random/{$slug}";

        $sentences = $this->execute($action);

        return is_array($sentences) ? $sentences : [$sentences];
    }

    /**
     * List of houses with their members
     *
     * @return array
     */
    public function houses(): array
    {
        return $this->execute('houses');
    }

    /**
     * Get house's details
     *
     * @param string $slug
     * @return object|false
     */
    public function house(string $slug): object|false
    {
        return current($this->execute("house/{$slug}"));
    }

    /**
     * List of characters with their quotes
     *
     * @return array
     */
    public function characters(): array
    {
        return $this->execute('characters');
    }

    /**
     * Get character's details with his quotes
     *
     * @param string $slug
     * @return object|false
     */
    public function character(string $slug): object|false
    {
        return current($this->execute("character/{$slug}"));
    }
}
