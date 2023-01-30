<?php

declare(strict_types=1);

/*
 * @copyright 2023 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2023 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\Sentry\Http;

use Exception;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use function get_class;
use function Sentry\startTransaction;

class PerformanceMonitoringMiddleware extends Middleware {

	private ?Transaction $transaction = null;

	public function beforeController($controller, $methodName) {
		$transactionContext = new TransactionContext();
		$transactionContext->setName(get_class($controller) . '::' . $methodName);
		$transactionContext->setOp('http.request');
		$this->transaction = startTransaction($transactionContext);
		SentrySdk::getCurrentHub()->setSpan($this->transaction);
	}

	public function afterException($controller, $methodName, Exception $exception): Response {
		if ($this->transaction !== null) {
			$this->transaction->setStatus(SpanStatus::unknownError());
			$this->transaction->finish();
		}
		throw $exception;
	}

	public function afterController($controller, $methodName, Response $response) {
		if ($this->transaction !== null) {
			$this->transaction->setStatus(SpanStatus::ok());
			$this->transaction->finish();
		}
		return $response;
	}

}
