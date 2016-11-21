<?php
$error = filter_input(INPUT_GET, 'err', $filter = FILTER_SANITIZE_STRING);
 
if (! $error) {
    $error = 'Ops! Um erro desconhecido aconteceu.';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>DuckeWiki: Erro</title>
        <!--link rel="stylesheet" href="styles/main.css" /-->
    </head>
    <body>
        <h1>Algo deu errado</h1>
        <p class="error"><?php echo $error; ?></p>  
    </body>
</html>
