<?php
require "../lib/auth.php";
session_start();

$id = get_user_id();

if (!$id) header("Location: /site/login/");

$role = get_user_role($id);

if (!$role) header("Location: /site/login/");

header("Location: /site/".strtolower($role->value));
