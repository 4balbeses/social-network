<?php

/**
 * Authentication Code Analysis Test
 * Validates the authentication implementation by analyzing the code structure,
 * configuration, and security measures.
 */

class AuthCodeAnalysis
{
    private string $projectRoot;
    private array $results = [];

    public function __construct(string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?: dirname(__DIR__);
    }

    private function addResult(string $test, bool $success, string $message, array $details = []): void
    {
        $this->results[] = [
            'test' => $test,
            'success' => $success,
            'message' => $message,
            'details' => $details
        ];
    }

    public function testUserEntity(): void
    {
        $file = $this->projectRoot . '/src/Entity/User.php';
        $success = false;
        $message = '';
        $details = [];

        if (!file_exists($file)) {
            $message = 'User entity file not found';
        } else {
            $content = file_get_contents($file);
            $checks = [
                'UserInterface' => str_contains($content, 'implements UserInterface'),
                'PasswordAuthenticatedUserInterface' => str_contains($content, 'PasswordAuthenticatedUserInterface'),
                'Email field' => str_contains($content, 'private ?string $email'),
                'Password field' => str_contains($content, 'private string $password'),
                'Roles field' => str_contains($content, 'private array $roles'),
                'UniqueEntity constraint' => str_contains($content, '#[UniqueEntity'),
                'Password hashing' => str_contains($content, 'getPassword()'),
                'User identifier' => str_contains($content, 'getUserIdentifier()'),
                'Role management' => str_contains($content, 'getRoles()'),
            ];

            $passed = array_filter($checks);
            $success = count($passed) === count($checks);
            $message = sprintf('User entity validation: %d/%d checks passed', count($passed), count($checks));
            $details = $checks;
        }

        $this->addResult('User Entity Structure', $success, $message, $details);
    }

    public function testAuthController(): void
    {
        $file = $this->projectRoot . '/src/Controller/Api/AuthController.php';
        $success = false;
        $message = '';
        $details = [];

        if (!file_exists($file)) {
            $message = 'AuthController file not found';
        } else {
            $content = file_get_contents($file);
            $checks = [
                'Register route' => str_contains($content, '#[Route(\'/register\'') || str_contains($content, '#[Route(\'/api/register\''),
                'Login route' => str_contains($content, '#[Route(\'/login\'') || str_contains($content, '#[Route(\'/api/login\''),
                'Password hashing' => str_contains($content, 'UserPasswordHasherInterface'),
                'Entity manager' => str_contains($content, 'EntityManagerInterface'),
                'Email validation' => str_contains($content, 'FILTER_VALIDATE_EMAIL'),
                'Password length check' => str_contains($content, 'strlen($plain)'),
                'Duplicate user check' => str_contains($content, 'findOneBy'),
                'Exception handling' => str_contains($content, 'try {') && str_contains($content, 'catch'),
                'JSON responses' => str_contains($content, 'JsonResponse'),
                'HTTP status codes' => str_contains($content, '201') && str_contains($content, '422'),
            ];

            $passed = array_filter($checks);
            $success = count($passed) === count($checks);
            $message = sprintf('AuthController validation: %d/%d checks passed', count($passed), count($checks));
            $details = $checks;
        }

        $this->addResult('Auth Controller Implementation', $success, $message, $details);
    }

    public function testTokenController(): void
    {
        $file = $this->projectRoot . '/src/Controller/Api/TokenController.php';
        $success = false;
        $message = '';
        $details = [];

        if (!file_exists($file)) {
            $message = 'TokenController file not found';
        } else {
            $content = file_get_contents($file);
            $checks = [
                'Refresh route' => str_contains($content, '/token/refresh'),
                'JWT token manager' => str_contains($content, 'JWTTokenManagerInterface'),
                'Refresh token repository' => str_contains($content, 'RefreshTokenRepository'),
                'Token validation' => str_contains($content, 'isExpired()'),
                'Token rotation' => str_contains($content, '$this->em->remove($rt)'),
                'New token generation' => str_contains($content, 'bin2hex(random_bytes'),
                'Expiration setting' => str_contains($content, 'setExpiresAt'),
                'JSON response' => str_contains($content, 'JsonResponse'),
                'Error handling' => str_contains($content, 'invalid or expired'),
            ];

            $passed = array_filter($checks);
            $success = count($passed) === count($checks);
            $message = sprintf('TokenController validation: %d/%d checks passed', count($passed), count($checks));
            $details = $checks;
        }

        $this->addResult('Token Controller Implementation', $success, $message, $details);
    }

