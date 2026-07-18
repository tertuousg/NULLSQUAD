USE primedesk_ecommerce;

-- Resets both sample accounts to the password: password
UPDATE users
SET password_hash = '$2y$10$RqW/.NT15PUm.K.YekhUdOrOACWffZu8MhdQ/Rb/.bfrvkvT2NnJm',
    status = 'active',
    email_verified_at = CURRENT_TIMESTAMP
WHERE email IN ('admin@primedesk.test', 'customer@primedesk.test');

