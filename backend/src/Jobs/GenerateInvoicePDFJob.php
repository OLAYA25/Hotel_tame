<?php

namespace App\Jobs;

use Database\Database;
use App\Services\AppLogger;

class GenerateInvoicePDFJob extends Job {
    
    public function __construct(array $payload) {
        parent::__construct($payload);
        $this->queue = 'high';
    }
    
    public function handle(): bool {
        try {
            $reservationId = $this->payload['reservation_id'];
            $emailToClient = $this->payload['email_to_client'] ?? false;
            
            // Get reservation data
            $reservationSql = "SELECT r.*, c.nombre, c.apellido, c.email, h.numero as habitacion_numero
                              FROM reservas r
                              JOIN reserva_clientes rc ON r.id = rc.reserva_id
                              JOIN clientes c ON rc.cliente_id = c.id
                              JOIN habitaciones h ON r.habitacion_id = h.id
                              WHERE r.id = :reservation_id AND rc.rol = 'titular' AND r.deleted_at IS NULL";
            
            $reservation = Database::fetch($reservationSql, [':reservation_id' => $reservationId]);
            
            if (!$reservation) {
                throw new \Exception("Reservation not found: $reservationId");
            }
            
            // Generate PDF (mock implementation)
            $pdfPath = $this->generatePDF($reservation);
            
            // Save to database
            $this->saveInvoicePDF($reservationId, $pdfPath);
            
            // Send email if requested
            if ($emailToClient) {
                $this->sendInvoiceEmail($reservation, $pdfPath);
            }
            
            AppLogger::business('Invoice PDF generated', [
                'reservation_id' => $reservationId,
                'pdf_path' => $pdfPath,
                'emailed' => $emailToClient
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            AppLogger::error('Failed to generate invoice PDF', [
                'reservation_id' => $this->payload['reservation_id'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    private function generatePDF(array $reservation): string {
        // Mock PDF generation - in real implementation, use TCPDF or similar
        $pdfContent = "Invoice for Reservation #{$reservation['id']}\n";
        $pdfContent .= "Guest: {$reservation['nombre']} {$reservation['apellido']}\n";
        $pdfContent .= "Room: {$reservation['habitacion_numero']}\n";
        $pdfContent .= "Dates: {$reservation['fecha_entrada']} to {$reservation['fecha_salida']}\n";
        $pdfContent .= "Total: {$reservation['precio_total']}\n";
        
        $filename = "invoice_{$reservation['id']}_" . date('Y-m-d_H-i-s') . ".pdf";
        $path = __DIR__ . "/../../../storage/invoices/$filename";
        
        // Create directory if it doesn't exist
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // For demo, save as text file instead of PDF
        file_put_contents(str_replace('.pdf', '.txt', $path), $pdfContent);
        
        return $filename;
    }
    
    private function saveInvoicePDF(int $reservationId, string $pdfPath): void {
        $sql = "UPDATE facturas SET pdf_path = :pdf_path WHERE reserva_id = :reservation_id";
        Database::execute($sql, [
            ':pdf_path' => $pdfPath,
            ':reservation_id' => $reservationId
        ]);
    }
    
    private function sendInvoiceEmail(array $reservation, string $pdfPath): void {
        // Mock email sending - in real implementation, use PHPMailer or similar
        AppLogger::business('Invoice email sent', [
            'reservation_id' => $reservation['id'],
            'client_email' => $reservation['email'],
            'pdf_path' => $pdfPath
        ]);
    }
}