    public function testSecurityConfiguration(): void
    {
        $file = $this->projectRoot . '/config/packages/security.yaml';
        $success = false;
        $message = '';
        $details = [];

        if (!file_exists($file)) {
            $message = 'Security configuration file not found';
        } else {
            $content = file_get_contents($file);
            $checks = [
                'Password hashers' => str_contains($content, 'password_hashers:'),
                'User provider' => str_contains($content, 'app_user_provider:'),
                'Entity provider' => str_contains($content, 'entity:'),
                'Login firewall' => str_contains($content, 'pattern: ^/api/login'),
                'JSON login' => str_contains($content, 'json_login:'),
                'JWT authentication' => str_contains($content, 'jwt: ~'),
                'Access control' => str_contains($content, 'access_control:'),
                'Public register' => str_contains($content, '/api/register') && str_contains($content, 'PUBLIC_ACCESS'),
                'Public login' => str_contains($content, '/api/login') && str_contains($content, 'PUBLIC_ACCESS'),
                'Public token refresh' => str_contains($content, '/api/token/refresh') && str_contains($content, 'PUBLIC_ACCESS'),
                'Protected API' => str_contains($content, '^/api') && str_contains($content, 'IS_AUTHENTICATED_FULLY'),
            ];

            $passed = array_filter($checks);
            $success = count($passed) === count($checks);
            $message = sprintf('Security configuration validation: %d/%d checks passed', count($passed), count($checks));
            $details = $checks;
        }

        $this->addResult('Security Configuration', $success, $message, $details);
    }

    public function testJWTConfiguration(): void
    {
        $envFile = $this->projectRoot . '/.env';
        $success = false;
        $message = '';
        $details = [];

        if (!file_exists($envFile)) {
            $message = '.env file not found';
        } else {
            $content = file_get_contents($envFile);
            $checks = [
                'JWT secret key' => str_contains($content, 'JWT_SECRET_KEY='),
                'JWT public key' => str_contains($content, 'JWT_PUBLIC_KEY='),
                'JWT passphrase' => str_contains($content, 'JWT_PASSPHRASE='),
                'Key paths configured' => str_contains($content, '/config/jwt/'),
            ];

            // Check if JWT keys actually exist
            $privateKeyExists = file_exists($this->projectRoot . '/config/jwt/private.pem');
            $publicKeyExists = file_exists($this->projectRoot . '/config/jwt/public.pem');
            
            $checks['Private key file'] = $privateKeyExists;
            $checks['Public key file'] = $publicKeyExists;

            $passed = array_filter($checks);
            $success = count($passed) === count($checks);
            $message = sprintf('JWT configuration validation: %d/%d checks passed', count($passed), count($checks));
            $details = $checks;
        }

        $this->addResult('JWT Configuration', $success, $message, $details);
    }

    public function testRefreshTokenEntity(): void
    {
        $file = $this->projectRoot . '/src/Entity/RefreshToken.php';
        $success = false;
        $message = '';
        $details = [];

        if (!file_exists($file)) {
            $message = 'RefreshToken entity file not found';
        } else {
            $content = file_get_contents($file);
            $checks = [
                'Token field' => str_contains($content, 'token'),
                'User relation' => str_contains($content, 'User') && str_contains($content, 'ManyToOne'),
                'Created at field' => str_contains($content, 'createdAt') || str_contains($content, 'created_at'),
                'Expires at field' => str_contains($content, 'expiresAt') || str_contains($content, 'expires_at'),
                'isExpired method' => str_contains($content, 'isExpired'),
                'Getters and setters' => str_contains($content, 'getToken') && str_contains($content, 'setToken'),
            ];

            $passed = array_filter($checks);
            $success = count($passed) === count($checks);
            $message = sprintf('RefreshToken entity validation: %d/%d checks passed', count($passed), count($checks));
            $details = $checks;
        }

        $this->addResult('Refresh Token Entity', $success, $message, $details);
    }

