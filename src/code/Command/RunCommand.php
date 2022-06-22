<?php

namespace TickX\Challenge\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TickX\Challenge\Service\GameOfThronesQuotesService;
use TickX\Challenge\Service\GotService;
use TickX\Challenge\Service\ThroneService;

class RunCommand extends Command
{
    private array $characters = [];
    private array $quotes = [];

    protected function configure()
    {
        $this->setName('run');
    }

    private function getCharacters($force = false): array
    {
        if (!$this->characters || $force) {
            $throneService = new ThroneService();
            $this->characters = $throneService->characters();
        }
        return $this->characters;
    }

    private function getQuotes($force = false): array
    {
        if (!$this->quotes || $force) {
            $gotQuotesService = new GameOfThronesQuotesService();
            $this->quotes = $gotQuotesService->characters();
        }
        return $this->quotes;
    }

    private function getCharacterQuotes(object $character): array
    {
        $quotes = $this->getQuotes();

        $gotCharacter = array_filter($quotes, function ($quote) use ($character) {
            return $quote->name == $character->fullName;
        });
        return $gotCharacter ? current($gotCharacter)->quotes : [];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $characters = $this->getCharacters();

        $gotService = new GotService();

        $count = ['characters' => 0, 'quotes' => 0];
        foreach ($characters as $character) {
            $characterQuotes = $this->getCharacterQuotes($character);

            //TODO Add characters with no quotes?
            //TODO What to do with characters in gameofthronesquotes.xyz that are not in throneapi.com?
            if (count($characterQuotes) == 0) {
                continue;
            }

            $id = $gotService->createCharacter($character->fullName, $character->imageUrl);
            if (!$id) {
                continue;
            }

            $count['characters']++;
            $output->writeln("Created character id: {$id}, name: {$character->fullName}, image: {$character->imageUrl}");

            foreach ($characterQuotes as $text) {
                $quoteId = $gotService->addQuote($id, $text);
                if (!$quoteId) {
                    continue;
                }

                $count['quotes']++;
                $output->writeln("Added quote id: {$quoteId}, text: {$text}");
            }
        }

        $output->writeln("Total added: {$count['characters']} characters and {$count['quotes']} quotes!");

        //TODO I added the following line for testing purposes only, it can be removed
        // $output->writeln('Total deleted: ' . $gotService->deleteAll());

        return $count['characters'];
    }
}
