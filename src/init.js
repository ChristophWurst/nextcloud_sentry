/* global OC, oc_config */

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

import * as Sentry from '@sentry/browser'
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'

import Logger from './logger'

const attachErrorIntegration = {
	name: 'AttachErrorIntegration',
	processEvent(event, hint, client) {
		if (hint.originalException) {
			event.extra ??= {}
			event.extra['error'] = hint.originalException
		}
		return event
	},
}

try {
	const dsn = loadState('sentry', 'dsn')

	if (typeof dsn !== 'string') {
		Logger.warn('no sentry dsn set')
	} else {
		const config = {
			dsn,
			integrations: [
				attachErrorIntegration,
			],
		}

		if (typeof OC.config.version !== 'undefined') {
			config.release = oc_config.version
		}

		Sentry.init(config)

		const user = getCurrentUser();
		if (user !== null) {
			Sentry.setUser({
				id: user.uid
			})
		}

		Logger.debug('initialized')
	}
} catch (e) {
	Logger.error('could not load sentry config', e)
}
