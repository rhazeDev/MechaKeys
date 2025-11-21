<?php
function hash_password(string $plaintext): string {
    if ($plaintext === null || $plaintext === '') return $plaintext;
    return password_hash($plaintext, PASSWORD_DEFAULT);
}

function verify_password(string $plaintext, string $hash): bool {
    if ($plaintext === null || $plaintext === '') return false;
    if ($hash === null || $hash === '') return false;
    return password_verify($plaintext, $hash);
}

function is_password_hash(string $value): bool{
    if ($value === null || $value === '') return false;
    $info = password_get_info($value);
    return (!empty($info['algo']) && $info['algo'] !== 'unknown');
}

function needs_rehash_password(string $hash): bool{
    if ($hash === null || $hash === '') return false;
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}