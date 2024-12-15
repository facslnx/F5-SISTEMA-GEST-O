<?php
class GitSync {
    private $config;
    private $lastHash;
    private $rootPath;
    private $logFile;

    public function __construct() {
        $this->rootPath = dirname(__DIR__);
        $this->logFile = $this->rootPath . '/logs/git_sync.log';
        
        // Carregar configurações
        if (file_exists($this->rootPath . '/config/github.php')) {
            $this->config = require $this->rootPath . '/config/github.php';
        } else {
            $this->log("Erro: Arquivo de configuração github.php não encontrado");
            die("Erro: Configure o arquivo github.php antes de continuar");
        }

        // Criar diretório de logs se não existir
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
    }

    private function log($message) {
        $dateTime = date('Y-m-d H:i:s');
        $logMessage = "[$dateTime] $message\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        echo $logMessage;
    }

    private function getLastCommitHash() {
        $output = [];
        exec('git rev-parse HEAD 2>&1', $output);
        return isset($output[0]) ? trim($output[0]) : '';
    }

    private function hasChanges() {
        $output = [];
        exec('git status --porcelain', $output);
        return !empty($output);
    }

    private function configureGit() {
        // Configurar credenciais
        $repoUrl = str_replace(
            'https://',
            'https://' . urlencode($this->config['username']) . ':' . urlencode($this->config['token']) . '@',
            $this->config['repository']
        );
        
        exec('git config user.name "' . $this->config['author_name'] . '"');
        exec('git config user.email "' . $this->config['author_email'] . '"');
        exec('git remote remove origin 2>&1');
        exec('git remote add origin ' . $repoUrl . ' 2>&1');
    }

    public function sync() {
        try {
            chdir($this->rootPath);
            
            if (!$this->hasChanges()) {
                $this->log("Nenhuma alteração detectada");
                return false;
            }

            $this->configureGit();

            // Adicionar alterações
            exec('git add . 2>&1', $output);
            $this->log("Arquivos preparados para commit");

            // Criar commit
            $commitMessage = $this->config['commit_message'] . date('Y-m-d H:i:s');
            exec('git commit -m "' . $commitMessage . '" 2>&1', $output);
            $this->log("Commit criado: $commitMessage");

            // Push para o repositório
            exec('git push -u origin ' . $this->config['branch'] . ' 2>&1', $output);
            $this->log("Push realizado com sucesso");

            return true;
        } catch (Exception $e) {
            $this->log("Erro durante a sincronização: " . $e->getMessage());
            return false;
        }
    }

    public function watchAndSync($interval = 300) {
        $this->log("Iniciando monitoramento de alterações...");
        
        while (true) {
            if ($this->sync()) {
                $this->log("Sincronização realizada com sucesso");
            }
            
            sleep($interval);
        }
    }
}

// Uso do script
if (php_sapi_name() === 'cli') {
    $gitSync = new GitSync();
    
    // Intervalo de 5 minutos (300 segundos)
    $gitSync->watchAndSync(300);
}
