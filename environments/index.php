<?php
/**
 * The manifest of files that are local to specific environment.
 * This file returns a list of environments that the application
 * may be installed under. The returned data must be in the following
 * format:
 *
 * ```php
 * return [
 *     'environment name' => [
 *         'path' => 'directory storing the local files',
 *         'setWritable' => [
 *             // list of directories that should be set writable
 *         ],
 *         'setExecutable' => [
 *             // list of files that should be set executable
 *         ],
 *         'setCookieValidationKey' => [
 *             // list of config files that need to be inserted with automatically generated cookie validation keys
 *         ],
 *         'createSymlink' => [
 *             // list of symlinks to be created. Keys are symlinks, and values are the targets.
 *         ],
 *     ],
 * ];
 * ```
 */
return [
	'Production' => [
		'path' => 'prod',
		'setWritable' => [
			/* Common */
			'common/runtime',

			/* Backend application */
			'backend/runtime',
			'backend/web/assets',

			/* Frontend application */
			'frontend/runtime',
			'frontend/web/assets',

			/* API application */
			'api/runtime',
			'api/web/assets',

			/* Static application */
			'static',
		],
		'setExecutable' => [
			'yii',
		],
		'setCookieValidationKey' => [],
	],
	'Development' => [
		'path' => 'dev',
		'setWritable' => [
			/* Common */
			'common/runtime',

			/* Backend application */
			'backend/runtime',
			'backend/web/assets',

			/* Frontend application */
			'frontend/runtime',
			'frontend/web/assets',

			/* API application */
			'api/runtime',
			'api/web/assets',
			'api/web/doc',

			/* Static application */
			'static',
		],
		'setExecutable' => [
			'yii',
		],
		'setCookieValidationKey' => [],
	],
	'DevelopmentJC' => [
		'path' => 'dev_jc',
		'setWritable' => [
			/* Common */
			'common/runtime',

			/* Backend application */
			'backend/runtime',
			'backend/web/assets',

			/* Frontend application */
			'frontend/runtime',
			'frontend/web/assets',

			/* API application */
			'api/runtime',
			'api/web/assets',
			'api/web/doc',

			/* Static application */
			'static',
		],
		'setExecutable' => [
			'yii',
		],
		'setCookieValidationKey' => [],
	],
	'AkokorevJC' => [
		'path' => 'akokorev_jc',
		'setWritable' => [
			/* Common */
			'common/runtime',

			/* Backend application */
			'backend/runtime',
			'backend/web/assets',

			/* Frontend application */
			'frontend/runtime',
			'frontend/web/assets',

			/* API application */
			'api/runtime',
			'api/web/assets',
			'api/web/doc',

			/* Static application */
			'static',
		],
		'setExecutable' => [
			'yii',
		],
		'setCookieValidationKey' => [],
	],
];
