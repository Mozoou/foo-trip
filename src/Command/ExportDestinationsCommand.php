<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:export-destinations',
    description: 'Fetch all destinations from the API and export them to a CSV file.',
)]
class ExportDestinationsCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $defaultUri,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('output', InputArgument::OPTIONAL, 'Output CSV file path', 'destinations.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $outputFile = $input->getArgument('output');

        $url = rtrim($this->defaultUri, '/') . '/api/destinations';

        $io->info(sprintf('Fetching destinations from %s', $url));

        try {
            $response = $this->httpClient->request('GET', $url);
            $destinations = $response->toArray();
        } catch (\Throwable $e) {
            $io->error(sprintf('Failed to fetch destinations: %s', $e->getMessage()));
            return Command::FAILURE;
        }

        if (empty($destinations)) {
            $io->warning('No destinations found.');
            return Command::SUCCESS;
        }

        $handle = fopen($outputFile, 'w');
        if ($handle === false) {
            $io->error(sprintf('Cannot open file for writing: %s', $outputFile));
            return Command::FAILURE;
        }

        fputcsv($handle, ['name', 'description', 'price', 'duration']);

        foreach ($destinations as $destination) {
            fputcsv($handle, [
                $destination['name'],
                $destination['description'],
                $destination['price'],
                $destination['duration'],
            ]);
        }

        fclose($handle);

        $io->success(sprintf('Exported %d destination(s) to %s', count($destinations), $outputFile));

        return Command::SUCCESS;
    }
}
