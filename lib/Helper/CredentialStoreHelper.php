<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2018, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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

namespace OCA\Sentry\Helper;

use OCP\Authentication\Exceptions\CredentialsUnavailableException;
use OCP\Authentication\LoginCredentials\ICredentials;
use OCP\Authentication\LoginCredentials\IStore;

/*
 * This helper class is added to break the circle when fetching the login credentials.
 * If this fails a debug message will be logged which will be added as a breadcrumb
 * which will try to fetch the credentials etc.
 *
 * This breaks that cycle.
 */
class CredentialStoreHelper {
	/** @var IStore */
	private $store;

	/** @var bool */
	private $fetching = false;

	public function __construct(IStore $store) {
		$this->store = $store;
	}

	/**
	 * @return ICredentials
	 * @throws CredentialsUnavailableException
	 */
	public function getLoginCredentials(): ICredentials {
		if ($this->fetching) {
			// Throw the exception here if we are already fetching the credentials.
			throw new CredentialsUnavailableException('Credentials are already being fetched');
		}

		$this->fetching = true;
		$credentials = $this->store->getLoginCredentials();
		$this->fetching = false;

		return $credentials;
	}
}
