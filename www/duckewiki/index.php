<?php
include_once '../../includes_pl/db_connect.php';
include_once '../../includes_pl/functions.php';

// este é o lugar certo pra isso?
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
session_save_path($sessionsavepath); // não funciona com caminho relativo ./sessions por quê?
sec_session_start();
$loginErro = login_error($conn);
if (!$loginErro) {
	$logged = 'in';
	session_regenerate_id(true); // apenas aqui (ou em main.php? index.php não aparece na lista do F5)
} else {
	$logged = 'out';
}
?>
<!DOCTYPE html>
<html>
    <head>
		<meta charset='UTF-8'>
        <title>DuckeWiki - Log In</title>        
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script> 

        <link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
        <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>


        <link rel="stylesheet" type="text/css" href="css/login.css" >        

    </head>
    <body onload='aoCarregar()'>        
        <div aria-hidden="false" style="display: block;" class="modal fade in" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-backdrop fade in"></div>

          <div class="modal-dialog">
                <div class="loginmodal-container">                                
                    <h1>Login </h1><br>
                    <form action="process_login.php" method="post" name="login_form">
                        <input type="text" name="email" placeholder="Email">
                        <input type="password" name="password" placeholder="Senha" id="password">
                        <input type="submit" name="login" class="login loginmodal-submit" value="Login" onclick="formhash(this.form, this.form.password);">
                    </form>
                    
                      <div class="login-help">
                        <a href='register.php'> Registre-se | </a> <a href="#"> Esqueci minha senha</a>
                      </div>

                <?php    
                    if (!$loginErro) {
                        echo '<p>Currently logged ' . $logged . ' as <i>' . htmlentities($_SESSION['username']) . '</i>. <a href=\'main.php\'>Entrar</a>.</p>';
                        echo '<p>Deseja trocar de usuário? <a href="logout.php">Log out</a>.</p>';
                    } else {
                        echo '<p>Currently logged ' . $logged . '.</p>';        
                    }
                ?>      
                <?php
                    if (isset($_GET['error'])) {
                        echo '<p class="error">Erro ao tentar entrar!</p>';
                    }
                ?>     
                </div>
        </div>
          
        
    </body>
</html>
