<?php declare(strict_types=1);

namespace Alexbusu\Monolog\Formatter;

use Monolog\Formatter\LogstashFormatter;

class LogstashStrictFormatter extends LogstashFormatter
{
    public function format(array $record): string
    {
        return parent::format($this->sanitizeData($record));
    }

    protected function sanitizeData(array $record): array
    {
        if (
            array_key_exists('channel', $record)
            && 'doctrine' === $record['channel']
        ) {
            // The `context` key would contain a map of ["string" => "mixed"] pairs, resulting in a JSON object.
            // Doctrine component puts the bind list values in `context`, thus resulting in a JSON array;
            //  This results in map setting conflicts in the Elasticsearch's index.
            if (array_key_exists('context', $record)) {
                $record['context'] = ['binds' => var_export($record['context'], true)];
            }
        }
        if (
            array_key_exists('extra', $record)
            && is_array($record['extra'])
            && array_key_exists('token', $record['extra'])
            && is_null($record['extra']['token'])
        ) {
            // This token contain a map of ["string" => "string"] values when the token is present; and is null when
            //  the token is not set (yet). To avoid conflicts with ES index mapping settings, unset the token when
            //  it's null.
            unset($record['extra']['token']);
        }
        return $record;
    }
}