    public function testComposerDependencies(): void
    {
        $file = $this->projectRoot . '/composer.json';
        $success = false;
        $message = '';
        $details = [];

        if (!file_exists($file)) {
            $message = 'composer.json file not found';
        } else {
            $content = file_get_contents($file);
            $composerData = json_decode($content, true);
            
            if (!$composerData) {
                $message = 'Invalid composer.json format';
            } else {
                $require = $composerData['require'] ?? [];
                $checks = [
                    'Symfony Security Bundle' => isset($require['symfony/security-bundle']),
                    'Lexik JWT Bundle' => isset($require['lexik/jwt-authentication-bundle']),
                    'Doctrine ORM' => isset($require['doctrine/orm']),
                    'Password Hasher' => isset($require['symfony/password-hasher']) || isset($require['symfony/security-bundle']),
                    'API Platform' => isset($require['api-platform/symfony']),
                    'CORS Bundle' => isset($require['nelmio/cors-bundle']),
                    'JWT Refresh Token Bundle' => isset($require['gesdinet/jwt-refresh-token-bundle']),
                ];

                $passed = array_filter($checks);
                $success = count($passed) >= 5; // At least 5 key dependencies
                $message = sprintf('Composer dependencies validation: %d/%d checks passed', count($passed), count($checks));
                $details = $checks;
            }
        }

        $this->addResult('Dependencies Configuration', $success, $message, $details);
    }

    public function testEventListeners(): void
    {
        $file = $this->projectRoot . '/src/EventListener/AttachRefreshTokenOnLoginListener.php';
        $success = false;
        $message = '';
        $details = [];

        if (!file_exists($file)) {
            $message = 'Refresh token event listener not found';
        } else {
            $content = file_get_contents($file);
            $checks = [
                'Event listener class' => str_contains($content, 'AttachRefreshTokenOnLoginListener'),
                'Login success event' => str_contains($content, 'LoginSuccessEvent') || str_contains($content, 'onLoginSuccess'),
                'Refresh token creation' => str_contains($content, 'RefreshToken'),
                'Entity manager usage' => str_contains($content, 'EntityManagerInterface'),
            ];

            $passed = array_filter($checks);
            $success = count($passed) === count($checks);
            $message = sprintf('Event listeners validation: %d/%d checks passed', count($passed), count($checks));
            $details = $checks;
        }

        $this->addResult('Event Listeners', $success, $message, $details);
    }

    public function runAllTests(): array
    {
        echo "🔍 Analyzing authentication implementation...\n\n";

        $this->testUserEntity();
        $this->testAuthController();
        $this->testTokenController();
        $this->testSecurityConfiguration();
        $this->testJWTConfiguration();
        $this->testRefreshTokenEntity();
        $this->testComposerDependencies();
        $this->testEventListeners();

        return $this->results;
    }

    public function printResults(array $results = null): void
    {
        $results = $results ?: $this->results;
        
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "AUTHENTICATION CODE ANALYSIS RESULTS\n";
        echo str_repeat("=", 70) . "\n";

        $totalTests = count($results);
        $passedTests = count(array_filter($results, fn($r) => $r['success']));

        foreach ($results as $result) {
            $status = $result['success'] ? '✅ PASS' : '❌ FAIL';
            echo sprintf("%-45s %s\n", $result['test'], $status);
            echo "   " . $result['message'] . "\n";

            if (!$result['success'] && !empty($result['details'])) {
                foreach ($result['details'] as $check => $passed) {
                    $checkStatus = $passed ? '✓' : '✗';
                    echo "     $checkStatus $check\n";
                }
            }
            echo "\n";
        }

        echo str_repeat("-", 70) . "\n";
        echo sprintf("Tests: %d/%d passed (%.1f%%)\n", 
                    $passedTests, $totalTests, 
                    $totalTests > 0 ? ($passedTests / $totalTests) * 100 : 0);
        
        if ($passedTests === $totalTests) {
            echo "🎉 All authentication components are properly implemented!\n";
        } else {
            echo "⚠️  Some authentication components need attention.\n";
        }
        
        echo str_repeat("=", 70) . "\n";
    }
}

// Run analysis if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $analyzer = new AuthCodeAnalysis();
    $results = $analyzer->runAllTests();
    $analyzer->printResults($results);
}