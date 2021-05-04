<?php

namespace Tests\Alexbusu\Monolog\Formatter;

use Alexbusu\Monolog\Formatter\LogstashStrictFormatter;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LogstashStrictFormatterTest extends TestCase
{
    protected $formatter;

    protected function setUp(): void
    {
        $this->formatter = new class('testcase') extends LogstashStrictFormatter {
            public $formatWasCalled = false;

            public function format(array $record): string
            {
                $this->formatWasCalled = true;
                return '';
            }

            public function doSanitizeData(array $record): array
            {
                return $this->sanitizeData($record);
            }
        };
    }

    public function testFormatCallsSanitize()
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('sanitizeData() was called');
        $formatter = new class('testcase') extends LogstashStrictFormatter {
            /** @var TestCase */
            public $testCase;

            protected function sanitizeData(array $record): array
            {
                $this->testCase::fail('sanitizeData() was called');
            }

            public function setTestInstance(TestCase $testCase)
            {
                $this->testCase = $testCase;
            }
        };

        $formatter->setTestInstance($this);

        $formatter->format($this->getSampleLogRecord());
    }

    public function testFormatterForDoctrineContextArgs(): void
    {
        $record = $this->getSampleLogRecord();
        $record['channel'] = 'doctrine';
        $record['context'] = [
            'first-placeholder-value',
            'second-placeholder-value',
        ];
        $resultRecord = $this->formatter->doSanitizeData($record);
        self::assertArrayHasKey('context', $resultRecord, 'expected the "context" key in the formatted record');
        self::assertArrayHasKey('binds', $resultRecord['context'], 'expected the "binds" key in the formatted record.context');
    }

    public function testFormatterForTokenContextWithMoreExtra(): void
    {
        $record = $this->getSampleLogRecord();
        $record['extra'] = [
            'token' => null,
            'something' => 'else',
        ];
        $resultRecord = $this->formatter->doSanitizeData($record);
        self::assertArrayHasKey('extra', $resultRecord, 'expected the "extra" key in the formatted record');
        self::assertArrayNotHasKey('token', $resultRecord['extra'], 'expected no "token" key in the formatted record.extra');
    }

    public function testFormatterForTokenContextWithNoOtherExtra(): void
    {
        $record = $this->getSampleLogRecord();
        $record['extra'] = [
            'token' => null,
        ];
        $resultRecord = $this->formatter->doSanitizeData($record);
        self::assertArrayHasKey('extra', $resultRecord, 'expected the "extra" key in the formatted record');
        self::assertArrayNotHasKey('token', $resultRecord['extra'], 'expected no "token" key in the formatted record.extra');
    }

    private function getSampleLogRecord(): array
    {
        return [
            'datetime' => gmdate('c'),
            'message' => 'message',
            'channel' => 'cli',
            'level' => LogLevel::DEBUG,
            'extra' => [],
            'context' => [],
        ];
    }
}
