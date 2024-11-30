<?php 
//require_once "conexion.php";
require_once "config.php";
require_once "conexion.php";


function ordenes_cargadas(){
	global $tipobd_totvsDev2,$conexion_totvsDev2;
	$hoy = date('Ymd');
	$querycount = "SELECT COUNT(DISTINCT ZC6_OC) AS OC FROM ZC6010 WHERE ZC6_FILIAL='01' AND D_E_L_E_T_<>'*'";
	$rsc = querys($querycount, $tipobd_totvsDev2,$conexion_totvsDev2);
	$v = ver_result($rsc, $tipobd_totvsDev2);
	$oc = $v["OC"];
	
	return $oc;
}
function ordenes_procesadas(){
	global $tipobd_totvsDev2,$conexion_totvsDev2;
	$hoy = date('Ymd');
	$querycount = "SELECT COUNT(DISTINCT ZC6_OC) AS OC FROM ZC6010 WHERE ZC6_FILIAL='01' AND ZC6_OKDIGI='N' AND D_E_L_E_T_<>'*'";
	$rsc = querys($querycount, $tipobd_totvsDev2,$conexion_totvsDev2);
	$v = ver_result($rsc, $tipobd_totvsDev2);
	$oc = $v["OC"];
	
	return $oc;
}
function usuarios_ensistema(){
	global $tipobd_portal,$conexion_portal;
	$hoy = date('Ymd');
	$querycount = "select count(*) as FILAS from USUARIOS";
	$rsc = querys($querycount, $tipobd_portal,$conexion_portal);
	$v = ver_result($rsc, $tipobd_portal);
	$usuarios = $v["FILAS"];
	
	return $usuarios;
}
function ventas(){
	global $tipobd_totvsDev2,$conexion_totvsDev2;
	$hoy = date('Ymd');
	$querycount = "SELECT ROUND(sum(ZC6_VALOR)) AS TOT FROM ZC6010 WHERE ZC6_FILIAL='01' AND D_E_L_E_T_<>'*'";
	$rsc = querys($querycount, $tipobd_totvsDev2,$conexion_totvsDev2);
	$v = ver_result($rsc, $tipobd_totvsDev2);
	$total = "$ ".number_format($v["TOT"],0,',','.');
	
	return $total;
}
?>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal</title>

 <!-- Google Font: Source Sans Pro -->
 <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- SweetAlert2 -->
  <link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Preloader 
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="img/monarch_esencial-remove.png" alt="Monarch" height="100" width="100">
    </div>-->

      <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="principal.php" class="nav-link">Home</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
          <a href="#" class="nav-link">Contact</a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline">
              <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                  <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                  </button>
                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </li>

        <!-- Messages Dropdown Menu -->


        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
            <i class="fas fa-th-large"></i>
          </a>
        </li> 
      <li class="nav-item">
          <a class="nav-link" name="logout" id="logout" title="Cerrar Sesion" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
            <i class="fas fa-power-off"></i>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="principal.php" class="brand-link">
        <img src="img/monarch.ico" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text text-light font-weight-lighter">Portal</span>      
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="dist/img/avatar5.png" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info" id='current-user'>
            <!-- <a href="#" class="d-block">Gonzalo Puyol</a> -->
          </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
          <div class="input-group" data-widget="sidebar-search">
            <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
              <button class="btn btn-sidebar">
                <i class="fas fa-search fa-fw"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2"   id="sidebar">
    
          
            
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    
    <div class="content-wrapper main_new">
      <!-- Content Header (Page header) -->
 <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Dashboard v1</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
              <h3 id="ventas"><?php $ordenes =  ordenes_cargadas();echo $ordenes;?></h3>

                <p>Ordenes Cargadas</p>
              </div>
              <div class="icon">
              <i class="fas fa-shopping-cart"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
              <h3 id="ventas"><?php $ordenes =  ordenes_procesadas();echo $ordenes;?></h3>

                <p>Ordenes Pendientes por Procesar</p>
              </div>
              <div class="icon">
                <i class="fas fa-clipboard-check"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
              <h3 id="ventas"><?php $usuarios =  usuarios_ensistema();echo $usuarios;?></h3>

                <p>Usuarios Registrados</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
              <h3 id="ventas"><?php $ventas =  ventas();echo $ventas;?></h3>

                <p>Total Ventas</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
        <section class="content">
           <!-- BAR CHART -->
           
            <div class="card card-success">
                <div class="card-header">
                  <h3 class="card-title">Ventas Cargadas Año 2024</h3>

                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <div class="chart">
                    <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                  </div>
                </div>
                <!-- /.card-body -->

          </div>
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6">
            <!-- AREA CHART -->

            <!-- /.card -->

            <!-- DONUT CHART -->
            <div class="card card-danger">
              <div class="card-header">
                <h3 class="card-title">Ordenes de compra por Cliente</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

            

          </div>
          <!-- /.col (LEFT) -->
          <div class="col-md-6">
            <!-- LINE CHART -->
            <div class="card card-info">
              <div class="card-header">
                <h3 class="card-title">Articulos Vendidos</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

           
            <!-- /.card -->

            <!-- STACKED BAR CHART -->
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title">Stacked Bar Chart</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="chart">
                  <canvas id="stackedBarChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

          </div>
          <!-- /.col (RIGHT) -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
        </div>
  </div> <!-- aqui termina -->
