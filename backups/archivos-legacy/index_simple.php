<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

include 'includes/header_simple.php';
include 'includes/sidebar_simple.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Dashboard - Sistema de Gestión Hotelera</h1>
        <p class="text-muted mb-0">Panel principal de control</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-bed fa-3x mb-2"></i>
                    <h6 class="card-title">Total Habitaciones</h6>
                    <h3>25</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-3x mb-2"></i>
                    <h6 class="card-title">Reservas Activas</h6>
                    <h3>12</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-user-tie fa-3x mb-2"></i>
                    <h6 class="card-title">Total Clientes</h6>
                    <h3>156</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-3x mb-2"></i>
                    <h6 class="card-title">Ingresos del Mes</h6>
                    <h3>$15.2M</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Actividad Reciente</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Habitación</th>
                            <th>Estado</th>
                            <th>Precio</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Juan Pérez</td>
                            <td>Hab. 101</td>
                            <td><span class="badge bg-success">Confirmada</span></td>
                            <td>$250,000</td>
                            <td>2024-12-15</td>
                        </tr>
                        <tr>
                            <td>María García</td>
                            <td>Hab. 205</td>
                            <td><span class="badge bg-warning">Pendiente</span></td>
                            <td>$180,000</td>
                            <td>2024-12-14</td>
                        </tr>
                        <tr>
                            <td>Carlos López</td>
                            <td>Hab. 302</td>
                            <td><span class="badge bg-success">Confirmada</span></td>
                            <td>$320,000</td>
                            <td>2024-12-13</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer_simple.php'; ?>
