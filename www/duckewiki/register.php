<?php
include_once '../../includes_pl/register.inc.php';
include_once '../../includes_pl/functions.php';
/* ficava em functions.php, mas só é usada aqui */
function esc_url($url) {
	if ('' == $url) {
		return $url;
	}
	$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url); // ?
	$strip = array('%0d', '%0a', '%0D', '%0A'); // ?
	$url = (string) $url; // ?
	$count = 1;
	while ($count) {
		$url = str_replace($strip, '', $url, $count); // ?
	}
	$url = str_replace(';//', '://', $url); // ? ? ? ?
	$url = htmlentities($url);
	$url = str_replace('&amp;', '&#038;', $url);
	$url = str_replace("'", '&#039;', $url);
	if ($url[0] !== '/') {
		// We're only interested in relative links from $_SERVER['PHP_SELF']
		return '';
	} else {
		return $url;
	}
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>DuckeWiki: Cadastro</title>
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script>
        <!--link rel="stylesheet" href="styles/main.css" /-->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">        
        <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    </head>
    <body>
            
        <?php
        if (!empty($error_msg)) {
            echo $error_msg;
        }
        ?>
        <ul>
            <li>Nomes de usuário só podem conter números, letras minúsculas e maiúsculas e underscores (_)</li>
            <li>Emails devem ter um formato de email válido</li>
            <li>Senhas devem ter pelo menos 6 caracteres</li>
            <li>Senhas devem conter
                <ul>
                    <li>Pelo menos uma letra maiúscula (A..Z)</li>
                    <li>Pelo menos uma letra minúscula (a..z)</li>
                    <li>Pelo menos um número (0..9)</li>
                </ul>
            </li>
            <li>A senha e a confirmação devem ser idênticas</li>
        </ul>        
        <div class="span3">    
        <h2>Cadastre-se</h2>
        <form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" method="post" name="registration_form">
            <label>Nome de Usuário</label>
            <input type="text" name='username' id='username' class="span3">

            <label>Email</label>
            <input type="text"  name="email" id="email" class="span3">
            
            <label>Senha</label>
            <input type="password" name="password" id="password" class="span3">

            <label>Confirme a senha:</label>
            <input type="password" name="confirmpwd" id="confirmpwd" class="span3">

            <label><input type="checkbox" name="terms"> I agree with the <a href="#">Terms and Conditions</a>.</label>    
            <input type="button" value="Registar" class="btn btn-primary pull-right" onclick="return regformhash(this.form,this.form.username,this.form.email,this.form.password, this.form.confirmpwd);" />
            
            <div class="clearfix"></div>

            <p>Retornar para a <a href="index.php">página de login</a>.</p>
        </form>
</div>
    </body>
</html>
