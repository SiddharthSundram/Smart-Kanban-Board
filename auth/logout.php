<?php
require_once '../config/config.php';
require_once '../utils/helpers.php'; // ✅ Add this to define redirect()

session_start();
session_unset();
session_destroy();

redirect('../auth/login.php');
