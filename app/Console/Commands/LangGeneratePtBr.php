<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LangGeneratePtBr extends Command
{
    protected $signature = 'lang:generate-ptbr';
    protected $description = 'Gera resources/lang/pt_br.json a partir de en.json, traduzindo automaticamente e preservando edições existentes';

    public function handle(): int
    {
    $enPath   = resource_path('lang/en.json');
    $ptPath   = resource_path('lang/pt_br.json');
    $ptLegacy = resource_path('lang/pt_BR.json');
        if (!file_exists($enPath)) {
            $this->error('Arquivo en.json não encontrado.');
            return self::FAILURE;
        }
        $en = json_decode(file_get_contents($enPath), true) ?? [];

        // Mapeamento de traduções comuns
        $map = [
            'Welcome to' => 'Bem-vindo ao',
            'Dashboard' => 'Dashboard',
            'Username' => 'Usuário',
            'Password' => 'Senha',
            'Forgot Password?' => 'Esqueceu a senha?',
            'LOGIN' => 'ENTRAR',
            'Verify Code' => 'Verificar Código',
            'Submit' => 'Enviar',
            'Back to Login' => 'Voltar para o Login',
            'Email' => 'E-mail',
            'New Password' => 'Nova Senha',
            'Re-type New Password' => 'Confirmar Nova Senha',
            'Market' => 'Mercado',
            'Outcome' => 'Resultado',
            'Status' => 'Status',
            'User' => 'Usuário',
            'Type' => 'Tipo',
            'Amount' => 'Valor',
            'Action' => 'Ação',
            'Details' => 'Detalhes',
            'Edit' => 'Editar',
            'Delete' => 'Excluir',
            'Update' => 'Atualizar',
            'Add New' => 'Adicionar Novo',
            'Search' => 'Pesquisar',
            'Save Changes' => 'Salvar Alterações',
            'Profile' => 'Perfil',
            'Logout' => 'Sair',
            'Change Password' => 'Alterar Senha',
            'Confirm Password' => 'Confirmar Senha',
            'Language Keywords' => 'Palavras-chave do Idioma',
            'Key' => 'Chave',
            'Value' => 'Valor',
            'Add New Key' => 'Adicionar Nova Chave',
            'Import Keywords' => 'Importar Palavras-chave',
            'System Setting' => 'Configurações do Sistema',
            'Yes' => 'Sim',
            'No' => 'Não',
            'Close' => 'Fechar',
            'Home' => 'Início',
            'Login' => 'Entrar',
            'Register' => 'Registrar',
            'Submit' => 'Enviar',
            'Cancel' => 'Cancelar',
            'Confirm' => 'Confirmar',
            'Settings' => 'Configurações',
            'Language Manager' => 'Gerenciador de Idiomas',
            'Language Name' => 'Nome do Idioma',
            'Language Code' => 'Código do Idioma',
            'Default Language' => 'Idioma Padrão',
            'Default' => 'Padrão',
            'UNSET' => 'REMOVER',
            'SET' => 'DEFINIR',
            'Translate' => 'Traduzir',
            'Import From' => 'Importar de',
            'System' => 'Sistema',
            'Bulk Action' => 'Ação em Massa',
            'Enabled' => 'Ativado',
            'Disabled' => 'Desativado',
            'Apply Filter' => 'Aplicar Filtro',
            'All' => 'Todos',
            'Filter' => 'Filtrar',
            'Date' => 'Data',
            'Method' => 'Método',
            'Charge' => 'Taxa',
            'Approve' => 'Aprovar',
            'Reject' => 'Rejeitar',
            'Download' => 'Baixar',
            'Upload' => 'Enviar',
            'Phone' => 'Telefone',
            'Mobile' => 'Celular',
            'Address' => 'Endereço',
            'City' => 'Cidade',
            'State' => 'Estado',
            'Country' => 'País',
            'Browser' => 'Navegador',
            'OS' => 'SO',
            'Ticket' => 'Ticket',
            'Reply' => 'Responder',
            'Notification' => 'Notificação',
            'Email Setting' => 'Configuração de E-mail',
            'SMS Setting' => 'Configuração de SMS',
            'Push Notification Setting' => 'Configuração de Push',
            'Templates' => 'Modelos',
            'Cookie' => 'Cookie',
            'Maintenance Mode' => 'Modo de Manutenção',
            'Logo' => 'Logo',
            'Favicon' => 'Favicon',
        ];

        // Carregar PT já existente para preservar edições (prioriza pt_br.json, depois pt_BR.json legado)
        $ptExisting = [];
        if (file_exists($ptPath)) {
            $ptExisting = json_decode(file_get_contents($ptPath), true) ?? [];
        } elseif (file_exists($ptLegacy)) {
            $ptExisting = json_decode(file_get_contents($ptLegacy), true) ?? [];
        }

        $translateWord = function (string $word) use ($map) {
            return $map[$word] ?? $word;
        };

        $autoTranslate = function (string $text) use ($map, $translateWord) {
            // Tradução exata
            if (isset($map[$text])) return $map[$text];

            // Preservar placeholders e tags simples
            $placeholders = [];
            $phIndex = 0;
            $safe = preg_replace_callback('/(:\w+|\%s|\%d|\{\w+\}|\:attribute|\:min|\:max)/', function($m) use (&$placeholders, &$phIndex){
                $key = "__PH{$phIndex}__";
                $placeholders[$key] = $m[0];
                $phIndex++;
                return $key;
            }, $text);

            // Tradução token a token simples (melhor esforço)
            $tokens = preg_split('/(\s+)/', $safe, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($tokens as &$t) {
                $trim = trim($t);
                if ($trim === '') continue;
                $t = $translateWord($t);
            }
            $out = implode('', $tokens);

            // Restaurar placeholders
            foreach ($placeholders as $k => $v) {
                $out = str_replace($k, $v, $out);
            }
            return $out;
        };

        $pt = [];
        foreach ($en as $k => $v) {
            // Preserva se já houver tradução diferente de vazio e diferente do inglês
            if (array_key_exists($k, $ptExisting) && $ptExisting[$k] !== '' && $ptExisting[$k] !== $v) {
                $pt[$k] = $ptExisting[$k];
                continue;
            }
            $pt[$k] = $autoTranslate(is_string($v) ? $v : (string)$v);
        }

    $json = json_encode($pt, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    file_put_contents($ptPath, $json);
    $this->info('pt_br.json gerado/atualizado.');
        return self::SUCCESS;
    }
}
