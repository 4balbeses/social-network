<?php

/**
 * Simple authentication test script for social-network backend
 * Tests registration, login, token refresh, and protected endpoints
 */

class AuthTest
{
    private string $baseUrl;
    private array $headers;

    public function __construct(string $baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = $baseUrl;
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

    private function makeRequest(string $method, string $endpoint, array $data = null): array
    {
        $ch = curl_init();
        $url = $this->baseUrl . $endpoint;
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: $error");
        }

        return [
            'status' => $httpCode,
            'body' => json_decode($response, true) ?: $response
        ];
    }

    public function testRegistration(): array
    {
        echo "Testing user registration...\n";
        
        $userData = [
            'email' => 'test' . time() . '@example.com',
            'password' => 'testpassword123'
        ];

        $response = $this->makeRequest('POST', '/api/register', $userData);
        
        $success = $response['status'] === 201 && 
                   is_array($response['body']) && 
                   $response['body']['message'] === 'Registered';

        return [
            'test' => 'Registration',
            'success' => $success,
            'status' => $response['status'],
            'response' => $response['body'],
            'userData' => $userData
        ];
    }

    public function testRegistrationValidation(): array
    {
        echo "Testing registration validation...\n";
        
        $tests = [
            [
                'name' => 'Invalid email',
                'data' => ['email' => 'invalid-email', 'password' => 'testpassword123'],
                'expectedStatus' => 422
            ],
            [
                'name' => 'Short password',
                'data' => ['email' => 'test@example.com', 'password' => '123'],
                'expectedStatus' => 422
            ],
            [
                'name' => 'Missing fields',
                'data' => ['email' => 'test@example.com'],
                'expectedStatus' => 422
            ]
        ];

        $results = [];
        foreach ($tests as $test) {
            $response = $this->makeRequest('POST', '/api/register', $test['data']);
            $success = $response['status'] === $test['expectedStatus'];
            
            $results[] = [
                'test' => "Registration Validation - {$test['name']}",
                'success' => $success,
                'status' => $response['status'],
                'expected' => $test['expectedStatus'],
                'response' => $response['body']
            ];
        }

        return $results;
    }

    public function testLogin(array $userData): array
    {
        echo "Testing user login...\n";
        
        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];

        $response = $this->makeRequest('POST', '/api/login', $loginData);
        
        $success = $response['status'] === 200 && 
                   is_array($response['body']) &&
                   isset($response['body']['token']) &&
                   isset($response['body']['refresh_token']);

