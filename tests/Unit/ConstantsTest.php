<?php

namespace tests\Unit;

use Tests\TestCase;


class ConstantsTest extends TestCase
{

    public function testConstants()
    {
        $this->assertEquals('password', PASSWORD);
        $this->assertEquals('Data has been saved successfully', SAVE_SUCCESS);
    }

}
