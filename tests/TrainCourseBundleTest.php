<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\TrainCourseBundle\TrainCourseBundle;

/**
 * @internal
 */
#[CoversClass(TrainCourseBundle::class)]
#[RunTestsInSeparateProcesses]
final class TrainCourseBundleTest extends AbstractBundleTestCase
{
}
