<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Sentry\Listener;

use OCP\BackgroundJob\Events\BeforeJobExecutedEvent;
use OCP\BackgroundJob\Events\JobExecutedEvent;
use OCP\Command\Events\BeforeCommandExecutedEvent;
use OCP\Command\Events\CommandExecutedEvent;
use OCP\Command\Events\CommandFailedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use function array_key_exists;
use function get_class;
use function Sentry\startTransaction;

class CommandListener implements IEventListener {

	private array $transactions;

	public function handle(Event $event): void {
		if ($event instanceof BeforeCommandExecutedEvent) {
			$transactionContext = new TransactionContext();
			$transactionContext->setName('occ ' . $event->getCommand());
			$transactionContext->setOp('cli');
			$transaction = $this->transactions[$event->getCommand()] = startTransaction($transactionContext);
			SentrySdk::getCurrentHub()->setSpan($transaction);
		}
		if ($event instanceof CommandExecutedEvent) {
			$array_key_exists = array_key_exists($event->getCommand(), $this->transactions);
			if ($array_key_exists) {
				$transaction = $this->transactions[$event->getCommand()];
				$transaction->setStatus(SpanStatus::ok());
				$transaction->finish();
				unset($this->transactions[$event->getCommand()]);
			}
		}
		if ($event instanceof CommandFailedEvent) {
			$array_key_exists = array_key_exists($event->getCommand(), $this->transactions);
			if ($array_key_exists) {
				$transaction = $this->transactions[$event->getCommand()];
				$transaction->setStatus(SpanStatus::unknownError());
				$transaction->finish();
				unset($this->transactions[$event->getCommand()]);
			}
		}
	}

}
