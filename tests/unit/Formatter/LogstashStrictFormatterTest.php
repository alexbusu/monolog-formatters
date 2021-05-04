<?php

namespace Tests\Alexbusu\Monolog\Formatter;

use Alexbusu\Monolog\Formatter\LogstashStrictFormatter;
use PHPUnit\Framework\TestCase;

class LogstashStrictFormatterTest extends TestCase
{
    protected $formatter;

    protected function setUp(): void
    {
        $this->formatter = new LogstashStrictFormatter('testcase');
    }

    public function testFormatterForDoctrineContextArgs(): void
    {
        $record = $this->getLogRecordForDoctrine();
        $resultRecord = json_decode($this->formatter->format($record), true);
        self::assertArrayHasKey('context', $resultRecord, 'expected the "context" key in the formatted record');
        self::assertArrayHasKey('binds', $resultRecord['context'], 'expected the "binds" key in the formatted record.context');
    }

    public function testFormatterForTokenContextWithMoreExtra(): void
    {
        $record = $this->getLogRecordForAuthToken();
        $resultRecord = json_decode($this->formatter->format($record), true);
        self::assertArrayHasKey('extra', $resultRecord, 'expected the "extra" key in the formatted record');
        self::assertArrayNotHasKey('token', $resultRecord['extra'], 'expected no "token" key in the formatted record.extra');
    }

    public function testFormatterForTokenContextWithNoOtherExtra(): void
    {
        $record = $this->getLogRecordForAuthToken();
        $record['extra'] = [
            'token' => null,
        ];
        $resultRecord = json_decode($this->formatter->format($record), true);
        self::assertArrayNotHasKey('extra', $resultRecord, 'expected no "extra" key in the formatted record');
    }

    private function getLogRecordForDoctrine(): array
    {
        return [
            'channel' => 'doctrine',
            'context' => [
                'first-placeholder-value',
                'second-placeholder-value',
            ],
        ];
    }

    private function getLogRecordForAuthToken(): array
    {
        return [
            'channel' => 'auth',
            'extra' => [
                'token' => null,
                'something' => 'else',
            ],
        ];
    }
}
