<?php

namespace TickX\Challenge\Service;

/**
 * Service to access backend-challenge.hasura.app API
 */
class GotService
{
    private const API_URL = 'https://backend-challenge.hasura.app/v1/graphql';

    private function execute(string $payload)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => self::API_URL,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => [
                'content-type: application/json',
                'x-hasura-admin-secret: uALQXDLUu4D9BC8jAfXgDBWm1PMpbp0pl5SQs4chhz2GG14gAVx5bfMs4I553keV'
            ],
            CURLOPT_POSTFIELDS => $payload
        ]);

        $response = curl_exec($curl);

        $errors = curl_error($curl);
        if ($errors) {
            print_r(json_decode($errors));
        }

        curl_close($curl);

        return json_decode($response);
    }

    private function getPayload(string $query, string $operationName = null): string
    {
        $payload = new \stdClass();
        $payload->query = $query;
        if ($operationName) {
            $payload->operationName = $operationName;
        }
        return json_encode($payload);
    }

    public function createCharacter(string $name, string $imageUrl = ''): int|false
    {
        $query = "mutation CreateCharacter {\n  insert_Character(objects: {name: \"{$name}\", image_url: \"{$imageUrl}\"}) {\n    returning {\n      id\n    }\n  }\n}";
        $payload = $this->getPayload($query, 'CreateCharacter');

        $response = $this->execute($payload);

        if (empty($response->data->insert_Character->returning)) {
            return false;
        }

        return current($response->data->insert_Character->returning)->id;
    }

    public function addQuote(int $characterId, string $text): int|false
    {
        $query = "mutation CreateQuote {\n  insert_Quote(objects: {text: \"{$text}\", character_id: {$characterId}}) {\n    returning {\n      id\n      text\n    }\n  }\n}\n";
        $payload = $this->getPayload($query, 'CreateQuote');

        $response = $this->execute($payload);

        if (empty($response->data->insert_Quote->returning)) {
            return false;
        }

        return current($response->data->insert_Quote->returning)->id;
    }

    public function getCharacters(): array
    {
        $query = "{\n  Character {\n    Quotes {\n      text\n      id\n    }\n    id\n    image_url\n    name\n  }\n}\n";
        $payload = $this->getPayload($query);

        $response = $this->execute($payload);

        if (empty($response->data->Character)) {
            return [];
        }

        return (array) $response->data;
    }

    public function deleteAll(): int
    {
        $query = "mutation DeleteAll {\n  delete_Character(where: {id: {_gt: 0}}) {\n    affected_rows\n  }\n}\n";
        $payload = $this->getPayload($query, 'DeleteAll');

        $response = $this->execute($payload);

        if (empty($response->data->delete_Character)) {
            return 0;
        }

        return $response->data->delete_Character->affected_rows;
    }
}
