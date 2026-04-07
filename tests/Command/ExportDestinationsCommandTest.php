<?php

namespace App\Tests\Command;

use App\Command\ExportDestinationsCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ExportDestinationsCommandTest extends KernelTestCase
{
    private function buildTester(array $apiResponse): CommandTester
    {
        $mockClient = new MockHttpClient(
            new MockResponse(json_encode($apiResponse), ['http_code' => 200, 'response_headers' => ['Content-Type: application/json']])
        );

        $command = new ExportDestinationsCommand($mockClient, 'http://localhost');

        return new CommandTester($command);
    }

    public function testCommandSucceedsAndCreatesCsvFile(): void
    {
        $apiData = [
            ['name' => 'Paris', 'description' => '3 nights in a hotel', 'price' => 100.0, 'duration' => '7 days', 'image' => 'http://example.com/paris.jpg'],
            ['name' => 'Tunis', 'description' => '10 nights in a villa', 'price' => 200.0, 'duration' => '17 days', 'image' => 'http://example.com/tunis.jpg'],
        ];

        $outputFile = sys_get_temp_dir() . '/test_destinations_' . uniqid() . '.csv';

        $exitCode = $this->buildTester($apiData)->execute(['output' => $outputFile]);

        $this->assertSame(0, $exitCode);
        $this->assertFileExists($outputFile);

        unlink($outputFile);
    }

    public function testCommandExportsCsvWithCorrectHeaders(): void
    {
        $apiData = [
            ['name' => 'Paris', 'description' => '3 nights in a hotel', 'price' => 100.0, 'duration' => '7 days', 'image' => 'http://example.com/paris.jpg'],
        ];

        $outputFile = sys_get_temp_dir() . '/test_destinations_' . uniqid() . '.csv';

        $this->buildTester($apiData)->execute(['output' => $outputFile]);

        $lines = file($outputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $header = str_getcsv($lines[0], escape: '\\');

        $this->assertSame(['name', 'description', 'price', 'duration'], $header);

        unlink($outputFile);
    }

    public function testCommandExportsCsvWithCorrectData(): void
    {
        $apiData = [
            ['name' => 'Paris', 'description' => '3 nights in a hotel', 'price' => 100.0, 'duration' => '7 days', 'image' => 'http://example.com/paris.jpg'],
            ['name' => 'Tunis', 'description' => '10 nights in a villa with a swimming pool', 'price' => 200.0, 'duration' => '17 days', 'image' => 'http://example.com/tunis.jpg'],
        ];

        $outputFile = sys_get_temp_dir() . '/test_destinations_' . uniqid() . '.csv';

        $this->buildTester($apiData)->execute(['output' => $outputFile]);

        $lines = file($outputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $paris = str_getcsv($lines[1], escape: '\\');
        $this->assertSame('Paris', $paris[0]);
        $this->assertSame('3 nights in a hotel', $paris[1]);
        $this->assertSame('100', $paris[2]);
        $this->assertSame('7 days', $paris[3]);

        $tunis = str_getcsv($lines[2], escape: '\\');
        $this->assertSame('Tunis', $tunis[0]);
        $this->assertSame('17 days', $tunis[3]);

        unlink($outputFile);
    }

    public function testCommandOutputsSuccessMessage(): void
    {
        $apiData = [
            ['name' => 'Paris', 'description' => 'A city', 'price' => 100.0, 'duration' => '7 days', 'image' => 'http://example.com/paris.jpg'],
        ];

        $outputFile = sys_get_temp_dir() . '/test_destinations_' . uniqid() . '.csv';

        $tester = $this->buildTester($apiData);
        $tester->execute(['output' => $outputFile]);

        $this->assertStringContainsString('Exported 1 destination(s)', $tester->getDisplay());

        unlink($outputFile);
    }

    public function testCommandHandlesEmptyApiResponse(): void
    {
        $tester = $this->buildTester([]);
        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No destinations found', $tester->getDisplay());
    }

    public function testCommandFailsOnHttpError(): void
    {
        $mockClient = new MockHttpClient(
            new MockResponse('Server Error', ['http_code' => 500])
        );

        $command = new ExportDestinationsCommand($mockClient, 'http://localhost');
        $tester = new CommandTester($command);
        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Failed to fetch destinations', $tester->getDisplay());
    }
}
