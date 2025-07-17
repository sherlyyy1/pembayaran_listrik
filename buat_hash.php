<?php
$plain = "admin123";
$hash = password_hash($plain, PASSWORD_DEFAULT);
echo "Password Hash: <br>$hash";
