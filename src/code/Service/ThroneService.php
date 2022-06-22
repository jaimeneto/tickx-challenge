<?php

namespace TickX\Challenge\Service;

/**
 * Service to access throneapi.com API
 */
class ThroneService
{
    private const API_URL = 'https://thronesapi.com/api/v2/';

    private function execute(string $action): array|object
    {
        $context = stream_context_create([
            'http' => ['ignore_errors' => true],
        ]);

        $data = file_get_contents(self::API_URL . $action, false, $context);
        return json_decode($data);
    }

    /**
     * Returns all the characters.
     *
     * @return array
     */
    public function characters(): array
    {
        return $this->execute('Characters');
    }

    /**
     * Returns a specific character, by id.
     *
     * @param integer $id
     * @return object
     */
    public function character(int $id): object|false
    {
        $data = $this->execute("Characters/{$id}");

        if (!isset($data->id)) {
            return false;
        }

        return $data;
    }

    /**
     * Returns all continents.
     *
     * @return array
     */
    public function continents(): array
    {
        return $this->execute('Continents');
    }

    /**
     * Returns a specific continent, by id.
     *
     * @param integer $id
     * @return array
     */
    public function continent(int $id): object|false
    {
        $data = $this->execute("Continents/{$id}");

        if (!isset($data->id)) {
            return false;
        }

        return $data;
    }
}
