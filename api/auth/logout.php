<?php
// backend/auth/logout.php

require_once __DIR__ . '/../config.php';

session_unset();
session_destroy();

header("Location: ../../frontend/admin/login.html");
exit;
