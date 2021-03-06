<?php

declare(strict_types=1);

namespace Scheb\Tombstone\Tests\Analyzer\Log;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\Tombstone\Analyzer\Cli\ConsoleOutputInterface;
use Scheb\Tombstone\Analyzer\Cli\ProgressBar;
use Scheb\Tombstone\Analyzer\Log\AnalyzerLogFileReader;
use Scheb\Tombstone\Analyzer\Log\AnalyzerLogProvider;
use Scheb\Tombstone\Core\Model\Vampire;
use Scheb\Tombstone\Tests\TestCase;

class AnalyzerLogProviderTest extends TestCase
{
    /**
     * @var MockObject|AnalyzerLogFileReader
     */
    private $logFileReader;

    /**
     * @var MockObject|ConsoleOutputInterface
     */
    private $consoleOutput;

    /**
     * @var AnalyzerLogProvider
     */
    private $logProvider;

    protected function setUp(): void
    {
        $this->logFileReader = $this->createMock(AnalyzerLogFileReader::class);
        $this->consoleOutput = $this->createMock(ConsoleOutputInterface::class);
        $this->logProvider = new AnalyzerLogProvider($this->logFileReader, __DIR__.'/fixtures', $this->consoleOutput);
    }

    /**
     * @test
     */
    public function iterateVampires_fixtureDirectoryGiven_yieldVampiresFromAllLogFiles(): void
    {
        $vampire1 = $this->createMock(Vampire::class);
        $vampire2 = $this->createMock(Vampire::class);
        $vampire3 = $this->createMock(Vampire::class);

        $this->logFileReader
            ->expects($this->exactly(2))
            ->method('readLogFile')
            ->with($this->logicalOr(
                __DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'allValid.tombstone',
                __DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'malformedData.tombstone'
            ))
            ->willReturnOnConsecutiveCalls(
                new \ArrayIterator([$vampire1, $vampire2]),
                new \ArrayIterator([$vampire3])
            );

        $traversable = $this->logProvider->getVampires();
        $items = iterator_to_array($traversable, false);

        $this->assertCount(3, $items);
        $this->assertSame([$vampire1, $vampire2, $vampire3], $items);
    }

    /**
     * @test
     */
    public function iterateVampires_fixtureDirectoryGiven_advanceProgressBarForEachFile(): void
    {
        $this->logFileReader
            ->expects($this->any())
            ->method('readLogFile')
            ->willReturn(new \ArrayIterator([]));

        $progressBar = $this->createMock(ProgressBar::class);
        $progressBar
            ->expects($this->exactly(2))
            ->method('advance');

        $this->consoleOutput
            ->expects($this->any())
            ->method('createProgressBar')
            ->willReturn($progressBar);

        $traversable = $this->logProvider->getVampires();
        iterator_to_array($traversable, false);
    }
}
