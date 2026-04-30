<?php

namespace App\Services;

use Database\Database;
use App\Services\AppLogger;
use App\Jobs\Job;
use Exception;

class QueueService {
    
    /**
     * Push a job to the queue
     */
    public function push(Job $job): int {
        $sql = "INSERT INTO jobs 
                (queue, job_class, payload, max_attempts, delay_seconds, available_at, hotel_id) 
                VALUES (:queue, :job_class, :payload, :max_attempts, :delay_seconds, :available_at, :hotel_id)";
        
        $availableAt = date('Y-m-d H:i:s', time() + $job->getDelaySeconds());
        
        Database::execute($sql, [
            ':queue' => $job->getQueue(),
            ':job_class' => get_class($job),
            ':payload' => json_encode($job->getPayload()),
            ':max_attempts' => $job->getMaxAttempts(),
            ':delay_seconds' => $job->getDelaySeconds(),
            ':available_at' => $availableAt,
            ':hotel_id' => 1
        ]);
        
        $jobId = Database::lastInsertId();
        
        AppLogger::business('Job pushed to queue', [
            'job_id' => $jobId,
            'job_class' => get_class($job),
            'queue' => $job->getQueue()
        ]);
        
        return $jobId;
    }
    
    /**
     * Pop a job from the queue
     */
    public function pop(string $queue = 'default'): ?array {
        Database::beginTransaction();
        
        try {
            // Get next available job
            $sql = "SELECT * FROM jobs 
                    WHERE queue = :queue 
                    AND available_at <= NOW() 
                    AND reserved_at IS NULL 
                    AND failed_at IS NULL
                    ORDER BY created_at ASC
                    LIMIT 1 FOR UPDATE SKIP LOCKED";
            
            $job = Database::fetch($sql, [':queue' => $queue]);
            
            if (!$job) {
                Database::rollBack();
                return null;
            }
            
            // Reserve the job
            $reserveSql = "UPDATE jobs SET reserved_at = NOW(), attempts = attempts + 1 WHERE id = :id";
            Database::execute($reserveSql, [':id' => $job['id']]);
            
            Database::commit();
            
            return $job;
            
        } catch (Exception $e) {
            Database::rollBack();
            AppLogger::error('Failed to pop job from queue', [
                'queue' => $queue,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Process jobs from queue
     */
    public function processJobs(string $queue = 'default', int $maxJobs = 10): array {
        $processed = ['success' => 0, 'failed' => 0, 'released' => 0];
        
        for ($i = 0; $i < $maxJobs; $i++) {
            $jobData = $this->pop($queue);
            
            if (!$jobData) {
                break; // No more jobs in queue
            }
            
            try {
                $job = $this->instantiateJob($jobData['job_class'], json_decode($jobData['payload'], true));
                $job->setAttempts($jobData['attempts']);
                
                if ($job->handle()) {
                    // Job successful, remove from queue
                    $this->deleteJob($jobData['id']);
                    $processed['success']++;
                    
                    AppLogger::business('Job processed successfully', [
                        'job_id' => $jobData['id'],
                        'job_class' => $jobData['job_class']
                    ]);
                } else {
                    // Job failed but should be retried
                    $this->releaseJob($jobData['id'], $job->getReleaseDelay());
                    $processed['released']++;
                }
                
            } catch (Exception $e) {
                // Job failed with exception
                $job = $this->instantiateJob($jobData['job_class'], json_decode($jobData['payload'], true));
                $job->setAttempts($jobData['attempts']);
                $job->failed($e);
                
                if ($job->shouldRelease()) {
                    // Release for retry
                    $this->releaseJob($jobData['id'], $job->getReleaseDelay());
                    $processed['released']++;
                } else {
                    // Mark as failed permanently
                    $this->failJob($jobData['id'], $e->getMessage());
                    $processed['failed']++;
                }
            }
        }
        
        return $processed;
    }
    
    /**
     * Run queue worker
     */
    public function runWorker(string $queue = 'default', int $memory = 128, int $timeout = 60): void {
        $startTime = time();
        
        while (true) {
            // Check memory limit
            if (memory_get_usage() / 1024 / 1024 >= $memory) {
                AppLogger::warning('Queue worker stopped due to memory limit');
                break;
            }
            
            // Check timeout
            if (time() - $startTime >= $timeout) {
                AppLogger::info('Queue worker stopped due to timeout');
                break;
            }
            
            $processed = $this->processJobs($queue);
            
            if ($processed['success'] + $processed['failed'] + $processed['released'] === 0) {
                // No jobs processed, sleep for a while
                sleep(1);
            }
        }
    }
    
    /**
     * Get queue statistics
     */
    public function getQueueStats(string $queue = null): array {
        $sql = "SELECT 
                    queue,
                    COUNT(*) as total_jobs,
                    COUNT(CASE WHEN reserved_at IS NULL AND failed_at IS NULL THEN 1 END) as pending_jobs,
                    COUNT(CASE WHEN reserved_at IS NOT NULL AND failed_at IS NULL THEN 1 END) as running_jobs,
                    COUNT(CASE WHEN failed_at IS NOT NULL THEN 1 END) as failed_jobs
                FROM jobs";
        
        $params = [];
        
        if ($queue) {
            $sql .= " WHERE queue = :queue";
            $params[':queue'] = $queue;
        }
        
        $sql .= " GROUP BY queue ORDER BY queue";
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Clear queue
     */
    public function clearQueue(string $queue = null): int {
        $sql = "DELETE FROM jobs";
        $params = [];
        
        if ($queue) {
            $sql .= " WHERE queue = :queue";
            $params[':queue'] = $queue;
        }
        
        Database::execute($sql, $params);
        $deletedCount = Database::fetch("SELECT ROW_COUNT() as count")['count'];
        
        AppLogger::business('Queue cleared', [
            'queue' => $queue,
            'deleted_count' => $deletedCount
        ]);
        
        return $deletedCount;
    }
    
    /**
     * Retry failed jobs
     */
    public function retryFailedJobs(string $queue = null, int $limit = 100): int {
        $sql = "SELECT * FROM failed_jobs";
        $params = [];
        
        if ($queue) {
            $sql .= " WHERE queue = :queue";
            $params[':queue'] = $queue;
        }
        
        $sql .= " ORDER BY failed_at DESC LIMIT :limit";
        
        $failedJobs = Database::fetchAll($sql, array_merge($params, [':limit' => $limit]));
        
        $retriedCount = 0;
        
        foreach ($failedJobs as $failedJob) {
            // Re-add to jobs table
            $insertSql = "INSERT INTO jobs 
                          (queue, job_class, payload, max_attempts, hotel_id) 
                          VALUES (:queue, :job_class, :payload, :max_attempts, :hotel_id)";
            
            Database::execute($insertSql, [
                ':queue' => $failedJob['queue'],
                ':job_class' => $failedJob['job_class'],
                ':payload' => $failedJob['payload'],
                ':max_attempts' => 3,
                ':hotel_id' => $failedJob['hotel_id']
            ]);
            
            // Remove from failed jobs
            $deleteSql = "DELETE FROM failed_jobs WHERE id = :id";
            Database::execute($deleteSql, [':id' => $failedJob['id']]);
            
            $retriedCount++;
        }
        
        AppLogger::business('Failed jobs retried', [
            'queue' => $queue,
            'retried_count' => $retriedCount
        ]);
        
        return $retriedCount;
    }
    
    /**
     * Delete job from queue
     */
    private function deleteJob(int $jobId): void {
        $sql = "DELETE FROM jobs WHERE id = :id";
        Database::execute($sql, [':id' => $jobId]);
    }
    
    /**
     * Release job back to queue
     */
    private function releaseJob(int $jobId, int $delaySeconds): void {
        $availableAt = date('Y-m-d H:i:s', time() + $delaySeconds);
        
        $sql = "UPDATE jobs 
                SET reserved_at = NULL, 
                    available_at = :available_at 
                WHERE id = :id";
        
        Database::execute($sql, [
            ':available_at' => $availableAt,
            ':id' => $jobId
        ]);
    }
    
    /**
     * Mark job as failed
     */
    private function failJob(int $jobId, string $exception): void {
        Database::beginTransaction();
        
        try {
            // Get job data
            $jobData = Database::fetch("SELECT * FROM jobs WHERE id = :id", [':id' => $jobId]);
            
            if ($jobData) {
                // Move to failed_jobs table
                $failedSql = "INSERT INTO failed_jobs 
                             (uuid, queue, payload, exception, hotel_id) 
                             VALUES (:uuid, :queue, :payload, :exception, :hotel_id)";
                
                Database::execute($failedSql, [
                    ':uuid' => uniqid('job_', true),
                    ':queue' => $jobData['queue'],
                    ':payload' => $jobData['payload'],
                    ':exception' => $exception,
                    ':hotel_id' => $jobData['hotel_id']
                ]);
                
                // Remove from jobs table
                $this->deleteJob($jobId);
            }
            
            Database::commit();
            
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
    
    /**
     * Instantiate job class
     */
    private function instantiateJob(string $className, array $payload): Job {
        if (!class_exists($className)) {
            throw new Exception("Job class not found: $className");
        }
        
        $job = new $className($payload);
        
        if (!$job instanceof Job) {
            throw new Exception("Job class must extend Job: $className");
        }
        
        return $job;
    }
    
    /**
     * Schedule recurring job
     */
    public function schedule(string $jobClass, array $payload, string $schedule): void {
        // This would integrate with a cron-like scheduler
        // For now, just push the job immediately
        $job = new $jobClass($payload);
        $this->push($job);
        
        AppLogger::business('Recurring job scheduled', [
            'job_class' => $jobClass,
            'schedule' => $schedule
        ]);
    }
}
