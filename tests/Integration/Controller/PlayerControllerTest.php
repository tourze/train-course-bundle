<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tourze\TrainCourseBundle\Controller\PlayerController;

class PlayerControllerTest extends WebTestCase
{
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(PlayerController::class));
    }
}