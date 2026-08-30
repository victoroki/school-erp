<?php

/**
 * Generate a secure deploy-hook token (64 hex chars).
 *
 * Run:  php generate-deploy-secret.php
 *
 * Copy the printed value to BOTH places:
 *   1) GitHub → Settings → Secrets and variables → Actions
 *      → update SCHOOL1_DEPLOY_HOOK_SECRET
 *   2) cPanel File Manager → <SERVER_DIR>/.env
 *      → DEPLOY_HOOK_SECRET='<value>'
 */

$value = bin2hex(random_bytes(32));

echo "Copy this value to BOTH places:\n\n";
echo "    {$value}\n\n";
echo "GitHub secret name : SCHOOL1_DEPLOY_HOOK_SECRET\n";
echo "Server .env line   : DEPLOY_HOOK_SECRET='{$value}'\n";
