<?php


require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


$cpf = isset($_GET['cpf']) ? preg_replace('/[^0-9]/', '', $_GET['cpf']) : null;

if (!$cpf) {
    die("Erro Crítico: O parâmetro identificador CPF está ausente.");
}


try {
 
   $caminhoBanco = dirname(__DIR__) . '/db.sqlite3';
    
    $pdo = new PDO("sqlite:" . $caminhoBanco);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
   
    $stmt = $pdo->prepare("SELECT * FROM usuarios_aluno WHERE cpf = :cpf LIMIT 1");
    $stmt->execute(['cpf' => $cpf]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$aluno) {
        die("Erro: Não foi encontrada nenhuma matrícula ativa para o CPF: " . htmlspecialchars($cpf));
    }

} catch (PDOException $e) {
    die("Erro de conexão ao banco de dados unificado: " . $e->getMessage());
}


$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); 

$dompdf = new Dompdf($options);


$html = '
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comprovante SISSEL</title>
    <style>
        body { font-family: "Helvetica Neue", Arial, sans-serif; color: #2d3748; margin: 25px; line-height: 1.5; }
        .cabecalho { text-align: center; border-bottom: 3px solid #002244; padding-bottom: 12px; margin-bottom: 25px; }
        .cabecalho h1 { color: #002244; margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 0.5px; }
        .cabecalho p { margin: 4px 0 0 0; color: #718096; font-size: 13px; }
        .status-box { background-color: #f0fff4; border: 1px solid #c6f6d5; color: #22543d; padding: 12px; border-radius: 4px; font-size: 13px; margin-bottom: 25px; text-align: center; }
        .bloco-titulo { background-color: #f7fafc; border-left: 4px solid #002244; padding: 5px 10px; font-weight: bold; color: #002244; text-transform: uppercase; font-size: 13px; margin-top: 20px; }
        .tabela-dados { width: 100%; margin-top: 8px; border-collapse: collapse; }
        .tabela-dados td { padding: 6px 4px; font-size: 13px; vertical-align: top; }
        .negrito { font-weight: bold; color: #4a5568; }
        .area-assinatura { margin-top: 55px; text-align: center; font-size: 13px; }
        .rodape { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; font-size: 10px; color: #a0aec0; border-top: 1px solid #e2e8f0; padding-top: 6px; }
    </style>
</head>
<body>

    <div class="cabecalho">
        <h1>SISSEL - Sistema Unificado de Matrícula</h1>
        <p>Comprovante Eletrônico de Confirmação de Cadastro</p>
    </div>

    <div class="status-box">
        <strong>Sucesso!</strong> A ficha cadastral e os documentos digitais foram anexados e processados pelo sistema.
    </div>

    <div class="bloco-titulo">Dados do Aluno</div>
    <table class="tabela-dados">
        <tr>
            <td width="50%"><span class="negrito">Nome Completo:</span><br>' . htmlspecialchars($aluno['nome_completo']) . '</td>
            <td width="50%"><span class="negrito">Data de Nascimento:</span><br>' . date('d/m/Y', strtotime($aluno['data_nascimento'])) . '</td>
        </tr>
        <tr>
            <td><span class="negrito">Inscrição CPF:</span><br>' . htmlspecialchars($aluno['cpf']) . '</td>
            <td><span class="negrito">Telefone Móvel:</span><br>' . htmlspecialchars($aluno['telefone']) . '</td>
        </tr>
        <tr>
            <td colspan="2"><span class="negrito">Mãe / Responsável Legal:</span><br>' . htmlspecialchars($aluno['nome_mae']) . '</td>
        </tr>
    </table>

    <div class="bloco-titulo">Validação de Sistema</div>
    <table class="tabela-dados">
        <tr>
            <td><span class="negrito">Situação Cadastral:</span> Em análise</td>
        </tr>
        <tr>
            <td><span class="negrito">Data e hora da matrícula:</span> ' . date('d/m/Y H:i:s') . '</td>
        </tr>
    </table>

    <div class="area-assinatura">
        <p> ' . htmlspecialchars($aluno['nome_completo']) . ' </p>
        <p>__________________________________________________________________</p>
        <p>Assinatura do Candidato ou Responsável</p>
    </div>

    <div class="rodape">
        Este documento é um recibo eletrônico de envio de dados. A validação final dar-se-á após a conferência documental na secretaria escolar.
    </div>

</body>
</html>
';


$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("comprovante_sissel_" . $cpf . ".pdf", array("Attachment" => false));
exit;