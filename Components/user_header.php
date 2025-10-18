<?php

?>

<header class="header">
    <h1>Welcome back, <?= htmlspecialchars($user['name'] ?? 'Reader'); ?>!</h1>
    <p>Here is your library activity overview.</p>
</header>
