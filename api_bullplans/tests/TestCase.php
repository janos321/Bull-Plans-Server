<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\ApiTestHelpers;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ApiTestHelpers;
}