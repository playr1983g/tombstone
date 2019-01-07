<?php

namespace Scheb\Tombstone\Test;

use Scheb\Tombstone\BufferedGraveyard;
use Scheb\Tombstone\Graveyard;
use Scheb\Tombstone\GraveyardInterface;

class BufferedGraveyardTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerGraveyard;

    /**
     * @var Graveyard
     */
    private $graveyard;

    protected function setUp()
    {
        $this->innerGraveyard = $this->createMock(GraveyardInterface::class);
        $this->graveyard = new BufferedGraveyard($this->innerGraveyard);
    }

    /**
     * @test
     */
    public function tombstone_tombstoneInvoked_notAddToInnerGraveyard(): void
    {
        $this->innerGraveyard
            ->expects($this->never())
            ->method($this->anything());

        $this->graveyard->tombstone(['args'], ['trace1']);
    }

    /**
     * @test
     */
    public function flush_tombstonesBuffered_addBufferedTombstonesAndFlush(): void
    {
        $this->innerGraveyard
            ->expects($this->exactly(2))
            ->method('tombstone')
            ->withConsecutive(
                [['args'], ['trace1']],
                [['args'], ['trace2']]
            );

        $this->innerGraveyard
            ->expects($this->exactly(2))
            ->method('flush');

        $this->graveyard->tombstone(['args'], ['trace1']);
        $this->graveyard->flush();

        $this->graveyard->tombstone(['args'], ['trace2']);
        $this->graveyard->flush();
    }
}
