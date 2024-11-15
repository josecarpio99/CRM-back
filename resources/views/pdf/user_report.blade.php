<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 20px;
        }
        .profile-pic {
            width: 40px;
            height: 40px;
            background: #333;
            border-radius: 50%;
            display: inline-block;
            vertical-align: middle;
        }
        .header-info {
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
        .header-info span {
            margin-right: 10px;
        }
        .metric-card {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }
        .metric-number {
            font-size: 18pt;
            font-weight: bold;
        }
        .metric-label {
            font-size: 8pt;
            color: white;
            padding: 3px;
            border-radius: 2px;
            display: inline-block;
            margin-top: 5px;
        }
        .blue-bg {
            background: #3498db;
        }
        .green-bg {
            background: #2ecc71;
        }
        .table-container {
            margin-bottom: 20px;
        }
        .table-title {
            color: #3498db;
            font-size: 12pt;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border-bottom: 1px solid #eee;
            padding: 5px;
            text-align: left;
        }
        .blue-text {
            color: #3498db;
        }
        .monthly-average {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            width: 200px;
            margin: 0 auto;
        }
        .average-amount {
            color: #3498db;
            font-size: 18pt;
            font-weight: bold;
        }
        .average-label {
            background: #3498db;
            color: white;
            padding: 3px;
            border-radius: 2px;
            font-size: 8pt;
            display: inline-block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="profile-pic"></div>
        <div class="header-info">
            <span>NOMBRE DEL ASESOR</span>
            <span>SUCURSAL</span>
            <span>FECHA</span>
            <span>GERENTE</span>
        </div>
    </div>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 25%;">
                <div class="metric-card">
                    <div class="metric-number">99</div>
                    <div class="metric-label blue-bg">PROYECTOS ACTIVOS</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-card">
                    <div class="metric-number">99</div>
                    <div class="metric-label blue-bg">PROYECTOS EN ROJO</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-card">
                    <div class="metric-number">$30.5</div>
                    <div class="metric-label green-bg">ROA</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="metric-card">
                    <div class="metric-number">7.4%</div>
                    <div class="metric-label green-bg">HIT RATE</div>
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="table-container">
                    <h3 class="table-title">RESUMEN DE VENTA</h3>
                    <table>
                        <tr>
                            <th>Período</th>
                            <th>Cantidad</th>
                            <th>Valor</th>
                        </tr>
                        <tr>
                            <td class="blue-text">Semana pasada</td>
                            <td>99</td>
                            <td>$9,999</td>
                        </tr>
                        <tr>
                            <td class="blue-text">Últimos 30 días</td>
                            <td>99</td>
                            <td>$9,999</td>
                        </tr>
                        <tr>
                            <td class="blue-text">Últimos 90 días</td>
                            <td>99</td>
                            <td>$9,999</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="table-container">
                    <h3 class="table-title">PROYECTOS COTIZADOS</h3>
                    <table>
                        <tr>
                            <th>Período</th>
                            <th>Cantidad</th>
                            <th>Valor</th>
                        </tr>
                        <tr>
                            <td class="blue-text">Semana pasada</td>
                            <td>99</td>
                            <td>$9,999</td>
                        </tr>
                        <tr>
                            <td class="blue-text">Últimos 30 días</td>
                            <td>99</td>
                            <td>$9,999</td>
                        </tr>
                        <tr>
                            <td class="blue-text">Últimos 90 días</td>
                            <td>99</td>
                            <td>$9,999</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-container">
        <h3 class="table-title">TOP 5 PROYECTOS</h3>
        <table>
            <tr>
                <th class="blue-text">Empresa</th>
                <th class="blue-text" style="text-align: right;">Valor</th>
            </tr>
            <tr>
                <td>1) Nombre Empresa</td>
                <td style="text-align: right;">$9,999</td>
            </tr>
            <tr>
                <td>2) Nombre Empresa</td>
                <td style="text-align: right;">$9,999</td>
            </tr>
            <tr>
                <td>3) Nombre Empresa</td>
                <td style="text-align: right;">$9,999</td>
            </tr>
            <tr>
                <td>4) Nombre Empresa</td>
                <td style="text-align: right;">$9,999</td>
            </tr>
            <tr>
                <td>5) Nombre Empresa</td>
                <td style="text-align: right;">$9,999</td>
            </tr>
        </table>
    </div>

    <div class="monthly-average">
        <div class="average-amount">$9,999</div>
        <div class="average-label">PROMEDIO MENSUAL DE VENTA</div>
        <div style="margin-top: 5px; font-size: 8pt; color: #666;">Últimos 6 meses</div>
    </div>
</body>
</html>
