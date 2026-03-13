-- Jobs Queue System Tables
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(100) NOT NULL DEFAULT 'default',
    job_class VARCHAR(255) NOT NULL,
    payload JSON NOT NULL,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    delay_seconds INT DEFAULT 0,
    reserved_at TIMESTAMP NULL,
    available_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    failed_at TIMESTAMP NULL,
    exception_message TEXT,
    hotel_id INT DEFAULT 1,
    INDEX idx_queue (queue),
    INDEX idx_available_at (available_at),
    INDEX idx_reserved_at (reserved_at),
    INDEX idx_failed_at (failed_at),
    INDEX idx_hotel_id (hotel_id)
);

-- Failed Jobs
CREATE TABLE IF NOT EXISTS failed_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    queue VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hotel_id INT DEFAULT 1,
    INDEX idx_uuid (uuid),
    INDEX idx_queue (queue),
    INDEX idx_failed_at (failed_at),
    INDEX idx_hotel_id (hotel_id)
);

-- Job Batches
CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids TEXT,
    options JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancelled_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    hotel_id INT DEFAULT 1,
    INDEX idx_finished_at (finished_at),
    INDEX idx_cancelled_at (cancelled_at),
    INDEX idx_hotel_id (hotel_id)
);

-- Insert some sample jobs
INSERT INTO jobs (queue, job_class, payload, delay_seconds) VALUES
('high', 'GenerateInvoicePDF', '{"reservation_id": 1, "email_to_client": true}', 0),
('default', 'SendBookingConfirmation', '{"reservation_id": 1, "client_email": "client@example.com"}', 0),
('low', 'GenerateDailyReport', '{"date": "2024-01-01", "type": "occupancy"}', 0),
('default', 'ProcessPaymentReminder', '{"reservation_id": 1, "days_overdue": 1}', 3600);
