<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado

require_once __DIR__ . "/../../../vendor/autoload.php";

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../'); // Apunta a la ubicación de tu .env
$dotenv->load();
?>

<!doctype html>
<html>

<head>

	<link rel="shortcut icon" href="<?php echo $_ENV['SERVER'] ?>/img/Monograma-login.ico">
	<title>Softlogis</title>
	<?php $time = time() ?>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=8;FF=3;OtherUA=4" />
	<meta http-equiv="Expires" content="0">
	<meta http-equiv="Last-Modified" content="0">
	<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
	<meta http-equiv="Pragma" content="no-cache">
	<script type="module" src="<?php echo $_ENV['SERVER'] ?>/assets/js/routes.js?q=8"></script>
	<script type="module" src="<?php echo $_ENV['SERVER'] ?>/assets/js/classMenu.js"></script>
	<script src="<?php echo $_ENV['SERVER'] ?>/assets/js/events.js?q=3"></script>
	<!-- Incluir jQuery desde el CDN de Google -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.7/handlebars.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
		crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
		integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p"
		crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
		integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF"
		crossorigin="anonymous"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Rajdhani&display=swap" rel="stylesheet">
	<link href="<?php echo $_ENV['SERVER'] ?>/assets/css/ui/navbar.css?q=<?php echo $time ?>" rel="stylesheet">
	<link href="<?php echo $_ENV['SERVER'] ?>/assets/css/ui/forms.css?q=<?php echo $time ?>" rel="stylesheet">
	<link href="<?php echo $_ENV['SERVER'] ?>/assets/css/ui/labels.css?q=<?php echo $time ?>" rel="stylesheet">
	<link href="<?php echo $_ENV['SERVER'] ?>/assets/css/ui/ui.css?q=<?php echo $time ?>" rel="stylesheet">
	<link href="<?php echo $_ENV['SERVER'] ?>/assets/css/ui/animations.css?q=<?php echo $time ?>" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>