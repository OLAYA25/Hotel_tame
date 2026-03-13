<?php

namespace App\Jobs;

abstract class Job {
    protected array $payload;
    protected int $attempts = 0;
    protected int $maxAttempts = 3;
    protected int $delaySeconds = 0;
    protected string $queue = 'default';
    
    public function __construct(array $payload = []) {
        $this->payload = $payload;
    }
    
    /**
     * Execute the job
     */
    abstract public function handle(): bool;
    
    /**
     * Handle job failure
     */
    public function failed(\Exception $exception): void {
        AppLogger::error('Job failed', [
            'job_class' => get_class($this),
            'payload' => $this->payload,
            'exception' => $exception->getMessage(),
            'attempts' => $this->attempts
        ]);
    }
    
    /**
     * Get job payload
     */
    public function getPayload(): array {
        return $this->payload;
    }
    
    /**
     * Set job payload
     */
    public function setPayload(array $payload): void {
        $this->payload = $payload;
    }
    
    /**
     * Get attempts count
     */
    public function getAttempts(): int {
        return $this->attempts;
    }
    
    /**
     * Set attempts count
     */
    public function setAttempts(int $attempts): void {
        $this->attempts = $attempts;
    }
    
    /**
     * Get max attempts
     */
    public function getMaxAttempts(): int {
        return $this->maxAttempts;
    }
    
    /**
     * Set max attempts
     */
    public function setMaxAttempts(int $maxAttempts): void {
        $this->maxAttempts = $maxAttempts;
    }
    
    /**
     * Get delay seconds
     */
    public function getDelaySeconds(): int {
        return $this->delaySeconds;
    }
    
    /**
     * Set delay seconds
     */
    public function setDelaySeconds(int $delaySeconds): void {
        $this->delaySeconds = $delaySeconds;
    }
    
    /**
     * Get queue name
     */
    public function getQueue(): string {
        return $this->queue;
    }
    
    /**
     * Set queue name
     */
    public function setQueue(string $queue): void {
        $this->queue = $queue;
    }
    
    /**
     * Determine if the job should be released back to the queue
     */
    public function shouldRelease(): bool {
        return $this->attempts < $this->maxAttempts;
    }
    
    /**
     * Get the delay in seconds before the job should be released
     */
    public function getReleaseDelay(): int {
        return min(60 * (2 ** $this->attempts), 3600); // Exponential backoff, max 1 hour
    }
}
