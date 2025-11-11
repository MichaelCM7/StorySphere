<?php

?>

<header class="header">
    <?php
    // Ensure $user is available and sanitize output
    $displayName = 'Reader';
    // if (isset($user) && is_array($user)) {
    //     $displayName = $user['name'] ?? $displayName;
    // }
    ?>
    <h1>Welcome back, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>Here is your library activity overview.</p>
</header>
