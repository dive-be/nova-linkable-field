module.exports = {
    'resources/**/*.{js,scss,vue}': ['prettier --write'],
    '**/*.php': [
        'php ./vendor/bin/php-cs-fixer fix --config .php-cs-fixer.dist.php --allow-risky=yes',
    ],
};