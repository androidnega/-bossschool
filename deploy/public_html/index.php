<?php
/*
 * BossSchool — public_html/index.php fallback router.
 *
 * mod_rewrite handles 99% of requests via .htaccess. This file only matters
 * when mod_rewrite is disabled or the .htaccess is somehow ignored. It
 * forwards execution to Laravel's real front controller in public/.
 */

require __DIR__ . '/public/index.php';
