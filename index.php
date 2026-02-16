<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cherry MM • API</title>

<style>

body{

background:#0f0f14;
color:#fff;
font-family:Arial;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
margin:0;

}

.box{

background:#1a1b26;
padding:30px;
border-radius:12px;
box-shadow:0 0 40px rgba(0,0,0,.6);
text-align:center;

}

.ok{

color:#00ff88;
font-size:22px;
margin-bottom:10px;

}

.info{

color:#aaa;
font-size:14px;

}

.endpoint{

margin-top:20px;
font-size:13px;
color:#888;

}

</style>

</head>
<body>

<div class="box">

<div class="ok">

Cherry MM API Online

</div>

<div class="info">

Servidor funcionando corretamente

</div>

<div class="info">

<?php echo date("d/m/Y H:i:s"); ?>

</div>

<div class="endpoint">

Endpoints ativos:

<br><br>

transfer-events.php

<br>

withdraw-validate.php

</div>

</div>

</body>
</html>
