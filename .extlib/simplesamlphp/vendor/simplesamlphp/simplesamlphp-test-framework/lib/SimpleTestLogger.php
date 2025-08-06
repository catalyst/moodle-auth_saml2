<?php

declare(strict_types=1);

namespace SimpleSAML\TestUtils;

use Psr\Log\AbstractLogger;
use Stringable;

use function array_filter;
use function count;

/**
 * A very simple in-memory logger that allows querying the log for existence of messages
 *
 * @package simplesamlphp\simplesamlphp-test-framework
 */
final class SimpleTestLogger extends AbstractLogger
{
    /**
     * @var array
     */
    private array $messages = [];


    /**
     * @param mixed $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->messages[] = [
            'level'   => $level,
            'message' => $message,
            'context' => $context,
        ];
    }


    /**
     * Get all the messages logged at the specified level
     *
     * @param mixed $level
     *
     * @return array
     */
    public function getMessagesForLevel($level): array
    {
        return array_filter($this->messages, function (string $message) use ($level) {
            return $message['level'] === $level;
        });
    }


    /**
     * Check if the given message exists within the log
     *
     * @param string|\Stringable $messageToFind
     *
     * @return bool
     */
    public function hasMessage(string|Stringable $messageToFind): bool
    {
        $count = array_filter($this->messages, function ($message) use ($messageToFind) {
            return $message['message'] === $messageToFind;
        });

        return !!count($count);
    }
}
