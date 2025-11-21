<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cotización</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background-color: #fff;
            font-weight: 500;
        }

        h1,
        h2,
        h3 {
            color: #0748A5;
        }

        .header {
            border-bottom: 2px solid #EDB312;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .quote-info,
        .client-info {
            margin-bottom: 10px;
        }

        .quote-info table,
        .client-info table {
            width: 100%;
        }

        .quote-info td,
        .client-info td {
            padding: 5px 10px;
        }

        .products {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .products th,
        .products td {
            border-bottom: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }

        .table-quote tr td {

            padding: 2px !important;
        }

        .products th {
            background-color: #e2e8f0;
            color: #1e293b;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #64748b;
            text-align: center;
        }

        .total {
            font-weight: bold;
            font-size: 14px;
            color: #0f172a;
            border: none !important;
        }

        .totales-quote {
            text-align: right;
        }

        .totales-quote p {
            font-weight: bold;
            color: #0748A5;
            margin: 2px;
        }
    </style>
</head>

<body>

    <div class="header">
        <table width="100%">
            <tr>
                <td align="left" style="vertical-align: top;">
                    <h1 style="margin: 0;">Cotización</h1>
                    <p style="margin: 4px 0;">
                        <span style="font-size: 11px;">
                            <strong>MULTISERVICIO INTEGRAL</strong><br>
                            <strong>RFC:</strong> CAEP8706288S1<br>
                            <strong>Teléfono:</strong> 33 2409 8013<br>
                            <strong>Contacto:</strong> ventas@multiserinte.com
                        </span>
                        <br>
                        <strong>Fecha:</strong> <?= $quote['create_at'] ?>
                        <br>
                        <strong>Folio:</strong> #<?= $quote['id'] ?>
                    </p>
                </td>
                <td align="right" style="vertical-align: top;">
                    <img src="<?php echo  __DIR__ . "/../../img/images/Logo-multiserinte.png" ?>" width="110" style="margin-bottom: 5px;"><br>
                </td>
            </tr>
        </table>
    </div>
    <div class="quote-info">
        <h3>Información de la cotización</h3>
        <table class="table-quote">
            <tr>
                <td><strong>Cliente:</strong> <?= $client['name'] ?></td>
                <td><strong>RFC:</strong> <?= $client['rfc'] ?></td>
                <td><strong>Correo:</strong> <?= $client['emails'] ?></td>
            </tr>
            <tr>
                <td><strong>Tipo:</strong> <?= $quote['type'] ?></td>
                <td><strong>Vigencia:</strong> <?= $quote['days'] ?> días</td>
                <td><strong>Estatus:</strong> <?= $quote['status'] ?></td>
            </tr>
        </table>
    </div>

    <div class="client-info">
        <h3>Productos cotizados</h3>
        <table class="products">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>No. Parte</th>
                    <th>Marca</th>
                    <th>Cantidad</th>
                    <th>P. Unitario</th>
                    <th align="right">Importe</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                $subtotal = 0;
                $totalIva = 0;
                foreach ($products as $i => $p):

                    $import = floatval($p['import']);
                    $iva = $import / 1.16;

                    $subtotal += $import - $iva;
                    $total += $import;
                    $totalIva += $iva;
                ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= $p['name'] ?></td>
                        <td><?= $p['part_number'] ?></td>
                        <td><?= $p['brand'] ?></td>
                        <td align="center"><?= $p['quantity'] ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td align="right">$<?= number_format($p['import'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <div class="totales-quote">
            <p><strong>Subtotal:</strong> $<?= number_format($subtotal, 2) ?></p>
            <p><strong>Iva:</strong> $<?= number_format($totalIva, 2) ?></p>
            <p><strong>Total:</strong> $<?= number_format($total, 2) ?></p>
        </div>
    </div>

    <?php if (!empty($quote['notes'])): ?>
        <div style="margin-top: 25px;">
            <h3>Notas</h3>
            <p><?= nl2br($quote['notes']) ?></p>
        </div>
    <?php endif; ?>

    <div class="footer">
        * Precios sujetos a cambios sin previo aviso. <br>
        * Cotización vigente por <?= $quote['days'] ?> días.<br><br>
        Gracias por confiar en nosotros.
    </div>

</body>

</html>