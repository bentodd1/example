<?php

namespace Tests\Services;

use App\Services\GetLinesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GetLineServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_shouldSend()
    {
        $getLineService = new GetLinesService();

        $this->assertFalse($getLineService->shouldSend());
    }
}
