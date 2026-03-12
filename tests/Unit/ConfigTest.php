<?php

declare(strict_types=1);

/**
 * @copyright 2026 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Sentry\Test\Unit;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Sentry\Config;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigTest extends TestCase {

	private IConfig|MockObject $nextcloudConfig;
	private Config $config;

	protected function setUp(): void {
		parent::setUp();

		$this->nextcloudConfig = $this->createMock(IConfig::class);
		$this->config = new Config($this->nextcloudConfig);
	}

	public function testGetPublicDsnReturnsNullWhenEmpty(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.public-dsn')
			->willReturn('');

		$result = $this->config->getPublicDsn();

		self::assertNull($result);
	}

	public function testGetPublicDsnReturnsValue(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.public-dsn')
			->willReturn('https://key@sentry.example.com/1');

		$result = $this->config->getPublicDsn();

		self::assertSame('https://key@sentry.example.com/1', $result);
	}

	public function testGetDsnPrefersDeprecatedKey(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->willReturnMap([
				['sentry.dsn', '', 'https://secret@sentry.example.com/1'],
				['sentry.public-dsn', '', 'https://public@sentry.example.com/1'],
			]);

		$result = $this->config->getDsn();

		self::assertSame('https://secret@sentry.example.com/1', $result);
	}

	public function testGetDsnFallsBackToPublicDsn(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->willReturnMap([
				['sentry.dsn', '', ''],
				['sentry.public-dsn', '', 'https://public@sentry.example.com/1'],
			]);

		$result = $this->config->getDsn();

		self::assertSame('https://public@sentry.example.com/1', $result);
	}

	public function testGetDsnReturnsNullWhenBothEmpty(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->willReturn('');

		$result = $this->config->getDsn();

		self::assertNull($result);
	}

	public function testGetCspReportUrlReturnsNullWhenEmpty(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.csp-report-url', '')
			->willReturn('');

		$result = $this->config->getCspReportUrl();

		self::assertNull($result);
	}

	public function testGetCspReportUrlReturnsValue(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.csp-report-url', '')
			->willReturn('https://sentry.example.com/api/1/security/');

		$result = $this->config->getCspReportUrl();

		self::assertSame('https://sentry.example.com/api/1/security/', $result);
	}

	public function testGetServerVersionReturnsDefault(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('version', '0.0.0')
			->willReturn('0.0.0');

		$result = $this->config->getServerVersion();

		self::assertSame('0.0.0', $result);
	}

	public function testGetServerVersionReturnsConfigured(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('version', '0.0.0')
			->willReturn('28.0.1');

		$result = $this->config->getServerVersion();

		self::assertSame('28.0.1', $result);
	}

	public function testGetSamplingRateFromConfig(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.sampling-rate', '')
			->willReturn('0.5');

		$result = $this->config->getSamplingRate();

		self::assertSame(0.5, $result);
	}

	public function testGetSamplingRateFromLogLevel(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.sampling-rate', '')
			->willReturn('');
		$this->nextcloudConfig
			->method('getSystemValueInt')
			->with('loglevel', 2)
			->willReturn(0);

		$result = $this->config->getSamplingRate();

		self::assertSame(1.0, $result);
	}

	public function testGetSamplingRateDefaultLogLevel(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.sampling-rate', '')
			->willReturn('');
		$this->nextcloudConfig
			->method('getSystemValueInt')
			->with('loglevel', 2)
			->willReturn(2);

		$result = $this->config->getSamplingRate();

		self::assertSame(0.3, $result);
	}

	public function testGetProfilesSamplingRateFromConfig(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.profiles-sampling-rate', '')
			->willReturn('0.8');

		$result = $this->config->getProfilesSamplingRate();

		self::assertSame(0.8, $result);
	}

	public function testGetProfilesSamplingRateFromLogLevel(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.profiles-sampling-rate', '')
			->willReturn('');
		$this->nextcloudConfig
			->method('getSystemValueInt')
			->with('loglevel', 2)
			->willReturn(1);

		$result = $this->config->getProfilesSamplingRate();

		self::assertSame(0.7, $result);
	}

	public function testGetProfilesSamplingRateReturnsFloat(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.profiles-sampling-rate', '')
			->willReturn('0.42');

		$result = $this->config->getProfilesSamplingRate();

		self::assertIsFloat($result);
		self::assertSame(0.42, $result);
	}

	public function testGetEnvironmentReturnsDefault(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.environment', '')
			->willReturn('');

		$result = $this->config->getEnvironment();

		self::assertSame('production', $result);
	}

	public function testGetEnvironmentReturnsConfigured(): void {
		$this->nextcloudConfig
			->method('getSystemValueString')
			->with('sentry.environment', '')
			->willReturn('staging');

		$result = $this->config->getEnvironment();

		self::assertSame('staging', $result);
	}
}
