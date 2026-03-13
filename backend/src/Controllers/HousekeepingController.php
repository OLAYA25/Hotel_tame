<?php

namespace App\Controllers;

use App\Services\HousekeepingService;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Services\AppLogger;

class HousekeepingController {
    private HousekeepingService $housekeepingService;
    
    public function __construct() {
        $this->housekeepingService = new HousekeepingService();
    }
    
    /**
     * GET /api/housekeeping/tasks - Get tasks for date
     */
    public function getTasks(): void {
        PermissionMiddleware::require('ver_tareas_limpieza', 'housekeeping');
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $staffId = $_GET['staff_id'] ?? null;
        
        try {
            $tasks = $this->housekeepingService->getTasksByDateAndStaff($date, $staffId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $tasks
            ]);
            
        } catch (Exception $e) {
            AppLogger::error('Error getting housekeeping tasks', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error loading tasks',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * POST /api/housekeeping/tasks - Create task
     */
    public function createTask(): void {
        PermissionMiddleware::require('crear_tarea_limpieza', 'housekeeping');
        
        $data = $this->getJsonInput();
        
        try {
            $taskId = $this->housekeepingService->createTask($data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => ['task_id' => $taskId]
            ]);
            
        } catch (Exception $e) {
            AppLogger::error('Error creating housekeeping task', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error creating task',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * PUT /api/housekeeping/tasks/{id}/status - Update task status
     */
    public function updateTaskStatus(int $taskId): void {
        PermissionMiddleware::require('actualizar_tarea_limpieza', 'housekeeping');
        
        $data = $this->getJsonInput();
        $status = $data['status'] ?? null;
        $observaciones = $data['observaciones'] ?? null;
        
        if (!$status) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Status is required'
            ], 400);
            return;
        }
        
        try {
            $success = $this->housekeepingService->updateTaskStatus(
                $taskId, 
                $status, 
                AuthMiddleware::userId(),
                $observaciones
            );
            
            if ($success) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Task status updated successfully'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'error' => 'Task not found or update failed'
                ], 404);
            }
            
        } catch (Exception $e) {
            AppLogger::error('Error updating housekeeping task status', [
                'task_id' => $taskId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error updating task status',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * GET /api/housekeeping/statistics - Get housekeeping statistics
     */
    public function getStatistics(): void {
        PermissionMiddleware::require('ver_estadisticas_limpieza', 'housekeeping');
        
        $date = $_GET['date'] ?? date('Y-m-d');
        
        try {
            $stats = $this->housekeepingService->getStatistics($date);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (Exception $e) {
            AppLogger::error('Error getting housekeeping statistics', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error loading statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * POST /api/housekeeping/generate-checkout-tasks - Generate automatic checkout tasks
     */
    public function generateCheckoutTasks(): void {
        PermissionMiddleware::require('generar_tareas_checkout', 'housekeeping');
        
        try {
            $tasksCreated = $this->housekeepingService->generateCheckoutTasks();
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Checkout tasks generated successfully',
                'data' => ['tasks_created' => $tasksCreated]
            ]);
            
        } catch (Exception $e) {
            AppLogger::error('Error generating checkout tasks', [
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error generating checkout tasks',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * GET /api/housekeeping/room/{id}/history - Get room cleaning history
     */
    public function getRoomHistory(int $habitacionId): void {
        PermissionMiddleware::require('ver_historial_limpieza', 'housekeeping');
        
        $limit = $_GET['limit'] ?? 50;
        
        try {
            $history = $this->housekeepingService->getRoomCleaningHistory($habitacionId, $limit);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $history
            ]);
            
        } catch (Exception $e) {
            AppLogger::error('Error getting room cleaning history', [
                'habitacion_id' => $habitacionId,
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error loading room history',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * GET /api/housekeeping/staff/pending - Get pending tasks by staff
     */
    public function getStaffPendingTasks(): void {
        PermissionMiddleware::require('ver_tareas_pendientes_personal', 'housekeeping');
        
        try {
            $staffTasks = $this->housekeepingService->getPendingTasksCountByStaff();
            
            $this->jsonResponse([
                'success' => true,
                'data' => $staffTasks
            ]);
            
        } catch (Exception $e) {
            AppLogger::error('Error getting staff pending tasks', [
                'error' => $e->getMessage()
            ]);
            
            $this->jsonResponse([
                'success' => false,
                'error' => 'Error loading staff tasks',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get JSON input
     */
    private function getJsonInput(): array {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
