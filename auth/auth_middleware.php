<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    redirect('../auth/login.php');
}
