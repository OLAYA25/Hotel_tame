<?php

namespace App\Services;

use App\Services\Logger;
use Exception;

class EventDispatcher {
    private static array $listeners = [];
    
    /**
     * Register event listener
     */
    public static function listen(string $event, callable $listener, int $priority = 0): void {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }
        
        self::$listeners[$event][] = [
            'listener' => $listener,
            'priority' => $priority
        ];
        
        // Sort by priority (higher first)
        usort(self::$listeners[$event], function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
    }
    
    /**
     * Dispatch event
     */
    public static function dispatch(string $event, array $data = []): void {
        Logger::business("Event Dispatched: {$event}", ['event' => $event, 'data' => $data]);
        
        if (!isset(self::$listeners[$event])) {
            return;
        }
        
        foreach (self::$listeners[$event] as $listenerInfo) {
            try {
                call_user_func($listenerInfo['listener'], $data);
            } catch (Exception $e) {
                Logger::error("Event listener error for {$event}", [
                    'event' => $event,
                    'error' => $e->getMessage(),
                    'listener' => $listenerInfo['listener']
                ]);
            }
        }
    }
    
    /**
     * Get event listeners
     */
    public static function getListeners(string $event): array {
        return self::$listeners[$event] ?? [];
    }
    
    /**
     * Clear all listeners
     */
    public static function clear(): void {
        self::$listeners = [];
    }
    
    /**
     * Clear specific event listeners
     */
    public static function clearEvent(string $event): void {
        unset(self::$listeners[$event]);
    }
}
