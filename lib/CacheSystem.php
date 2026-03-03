<?php
/**
 * CacheSystem - Sistema de caché avanzado con múltiples estrategias
 * Soporta caché en memoria, archivo y Redis
 */
class CacheSystem {
    private static $instance = null;
    private $cache = [];
    private $config;
    private $redis = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->config = [
            'default_ttl' => 300, // 5 minutos
            'max_memory_items' => 1000,
            'cache_dir' => __DIR__ . '/../cache/',
            'redis_enabled' => false,
            'redis_host' => '127.0.0.1',
            'redis_port' => 6379
        ];
        
        // Crear directorio de caché
        if (!is_dir($this->config['cache_dir'])) {
            mkdir($this->config['cache_dir'], 0755, true);
        }
        
        // Inicializar Redis si está disponible
        $this->initializeRedis();
    }
    
    /**
     * Inicializar conexión Redis
     */
    private function initializeRedis() {
        if (class_exists('Redis') && $this->config['redis_enabled']) {
            try {
                $this->redis = new Redis();
                $this->redis->connect($this->config['redis_host'], $this->config['redis_port']);
                $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
            } catch (Exception $e) {
                error_log("Redis no disponible: " . $e->getMessage());
                $this->redis = null;
            }
        }
    }
    
    /**
     * Almacenar valor en caché
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->config['default_ttl'];
        $cache_item = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time(),
            'hits' => 0
        ];
        
        // Intentar Redis primero
        if ($this->redis) {
            try {
                $this->redis->setex($key, $ttl, $cache_item);
                return true;
            } catch (Exception $e) {
                error_log("Error Redis SET: " . $e->getMessage());
            }
        }
        
        // Caché en memoria
        $this->cache[$key] = $cache_item;
        
        // Limitar tamaño de caché en memoria
        if (count($this->cache) > $this->config['max_memory_items']) {
            $this->evictOldest();
        }
        
        // Caché en archivo como respaldo
        $this->setFileCache($key, $cache_item);
        
        return true;
    }
    
    /**
     * Obtener valor de caché
     */
    public function get($key, $default = null) {
        // Intentar Redis primero
        if ($this->redis) {
            try {
                $cached = $this->redis->get($key);
                if ($cached !== false) {
                    $cached['hits']++;
                    $this->redis->setex($key, $cached['expires'] - time(), $cached);
                    return $cached['value'];
                }
            } catch (Exception $e) {
                error_log("Error Redis GET: " . $e->getMessage());
            }
        }
        
        // Caché en memoria
        if (isset($this->cache[$key])) {
            $item = $this->cache[$key];
            if ($item['expires'] > time()) {
                $item['hits']++;
                $this->cache[$key] = $item;
                return $item['value'];
            } else {
                unset($this->cache[$key]);
                $this->deleteFileCache($key);
            }
        }
        
        // Caché en archivo
        $cached = $this->getFileCache($key);
        if ($cached !== null) {
            if ($cached['expires'] > time()) {
                $cached['hits']++;
                $this->cache[$key] = $cached;
                return $cached['value'];
            } else {
                $this->deleteFileCache($key);
            }
        }
        
        return $default;
    }
    
    /**
     * Verificar si existe en caché
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    /**
     * Eliminar de caché
     */
    public function delete($key) {
        // Redis
        if ($this->redis) {
            try {
                $this->redis->del($key);
            } catch (Exception $e) {
                error_log("Error Redis DELETE: " . $e->getMessage());
            }
        }
        
        // Memoria
        unset($this->cache[$key]);
        
        // Archivo
        $this->deleteFileCache($key);
        
        return true;
    }
    
    /**
     * Limpiar toda la caché
     */
    public function clear() {
        // Redis
        if ($this->redis) {
            try {
                $this->redis->flushDB();
            } catch (Exception $e) {
                error_log("Error Redis FLUSH: " . $e->getMessage());
            }
        }
        
        // Memoria
        $this->cache = [];
        
        // Archivos
        $this->clearFileCache();
        
        return true;
    }
    
    /**
     * Caché con tags
     */
    public function setWithTags($key, $value, $tags = [], $ttl = null) {
        $cache_item = [
            'value' => $value,
            'expires' => time() + ($ttl ?? $this->config['default_ttl']),
            'created' => time(),
            'hits' => 0,
            'tags' => $tags
        ];
        
        // Almacenar con tags
        $this->set($key, $cache_item, $ttl);
        
        // Indexar por tags
        foreach ($tags as $tag) {
            $tagged_keys = $this->get("tag:$tag", []);
            if (!in_array($key, $tagged_keys)) {
                $tagged_keys[] = $key;
                $this->set("tag:$tag", $tagged_keys, $ttl);
            }
        }
        
        return true;
    }
    
    /**
     * Invalidar por tags
     */
    public function invalidateByTag($tag) {
        $tagged_keys = $this->get("tag:$tag", []);
        foreach ($tagged_keys as $key) {
            $this->delete($key);
        }
        $this->delete("tag:$tag");
        return true;
    }
    
    /**
     * Caché de consultas SQL
     */
    public function cacheQuery($sql, $params = [], $ttl = null) {
        $cache_key = 'query:' . md5($sql . serialize($params));
        
        if ($this->has($cache_key)) {
            return $this->get($cache_key);
        }
        
        // Ejecutar consulta
        try {
            $database = new Database();
            $db = $database->getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll();
            
            // Almacenar en caché
            $this->set($cache_key, $result, $ttl);
            
            return $result;
        } catch (Exception $e) {
            error_log("Error en consulta cacheada: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Caché de vistas renderizadas
     */
    public function cacheView($view_name, $data, $callback, $ttl = null) {
        $cache_key = 'view:' . $view_name . ':' . md5(serialize($data));
        
        if ($this->has($cache_key)) {
            return $this->get($cache_key);
        }
        
        // Renderizar vista
        $content = call_user_func($callback, $data);
        
        // Almacenar en caché
        $this->set($cache_key, $content, $ttl);
        
        return $content;
    }
    
    /**
     * Almacenar en archivo
     */
    private function setFileCache($key, $data) {
        $file_path = $this->config['cache_dir'] . md5($key) . '.cache';
        return file_put_contents($file_path, serialize($data), LOCK_EX) !== false;
    }
    
    /**
     * Obtener de archivo
     */
    private function getFileCache($key) {
        $file_path = $this->config['cache_dir'] . md5($key) . '.cache';
        
        if (!file_exists($file_path)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($file_path));
        return $data !== false ? $data : null;
    }
    
    /**
     * Eliminar archivo de caché
     */
    private function deleteFileCache($key) {
        $file_path = $this->config['cache_dir'] . md5($key) . '.cache';
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return true;
    }
    
    /**
     * Limpiar archivos de caché
     */
    private function clearFileCache() {
        $files = glob($this->config['cache_dir'] . '*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
    
    /**
     * Evitar elementos más antiguos
     */
    private function evictOldest() {
        $oldest_key = null;
        $oldest_time = time();
        
        foreach ($this->cache as $key => $item) {
            if ($item['created'] < $oldest_time) {
                $oldest_time = $item['created'];
                $oldest_key = $key;
            }
        }
        
        if ($oldest_key) {
            unset($this->cache[$oldest_key]);
            $this->deleteFileCache($oldest_key);
        }
    }
    
    /**
     * Obtener estadísticas de caché
     */
    public function getStats() {
        $memory_usage = count($this->cache);
        $file_count = count(glob($this->config['cache_dir'] . '*.cache'));
        
        $stats = [
            'memory_items' => $memory_usage,
            'file_items' => $file_count,
            'redis_connected' => $this->redis !== null,
            'hit_rate' => $this->calculateHitRate(),
            'memory_usage' => memory_get_usage(true),
            'config' => $this->config
        ];
        
        return $stats;
    }
    
    /**
     * Calcular tasa de aciertos
     */
    private function calculateHitRate() {
        $total_hits = 0;
        $total_items = 0;
        
        foreach ($this->cache as $item) {
            $total_hits += $item['hits'];
            $total_items++;
        }
        
        return $total_items > 0 ? ($total_hits / $total_items) * 100 : 0;
    }
    
    /**
     * Limpiar caché expirada
     */
    public function cleanup() {
        $current_time = time();
        
        // Limpiar memoria
        foreach ($this->cache as $key => $item) {
            if ($item['expires'] <= $current_time) {
                unset($this->cache[$key]);
                $this->deleteFileCache($key);
            }
        }
        
        // Limpiar archivos
        $files = glob($this->config['cache_dir'] . '*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                $data = unserialize(file_get_contents($file));
                if ($data && $data['expires'] <= $current_time) {
                    unlink($file);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Pre-cargar datos comunes
     */
    public function preloadCommonData() {
        // Pre-cargar configuración del hotel
        $this->cacheQuery("SELECT * FROM hotel_config WHERE activa = 1");
        
        // Pre-cargar habitaciones disponibles
        $this->cacheQuery("
            SELECT id, numero, tipo, precio_noche, capacidad 
            FROM habitaciones 
            WHERE estado = 'disponible' AND deleted_at IS NULL
        ");
        
        // Pre-cargar tipos de habitación
        $this->cacheQuery("SELECT DISTINCT tipo FROM habitaciones WHERE deleted_at IS NULL");
        
        return true;
    }
}
?>
