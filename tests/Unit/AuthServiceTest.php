<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;
use Database\Database;
use Exception;

class AuthServiceTest extends TestCase {
    private AuthService $authService;
    
    protected function setUp(): void {
        parent::setUp();
        $this->authService = new AuthService();
        
        // Setup test database
        $this->createTestTables();
        $this->insertTestData();
    }
    
    protected function tearDown(): void {
        // Clean up test database
        Database::execute("DELETE FROM usuarios WHERE email LIKE 'test_%'");
        parent::tearDown();
    }
    
    /**
     * Test successful login
     */
    public function testSuccessfulLogin(): void {
        $result = $this->authService->login('test@example.com', 'password123');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertEquals('test@example.com', $result['user']['email']);
    }
    
    /**
     * Test login with invalid credentials
     */
    public function testLoginWithInvalidCredentials(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');
        
        $this->authService->login('test@example.com', 'wrongpassword');
    }
    
    /**
     * Test login with non-existent user
     */
    public function testLoginWithNonExistentUser(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');
        
        $this->authService->login('nonexistent@example.com', 'password123');
    }
    
    /**
     * Test token validation
     */
    public function testTokenValidation(): void {
        // First login to get token
        $result = $this->authService->login('test@example.com', 'password123');
        $token = $result['token'];
        
        // Validate token
        $payload = $this->authService->validateToken($token);
        
        $this->assertIsObject($payload);
        $this->assertObjectHasProperty('sub', $payload);
        $this->assertObjectHasProperty('user', $payload);
        $this->assertEquals('test@example.com', $payload->user->email);
    }
    
    /**
     * Test token validation with invalid token
     */
    public function testTokenValidationWithInvalidToken(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid token');
        
        $this->authService->validateToken('invalid.token.here');
    }
    
    /**
     * Test logout
     */
    public function testLogout(): void {
        // First login to get token
        $result = $this->authService->login('test@example.com', 'password123');
        $token = $result['token'];
        
        // Logout
        $this->authService->logout($token);
        
        // Token should be invalid now
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token has been invalidated');
        
        $this->authService->validateToken($token);
    }
    
    /**
     * Create test tables
     */
    private function createTestTables(): void {
        Database::execute("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                rol VARCHAR(50) NOT NULL,
                activo TINYINT(1) DEFAULT 1,
                ultimo_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL
            )
        ");
        
        Database::execute("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        Database::execute("
            CREATE TABLE IF NOT EXISTS token_blacklist (
                id INT AUTO_INCREMENT PRIMARY KEY,
                token VARCHAR(255) NOT NULL,
                expires_at TIMESTAMP NOT NULL
            )
        ");
    }
    
    /**
     * Insert test data
     */
    private function insertTestData(): void {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        Database::execute("
            INSERT INTO usuarios (nombre, apellido, email, password, rol) 
            VALUES ('Test', 'User', 'test@example.com', :password, 'user')
        ", ['password' => $hashedPassword]);
    }
}
