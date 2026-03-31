<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Sentry\Listener;

use OCP\BackgroundJob\Events\BeforeJobExecutedEvent;
use OCP\BackgroundJob\Events\JobExecutedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use function array_key_exists;
use function get_class;
use function Sentry\startTransaction;

class BackgroundJobListener implements IEventListener {

	private array $transactions;

	public function handle(Event $event): void {
		if ($event instanceof BeforeJobExecutedEvent) {
			$job = $event->getJob();
			$transactionContext = new TransactionContext();
			$transactionContext->setName('Background job ' . get_class($job));
			$transactionContext->setOp('cron');
			$transactionContext->setData([
				'id' => $job->getId(),
			]);
			$transaction = $this->transactions[$job->getId()] = startTransaction($transactionContext);
			SentrySdk::getCurrentHub()->setSpan($transaction);
		}
		if ($event instanceof JobExecutedEvent) {
			$job = $event->getJob();
			if (array_key_exists($job->getId(), $this->transactions)) {
				$transaction = $this->transactions[$job->getId()];
				$transaction->setStatus(SpanStatus::ok());
				$transaction->finish();
				unset($this->transactions[$job->getId()]);
			}
		}
	}

}
