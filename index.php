<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Cálculo de Pensión Ley 73</title>
</head>

<body>
    <h1>Calculadora de Pensión IMSS - Ley 73</h1>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $salario = floatval($_POST['salario']);
        $edad = intval($_POST['edad']);
        $semanas = intval($_POST['semanas']);
        $esposa = $_POST['esposa'] ?? 'no';
        $hijos = intval($_POST['hijos'] ?? 0);
        $ascendientes = $_POST['ascendientes'] ?? 'no';
        // Validación de edad y semanas
        if ($edad < 60 || $semanas < 500) {
            echo "<h2>No cumple con los requisitos para cotizar.</h2>";
            if($edad < 60 && $semanas < 500){
                echo "<p>Debe tener al menos 60 años de edad .</p>";
                echo "<p>Debe tener al menos 500 semanas cotizadas.</p>";
            }else if ($edad < 60){
                echo "<p>Debe tener al menos 60 años de edad .</p>";
            }else{
                echo "<p>Debe tener al menos 500 semanas cotizadas.</p>";
            }
            echo "<br><a href='index.php'>Volver</a>";
            exit; 
        }

        $salarioMinimoDF = 278.0; // Se ajusta según el año
        $ayudaAsistencial = 0;
        $decreto2004 = 1.11; // Ajuste de 2004 preguntas

        function calcularPensionLey73($salarioMensual, $salarioMinimoDF, $semanas, $edadRetiro, $ayudaAsistencial, $esposa, $hijos, $ascendientes, $decreto2004)
        {
            $tabla = [
                [0.01, 1.00, 80.00, 0.563],
                [1.01, 1.25, 77.11, 0.814],
                [1.26, 1.50, 58.18, 1.178],
                [1.51, 1.75, 49.23, 1.430],
                [1.76, 2.00, 42.67, 1.615],
                [2.01, 2.25, 37.65, 1.756],
                [2.26, 2.50, 33.68, 1.868],
                [2.51, 2.75, 30.48, 1.958],
                [2.76, 3.00, 27.83, 2.033],
                [3.01, 3.25, 25.60, 2.096],
                [3.26, 3.50, 23.70, 2.149],
                [3.51, 3.75, 22.07, 2.195],
                [3.76, 4.00, 20.65, 2.235],
                [4.01, 4.25, 19.39, 2.271],
                [4.26, 4.50, 18.29, 2.302],
                [4.51, 4.75, 17.30, 2.330],
                [4.76, 5.00, 16.41, 2.355],
                [5.01, 5.25, 15.61, 2.377],
                [5.26, 5.50, 14.88, 2.398],
                [5.51, 5.75, 14.22, 2.416],
                [5.76, 6.00, 13.62, 2.433],
                [6.01, 25.00, 13.00, 2.450],
            ];

            //Paso 1
            $salarioDiario = $salarioMensual / 30.4166667;
            $salarioVsmgdf = $salarioDiario / $salarioMinimoDF;

            //Paso 2
            $cuantiaBasica = 0;
            $incrementoAnual = 0;

            foreach ($tabla as $rango) {
                if ($salarioVsmgdf >= $rango[0] && $salarioVsmgdf <= $rango[1]) {
                    $cuantiaBasica = $rango[2] / 100;
                    $incrementoAnual = $rango[3] / 100;
                    break;
                }
            }

            $incrementosAnuales = max(0, round(($semanas - 500) / 52));
            $porcentajeTotalIncrementos = $incrementoAnual * $incrementosAnuales;

            $importeCB = $salarioDiario * $cuantiaBasica * 365 * $decreto2004;
            $importeIA = $salarioDiario * $porcentajeTotalIncrementos * 365 * $decreto2004;
            $totalVejez = $importeCB + $importeIA;

            $porcentajesEdad = [
                60 => 0.75,
                61 => 0.80,
                62 => 0.85,
                63 => 0.90,
                64 => 0.95,
                65 => 1.00,
            ];
            $porcentajeEdad = $porcentajesEdad[$edadRetiro] ?? 0.75;
            $importeEdad = $totalVejez * $porcentajeEdad;

            if ($esposa == 'S' || $hijos > 0 || $ascendientes == 'S') {

                if ($esposa == 'S') {
                    $ayudaAsistencial = 15;
                }
                if ($hijos > 0) {
                    $ayudaAsistencial += ($hijos * 10); // hijos
                }
                if ($ascendientes == 'S') {
                    $ayudaAsistencial += $ayudaAsistencial + 10;
                }
            } else {
                $ayudaAsistencial = 15;
            }

            var_dump($ayudaAsistencial);

            $importeAyuda = $importeEdad * ($ayudaAsistencial / 100);
            $pensionMensual = ($importeEdad + $importeAyuda) / 12;

            return [
                'pensionMensual' => $pensionMensual,
                'importeEdad' => $importeEdad / 12,
                'importeAyuda' => $importeAyuda / 12
            ];
        }
        $resultados = calcularPensionLey73($salario, $salarioMinimoDF, $semanas, $edad, $ayudaAsistencial, $esposa, $hijos, $ascendientes, $decreto2004);

        $pensionMensual = $resultados['pensionMensual'];
        $importeEdad = $resultados['importeEdad'];
        $importeAyuda = $resultados['importeAyuda'];

        echo "<h2>Resultado:</h2>";
        echo "<p><strong>Aguinaldo anual: $" . number_format($importeEdad, 2) . "</strong></p>";
        if ($esposa == 'S' || $hijos > 0 || $ascendientes == 'S') {
            echo "<p><strong>Asignacion Familiar $" . number_format($importeAyuda, 2) . "</strong></p>";
        } else {
            echo "<p><strong>Asignacion Asistencial $" . number_format($importeAyuda, 2) . "</strong></p>";
        }
        echo "<p><strong>Pensión estimada mensual: $" . number_format($pensionMensual, 2) . "</strong></p>";
        echo "<br><a href='index.php'>Volver</a>";
    } else {
    ?>
        <form method="post">
            <label>Salario mensual: <input type="number" name="salario" step="0.01" required></label><br>
            <label>Edad: <input type="number" name="edad" required></label><br>
            <label>Semanas cotizadas: <input type="number" name="semanas" required></label><br>
            <label>¿Tiene esposa/o?
                <select name="esposa">
                    <option value="N">No</option>
                    <option value="S">Sí</option>
                </select>
            </label><br>
            <label>Número de hijos: <input type="number" name="hijos" min="0"></label><br>
            <label>¿Tiene ascendientes dependientes?
                <select name="ascendientes">
                    <option value="N">No</option>
                    <option value="S">Sí</option>
                </select>
            </label><br><br>
            <input type="submit" value="Calcular Pensión">
        </form>
    <?php } ?>
</body>

</html>