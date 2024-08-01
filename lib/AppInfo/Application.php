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

use OCA\Sentry\Config;
use OCA\Sentry\Http\PerformanceMonitoringMiddleware;
use OCA\Sentry\InitialState\DsnProvider;
use OCA\Sentry\Listener\CustomCspListener;
use OCA\Sentry\Reporter\ISentryReporter;
use OCA\Sentry\Reporter\RecursionAwareReporter;
use OCA\Sentry\Reporter\SentryReporterAdapter;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;
use OCP\Util;
use Psr\Container\ContainerInterface;
use function Sentry\init as initSentry;

class Application extends App implements IBootstrap {

	public function __construct(array $urlParams = []) {
		parent::__construct('sentry', $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		// Register the autoloader
		include_once __DIR__ . '/../../vendor/autoload.php';

		// Wire the interface to our decorator and implementation
		$context->registerService(ISentryReporter::class, static function (ContainerInterface $c) {
			/** @var SentryReporterAdapter $inner */
			$inner = $c->get(SentryReporterAdapter::class);
			return new RecursionAwareReporter($inner);
		});
		$context->registerCrashReporter(ISentryReporter::class);
		/** @psalm-suppress TooManyArguments */
		$context->registerMiddleware(PerformanceMonitoringMiddleware::class, true);
		$context->registerEventListener(AddContentSecurityPolicyEvent::class, CustomCspListener::class);
		$context->registerInitialStateProvider(DsnProvider::class);
	}

	public function boot(IBootContext $context): void {
		Util::addScript('sentry', 'sentry');

		$context->injectFn(static function(Config $config) {
			// Now it's time to connect Sentry
			$dsn = $config->getDsn();
			if ($dsn !== null) {
				initSentry([
					'dsn' => $dsn,
					'release' => $config->getServerVersion(),
					'traces_sample_rate' => $config->getSamplingRate(),
					'profiles_sample_rate' => $config->getProfilesSamplingRate(),
					'environment' => $config->getEnvironment(),
				]);
			}
		});
	}

}