</body>

    <!-- Main content -->
  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
  
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2021 <a href="#">Informatica Monarch</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1
    </div>
  </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!--<script src="jquery-ui-1.11.4/jquery-ui.js" type="text/javascript"></script>-->

<!-- ChartJS -->
<script src="plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
 <script src="js/principal.js" type="text/javascript"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<!-- <script src="../../plugins/chart.js/Chart.min.js"></script> -->
<!-- InputMask -->
<script src="plugins/inputmask/jquery.inputmask.min.js"></script>
<!--<script src="plugins/sweetalert2/sweetalert2.js"></script>
 SweetAlert2 -->
<!--GRAFICO 1 -->
 <?php

global $tipobd_totvsDev2, $conexion_totvsDev2;
$query = "SELECT trim(ZC6_CANAL),ACY_DESCRI, ROUND(SUM(ZC6_VALOR)) AS VALOR 
FROM ZC6010 Z  LEFT JOIN  ACY010 A ON Z.ZC6_CANAL=A.ACY_GRPVEN
                                      and a.ACY_FILIAL=' '
                                      and a.D_E_L_E_T_<>'*'
WHERE ZC6_FILIAL='01' AND  Z.D_E_L_E_T_<>'*'
--AND trim(ACY_GRPVEN) IN ('4001','4002','4003')
GROUP BY trim(ZC6_CANAL),ACY_DESCRI
ORDER BY trim(ZC6_CANAL)";
$stid = querys($query, $tipobd_totvsDev2, $conexion_totvsDev2);
// Obtener los resultados y almacenarlos en arrays
$labels = [];
$values = [];
while ($row = ver_result($stid, $tipobd_totvsDev2)) {
    $labels[] = $row['ACY_DESCRI'];
    $values[] = $row['VALOR'];
}
// print_r($labels);
  oci_free_statement($stid);
  oci_close($conexion_totvsDev2);
    ?>
 <script>
  $(function () {
    // Obtener los datos de etiquetas y valores desde PHP
    const labels = <?php echo json_encode($labels); ?>;
    const values = <?php echo json_encode($values); ?>;

    // Construir barChartData con los datos obtenidos de SQL
    const barChartData = {
      labels: labels,
      datasets: [
        {
          label: 'Ventas',
          backgroundColor: 'rgba(60,141,188,0.9)',
          borderColor: 'rgba(60,141,188,1)',
          borderWidth: 1,
          data: values
        }
      ]
    };

    // Opciones del gráfico de barras con formato de separador de miles
    const barChartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      datasetFill: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            // Formatear los valores con separadores de miles
            callback: function (values) {
              return values.toLocaleString();
            }
          }
        }
      }
    };

    // Crear el gráfico de barras
    const barChartCanvas = $('#barChart').get(0).getContext('2d');
    new Chart(barChartCanvas, {
      type: 'bar',
      data: barChartData,
      options: barChartOptions
    });
  });

</script>
<!--FIN GRAFICO 1 -->

<!--GRAFICO 2 -->
<?php

