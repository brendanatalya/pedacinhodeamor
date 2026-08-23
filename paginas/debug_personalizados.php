<?php
/**
 * ARQUIVO DE DEBUG - Verifique os paths do seu sistema
 * Acesse: http://seu-site.com/pedacinhodeamor/paginas/debug.php
 */
if (!isset($_SESSION)) session_start();

$current_file = __FILE__;
$current_dir = dirname($current_file);
$parent_dir = dirname($current_dir);

// Tentar encontrar config.php
$config_path_1 = $parent_dir . '/config.php';
$config_path_2 = $current_dir . '/../config.php';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Verificar Paths</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #252526;
            padding: 20px;
            border-radius: 8px;
        }
        h1 { color: #4ec9b0; margin-top: 0; }
        h2 { color: #9cdcfe; margin-top: 30px; }
        .ok { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #9cdcfe; }
        .code {
            background: #1e1e1e;
            padding: 10px;
            border-left: 3px solid #4ec9b0;
            margin: 10px 0;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #464647;
        }
        th { background: #2d2d30; }
        tr:hover { background: #2d2d30; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug - Verificação de Paths</h1>

        <h2>📁 Caminhos do Sistema</h2>
        <table>
            <tr>
                <th>Descrição</th>
                <th>Caminho</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Arquivo Atual (debug.php)</td>
                <td><span class="code"><?php echo $current_file; ?></span></td>
                <td><span class="ok">✓</span></td>
            </tr>
            <tr>
                <td>Diretório Atual</td>
                <td><span class="code"><?php echo $current_dir; ?></span></td>
                <td><span class="ok">✓</span></td>
            </tr>
            <tr>
                <td>Diretório Pai</td>
                <td><span class="code"><?php echo $parent_dir; ?></span></td>
                <td><span class="ok">✓</span></td>
            </tr>
        </table>

        <h2>🔧 Verificação de Arquivos Críticos</h2>
        <table>
            <tr>
                <th>Arquivo</th>
                <th>Caminho</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>config.php (opção 1)</td>
                <td><span class="code"><?php echo $config_path_1; ?></span></td>
                <td>
                    <?php if (file_exists($config_path_1)): ?>
                        <span class="ok">✓ ENCONTRADO</span>
                    <?php else: ?>
                        <span class="error">✗ NÃO ENCONTRADO</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>config.php (opção 2)</td>
                <td><span class="code"><?php echo $config_path_2; ?></span></td>
                <td>
                    <?php if (file_exists($config_path_2)): ?>
                        <span class="ok">✓ ENCONTRADO</span>
                    <?php else: ?>
                        <span class="error">✗ NÃO ENCONTRADO</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>personalizados.php</td>
                <td><span class="code"><?php echo $current_dir . '/personalizados.php'; ?></span></td>
                <td>
                    <?php if (file_exists($current_dir . '/personalizados.php')): ?>
                        <span class="ok">✓ ENCONTRADO</span>
                    <?php else: ?>
                        <span class="error">✗ NÃO ENCONTRADO</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>add_carrinho.php</td>
                <td><span class="code"><?php echo $current_dir . '/add_carrinho.php'; ?></span></td>
                <td>
                    <?php if (file_exists($current_dir . '/add_carrinho.php')): ?>
                        <span class="ok">✓ ENCONTRADO</span>
                    <?php else: ?>
                        <span class="error">✗ NÃO ENCONTRADO</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>database.php</td>
                <td><span class="code"><?php echo $parent_dir . '/inc/database.php'; ?></span></td>
                <td>
                    <?php if (file_exists($parent_dir . '/inc/database.php')): ?>
                        <span class="ok">✓ ENCONTRADO</span>
                    <?php else: ?>
                        <span class="error">✗ NÃO ENCONTRADO</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <h2>ℹ️ Informações do PHP</h2>
        <table>
            <tr>
                <th>Informação</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Versão PHP</td>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <td>Sessão Ativa</td>
                <td><?php echo isset($_SESSION['logado']) && $_SESSION['logado'] ? '<span class="ok">✓ SIM</span>' : '<span class="info">✗ Não logado</span>'; ?></td>
            </tr>
            <tr>
                <td>Error Reporting</td>
                <td><?php echo ini_get('error_reporting'); ?></td>
            </tr>
        </table>

        <h2>🧪 Teste de add_carrinho.php</h2>
        <p>Clique no botão para testar se add_carrinho.php retorna JSON válido:</p>
        <button onclick="testarAddCarrinho()" style="padding: 10px 20px; background: #0e639c; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
            Testar add_carrinho.php
        </button>
        <div id="resultado-teste" style="margin-top: 20px;"></div>

        <h2>📋 Próximos Passos</h2>
        <ol>
            <li>Verifique se todos os arquivos críticos foram encontrados (status ✓)</li>
            <li>Se algum arquivo não foi encontrado, mova-o para o local correto</li>
            <li>Clique no botão de teste para verificar se add_carrinho.php funciona</li>
            <li>Acesse: <a href="./personalizados.php" style="color: #4ec9b0;">personalizados.php</a></li>
        </ol>

        <h2>📞 Se tiver problemas</h2>
        <p>Cole o conteúdo desta página no seu relatório de erro.</p>
    </div>

    <script>
        async function testarAddCarrinho() {
            const div = document.getElementById('resultado-teste');
            div.innerHTML = '<span class="info">Testando...</span>';
            
            try {
                const formData = new FormData();
                formData.append('product_id', '0');
                formData.append('quantity', '1');
                
                const response = await fetch('./add_carrinho.php', {
                    method: 'POST',
                    body: formData
                });
                
                const contentType = response.headers.get('content-type');
                const text = await response.text();
                
                if (contentType && contentType.includes('application/json')) {
                    const data = JSON.parse(text);
                    div.innerHTML = '<div style="background: #1e1e1e; padding: 10px; border-left: 3px solid #4ec9b0;"><span class="ok">✓ JSON VÁLIDO</span><br><br><strong>Resposta:</strong><br><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
                } else {
                    div.innerHTML = '<div style="background: #1e1e1e; padding: 10px; border-left: 3px solid #f48771;"><span class="error">✗ NÃO É JSON</span><br><br><strong>Content-Type:</strong> ' + (contentType || 'não definido') + '<br><br><strong>Resposta:</strong><br><pre>' + text.substring(0, 500) + '</pre></div>';
                }
            } catch (error) {
                div.innerHTML = '<div style="background: #1e1e1e; padding: 10px; border-left: 3px solid #f48771;"><span class="error">✗ ERRO:</span><br><br>' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>