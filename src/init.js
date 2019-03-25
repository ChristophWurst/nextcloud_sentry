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

import Raven from 'raven-js'

try {
	const initialState = OCP.InitialState.loadState('sentry', 'dsn');

	if (typeof initialState.dsn !== 'string') {
		console.warn('no sentry dsn set')
	} else {
		Raven.config(initialState.dsn).install()
		Raven.setUserContext({
			id: OC.currentUser
		})
		if (typeof oc_config.version !== 'undefined') {
			Raven.setRelease(oc_config.version)
		}
	}
} catch (e) {
	console.error('could not load sentry config', e)
}
