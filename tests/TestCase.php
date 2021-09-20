<?php

namespace Tests;

use App\Models\Entity;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;


abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, MigrateFreshSeedOnce;

    public $b24_entity_id = Entity::DEAL_ENTITY_ID;
    public $b24_id = 17199;
}