global $tipobd_totvsDev2, $conexion_totvsDev2;
$query = "SELECT 
            SUBSTR(ZC6_FEMIS,5,2) AS NMES,TO_CHAR(TO_DATE(EXTRACT(MONTH FROM TO_DATE(ZC6_FEMIS, 'YYYY/MM/DD')), 'MM'), 'Month') AS MES,
            COUNT(ZC6_INTCOD) AS CODIGO
            FROM 
            ZC6010 
            GROUP BY 
            SUBSTR(ZC6_FEMIS,5,2), TO_CHAR(TO_DATE(EXTRACT(MONTH FROM TO_DATE(ZC6_FEMIS, 'YYYY/MM/DD')), 'MM'), 'Month')
            ORDER BY 1";
$stid = querys($query, $tipobd_totvsDev2, $conexion_totvsDev2);
// Obtener los resultados y almacenarlos en arrays
$labels_line = [];
$values_line = [];
while ($row = ver_result($stid, $tipobd_totvsDev2)) {
    $labels_line[] = $row['MES'];
    $values_line[] = $row['CODIGO'];
}
// print_r($labels);
  oci_free_statement($stid);
  oci_close($conexion_totvsDev2);
    ?>
<script>
  $(document).ready(function() {
    // Datos de PHP en JavaScript
    const labels = <?php echo json_encode($labels_line); ?>;
    const data = <?php echo json_encode($values_line); ?>;

    // Configurar los datos para el gráfico de líneas
    const lineChartData = {
      labels: labels,
      datasets: [
        {
          label: 'Articulos',
          backgroundColor: 'rgba(60,141,188,0.3)',
          borderColor: 'rgba(60,141,188,1)',
          pointRadius: false,
          pointColor: '#3b8bba',
          pointStrokeColor: 'rgba(60,141,188,1)',
          pointHighlightFill: '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data: data,
          fill: false
        }
      ]
    };

    const lineChartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      datasetFill: false
    };

    // Crear el gráfico de línea
    const lineChartCanvas = $('#lineChart').get(0).getContext('2d');
    new Chart(lineChartCanvas, {
      type: 'line',
      data: lineChartData,
      options: lineChartOptions
    });
  });
</script>
<!--FIN GRAFICO 2 -->
<!--GRAFICO 3 -->

<?php

global $tipobd_totvsDev2, $conexion_totvsDev2;
$query = "SELECT trim(ZC6_CANAL),ACY_DESCRI, COUNT(DISTINCT ZC6_OC) AS VALOR 
            FROM ZC6010 Z  LEFT JOIN  ACY010 A ON Z.ZC6_CANAL=A.ACY_GRPVEN
                                                  and a.ACY_FILIAL=' '
                                                  and a.D_E_L_E_T_<>'*'
            WHERE ZC6_FILIAL='01' AND  Z.D_E_L_E_T_<>'*'
            --AND trim(ACY_GRPVEN) IN ('4001','4002','4003')
            GROUP BY trim(ZC6_CANAL),ACY_DESCRI";
$stid = querys($query, $tipobd_totvsDev2, $conexion_totvsDev2);
// Obtener los resultados y almacenarlos en arrays
$labels_donut = [];
$values_donut = [];
while ($row = ver_result($stid, $tipobd_totvsDev2)) {
    $labels_donut[] = $row['ACY_DESCRI'];
    $values_donut[] = $row['VALOR'];
}
// print_r($labels);
  oci_free_statement($stid);
  oci_close($conexion_totvsDev2);
    ?>
<script>
  $(document).ready(function() {
    // Datos de PHP en JavaScript
    const labels = <?php echo json_encode($labels_donut); ?>;
    const data = <?php echo json_encode($values_donut); ?>;

    // Configuración de los datos del gráfico de dona
    const donutData = {
      labels: labels,
      datasets: [
        {
          data: data,
          backgroundColor: ['#404ced', '#8500a6', '#48cf6c', '#00c0ef', '#3c8dbc', '#d2d6de'],
        }
      ]
    };

    // Configuración de las opciones del gráfico
    const donutOptions = {
      maintainAspectRatio: false,
      responsive: true,
    };

    // Crear el gráfico de dona
    const donutChartCanvas = $('#donutChart').get(0).getContext('2d');
    new Chart(donutChartCanvas, {
      type: 'doughnut',
      data: donutData,
      options: donutOptions
    });
  });
</script>
<!-- </body> -->
</html>
<style>
  body {
      display: flex;
      flex-direction: column;
  }
  

</style>
