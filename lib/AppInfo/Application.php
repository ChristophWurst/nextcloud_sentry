<?php
declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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
 *
 */

namespace OCA\Sentry\AppInfo;

use OC;
use OCA\Sentry\Reporter\SentryReporterAdapter;
use OCP\AppFramework\App;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\Security\IContentSecurityPolicyManager;
use OCP\Support\CrashReport\IRegistry;
use function Sentry\init as initSentry;

class Application extends App {

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = []) {
		parent::__construct('sentry', $urlParams);

		$container = $this->getContainer();

		/* @var $config IConfig */
		$config = $container->query(IConfig::class);
		$dsn = $config->getSystemValueString('sentry.dsn', '');
		if ($dsn !== '') {
			$this->registerClient($dsn);
		}
		$publicDsn = $config->getSystemValueString('sentry.public-dsn', '');
		$this->setInitialState($publicDsn);
		if ($publicDsn !== '') {
			$this->addCsp($publicDsn);
		}
	}

	/**
	 * @param string $dsn
	 */
	private function registerClient(string $dsn): void {
		$container = $this->getContainer();
		/* @var $config IConfig */
		$config = $container->query(IConfig::class);

		initSentry([
			'dsn' => $dsn,
			'release' => $config->getSystemValue('version', '0.0.0'),
		]);

		/* @var $registry IRegistry */
		$registry = $container->query(IRegistry::class);
		$reporter = $container->query(SentryReporterAdapter::class);
		$registry->register($reporter);
	}

	private function addCsp(string $publicDsn): void {
		$parsedUrl = parse_url($publicDsn);
		if (!isset($parsedUrl['scheme'], $parsedUrl['host'])) {
			// Misconfigured setup -> ignore
			return;
		}

		$domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
		$csp = new ContentSecurityPolicy();
		$csp->addAllowedConnectDomain($domain);
		$cspManager = OC::$server->query(IContentSecurityPolicyManager::class);
		$cspManager->addDefaultPolicy($csp);
	}

	private function setInitialState(string $dsn): void {
		$container = $this->getContainer();

		/** @var IInitialStateService $stateService */
		$stateService = $container->query(IInitialStateService::class);

		$stateService->provideLazyInitialState('sentry', 'dsn', function () use ($dsn) {
			return [
				'dsn' => $dsn === '' ? null : $dsn,
			];
		});
	}

}
