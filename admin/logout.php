<?php
require_once __DIR__ . '/../includes/init.php';

logout_user();
set_flash('success', 'Administrator logged out.');
redirect('admin/login.php');