        return [
            'test' => 'Login',
            'success' => $success,
            'status' => $response['status'],
            'response' => $response['body'],
            'tokens' => $success ? [
                'access_token' => $response['body']['token'],
                'refresh_token' => $response['body']['refresh_token']
            ] : null
        ];
    }

    public function testLoginValidation(): array
    {
        echo "Testing login validation...\n";
        
        $tests = [
            [
                'name' => 'Invalid credentials',
                'data' => ['email' => 'nonexistent@example.com', 'password' => 'wrongpassword'],
                'expectedStatus' => 401
            ],
            [
                'name' => 'Missing password',
                'data' => ['email' => 'test@example.com'],
                'expectedStatus' => 400
            ]
        ];

        $results = [];
        foreach ($tests as $test) {
            $response = $this->makeRequest('POST', '/api/login', $test['data']);
            $success = $response['status'] === $test['expectedStatus'];
            
            $results[] = [
                'test' => "Login Validation - {$test['name']}",
                'success' => $success,
                'status' => $response['status'],
                'expected' => $test['expectedStatus'],
                'response' => $response['body']
            ];
        }

        return $results;
    }

    public function testTokenRefresh(array $tokens): array
    {
        echo "Testing token refresh...\n";
        
        $refreshData = [
            'refresh_token' => $tokens['refresh_token']
        ];

        $response = $this->makeRequest('POST', '/api/token/refresh', $refreshData);
        
        $success = $response['status'] === 200 && 
                   is_array($response['body']) &&
                   isset($response['body']['token']) &&
                   isset($response['body']['refresh_token']);

        return [
            'test' => 'Token Refresh',
            'success' => $success,
            'status' => $response['status'],
            'response' => $response['body'],
            'newTokens' => $success ? [
                'access_token' => $response['body']['token'],
                'refresh_token' => $response['body']['refresh_token']
            ] : null
        ];
    }

    public function testTokenRefreshValidation(): array
    {
        echo "Testing token refresh validation...\n";
        
        $tests = [
            [
                'name' => 'Invalid refresh token',
                'data' => ['refresh_token' => 'invalid_token'],
                'expectedStatus' => 401
            ],
            [
                'name' => 'Missing refresh token',
                'data' => [],
                'expectedStatus' => 400
            ]
        ];

        $results = [];
        foreach ($tests as $test) {
            $response = $this->makeRequest('POST', '/api/token/refresh', $test['data']);
            $success = $response['status'] === $test['expectedStatus'];
            
            $results[] = [
                'test' => "Token Refresh Validation - {$test['name']}",
                'success' => $success,
                'status' => $response['status'],
                'expected' => $test['expectedStatus'],
                'response' => $response['body']
            ];
        }

        return $results;
    }

    public function testProtectedEndpoint(string $accessToken): array
    {
        echo "Testing protected endpoint access...\n";
        
        // Add Authorization header
        $this->headers[] = "Authorization: Bearer $accessToken";
        
        $response = $this->makeRequest('GET', '/api/profile');
        
        $success = $response['status'] === 200;

        return [
            'test' => 'Protected Endpoint Access',
            'success' => $success,
            'status' => $response['status'],
            'response' => $response['body']
        ];
    }

    public function testUnauthorizedAccess(): array
    {
        echo "Testing unauthorized access...\n";
        
        // Remove authorization header
        $this->headers = array_filter($this->headers, function($header) {
            return !str_starts_with($header, 'Authorization:');
        });
        
        $response = $this->makeRequest('GET', '/api/profile');
        
        $success = $response['status'] === 401;

        return [
            'test' => 'Unauthorized Access',
            'success' => $success,
            'status' => $response['status'],
            'expected' => 401,
            'response' => $response['body']
        ];
    }

    public function runAllTests(): array
    {
        $results = [];
        
        try {
            // Test registration
            $registrationResult = $this->testRegistration();
            $results[] = $registrationResult;
            
            if (!$registrationResult['success']) {
                echo "Registration failed, skipping dependent tests\n";
                return $results;
            }
            
            // Test registration validation
            $validationResults = $this->testRegistrationValidation();
            $results = array_merge($results, $validationResults);
            
            // Test login
            $loginResult = $this->testLogin($registrationResult['userData']);
            $results[] = $loginResult;
            
            if (!$loginResult['success']) {
                echo "Login failed, skipping dependent tests\n";
                return $results;
            }
            
            // Test login validation
            $loginValidationResults = $this->testLoginValidation();
            $results = array_merge($results, $loginValidationResults);
            
            // Test token refresh
            $refreshResult = $this->testTokenRefresh($loginResult['tokens']);
            $results[] = $refreshResult;
            
            // Test token refresh validation
            $refreshValidationResults = $this->testTokenRefreshValidation();
            $results = array_merge($results, $refreshValidationResults);
            
            // Test protected endpoint
            $accessToken = $loginResult['tokens']['access_token'];
            $protectedResult = $this->testProtectedEndpoint($accessToken);
            $results[] = $protectedResult;
            
            // Test unauthorized access
            $unauthorizedResult = $this->testUnauthorizedAccess();
            $results[] = $unauthorizedResult;
            
        } catch (Exception $e) {
            $results[] = [
                'test' => 'Exception',
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return $results;
    }

    public function printResults(array $results): void
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "AUTH TEST RESULTS\n";
        echo str_repeat("=", 60) . "\n";
        
        $totalTests = count($results);
        $passedTests = count(array_filter($results, fn($r) => $r['success']));
        
        foreach ($results as $result) {
            $status = $result['success'] ? '✅ PASS' : '❌ FAIL';
            echo sprintf("%-40s %s\n", $result['test'], $status);
            
            if (!$result['success']) {
                if (isset($result['expected'])) {
                    echo "  Expected Status: {$result['expected']}, Got: {$result['status']}\n";
                }
                if (isset($result['error'])) {
                    echo "  Error: {$result['error']}\n";
                }
                if (isset($result['response']) && is_array($result['response'])) {
                    echo "  Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
                }
                echo "\n";
            }
        }
        
        echo str_repeat("-", 60) . "\n";
        echo sprintf("Tests: %d/%d passed (%.1f%%)\n", 
                    $passedTests, $totalTests, 
                    $totalTests > 0 ? ($passedTests / $totalTests) * 100 : 0);
        echo str_repeat("=", 60) . "\n";
    }
}

// Run tests if script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new AuthTest();
    $results = $tester->runAllTests();
    $tester->printResults($results);
}