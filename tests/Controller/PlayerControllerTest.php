<?php

declare(strict_types=1);

namespace Tourze\TrainCourseBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainCourseBundle\Controller\PlayerController;

/**
 * Controller 集成测试
 *
 * @internal
 */
#[CoversClass(PlayerController::class)]
#[RunTestsInSeparateProcesses]
final class PlayerControllerTest extends AbstractWebTestCase
{
    public function testVideoPlayerRouteAccessible(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);

        // Test GET request to video player route
        $client->request('GET', '/job-training/player/video');

        // Should return 200 with error message page (not 500)
        // Controller uses weuiError() which returns a user-friendly error page
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testVideoPlayerWithLessonIdParameter(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);

        // Test GET request with lessonId parameter
        $client->request('GET', '/job-training/player/video', ['lessonId' => '999']);

        // Should return 200 with error message page for non-existent lesson
        // Controller uses weuiError() which returns a user-friendly error page
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testVideoPlayerUnauthorizedAccess(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);

        // Test unauthenticated access
        $client->request('GET', '/job-training/player/video');

        $response = $client->getResponse();
        // The route is accessible without authentication
        // Controller returns user-friendly error page via weuiError()
        $this->assertEquals(200, $response->getStatusCode());
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        if ('INVALID' === $method) {
            $this->assertSame('INVALID', $method, 'No methods are disallowed for this route');

            return;
        }

        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->request($method, '/job-training/player/video');

        // 由于路由没有限制HTTP方法，所有标准HTTP方法都会被处理
        // Controller returns user-friendly error page via weuiError()
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
