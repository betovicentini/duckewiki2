ESTA VERSÃO ESTÁ EM DESENVOLVIMENTO E TODAVIA É BASTANTE INCOMPLETA

INSTALACAO
		Requisitos
			Servidor/localhost com Apache e PostGres e PostGis instalados
		Faça o download dos arquivos e coloque no seu localhost:
			1) includes_pl - pasta que deve ficar na mesma pasta da pasta pública (www ou html) do seu servidor
			2) (www) duckewiki - pasta com os arquivos que devem ficar dentro da pasta pública do seu servidor. NOTA: o usuário do php (e.g. _www) deve ter permissão de escrita para esta pasta para criar pastas e salvar arquivos.
			3) dbase - contém um arquivo .sql com uma base de dados para testes e desenvolvimento
		Se for a primeira vez que estiver fazendo esta instalação neste servidor, crie um usuário no postgres que tenha a mesma senha que está especificada em includes_pl/psl_config.php (e.g. psql -h localhost -U postgres -W duckewiki e depois 
create user php_robot with password 'fh4j9e5e3x4h7s6';)
		Instalar a base de dados no postgres (e.g. psql -p5432 -h localhost -U postgres <  dbase/duckewiki_dev.sql;) - o arquivo inclui um DROP DATABASE duckewiki, portanto, irá apagar a base se já estiver instalada
		Editar o arquivo includes_pl/defs.php  e ajustar para suas definições (incluindo proxy se houver)

USO 
		Iniciar o apache
		URL: localhost/duckewiki
		Registrar o seu novo usuário e começar a explorar a base.
		admin@gmail.com   Admin123  (este usuário está cadastrado em Projetos) e pode ver dados de Plantas e Especimenes
