<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class LangInlinePtBr extends Command
{
    protected $signature = 'lang:inline-ptbr {--dry-run : Apenas exibe o que seria alterado} {--path= : Pasta base (default: resources/views)}';
    protected $description = 'Substitui __(), @lang() e trans() em Blades por textos em pt-br diretamente, criando backup.';

    public function handle(): int
    {
        $base = $this->option('path') ?: resource_path('views');
        if (!is_dir($base)) {
            $this->error("Diretório não encontrado: {$base}");
            return self::FAILURE;
        }

        $ptPath = resource_path('lang/pt_br.json');
        $enPath = resource_path('lang/en.json');
        $pt = file_exists($ptPath) ? (json_decode(file_get_contents($ptPath), true) ?: []) : [];
        $en = file_exists($enPath) ? (json_decode(file_get_contents($enPath), true) ?: []) : [];

        // Dicionário auxiliar para preencher lacunas
        $dict = [
            'to' => 'a',
            'Dashboard' => 'Dashboard',
            'Username' => 'Usuário',
            'Password' => 'Senha',
            'Forgot Password?' => 'Esqueceu a senha?',
            'LOGIN' => 'ENTRAR',
            'Verify Code' => 'Verificar Código',
            'Verification Code' => 'Código de Verificação',
            'Submit' => 'Enviar',
            'Back to Login' => 'Voltar para o Login',
            'Recover Account' => 'Recuperar Conta',
            'Email' => 'E-mail',
            'New Password' => 'Nova Senha',
            'Re-type New Password' => 'Confirmar Nova Senha',
            'Search' => 'Pesquisar',
            'Delete' => 'Excluir',
            'Update' => 'Atualizar',
            'Add New' => 'Adicionar Novo',
            'Cancel' => 'Cancelar',
            'Save Changes' => 'Salvar Alterações',
            'Home' => 'Início',
            'Login' => 'Entrar',
            'Register' => 'Registrar',
        ];

        $backupRoot = storage_path('lang-inline-backup/' . date('Ymd_His'));
        if (!$this->option('dry-run')) {
            @mkdir($backupRoot, 0777, true);
        }

        $files = $this->collectBladeFiles($base);
        $changed = 0; $scanned = 0; $replacements = 0;

    foreach ($files as $file) {
            $scanned++;
            $original = file_get_contents($file);
            $content = $original;

            // __('...') => substitui por 'Texto'
            $content = preg_replace_callback('/__\(\s*([\'\"])((?:\\\\.|(?!\1).)*)\1\s*\)/u', function ($m) use ($pt, $en, $dict, &$replacements) {
                $key = stripslashes($m[2]);
                if ($key === '' || preg_match('/\:|\{|\}|%s|%d/', $key)) {
                    return $m[0];
                }
                $translation = $pt[$key] ?? $dict[$key] ?? ($dict[$en[$key] ?? ''] ?? null);
                if ($translation === null || $translation === '') return $m[0];
                $replacements++;
                return "'" . str_replace("'", "\\'", $translation) . "'";
            }, $content);

            // @lang('...') => substitui por Texto
            $content = preg_replace_callback('/@lang\(\s*([\'\"])((?:\\\\.|(?!\1).)*)\1\s*\)/u', function ($m) use ($pt, $en, $dict, &$replacements) {
                $key = stripslashes($m[2]);
                if ($key === '' || preg_match('/\:|\{|\}|%s|%d/', $key)) {
                    return $m[0];
                }
                $translation = $pt[$key] ?? $dict[$key] ?? ($dict[$en[$key] ?? ''] ?? null);
                if ($translation === null || $translation === '') return $m[0];
                $replacements++;
                return $translation;
            }, $content);

            // trans('...') => substitui por 'Texto'
            $content = preg_replace_callback('/trans\(\s*([\'\"])((?:\\\\.|(?!\1).)*)\1\s*\)/u', function ($m) use ($pt, $en, $dict, &$replacements) {
                $key = stripslashes($m[2]);
                if ($key === '' || preg_match('/\:|\{|\}|%s|%d/', $key)) {
                    return $m[0];
                }
                $translation = $pt[$key] ?? $dict[$key] ?? ($dict[$en[$key] ?? ''] ?? null);
                if ($translation === null || $translation === '') return $m[0];
                $replacements++;
                return "'" . str_replace("'", "\\'", $translation) . "'";
            }, $content);

            // 2ª Passagem: Remover wrappers para argumentos não literais
            // __($expr) ou trans($expr) => $expr (mantém {{ }} já existentes)
            $stripCount = 0;
            $newContent = $this->stripNonLiteralFunctionCalls($content, '__', $stripCount);
            $content = $this->stripNonLiteralFunctionCalls($newContent, 'trans', $stripCount);
            $replacements += $stripCount;

            // @lang($expr) => {{ $expr }} (diretiva Blade)
            $langStrip = 0;
            $content = $this->stripNonLiteralLangDirective($content, $langStrip);
            $replacements += $langStrip;

            if ($content !== $original) {
                $changed++;
                if ($this->option('dry-run')) {
                    $this->line("Mudaria: {$file}");
                } else {
                    // Backup relativo à base escaneada
                    $rel = ltrim(str_replace($base . DIRECTORY_SEPARATOR, '', $file), DIRECTORY_SEPARATOR);
                    $dest = $backupRoot . DIRECTORY_SEPARATOR . $rel;
                    @mkdir(dirname($dest), 0777, true);
                    file_put_contents($dest, $original);
                    file_put_contents($file, $content);
                    $this->line("Atualizado: {$file}");
                }
            }
        }

        $this->info("Arquivos escaneados: {$scanned} | Arquivos alterados: {$changed} | Substituições: {$replacements}");
        if (!$this->option('dry-run')) {
            $this->info("Backup salvo em: {$backupRoot}");
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string>
     */
    private function collectBladeFiles(string $base): array
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
        $files = [];
        /** @var SplFileInfo $f */
        foreach ($rii as $f) {
            if (!$f->isFile()) continue;
            $path = $f->getPathname();
            if (str_ends_with($path, '.blade.php')) {
                $files[] = $path;
            }
        }
        sort($files);
        return $files;
    }

    /**
     * Remove chamadas a funções de tradução para argumentos não literais, preservando o conteúdo.
     * Ex.: {{ __($var) }} => {{ $var }} | keyToTitle(...) permanece intacto dentro do argumento.
     */
    private function stripNonLiteralFunctionCalls(string $content, string $fn, int &$count): string
    {
        $countLocal = 0;
        $i = 0;
        $len = strlen($content);
        while ($i < $len) {
            $pos = $this->strposFunctionCall($content, $fn, $i);
            if ($pos === -1) break;
            // encontra parêntese de abertura após possíveis espaços
            $open = $this->findNextChar($content, '(', $pos + strlen($fn));
            if ($open === -1) break;
            $close = $this->findMatchingParen($content, $open);
            if ($close === -1) { $i = $open + 1; continue; }
            $arg = substr($content, $open + 1, $close - $open - 1);
            $argTrim = ltrim($arg);
            if ($argTrim === '' || $argTrim[0] === '\'' || $argTrim[0] === '"') {
                // literal ou vazio: pula
                $i = $close + 1; continue;
            }
            // substitui a chamada inteira pelo argumento original
            $content = substr($content, 0, $pos) . $arg . substr($content, $close + 1);
            $delta = ($close + 1) - $pos - strlen($arg);
            $len -= $delta;
            $i = $pos + strlen($arg);
            $countLocal++;
        }
        $count += $countLocal;
        return $content;
    }

    /**
     * Converte diretiva @lang($expr) em {{ $expr }} quando não literal; mantém literais para 1ª passagem.
     */
    private function stripNonLiteralLangDirective(string $content, int &$count): string
    {
        $countLocal = 0;
        $i = 0;
        $len = strlen($content);
        while ($i < $len) {
            $pos = $this->strposDirective($content, '@lang', $i);
            if ($pos === -1) break;
            $open = $this->findNextChar($content, '(', $pos + 5);
            if ($open === -1) break;
            $close = $this->findMatchingParen($content, $open);
            if ($close === -1) { $i = $open + 1; continue; }
            $arg = substr($content, $open + 1, $close - $open - 1);
            $argTrim = ltrim($arg);
            if ($argTrim === '' || $argTrim[0] === '\'' || $argTrim[0] === '"') {
                $i = $close + 1; continue;
            }
            // Substitui @lang($expr) por {{ $expr }}
            $replacement = '{{ ' . $argTrim . ' }}';
            $content = substr($content, 0, $pos) . $replacement . substr($content, $close + 1);
            $delta = ($close + 1) - $pos - strlen($replacement);
            $len -= $delta;
            $i = $pos + strlen($replacement);
            $countLocal++;
        }
        $count += $countLocal;
        return $content;
    }

    private function strposFunctionCall(string $s, string $fn, int $offset): int
    {
        while (true) {
            $p = strpos($s, $fn, $offset);
            if ($p === false) return -1;
            // garantir fronteira de identificador à esquerda e '(' adiante
            $prev = $p > 0 ? $s[$p - 1] : '';
            if (preg_match('/[A-Za-z0-9_]/', $prev)) { $offset = $p + 1; continue; }
            // pula espaços até '('
            $j = $p + strlen($fn);
            while ($j < strlen($s) && ctype_space($s[$j])) $j++;
            if ($j < strlen($s) && $s[$j] === '(') return $p;
            $offset = $p + 1;
        }
    }

    private function strposDirective(string $s, string $name, int $offset): int
    {
        while (true) {
            $p = strpos($s, $name, $offset);
            if ($p === false) return -1;
            // deve iniciar com '@'
            if ($p === 0 || $s[$p - 1] !== '@') { $offset = $p + 1; continue; }
            return $p - 1; // inclui '@'
        }
    }

    private function findNextChar(string $s, string $char, int $offset): int
    {
        $p = strpos($s, $char, $offset);
        return $p === false ? -1 : $p;
    }

    private function findMatchingParen(string $s, int $openPos): int
    {
        $len = strlen($s);
        $depth = 0;
        $inStr = false; $strDelim = '';
        for ($i = $openPos; $i < $len; $i++) {
            $ch = $s[$i];
            if ($inStr) {
                if ($ch === '\\') { $i++; continue; }
                if ($ch === $strDelim) { $inStr = false; $strDelim = ''; }
                continue;
            }
            if ($ch === '\'' || $ch === '"') { $inStr = true; $strDelim = $ch; continue; }
            if ($ch === '(') { $depth++; continue; }
            if ($ch === ')') {
                $depth--;
                if ($depth === 0) return $i;
                continue;
            }
        }
        return -1;
    }
}
