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

namespace OCA\Sentry\Reporter;

use Exception;
use OCA\Sentry\Helper\CredentialStoreHelper;
use OCP\Authentication\Exceptions\CredentialsUnavailableException;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IUserSession;
use OCP\Support\CrashReport\ICollectBreadcrumbs;
use OCP\Support\CrashReport\IReporter;
use Raven_Client;
use Throwable;

class SentryReporterAdapter implements IReporter, ICollectBreadcrumbs {

	/** @var IUserSession */
	protected $userSession;

	/** @var Raven_Client */
	protected $client;

	/** @var CredentialStoreHelper */
	private $credentialStoreHelper;

	/** @var array mapping of log levels */
	protected $levels = [
		ILogger::DEBUG => 'debug',
		ILogger::INFO => 'info',
		ILogger::WARN => 'warning',
		ILogger::ERROR => 'error',
		ILogger::FATAL => 'fatal',
	];

	/** @var int */
	protected $minimumLogLevel;

	public function __construct(Raven_Client $client,
								IUserSession $userSession,
								IConfig $config,
								CredentialStoreHelper $credentialStoreHelper) {
		$this->client = $client;
		$this->userSession = $userSession;
		$this->minimumLogLevel = (int)$config->getSystemValue('sentry.minimum.log.level', ILogger::WARN);
		$this->credentialStoreHelper = $credentialStoreHelper;
	}

	/**
	 * Report an (unhandled) exception to Sentry
	 *
	 * @param Exception|Throwable $exception
	 * @param array $context
	 */
	public function report($exception, array $context = []) {
		if (isset($context['level'])
			&& $context['level'] < $this->minimumLogLevel) {
			// TODO: report as breadcrumb instead?
			return;
		}

		$sentryContext = $this->buildSentryContext($context);

		$this->client->captureException($exception, $sentryContext);
	}

	protected function buildSentryContext(array $context): array {
		$sentryContext = [];
		$sentryContext['tags'] = [];

		if (isset($context['level'])) {
			$sentryContext['level'] = $this->levels[$context['level']];
		}
		if (isset($context['app'])) {
			$sentryContext['tags']['app'] = $context['app'];
		}

		$user = $this->userSession->getUser();
		if (!is_null($user)) {
			$sentryContext['user'] = [
				'id' => $user->getUID(),
			];

			// Try to obtain the login name as well
			try {
				$credentials = $this->credentialStoreHelper->getLoginCredentials();
				$sentryContext['user']['username'] = $credentials->getLoginName();
			} catch (CredentialsUnavailableException $e) {

			}
		}
		return $sentryContext;
	}

	public function collect(string $message, string $category, array $context = []) {
		$sentryContext = $this->buildSentryContext($context);

		$sentryContext['message'] = $message;
		$sentryContext['category'] = $category;

		$this->client->breadcrumbs->record($sentryContext);
	}

}
