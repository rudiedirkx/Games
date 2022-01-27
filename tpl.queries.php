<? if (is_local() || is_debug_ip()): ?>
	<hr />

	<details>
		<summary><?= count($GLOBALS['db']->queries) ?> queries</summary>
		<pre><? print_r($GLOBALS['db']->queries) ?></pre>
	</details>
<? endif ?>
