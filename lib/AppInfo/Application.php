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
use OCA\Sentry\Reporter\SentryReporterBreadcrumbAdapter;
use OCA\Sentry\Reporter\SentryReporterAdapter;
use OCP\AppFramework\App;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\IConfig;
use OCP\Security\IContentSecurityPolicyManager;
use OCP\Support\CrashReport\IRegistry;
use Raven_Client;
use Raven_ErrorHandler;

class Application extends App {

	/**
	 * @param array $urlParams
	 */
	public function __construct($urlParams = []) {
		parent::__construct('sentry', $urlParams);

		$container = $this->getContainer();

		/* @var $config IConfig */
		$config = $container->query(IConfig::class);
		/** @var IContentSecurityPolicyManager $cspManager */
		$cspManager = $container->query(IContentSecurityPolicyManager::class);

		$dsn = $config->getSystemValue('sentry.dsn', null);
		$reportUrl = $config->getSystemValue('sentry.csp-report-url', null);
		if (!is_null($dsn)) {
			$this->registerClient($config, $dsn);
		}
		$publicDsn = $config->getSystemValue('sentry.public-dsn', null);
		$this->addCsp($cspManager, $publicDsn, $reportUrl);
	}

	/**
	 * @param string $dsn
	 */
	private function registerClient(IConfig $config, string $dsn) {
		$container = $this->getContainer();

		$client = new Raven_Client($dsn);
		$client->setRelease($config->getSystemValue('version', '0.0.0'));
		$container->registerService(Raven_Client::class, function () use ($client) {
			return $client;
		});

		/* @var $registry IRegistry */
		$registry = $container->query(IRegistry::class);
		$reporter = $container->query(SentryReporterAdapter::class);
		$registry->register($reporter);

		$this->registerErrorHandlers($client);
	}

	private function registerErrorHandlers(Raven_Client $client) {
		$errorHandler = new Raven_ErrorHandler($client);
		$errorHandler->registerExceptionHandler();
		$errorHandler->registerErrorHandler();
		$errorHandler->registerShutdownFunction();
	}

	public function addCsp(IContentSecurityPolicyManager $cspManager,
						   string $publicDsn = null,
						   string $reportUrl = null) {
		if (is_null($publicDsn) && is_null($reportUrl)) {
			// Don't add any custom CSP
			return;
		}

		$csp = new ContentSecurityPolicy();

		if (!is_null($publicDsn)) {
			$parsedUrl = parse_url($publicDsn);
			if (!isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
				// Misconfigured setup -> ignore
				return;
			}

			$domain = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
			$csp->addAllowedConnectDomain($domain);
		}

		if (!is_null($reportUrl)) {
			$csp->addReportTo($reportUrl);
		}

		$cspManager->addDefaultPolicy($csp);
	}

}
