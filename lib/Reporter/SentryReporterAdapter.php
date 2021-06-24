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
use OCP\Support\CrashReport\IMessageReporter;
use function Sentry\addBreadcrumb;
use Sentry\Breadcrumb;
use function Sentry\captureException;
use function Sentry\captureMessage;
use function Sentry\configureScope;
use Sentry\Severity;
use Sentry\State\Scope;
use Throwable;

class SentryReporterAdapter implements IMessageReporter, ICollectBreadcrumbs, ISentryReporter {

	/** @var IUserSession */
	protected $userSession;

	/** @var CredentialStoreHelper */
	private $credentialStoreHelper;

	/** @var bool */
	private $userScopeSet = false;

	/** @var array mapping of log levels */
	private const levels = [
		ILogger::DEBUG => Severity::DEBUG,
		ILogger::INFO => Severity::INFO,
		ILogger::WARN => Severity::WARNING,
		ILogger::ERROR => Severity::ERROR,
		ILogger::FATAL => Severity::FATAL,
	];

	/** @var int */
	private $minimumLogLevel;

	public function __construct(IUserSession $userSession,
								IConfig $config,
								CredentialStoreHelper $credentialStoreHelper) {
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

		$this->setSentryScope($context);

		captureException($exception);
	}

	protected function setSentryScope(array $context): void {
		configureScope(function (Scope $scope) use ($context): void {
			if (isset($context['level'])) {
				$scope->setLevel(
					new Severity(self::levels[$context['level']])
				);
			}
			if (isset($context['app'])) {
				$scope->setTag('app', $context['app']);
			}

			if ($this->userScopeSet) {
				// Run the code below just once
				return;
			}
			$this->userScopeSet = true;
			$user = $this->userSession->getUser();
			if ($user !== null) {
				// Try to obtain the login name as well
				try {
					$credentials = $this->credentialStoreHelper->getLoginCredentials();
					$username = $credentials->getLoginName();
				} catch (CredentialsUnavailableException $e) {
					$username = null;
				}

				$scope->setUser([
					'id' => $user->getUID(),
					'username' => $username,
				]);
			}
		});
	}

	public function collect(string $message, string $category, array $context = []) {
		if (isset($context['app'])) {
			$message = "[" . $context['app'] . "] " . $message;
		}

		$this->setSentryScope($context);

		$level = $context['level'] ?? ILogger::WARN;
		$sentryLevel = self::levels[$level] ?? Breadcrumb::LEVEL_WARNING;

		addBreadcrumb(new Breadcrumb($sentryLevel, Breadcrumb::TYPE_ERROR, $category, $message));
	}

	/**
	 * Report a (error) message
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function reportMessage(string $message, array $context = []): void {
		if (isset($context['level'])
			&& $context['level'] < $this->minimumLogLevel) {
			$this->collect($message, 'message', $context);
			return;
		}

		if (isset($context['app'])) {
			$message = "[" . $context['app'] . "] " . $message;
		}

		captureMessage(
			$message,
			new Severity(self::levels[$context['level'] ?? ILogger::WARN] ?? Severity::WARNING)
		);
	}

}
