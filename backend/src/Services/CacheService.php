<?php

namespace App\Services;

use App\Services\AppLogger;
use Exception;

class CacheService {
    private static ?\Redis $redis = null;
    private static string $prefix = 'hoteltame:';
    private static int $defaultTtl = 3600; // 1 hour
    
    /**
     * Initialize Redis connection
     */
    private static function connect(): \Redis {
        if (self::$redis === null) {
            try {
                self::$redis = new \Redis();
                self::$redis->connect('127.0.0.1', 6379);
                
                // Authenticate if password is set
                $password = $_ENV['REDIS_PASSWORD'] ?? null;
                if ($password) {
                    self::$redis->auth($password);
                }
                
                // Select database
                $database = $_ENV['REDIS_DATABASE'] ?? 0;
                self::$redis->select($database);
                
            } catch (Exception $e) {
                AppLogger::error('Failed to connect to Redis', [
                    'error' => $e->getMessage()
                ]);
                throw new Exception('Cache service unavailable');
            }
        }
        
        return self::$redis;
    }
    
    /**
     * Get value from cache
     */
    public static function get(string $key): mixed {
        try {
            $redis = self::connect();
            $value = $redis->get(self::prefix . $key);
            
            if ($value === false) {
                return null;
            }
            
            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $value;
            
        } catch (Exception $e) {
            AppLogger::error('Cache get failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Set value in cache
     */
    public static function set(string $key, mixed $value, int $ttl = null): bool {
        try {
            $redis = self::connect();
            $ttl = $ttl ?? self::$defaultTtl;
            
            $serialized = is_array($value) || is_object($value) ? json_encode($value) : $value;
            
            return $redis->setex(self::prefix . $key, $ttl, $serialized);
            
        } catch (Exception $e) {
            AppLogger::error('Cache set failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Delete value from cache
     */
    public static function delete(string $key): bool {
        try {
            $redis = self::connect();
            return $redis->del(self::prefix . $key) > 0;
            
        } catch (Exception $e) {
            AppLogger::error('Cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Check if key exists
     */
    public static function exists(string $key): bool {
        try {
            $redis = self::connect();
            return $redis->exists(self::prefix . $key) > 0;
            
        } catch (Exception $e) {
            AppLogger::error('Cache exists check failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Increment value
     */
    public static function increment(string $key, int $value = 1): int|false {
        try {
            $redis = self::connect();
            return $redis->incrby(self::prefix . $key, $value);
            
        } catch (Exception $e) {
            AppLogger::error('Cache increment failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Decrement value
     */
    public static function decrement(string $key, int $value = 1): int|false {
        try {
            $redis = self::connect();
            return $redis->decrby(self::prefix . $key, $value);
            
        } catch (Exception $e) {
            AppLogger::error('Cache decrement failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get multiple values
     */
    public static function mget(array $keys): array {
        try {
            $redis = self::connect();
            $prefixedKeys = array_map(fn($key) => self::prefix . $key, $keys);
            $values = $redis->mget($prefixedKeys);
            
            $result = [];
            foreach ($keys as $i => $key) {
                $value = $values[$i];
                if ($value !== false) {
                    $decoded = json_decode($value, true);
                    $result[$key] = $decoded !== null ? $decoded : $value;
                } else {
                    $result[$key] = null;
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            AppLogger::error('Cache mget failed', [
                'keys' => $keys,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Set multiple values
     */
    public static function mset(array $values, int $ttl = null): bool {
        try {
            $redis = self::connect();
            $prefixedValues = [];
            
            foreach ($values as $key => $value) {
                $serialized = is_array($value) || is_object($value) ? json_encode($value) : $value;
                $prefixedValues[self::prefix . $key] = $serialized;
            }
            
            $result = $redis->mset($prefixedValues);
            
            // Set TTL for all keys if specified
            if ($ttl !== null) {
                $ttl = $ttl ?? self::$defaultTtl;
                foreach (array_keys($values) as $key) {
                    $redis->expire(self::prefix . $key, $ttl);
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            AppLogger::error('Cache mset failed', [
                'keys' => array_keys($values),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Clear all cache
     */
    public static function clear(): bool {
        try {
            $redis = self::connect();
            $keys = $redis->keys(self::prefix . '*');
            
            if (!empty($keys)) {
                return $redis->del($keys) > 0;
            }
            
            return true;
            
        } catch (Exception $e) {
            AppLogger::error('Cache clear failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get cache statistics
     */
    public static function getStats(): array {
        try {
            $redis = self::connect();
            $info = $redis->info();
            
            return [
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info)
            ];
            
        } catch (Exception $e) {
            AppLogger::error('Cache stats failed', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Calculate hit rate
     */
    private static function calculateHitRate(array $info): float {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }
    
    /**
     * Cache dashboard data
     */
    public static function cacheDashboardData(string $period = 'day'): array {
        $cacheKey = "dashboard_data_$period";
        
        $data = self::get($cacheKey);
        if ($data !== null) {
            return $data;
        }
        
        // Generate fresh data
        $analyticsService = new \App\Services\AnalyticsService();
        $data = $analyticsService->getDashboardAnalytics($period);
        
        // Cache for 15 minutes
        self::set($cacheKey, $data, 900);
        
        return $data;
    }
    
    /**
     * Cache room availability
     */
    public static function cacheRoomAvailability(string $checkin, string $checkout): array {
        $cacheKey = "room_availability_{$checkin}_{$checkout}";
        
        $data = self::get($cacheKey);
        if ($data !== null) {
            return $data;
        }
        
        // Get fresh availability data
        $webBookingService = new \App\Services\WebBookingService();
        $searchParams = [
            'checkin' => $checkin,
            'checkout' => $checkout,
            'adults' => 2,
            'children' => 0
        ];
        
        $data = $webBookingService->searchAvailableRooms($searchParams);
        
        // Cache for 30 minutes
        self::set($cacheKey, $data, 1800);
        
        return $data;
    }
    
    /**
     * Cache user permissions
     */
    public static function cacheUserPermissions(int $userId): array {
        $cacheKey = "user_permissions_{$userId}";
        
        $data = self::get($cacheKey);
        if ($data !== null) {
            return $data;
        }
        
        // Get fresh permissions
        $permissionMiddleware = new \App\Middleware\PermissionMiddleware();
        $data = $permissionMiddleware->getUserPermissions($userId);
        
        // Cache for 1 hour
        self::set($cacheKey, $data, 3600);
        
        return $data;
    }
    
    /**
     * Invalidate cache patterns
     */
    public static function invalidatePattern(string $pattern): int {
        try {
            $redis = self::connect();
            $keys = $redis->keys(self::prefix . $pattern);
            
            if (!empty($keys)) {
                return $redis->del($keys);
            }
            
            return 0;
            
        } catch (Exception $e) {
            AppLogger::error('Cache pattern invalidation failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Remember pattern - get from cache or execute callback
     */
    public static function remember(string $key, callable $callback, int $ttl = null): mixed {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Lock for cache stampede prevention
     */
    public static function lock(string $key, int $ttl = 10): bool {
        try {
            $redis = self::connect();
            $lockKey = self::prefix . "lock:{$key}";
            
            return $redis->set($lockKey, 1, ['NX', 'EX' => $ttl]);
            
        } catch (Exception $e) {
            AppLogger::error('Cache lock failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Release lock
     */
    public static function unlock(string $key): bool {
        try {
            $redis = self::connect();
            $lockKey = self::prefix . "lock:{$key}";
            return $redis->del($lockKey) > 0;
            
        } catch (Exception $e) {
            AppLogger::error('Cache unlock failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
