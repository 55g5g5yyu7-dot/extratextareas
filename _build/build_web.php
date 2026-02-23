<?php

declare(strict_types=1);

/**
 * Backward-compatibility wrapper.
 * Some local scripts include `_build/build_web.php` (underscore style),
 * while the main implementation lives in `_build/build.web.php`.
 */

require __DIR__ . '/build.web.php';
