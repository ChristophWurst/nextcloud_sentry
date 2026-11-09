<?php

declare(strict_types=1);

namespace OCA\Sentry\Tests\Integration;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCP\Http\Client\IClientService;
use Psr\Container\ContainerInterface;

final class GuzzleTest extends TestCase {

	/** @var ContainerInterface */
	private $container;

	protected function setUp(): void {
		parent::setUp();

		$this->container = \OC::$server;
	}

	public function testHttpClient(): void {
		/** @var IClientService $clientFactory */
		$clientFactory = $this->container->get(IClientService::class);
		$client = $clientFactory->newClient();

		$client->get('https://example.com/');

		$this->addToAssertionCount(1);
	}

}
