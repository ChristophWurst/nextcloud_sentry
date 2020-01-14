<?php

declare(strict_types=1);

namespace OCA\Sentry\Tests\Integration;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCP\Http\Client\IClientService;
use OCP\IServerContainer;

class GuzzleTest extends TestCase {

	/** @var IServerContainer */
	private $container;

	protected function setUp(): void {
		parent::setUp();

		$this->container = \OC::$server;
	}

	public function testHttpClient(): void {
		/** @var IClientService $clientFactory */
		$clientFactory = $this->container->query(IClientService::class);
		$client = $clientFactory->newClient();

		$client->get('https://example.com/');

		$this->addToAssertionCount(1);
	}

}
