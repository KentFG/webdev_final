<?php
declare(strict_types=1);

function clean_text(string $value): string
{
    return trim(preg_replace('/\s+/', ' ', strip_tags($value)) ?? '');
}

function validate_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_login(string $email, string $password): array
{
    $errors = [];
    if (!validate_email($email)) $errors[] = 'Please enter a valid email address.';
    if ($password === '') $errors[] = 'Please enter your password.';
    return $errors;
}

function validate_password_strength(string $password): array
{
    $errors = [];
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long.';
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) $errors[] = 'Password must contain at least 1 special character.';
    return $errors;
}

function validate_signup(string $name, string $email, string $password, string $confirm): array
{
    $errors = [];
    if ($name === '' || strlen($name) < 2 || strlen($name) > 100) $errors[] = 'Please enter a valid name.';
    if (!validate_email($email)) $errors[] = 'Please enter a valid email address.';
    $errors = array_merge($errors, validate_password_strength($password));
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    return $errors;
}

function validate_product(string $name, string $description, string $price, string $stock, string $image, ?string $categoryId = null): array
{
    $errors = [];
    if ($name === '' || strlen($name) > 150) $errors[] = 'Product name is required and must be 150 characters or fewer.';
    if (strlen($description) > 2000) $errors[] = 'Description is too long.';
    if (!is_numeric($price) || (float)$price < 0) $errors[] = 'Please enter a valid non-negative price.';
    if (filter_var($stock, FILTER_VALIDATE_INT) === false || (int)$stock < 0) $errors[] = 'Please enter a valid non-negative stock quantity.';
    if ($image !== '' && strlen($image) > 255) $errors[] = 'Image path is too long (max 255 characters).';
    if ($categoryId !== null && (int)$categoryId < 1) $errors[] = 'Please select a category.';
    return $errors;
}

function validate_profile(array $data): array
{
    $errors = [];
    $required = ['name','phone','address_line1','city','province','postal_code','country'];
    foreach ($required as $field) {
        if (clean_text((string)($data[$field] ?? '')) === '') $errors[] = ucfirst(str_replace('_',' ',$field)) . ' is required.';
    }
    if (strlen(clean_text((string)($data['name'] ?? ''))) > 100) $errors[] = 'Name is too long.';
    if (strlen(clean_text((string)($data['phone'] ?? ''))) > 30) $errors[] = 'Phone number is too long.';
    if (strlen(clean_text((string)($data['address_line1'] ?? ''))) > 190) $errors[] = 'Address is too long.';
    return $errors;
}

function validate_checkout(array $data): array
{
    $errors = validate_profile($data);
    if (!in_array($data['payment_method'] ?? '', ['cod','gcash','bank_transfer'], true)) {
        $errors[] = 'Please select a valid payment method (Cash on Delivery or GCash).';
    }
    if (strlen(clean_text((string)($data['notes'] ?? ''))) > 500) $errors[] = 'Order notes are too long.';
    return $errors;
}
