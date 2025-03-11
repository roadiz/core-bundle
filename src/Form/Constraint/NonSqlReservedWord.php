<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class NonSqlReservedWord extends Constraint
{
    /**
     * List of forbidden field names. These are SQL reserved words.
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/keywords.html
     *
     * @var array<string>
     */
    public static array $forbiddenNames = [
        'accessible',
        'account',
        'action',
        'active',
        'add',
        'admin',
        'after',
        'against',
        'aggregate',
        'algorithm',
        'all',
        'alter',
        'always',
        'analyze',
        'and',
        'any',
        'array',
        'as',
        'asc',
        'ascii',
        'asensitive',
        'at',
        'attribute',
        'authentication',
        'auto_increment',
        'autoextend_size',
        'avg',
        'avg_row_length',
        'backup',
        'before',
        'begin',
        'between',
        'bigint',
        'binary',
        'binlog',
        'bit',
        'blob',
        'block',
        'bool',
        'boolean',
        'both',
        'btree',
        'buckets',
        'bulk',
        'bulk',
        'by',
        'byte',
        'cache',
        'call',
        'cascade',
        'cascaded',
        'case',
        'catalog_name',
        'chain',
        'challenge_response',
        'challenge_response',
        'change',
        'changed',
        'channel',
        'char',
        'character',
        'charset',
        'check',
        'checksum',
        'cipher',
        'class_origin',
        'client',
        'clone',
        'clone',
        'close',
        'coalesce',
        'code',
        'collate',
        'collation',
        'column',
        'column_format',
        'column_name',
        'columns',
        'comment',
        'commit',
        'committed',
        'compact',
        'completion',
        'component',
        'component',
        'compressed',
        'compression',
        'concurrent',
        'condition',
        'connection',
        'consistent',
        'constraint',
        'constraint_catalog',
        'constraint_name',
        'constraint_schema',
        'contains',
        'context',
        'continue',
        'convert',
        'cpu',
        'create',
        'cross',
        'cube',
        'cume_dist',
        'current',
        'current_date',
        'current_time',
        'current_timestamp',
        'current_user',
        'cursor',
        'cursor_name',
        'data',
        'database',
        'databases',
        'datafile',
        'date',
        'datetime',
        'day',
        'day_hour',
        'day_microsecond',
        'day_minute',
        'day_second',
        'deallocate',
        'dec',
        'decimal',
        'declare',
        'default',
        'default_auth',
        'definer',
        'definition',
        'delay_key_write',
        'delayed',
        'delete',
        'dense_rank',
        'dense_rank',
        'des_key_file',
        'desc',
        'describe',
        'description',
        'deterministic',
        'diagnostics',
        'directory',
        'disable',
        'discard',
        'disk',
        'distinct',
        'distinctrow',
        'div',
        'do',
        'double',
        'drop',
        'dual',
        'dumpfile',
        'duplicate',
        'dynamic',
        'each',
        'else',
        'elseif',
        'empty',
        'enable',
        'enclosed',
        'encryption',
        'end',
        'ends',
        'enforced',
        'engine',
        'engine_attribute',
        'engines',
        'enum',
        'error',
        'errors',
        'escape',
        'escaped',
        'event',
        'events',
        'every',
        'except',
        'except',
        'exchange',
        'exclude',
        'execute',
        'exists',
        'exit',
        'expansion',
        'expire',
        'explain',
        'export',
        'extended',
        'extent_size',
        'factor',
        'factor',
        'failed_login_attempts',
        'false',
        'fast',
        'faults',
        'fetch',
        'fields',
        'file',
        'file_block_size',
        'filter',
        'finish',
        'first',
        'first_value',
        'fixed',
        'float',
        'float4',
        'float8',
        'flush',
        'following',
        'follows',
        'for',
        'force',
        'foreign',
        'format',
        'found',
        'from',
        'full',
        'fulltext',
        'function',
        'general',
        'generate',
        'generated',
        'geomcollection',
        'geometry',
        'geometrycollection',
        'get',
        'get_format',
        'get_master_public_key',
        'get_source_public_key',
        'global',
        'grant',
        'grants',
        'group',
        'group_replication',
        'grouping',
        'groups',
        'gtid_only',
        'handler',
        'hash',
        'having',
        'help',
        'high_priority',
        'histogram',
        'history',
        'host',
        'hosts',
        'hour',
        'hour_microsecond',
        'hour_minute',
        'hour_second',
        'identified',
        'if',
        'ignore',
        'ignore_server_ids',
        'import',
        'in',
        'inactive',
        'index',
        'indexes',
        'infile',
        'initial',
        'initial_size',
        'initiate',
        'inner',
        'inout',
        'insensitive',
        'insert',
        'insert_method',
        'install',
        'instance',
        'int',
        'int1',
        'int2',
        'int3',
        'int4',
        'int8',
        'integer',
        'intersect',
        'interval',
        'into',
        'invisible',
        'invoker',
        'io',
        'io_after_gtids',
        'io_before_gtids',
        'io_thread',
        'ipc',
        'is',
        'isolation',
        'issuer',
        'iterate',
        'join',
        'json',
        'json_table',
        'json_value',
        'key',
        'key_block_size',
        'keyring',
        'keys',
        'kill',
        'lag',
        'lag',
        'language',
        'last',
        'last_value',
        'lateral',
        'lateral',
        'lead',
        'leading',
        'leave',
        'leaves',
        'left',
        'less',
        'level',
        'like',
        'limit',
        'linear',
        'lines',
        'linestring',
        'list',
        'load',
        'local',
        'localtime',
        'localtimestamp',
        'lock',
        'locked',
        'locked',
        'locks',
        'logfile',
        'logs',
        'long',
        'longblob',
        'longtext',
        'loop',
        'low_priority',
        'master',
        'master_auto_position',
        'master_bind',
        'master_compression_algorithms',
        'master_compression_algorithms',
        'master_connect_retry',
        'master_delay',
        'master_heartbeat_period',
        'master_host',
        'master_log_file',
        'master_log_pos',
        'master_password',
        'master_port',
        'master_public_key_path',
        'master_public_key_path',
        'master_retry_count',
        'master_server_id',
        'master_ssl',
        'master_ssl_ca',
        'master_ssl_capath',
        'master_ssl_cert',
        'master_ssl_cipher',
        'master_ssl_crl',
        'master_ssl_crlpath',
        'master_ssl_key',
        'master_ssl_verify_server_cert',
        'master_tls_ciphersuites',
        'master_tls_ciphersuites',
        'master_tls_version',
        'master_user',
        'master_zstd_compression_level',
        'master_zstd_compression_level',
        'match',
        'max_connections_per_hour',
        'max_queries_per_hour',
        'max_rows',
        'max_size',
        'max_updates_per_hour',
        'max_user_connections',
        'maxvalue',
        'medium',
        'mediumblob',
        'mediumint',
        'mediumtext',
        'member',
        'memory',
        'merge',
        'message_text',
        'microsecond',
        'middleint',
        'migrate',
        'min_rows',
        'minute',
        'minute_microsecond',
        'minute_second',
        'mod',
        'mode',
        'modifies',
        'modify',
        'month',
        'multilinestring',
        'multipoint',
        'multipolygon',
        'mutex',
        'mysql_errno',
        'name',
        'names',
        'national',
        'natural',
        'nchar',
        'ndb',
        'ndbcluster',
        'nested',
        'nested',
        'network_namespace',
        'network_namespace',
        'never',
        'new',
        'next',
        'no',
        'no_wait',
        'no_write_to_binlog',
        'nodegroup',
        'none',
        'not',
        'nowait',
        'nowait',
        'nth_value',
        'nth_value',
        'ntile',
        'null',
        'nulls',
        'number',
        'numeric',
        'nvarchar',
        'of',
        'off',
        'offset',
        'oj',
        'old',
        'on',
        'one',
        'only',
        'open',
        'optimize',
        'optimizer_costs',
        'option',
        'optional',
        'optionally',
        'options',
        'or',
        'order',
        'ordinality',
        'organization',
        'others',
        'out',
        'outer',
        'outfile',
        'over',
        'owner',
        'pack_keys',
        'page',
        'parser',
        'partial',
        'partition',
        'partitioning',
        'partitions',
        'password',
        'password_lock_time',
        'path',
        'percent_rank',
        'persist',
        'persist_only',
        'phase',
        'plugin',
        'plugin_dir',
        'plugins',
        'point',
        'polygon',
        'port',
        'precedes',
        'preceding',
        'precision',
        'prepare',
        'preserve',
        'prev',
        'primary',
        'privilege_checks_user',
        'privileges',
        'procedure',
        'process',
        'processlist',
        'profile',
        'profiles',
        'proxy',
        'purge',
        'quarter',
        'query',
        'quick',
        'random',
        'range',
        'rank',
        'read',
        'read_only',
        'read_write',
        'reads',
        'real',
        'rebuild',
        'recover',
        'recursive',
        'redo_buffer_size',
        'redofile',
        'redundant',
        'reference',
        'references',
        'regexp',
        'registration',
        'relay',
        'relay_log_file',
        'relay_log_pos',
        'relay_thread',
        'relaylog',
        'release',
        'reload',
        'remote',
        'remove',
        'rename',
        'reorganize',
        'repair',
        'repeat',
        'repeatable',
        'replace',
        'replica',
        'replicas',
        'replicate_do_db',
        'replicate_do_table',
        'replicate_ignore_db',
        'replicate_ignore_table',
        'replicate_rewrite_db',
        'replicate_wild_do_table',
        'replicate_wild_ignore_table',
        'replication',
        'require',
        'require_row_format',
        'require_row_format',
        'reset',
        'resignal',
        'resource',
        'respect',
        'restart',
        'restore',
        'restrict',
        'resume',
        'retain',
        'return',
        'returned_sqlstate',
        'returning',
        'returns',
        'reuse',
        'reuse',
        'reverse',
        'revoke',
        'right',
        'rlike',
        'role',
        'rollback',
        'rollup',
        'rotate',
        'routine',
        'row',
        'row_count',
        'row_format',
        'row_number',
        'row_number',
        'rows',
        'rtree',
        'savepoint',
        'schedule',
        'schema',
        'schema_name',
        'schemas',
        'second',
        'second_microsecond',
        'secondary',
        'secondary_engine',
        'secondary_engine_attribute',
        'secondary_load',
        'secondary_unload',
        'security',
        'select',
        'sensitive',
        'separator',
        'serial',
        'serializable',
        'server',
        'session',
        'set',
        'share',
        'show',
        'shutdown',
        'signal',
        'signed',
        'simple',
        'skip',
        'skip',
        'slave',
        'slow',
        'smallint',
        'snapshot',
        'socket',
        'some',
        'soname',
        'sounds',
        'source',
        'source_auto_position',
        'source_bind',
        'source_compression_algorithms',
        'source_connect_retry',
        'source_delay',
        'source_heartbeat_period',
        'source_host',
        'source_log_file',
        'source_log_pos',
        'source_password',
        'source_port',
        'source_public_key_path',
        'source_retry_count',
        'source_ssl',
        'source_ssl_ca',
        'source_ssl_capath',
        'source_ssl_cert',
        'source_ssl_cipher',
        'source_ssl_crl',
        'source_ssl_crlpath',
        'source_ssl_key',
        'source_ssl_verify_server_cert',
        'source_tls_ciphersuites',
        'source_tls_version',
        'source_user',
        'source_zstd_compression_level',
        'spatial',
        'specific',
        'sql',
        'sql_after_gtids',
        'sql_after_mts_gaps',
        'sql_before_gtids',
        'sql_big_result',
        'sql_buffer_result',
        'sql_cache',
        'sql_calc_found_rows',
        'sql_no_cache',
        'sql_small_result',
        'sql_thread',
        'sql_tsi_day',
        'sql_tsi_hour',
        'sql_tsi_minute',
        'sql_tsi_month',
        'sql_tsi_quarter',
        'sql_tsi_second',
        'sql_tsi_week',
        'sql_tsi_year',
        'sqlexception',
        'sqlstate',
        'sqlwarning',
        'srid',
        'ssl',
        'stacked',
        'start',
        'starting',
        'starts',
        'stats_auto_recalc',
        'stats_persistent',
        'stats_sample_pages',
        'status',
        'stop',
        'storage',
        'stored',
        'straight_join',
        'stream',
        'string',
        'subclass_origin',
        'subject',
        'subpartition',
        'subpartitions',
        'super',
        'suspend',
        'swaps',
        'switches',
        'system',
        'table',
        'table_checksum',
        'table_name',
        'tables',
        'tablespace',
        'temporary',
        'temptable',
        'terminated',
        'text',
        'then',
        'thread_priority',
        'ties',
        'time',
        'timestamp',
        'timestampadd',
        'timestampdiff',
        'tinyblob',
        'tinyint',
        'tinytext',
        'tls',
        'to',
        'trailing',
        'transaction',
        'trigger',
        'triggers',
        'true',
        'truncate',
        'type',
        'types',
        'unbounded',
        'uncommitted',
        'undefined',
        'undo',
        'undo_buffer_size',
        'undofile',
        'unicode',
        'uninstall',
        'union',
        'unique',
        'unknown',
        'unlock',
        'unregister',
        'unsigned',
        'until',
        'update',
        'upgrade',
        'url',
        'url',
        'usage',
        'use',
        'use_frm',
        'user',
        'user_resources',
        'using',
        'utc_date',
        'utc_time',
        'utc_timestamp',
        'validation',
        'value',
        'values',
        'varbinary',
        'varchar',
        'varcharacter',
        'variables',
        'varying',
        'vcpu',
        'view',
        'virtual',
        'visible',
        'wait',
        'warnings',
        'week',
        'weight_string',
        'when',
        'where',
        'while',
        'window',
        'with',
        'without',
        'work',
        'wrapper',
        'write',
        'x509',
        'xa',
        'xid',
        'xml',
        'xor',
        'year',
        'year_month',
        'zerofill',
        'zone',
    ];

    public string $message = 'string.should.not.be.a.sql.reserved.word';
}
